<?php
namespace App\Http\Controllers;
use App\Models\{Personnel,Firearm,Ammunition,DataImportLog,NotificationLog};
use App\Services\{RecordValidation,RecordAudit,ImportReader};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
final class RecordManagementController extends Controller {
 private function account(Request $request): array {return $request->attributes->get('apao_user',[]);}
 private function model(string $module): string {return match($module){'personnel'=>Personnel::class,'firearms'=>Firearm::class,'ammunition'=>Ammunition::class,default=>abort(404)};}
 public static function headers(string $module): array {return match($module){
  'personnel'=>['item_number','first_name','last_name','middle_name','rank','unit','email','date_of_birth','afp_serial_number','atp_serial_number','pistol_nomenclature','pistol_serial_number','pistol_type','issued_by','qty_ammo','date_of_validity'],
  'firearms'=>['personnel_id','atp_serial_number','serial_number','nomenclature','type','expiration_date'],
  'ammunition'=>['personnel_id','lot_number','caliber','quantity'],default=>abort(404)};}
 public function index(Request $request,string $module='firearms') {
  $model=$this->model($module);
  return view('admin_records',['module'=>$module,'headers'=>self::headers($module),
   'records'=>$model::orderByDesc('id')->paginate(25),
   'people'=>Personnel::whereNull('archived_at')->select('id','item_number','first_name','last_name')->orderBy('item_number')->get()]);
 }
 public function store(Request $request,string $module) {return $this->save($request,$module,null);}
 public function update(Request $request,string $module,int $id) {return $this->save($request,$module,$id);}
 private function save(Request $request,string $module,?int $id) {
  abort_unless(in_array($module,['firearms','ammunition'],true),404);
  $class=$this->model($module);$data=RecordValidation::validate($module,$request->all(),$id);
  try{DB::transaction(function()use($class,$id,$data,$request,$module){
   $record=$id?$class::lockForUpdate()->findOrFail($id):new $class;
   $old=$record->getAttributes();$record->fill($data)->save();
   RecordAudit::record($this->account($request),$id?'updated':'created',$module,$module.' record #'.$record->id,$old,$record->getAttributes());
  });}catch(\Illuminate\Database\QueryException $e){if((string)$e->getCode()==='23000')throw ValidationException::withMessages(['atp_serial_number'=>'A duplicate identifier or conflicting related record exists.']);throw $e;}
  return back()->with('status','Record saved.');
 }
 public function destroy(Request $request,string $module,int $id) {
  abort_unless(in_array($module,['firearms','ammunition'],true),404);$class=$this->model($module);
  DB::transaction(function()use($class,$id,$request,$module){$record=$class::lockForUpdate()->findOrFail($id);$old=$record->getAttributes();$record->delete();
   RecordAudit::record($this->account($request),'deleted',$module,$module.' record #'.$id,$old);});
  return back()->with('status','Record deleted.');
 }
 public function imports() {return redirect('/admin/reports#data-import');}
 public function template(Request $request,string $module) {
  $headers=self::headers($module);
  RecordAudit::record($this->account($request),'template_exported','imports',$module.' template downloaded');
  return response()->streamDownload(function()use($headers){$out=fopen('php://output','wb');fputcsv($out,$headers,',','"','');fclose($out);},$module.'-template.csv',['Content-Type'=>'text/csv']);
 }
 public function import(Request $request,ImportReader $reader) {
  $input=$request->validate(['module'=>'required|in:personnel,firearms,ammunition','file'=>'required|file|max:5120|extensions:csv,xlsx,xlsm']);
  $file=$request->file('file');$module=$input['module'];
  try{$rows=$reader->read($file->getRealPath(),strtolower($file->getClientOriginalExtension()));}
  catch(\InvalidArgumentException $e){throw ValidationException::withMessages(['file'=>$e->getMessage()]);}
  if($reader->clientWorkbook && $module!=='personnel')throw ValidationException::withMessages(['module'=>'Select Personnel for a combined client pistol workbook.']);
  $legacy=$reader->clientWorkbook;
  $headers=self::headers($module);
  if(!$rows||array_diff(array_diff($headers,['middle_name','date_of_validity','expiration_date']),array_keys($rows[0]['values'])))throw ValidationException::withMessages(['file'=>'Required template columns are missing.']);
  $summary=['total_records'=>count($rows),'imported_records'=>0,'updated_records'=>0,'failed_records'=>0,'duplicate_records'=>0];$errors=[];
  DB::transaction(function()use($rows,$module,$request,$file,$legacy,&$summary,&$errors){
   foreach($rows as $row){
    $updated=false;
    try{DB::transaction(function()use($row,$module,$request,$legacy,&$updated){
     $sourceStatus=strtolower(trim((string)($row['values']['import_status']??'')));
     if($legacy && $module==='personnel' && in_array($sourceStatus,['active','for renewal','expired'],true)){
      $existing=Personnel::where('item_number',$row['values']['item_number']??null)
       ->where('afp_serial_number',trim($row['values']['afp_serial_number']??''))
       ->where('pistol_serial_number',trim($row['values']['pistol_serial_number']??''))->whereNull('archived_at')->lockForUpdate()->first();
      if($existing && in_array($existing->approved_status,['new','imported_active','imported_within','imported_expired'],true)
       && DB::table('audit_logs')->where('action','imported')->where('module','personnel')->where('subject','personnel record #'.$existing->id)->exists()){
       $old=$existing->getAttributes();$existing->update(['approved_status'=>match($sourceStatus){'active'=>'imported_active','for renewal'=>'imported_within','expired'=>'imported_expired'}]);
       RecordAudit::record($this->account($request),'import_status_updated','personnel','personnel record #'.$existing->id,$old,$existing->getAttributes());
       $updated=true;return;
      }
     }
     $values=RecordValidation::validate($module,$row['values'],null,$legacy);
     foreach($values as $key=>$value)if($value==='')$values[$key]=null;
     $importStatus=strtolower(trim((string)($values['import_status']??'')));unset($values['import_status']);
     $storedStatus=match($importStatus){'active'=>'imported_active','for renewal'=>'imported_within','expired'=>'imported_expired',default=>'new'};
     if($module==='personnel' && $storedStatus==='new')$values['date_of_validity']=null;
     if($module==='personnel')$values+=['approved_status'=>$storedStatus,'status'=>'active','ics_status'=>'inspection','is_archived'=>false];
     $class=$this->model($module);$record=$class::create($values);
     if($module==='personnel') DB::table('inspections')->insert(['personnel_id'=>$record->id,'item_number'=>$record->item_number,
      'afp_serial_number'=>$record->afp_serial_number,'pistol_type'=>$record->pistol_type,'status'=>'pending','date_registered'=>now()->toDateString(),'created_at'=>now(),'updated_at'=>now()]);
     RecordAudit::record($this->account($request),'imported',$module,$module.' record #'.$record->id,[],$record->getAttributes());
    });$summary[$updated?'updated_records':'imported_records']++;}
    catch(ValidationException $e){
     $duplicate=false;foreach($e->validator->failed() as $rules)if(isset($rules['Unique']))$duplicate=true;
     $summary[$duplicate?'duplicate_records':'failed_records']++;
     $errors[]=['row'=>$row['line'],'errors'=>$e->errors(),'duplicate'=>$duplicate];
    }catch(\Illuminate\Database\QueryException $e){
     if((string)$e->getCode()!=='23000')throw $e;
     $summary['duplicate_records']++;$errors[]=['row'=>$row['line'],'errors'=>['record'=>['Duplicate identifier or conflicting related record.']],'duplicate'=>true];
    }
   }
   $log=DataImportLog::create(array_diff_key($summary,['updated_records'=>true])+['user_id'=>$this->account($request)['id']??null,'filename'=>basename($file->getClientOriginalName()),
    'module'=>$module,'errors'=>$errors,'date_uploaded'=>now()]);
   // failed_records in history includes both invalid and duplicate rows.
   $log->update(['imported_records'=>$summary['imported_records']+$summary['updated_records'],'failed_records'=>$summary['failed_records']+$summary['duplicate_records']]);
   RecordAudit::record($this->account($request),'data_import','imports',$log->filename,[],$summary);
  });
  return back()->with('summary',$summary)->with('import_errors',$errors);
 }
 public function exportAudit(Request $request) {
  $data=$request->validate(['report'=>'required|in:renewal_pdf,renewal_excel,rpcsp_excel,audit_csv']);
  RecordAudit::record($this->account($request),'report_exported','reports',$data['report'].' export requested');
  return response()->json(['success'=>true]);
 }
 public function notificationHistory(Request $request){
  $type='automated';
  $query=NotificationLog::query()->whereNotNull('days_before');
  if(($this->account($request)['role']??'')==='staff'){
   $actor=$this->account($request)['id'];
   $query->where(fn($q)=>$q->where('user_id',$actor)->orWhere(fn($system)=>$system->whereNull('user_id')->whereNotNull('days_before')));
  }
  if($type==='automated')$query->whereNotNull('days_before');

  $automationReady=config('mail.default')==='smtp'
   ? (bool)(config('mail.mailers.smtp.username') && config('mail.mailers.smtp.password'))
   : (bool)(trim(env_value('BREVO_API_KEY')) && filter_var(env_value('BREVO_SENDER_EMAIL'),FILTER_VALIDATE_EMAIL));
  return view('admin_notification_history',['logs'=>$query->orderByDesc('id')->paginate(25)->withQueryString(),'type'=>$type,'automationReady'=>$automationReady]);
 }
}
