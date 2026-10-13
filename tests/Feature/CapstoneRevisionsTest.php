<?php
namespace Tests\Feature;
use App\Models\{User,Personnel,Firearm,NotificationLog};
use App\Services\{RecordValidation,RenewalReminderService,ImportReader};
use Illuminate\Support\Facades\{DB,Mail};
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
final class CapstoneRevisionsTest extends TestCase {
 protected function setUp(): void {parent::setUp();config(['apao.environment.BREVO_API_KEY'=>'','apao.environment.BREVO_SENDER_EMAIL'=>'']);\Illuminate\Support\Facades\Http::preventStrayRequests();if (DB::getDriverName()==='sqlite' && DB::connection()->getDatabaseName()===':memory:') { $this->artisan('migrate',['--force'=>true])->assertSuccessful(); } elseif(DB::connection()->getDatabaseName()!=='apao_laravel_test') throw new \RuntimeException('Use isolated test database.'); DB::beginTransaction();}
 protected function tearDown(): void {while(DB::transactionLevel())DB::rollBack();parent::tearDown();}
 public function test_manual_notification_routes_are_removed(): void {
  $this->signIn('staff');
  $this->postJson('/staff/personnel/1/notify',['message'=>'Manual'])->assertNotFound();
  $this->signIn('admin');
  $this->postJson('/admin/inspection/notify-staff',['itemNumber'=>1,'message'=>'Manual'])->assertNotFound();
 }
 public function test_automated_reminders_use_brevo_api(): void {
  config(['mail.default'=>'log','apao.environment.BREVO_API_KEY'=>'test-key','apao.environment.BREVO_SENDER_EMAIL'=>'sender@example.com']);
  \Illuminate\Support\Facades\Http::fake(['api.brevo.com/*'=>\Illuminate\Support\Facades\Http::response(['messageId'=>'test-id'],201)]);
  $p=$this->person(['date_of_validity'=>now('Asia/Manila')->addDays(7)->toDateString()]);
  $counts=app(RenewalReminderService::class)->run();
  $this->assertSame(1,$counts['sent']);
  $this->assertDatabaseHas('notification_logs',['personnel_id'=>$p->id,'days_before'=>7,'status'=>'Sent']);
  \Illuminate\Support\Facades\Http::assertSent(fn($request)=>$request->url()==='https://api.brevo.com/v3/smtp/email'&&$request['to'][0]['email']===$p->email);
 }
 private function signIn(string $role='admin'): void {
  $u=User::create(['name'=>'Capstone Test','email'=>uniqid().'@example.com','password'=>'TestPassword!123','role'=>$role,'is_active'=>true,'session_version'=>1]);
  $this->actingAs($u)->withSession(['apao'=>['user'=>$u->only(['id','name','email','role','session_version','must_change_password']),'_last_activity'=>time()]]);
 }
 private function person(array $extra=[]): Personnel {return Personnel::create($extra+['item_number'=>random_int(910000,990000),'first_name'=>'Renewal','last_name'=>'Test','email'=>uniqid().'@example.com','date_of_birth'=>'1990-01-01','approved_status'=>'new','status'=>'active','ics_status'=>'inspection']);}
 public function test_admin_views_and_staff_restrictions(): void {
  $this->signIn();foreach(['/admin/notification-history','/admin/records/firearms','/admin/records/ammunition','/admin/records/personnel'] as $url)$this->get($url)->assertOk();
  $this->get('/admin/imports')->assertRedirect('/admin/reports#data-import');
  $this->signIn('staff');$this->getJson('/admin/imports')->assertForbidden();$this->postJson('/admin/records/firearms',[])->assertForbidden();
 }
 public function test_firearm_crud_duplicate_atp_and_audit(): void {
  $this->signIn();$p=$this->person();$data=['personnel_id'=>$p->id,'atp_serial_number'=>'ATP-'.uniqid(),'serial_number'=>'GUN-'.uniqid(),'nomenclature'=>'Pistol','type'=>'9mm','expiration_date'=>'2027-10-12'];
  $this->post('/admin/records/firearms',$data)->assertRedirect();$f=Firearm::where('atp_serial_number',$data['atp_serial_number'])->firstOrFail();
  $this->postJson('/admin/records/firearms',$data)->assertUnprocessable()->assertJsonValidationErrors('atp_serial_number')->assertSee('This ATP Serial Number already exists.');
  $this->put('/admin/records/firearms/'.$f->id,['nomenclature'=>'Updated Pistol']+$data)->assertRedirect();
  $this->assertDatabaseHas('audit_logs',['module'=>'firearms','action'=>'updated']);
  $this->delete('/admin/records/firearms/'.$f->id)->assertRedirect();$this->assertDatabaseMissing('firearms',['id'=>$f->id]);
 }
 public function test_ammunition_crud_and_duplicate_lots(): void {
  $this->signIn();$p=$this->person();$data=['personnel_id'=>$p->id,'lot_number'=>'LOT-'.uniqid(),'caliber'=>'9mm','quantity'=>50];
  $this->post('/admin/records/ammunition',$data)->assertRedirect();$id=DB::table('ammunition')->where('lot_number',$data['lot_number'])->value('id');
  $this->postJson('/admin/records/ammunition',$data)->assertUnprocessable();
  $this->put('/admin/records/ammunition/'.$id,['quantity'=>60]+$data)->assertRedirect();
  $this->assertDatabaseHas('ammunition',['id'=>$id,'quantity'=>60]);$this->delete('/admin/records/ammunition/'.$id)->assertRedirect();
 }
 public function test_reminders_cover_milestones_and_skip_repeat_sends(): void {
  Mail::fake();config(['mail.default'=>'smtp']);
  $ids=[];foreach([30,15,7,0] as $days)$ids[]=$this->person(['date_of_validity'=>now('Asia/Manila')->addDays($days)->toDateString()])->id;
  $this->person(['date_of_validity'=>now('Asia/Manila')->addDays(10)->toDateString()]);
  app(RenewalReminderService::class)->run();
  $this->assertSame(4,NotificationLog::whereIn('personnel_id',$ids)->where('status','Sent')->count());
  Mail::assertSent(\App\Mail\PersonnelNotice::class,4);
  $before=NotificationLog::whereIn('personnel_id',$ids)->count();app(RenewalReminderService::class)->run();
  $this->assertSame($before,NotificationLog::whereIn('personnel_id',$ids)->count());Mail::assertSent(\App\Mail\PersonnelNotice::class,4);
 }
 public function test_failed_mail_is_recorded_and_retryable(): void {
  $p=$this->person(['date_of_validity'=>now('Asia/Manila')->toDateString()]);config(['mail.default'=>'log']);
  app(RenewalReminderService::class)->run();$this->assertDatabaseHas('notification_logs',['personnel_id'=>$p->id,'status'=>'Failed']);
  Mail::fake();config(['mail.default'=>'smtp']);app(RenewalReminderService::class)->run();$this->assertDatabaseHas('notification_logs',['personnel_id'=>$p->id,'status'=>'Sent']);
 }
 public function test_csv_import_summary_and_history(): void {
  $this->signIn();$p=$this->person();$csv="personnel_id,lot_number,caliber,quantity\n{$p->id},LOT1,9mm,50\n{$p->id},LOT1,9mm,50\n{$p->id},LOT2,9mm,-1\n";
  $this->post('/admin/imports',['module'=>'ammunition','file'=>UploadedFile::fake()->createWithContent('ammo.csv',$csv)])
   ->assertRedirect()->assertSessionHas('summary.imported_records',1)->assertSessionHas('summary.duplicate_records',1)->assertSessionHas('summary.failed_records',1);
  $this->assertDatabaseHas('data_import_logs',['total_records'=>3,'imported_records'=>1,'failed_records'=>2]);
 }
 public function test_invalid_personnel_email_and_birth_date_are_rejected(): void {
  $this->expectException(ValidationException::class);
  RecordValidation::validate('personnel',['email'=>'john@gmail','date_of_birth'=>'2099-01-01']);
 }
 public function test_xlsx_reader_imports_inline_cells_and_rejects_formulas(): void {
  if(!class_exists(\ZipArchive::class))$this->markTestSkipped('Enable PHP zip for XLSX testing.');
  $this->signIn();$p=$this->person();$file=tempnam(sys_get_temp_dir(),'xlsx');$zip=new \ZipArchive();$zip->open($file,\ZipArchive::OVERWRITE);
  $zip->addFromString('xl/workbook.xml','<workbook xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Import" sheetId="1" r:id="rId1"/></sheets></workbook>');
  $zip->addFromString('xl/_rels/workbook.xml.rels','<Relationships><Relationship Id="rId1" Target="worksheets/sheet1.xml"/></Relationships>');
  $xml='<worksheet><sheetData>';$rows=[['personnel_id','lot_number','caliber','quantity'],[(string)$p->id,'XLSX1','9mm','30']];
  foreach($rows as $i=>$row){$xml.='<row>';foreach($row as $j=>$value)$xml.='<c r="'.chr(65+$j).($i+1).'" t="inlineStr"><is><t>'.$value.'</t></is></c>';$xml.='</row>';}$xml.='</sheetData></worksheet>';
  $zip->addFromString('xl/worksheets/sheet1.xml',$xml);$zip->close();
  try{$this->post('/admin/imports',['module'=>'ammunition','file'=>new UploadedFile($file,'ammo.xlsx',null,null,true)])->assertRedirect()->assertSessionHas('summary.imported_records',1);
   $this->post('/admin/imports',['module'=>'ammunition','file'=>new UploadedFile($file,'ammo.xlsm',null,null,true)])->assertRedirect()->assertSessionHas('summary.duplicate_records',1);
   $zip->open($file);$zip->addFromString('xl/worksheets/sheet1.xml',str_replace('<is><t>30</t></is>','<f>1+1</f><v>2</v>',$xml));$zip->close();
   try{app(ImportReader::class)->read($file,'xlsx');$this->fail('Formula accepted.');}catch(\InvalidArgumentException $e){$this->assertStringContainsString('Formula',$e->getMessage());}
  }finally{if(file_exists($file))unlink($file);}
 }
 public function test_existing_personnel_registration_update_and_archive(): void {
  if(DB::getDriverName()!=='mysql')$this->markTestSkipped('Legacy personnel writers require MySQL.');
  $this->signIn('staff');$suffix=uniqid();$data=['firstName'=>'New','lastName'=>'Personnel','rank'=>'PVT','unit'=>'4ID','dateOfBirth'=>'1990-01-01',
   'email'=>$suffix.'@gmail.com','afpSerialNumber'=>'AFP-'.$suffix,'atpSerialNumber'=>'ATP-'.$suffix,
   'pistolNomenclature'=>'Pistol','pistolType'=>'9mm','pistolSerialNumber'=>'PS-'.$suffix,'issuedBy'=>'10th APAO','qtyAmmo'=>10];
  $response=$this->postJson('/staff/personnel',$data)->assertCreated();$item=$response->json('data.itemNumber');
  $this->assertDatabaseHas('personnel',['item_number'=>$item,'atp_serial_number'=>$data['atpSerialNumber']]);
  $this->assertDatabaseHas('inspections',['item_number'=>$item,'status'=>'pending']);
  $this->postJson('/staff/personnel',$data)->assertUnprocessable()->assertJsonPath('errors.atpSerialNumber.0','This ATP Serial Number already exists.');
  $this->signIn();$this->putJson('/admin/personnel-data/'.$item,['dateOfBirth'=>'2099-01-01'])->assertUnprocessable();
  $this->putJson('/admin/personnel-data/'.$item,['email'=>'john@gmail'])->assertUnprocessable();
  $this->putJson('/admin/personnel-data/'.$item,['firstName'=>'Updated'])->assertOk();
  $this->assertDatabaseHas('audit_logs',['action'=>'personnel_updated','module'=>'personnel']);
  $this->deleteJson('/admin/personnel-data/'.$item)->assertOk();$this->assertDatabaseHas('personnel',['item_number'=>$item,'is_archived'=>1]);
  $this->postJson('/admin/archive/restore',['id'=>$item])->assertOk();$this->assertDatabaseHas('personnel',['item_number'=>$item,'is_archived'=>0]);
 }
 public function test_manual_smtp_email_is_logged_with_the_actor(): void {
  Mail::fake();config(['mail.default'=>'smtp']);$p=$this->person();
  $u=User::create(['name'=>'Sender','email'=>uniqid().'@example.com','password'=>'TestPassword!123','role'=>'staff','is_active'=>true]);
  \App\Services\PersonnelEmailService::send($p->getAttributes(),['id'=>$u->id], 'Recipient','Test Reminder','Renew now.','<p>Renew now.</p>');
  $this->assertDatabaseHas('notification_logs',['personnel_id'=>$p->id,'user_id'=>$u->id,'status'=>'Sent','subject'=>'Test Reminder']);
 }
 public function test_import_requires_dotted_email_domain_and_nonfuture_birth_date(): void {
  $data=['item_number'=>999999,'first_name'=>'Import','last_name'=>'Test','rank'=>'PVT','unit'=>'4ID','issued_by'=>'APAO',
   'email'=>'john@gmail','date_of_birth'=>'2099-01-01','afp_serial_number'=>'AFP-INVALID','atp_serial_number'=>'ATP-INVALID',
   'pistol_serial_number'=>'PS-INVALID','pistol_nomenclature'=>'Pistol','pistol_type'=>'9mm','qty_ammo'=>0];
  try{RecordValidation::validate('personnel',$data);$this->fail('Invalid data accepted.');}
  catch(ValidationException $e){$this->assertArrayHasKey('email',$e->errors());$this->assertArrayHasKey('date_of_birth',$e->errors());}
 }

