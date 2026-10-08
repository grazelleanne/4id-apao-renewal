<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LaravelMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'apao_laravel_test') {
            throw new \RuntimeException('Integration tests require the isolated test database.');
        }
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel()) DB::rollBack();
        parent::tearDown();
    }

    private function signIn(string $role = 'admin', bool $temporary = false): void
    {
        $user = User::create(['name'=>'Migration Test','email'=>uniqid().'@example.test',
            'password'=>'TestPassword!123','role'=>$role,'is_active'=>true,
            'session_version'=>1,'must_change_password'=>$temporary]);
        $this->actingAs($user)->withSession(['apao'=>['user'=>$user->only(['id','name','email','role','session_version','must_change_password']),
            '_last_activity'=>time()]]);
    }

    public function test_login_and_guest_access(): void
    {
        $this->get('/login')->assertOk()->assertSee('csrf',false);
        $this->getJson('/admin/personnel-data')->assertUnauthorized();
    }

    public function test_login_creates_native_session_and_logout_revokes_access(): void
    {
        $email=uniqid().'@example.test';
        User::create(['name'=>'Login Test','email'=>$email,'password'=>'LoginPassword!123',
            'role'=>'staff','is_active'=>true,'session_version'=>1]);
        $this->withSession(['apao'=>['captcha'=>9]])->postJson('/login',
            ['email'=>$email,'password'=>'LoginPassword!123','captcha'=>'9'])
            ->assertOk()->assertJsonPath('redirect','/staff/dashboard');
        $this->get('/staff/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect('/login');
        $this->getJson('/staff/dashboard-data')->assertUnauthorized();
    }

    public function test_all_admin_views_render_with_native_blade(): void
    {
        $this->signIn();
        foreach (['dashboard','personnel','inspection','reports','archive','users','audit'] as $module) {
            $this->get('/admin/'.$module)->assertOk();
        }
    }

    public function test_staff_view_and_role_access(): void
    {
        $this->signIn('staff');
        $this->get('/staff/dashboard')->assertOk();
        $this->getJson('/admin/users-data')->assertForbidden();
    }

    public function test_temporary_password_blocks_dashboard_until_changed(): void
    {
        $this->signIn('staff',true);
        $this->get('/staff/dashboard')->assertRedirect('/staff/first-password');
        $this->get('/staff/first-password')->assertOk();
    }

    public function test_unsubmitted_personnel_are_excluded_and_notified_personnel_leave_renewal_list(): void
    {
        $this->signIn();
        $personnel=DB::table('personnel')->insertGetId(['item_number'=>900001,
            'first_name'=>'Workflow','last_name'=>'Test','approved_status'=>'new',
            'ics_status'=>'inspection','created_at'=>now(),'updated_at'=>now()]);
        $inspection=DB::table('inspections')->insertGetId(['personnel_id'=>$personnel,
            'item_number'=>900001,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
        $this->getJson('/admin/inspection-data')->assertOk()->assertJsonPath('pending',0);
        $this->postJson('/admin/inspection/notify-staff',['itemNumber'=>900001,'message'=>'Ready'])
            ->assertStatus(409);
        DB::table('personnel')->where('id',$personnel)->update(['ics_status'=>'ready','approved_status'=>'renewed']);
        DB::table('inspections')->where('id',$inspection)->update(['status'=>'approved']);
        $this->getJson('/admin/inspection-data')->assertOk()->assertJsonPath('approved',1);
        $this->postJson('/admin/inspection/notify-staff',['itemNumber'=>900001,'message'=>'Please process renewal.'])
            ->assertOk()->assertJsonPath('success',true);
        $this->getJson('/admin/inspection-data')->assertOk()->assertJsonPath('approved',0)->assertJsonPath('data',[]);
        $this->assertDatabaseHas('notifications',['personnel_id'=>$personnel,'type'=>'renewal_ready','read_by_staff'=>0]);
    }

    public function test_par_writes_are_persistent_and_replacement_preserves_history(): void
    {
        $this->signIn('staff');
        DB::table('personnel')->insert(['item_number'=>900002,'first_name'=>'PAR','last_name'=>'Test',
            'ics_status'=>'ready','pistol_nomenclature'=>'Glock 17','qty_ammo'=>30,'created_at'=>now(),'updated_at'=>now()]);
        $data=['mode'=>'issue','parNumber'=>'PAR-TEST-900002','dateIssued'=>now()->format('Y-m-d'),
            'issuedBy'=>'Issuer','approvedBy'=>'Approver'];
        $this->postJson('/staff/par/900002/save',$data)->assertOk()->assertJsonPath('success',true);
        $this->getJson('/staff/par-data')->assertOk()->assertJsonPath('state.900002.parNumber','PAR-TEST-900002');
        $this->postJson('/staff/par/900002/save',$data)->assertStatus(409);
        $data['mode']='replace'; $data['parNumber']='PAR-TEST-900002-R';
        $this->postJson('/staff/par/900002/save',$data)->assertOk();
        $this->assertDatabaseHas('property_acknowledgement_receipts',['par_number'=>'PAR-TEST-900002','status'=>'Replaced']);
        $this->getJson('/staff/par-data')->assertJsonPath('state.900002.wasReplaced',true);
    }

    public function test_password_recovery_verifies_code_and_prevents_replay(): void
    {
        config(['apao.environment.BREVO_API_KEY'=>'test-key','apao.environment.BREVO_SENDER_EMAIL'=>'sender@example.test']);
        Http::fake(['api.brevo.com/*'=>Http::response(['messageId'=>'test'],201)]);
        $email=uniqid().'@example.test';
        $user=User::create(['name'=>'Recovery Test','email'=>$email,'password'=>'OriginalPassword!123',
            'role'=>'staff','is_active'=>true,'session_version'=>1]);
        $this->postJson('/forgot-password/send-otp',['email'=>$email])->assertOk();
        $sent=Http::recorded()->first()[0];
        preg_match('/<strong>([0-9]{6})<\/strong>/',$sent['htmlContent'],$match);
        $this->postJson('/forgot-password/verify-otp',['email'=>$email,'code'=>$match[1]])->assertOk();
        $input=['email'=>$email,'password'=>'ChangedPassword!123','password_confirmation'=>'ChangedPassword!123'];
        $this->postJson('/forgot-password/reset',$input)->assertOk();
        $this->assertTrue(Hash::check($input['password'],$user->fresh()->password));
        $this->assertSame(2,$user->fresh()->session_version);
        $this->postJson('/forgot-password/reset',$input)->assertStatus(422);
    }

    public function test_staff_submission_and_approval_use_birthday_two_years_ahead(): void
    {
        $this->signIn('staff');
        $personnel=DB::table('personnel')->insertGetId(['item_number'=>900003,'first_name'=>'Inspection',
            'last_name'=>'Test','date_of_birth'=>'1992-01-23','approved_status'=>'new',
            'ics_status'=>'inspection','created_at'=>now(),'updated_at'=>now()]);
        $this->postJson('/staff/ics/900003/send-inspection')->assertOk()->assertJsonPath('alreadySent',false);
        $this->postJson('/staff/ics/900003/send-inspection')->assertOk()->assertJsonPath('alreadySent',true);
        $this->assertDatabaseHas('personnel',['id'=>$personnel,'approved_status'=>'pending','ics_status'=>'under']);
        $this->assertSame(1,DB::table('notifications')->where('personnel_id',$personnel)->where('type','inspection_submitted')->count());
        $this->signIn('admin');
        $this->postJson('/admin/inspection/save',['itemNumber'=>900003,'status'=>'approved'])->assertOk();
        $expected=(now()->year+2).'-01-23';
        $this->assertDatabaseHas('inspections',['personnel_id'=>$personnel,'status'=>'approved','next_renewal_date'=>$expected]);
        $this->get('/admin/inspection/900003/print')->assertOk()->assertSee('NEXT RENEWAL DATE')
            ->assertSee('Test, Inspection')->assertSee('23 January '.(now()->year+2));
    }

    public function test_account_creation_sends_otp_to_new_user_and_requires_new_password(): void
    {
        $this->signIn();
        config(['apao.environment.BREVO_API_KEY'=>'test-key','apao.environment.BREVO_SENDER_EMAIL'=>'sender@example.test']);
        Http::fake(['api.brevo.com/*'=>Http::response(['messageId'=>'test'],201)]);
        $email=uniqid().'@example.test';
        $input=['username'=>$email,'fullName'=>'New Test User','role'=>'staff','status'=>'Active',
            'password'=>'TemporaryPassword!123','adminPassword'=>'TestPassword!123'];
        $this->postJson('/admin/users',$input)->assertOk()->assertJsonPath('otpRequired',true);
        $sent=Http::recorded()->first()[0];
        $this->assertSame($email,$sent['to'][0]['email']);
        preg_match('/<strong>([0-9]{6})<\/strong>/',$sent['htmlContent'],$match);
        $input['otp_code']=$match[1];
        $this->postJson('/admin/users',$input)->assertCreated()->assertJsonPath('success',true);
        $this->assertDatabaseHas('users',['email'=>$email,'role'=>'staff','must_change_password'=>1]);
    }
}