 public function test_personnel_and_firearm_csv_imports_preserve_relationships(): void {
  $this->signIn();$suffix=uniqid();$values=[random_int(991000,999000),'Imported','Personnel','','PVT','4ID',$suffix.'@example.com','1990-01-01','AFP-'.$suffix,'ATP-'.$suffix,'Pistol','PS-'.$suffix,'9mm','APAO',10,'2027-10-12'];
  $headers=\App\Http\Controllers\RecordManagementController::headers('personnel');
  $csv=implode(',',$headers)."\n".implode(',',$values)."\n";
  $this->post('/admin/imports',['module'=>'personnel','file'=>UploadedFile::fake()->createWithContent('personnel.csv',$csv)])
   ->assertRedirect()->assertSessionHas('summary.imported_records',1);
  $p=Personnel::where('email',$suffix.'@example.com')->firstOrFail();
  $this->assertDatabaseHas('inspections',['personnel_id'=>$p->id,'status'=>'pending']);
  $csv="personnel_id,atp_serial_number,serial_number,nomenclature,type,expiration_date\n{$p->id},ATP2-{$suffix},PS2-{$suffix},Pistol,9mm,2027-10-12\n";
  $this->post('/admin/imports',['module'=>'firearms','file'=>UploadedFile::fake()->createWithContent('firearms.csv',$csv)])
   ->assertRedirect()->assertSessionHas('summary.imported_records',1);
  $this->assertDatabaseHas('firearms',['personnel_id'=>$p->id,'atp_serial_number'=>'ATP2-'.$suffix]);
  $this->assertSame(1,$p->firearms()->count());
 }
 public function test_additional_firearm_reminders_and_archived_exclusion(): void {
  Mail::fake();config(['mail.default'=>'smtp']);$p=$this->person();
  $f=Firearm::create(['personnel_id'=>$p->id,'atp_serial_number'=>'ATP-'.uniqid(),'serial_number'=>'GUN-'.uniqid(),'nomenclature'=>'Additional Pistol','type'=>'9mm','expiration_date'=>now('Asia/Manila')->addDays(7)->toDateString()]);
  $archived=$this->person(['archived_at'=>now()->toDateString(),'is_archived'=>true,'date_of_validity'=>now('Asia/Manila')->toDateString()]);
  $result=app(RenewalReminderService::class)->run();$this->assertSame(1,$result['sent']);
  $this->assertDatabaseHas('notification_logs',['firearm_id'=>$f->id,'days_before'=>7,'status'=>'Sent']);
  $this->assertDatabaseMissing('notification_logs',['personnel_id'=>$archived->id]);
  Mail::assertSent(\App\Mail\PersonnelNotice::class,fn($m)=>str_contains($m->body,$f->serial_number)&&str_contains($m->body,'Additional Pistol'));
 }

 public function test_staff_email_history_shows_only_their_sent_emails(): void {
  $this->signIn('staff');$p=$this->person();$actor=auth()->id();
  $other=User::create(['name'=>'Other Sender','email'=>uniqid().'@example.com','password'=>'TestPassword!123','role'=>'staff','is_active'=>true]);
  foreach([[$actor,'OwnEmailSubject'],[$other->id,'OtherEmailSubject'],[null,'ScheduledEmailSubject']] as [$id,$subject])NotificationLog::create(['user_id'=>$id,'personnel_id'=>$p->id,'record_key'=>uniqid(),'recipient'=>$p->email,'subject'=>$subject,'days_before'=>$id===null?7:null,'message'=>'Test message','status'=>'Sent','date_sent'=>now()]);
  $this->get('/staff/email-history')->assertOk()->assertDontSee('OwnEmailSubject')->assertDontSee('OtherEmailSubject')->assertSee('ScheduledEmailSubject')->assertSee('STAFF WORKSPACE');
  $this->get('/staff/email-history?type=automated')->assertOk()->assertSee('ScheduledEmailSubject')->assertDontSee('OwnEmailSubject');
  $this->get('/staff/email-history?type=manual')->assertOk()->assertDontSee('OwnEmailSubject')->assertSee('ScheduledEmailSubject')->assertDontSee('id="notificationType"',false);
  $this->get('/admin/notification-history')->assertForbidden();
  $this->signIn();$this->get('/admin/notification-history')->assertOk()->assertDontSee('OwnEmailSubject')->assertDontSee('OtherEmailSubject')->assertSee('ScheduledEmailSubject');
 }

}
