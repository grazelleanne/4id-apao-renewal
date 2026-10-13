<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('apao:create-admin {email} {name}', function () {
    $email=strtolower(trim($this->argument('email')));
    $name=trim($this->argument('name'));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL) || !$name || strlen($name)>255) {
        $this->error('Provide a valid email and name.'); return 1;
    }
    if (User::where('email',$email)->exists()) {
        $this->error('That account already exists. Use Manage Users to update it.'); return 1;
    }
    $password=$this->secret('New administrator password');
    if (!is_string($password) || !password_is_strong($password) || $password!==$this->secret('Confirm password')) {
        $this->error('Passwords must match and contain 8+ characters, uppercase, lowercase, a number and a symbol.'); return 1;
    }
    User::create(['email'=>$email,'name'=>$name,'password'=>$password,'role'=>'admin',
        'is_active'=>true,'session_version'=>1,'must_change_password'=>true]);
    $this->info('Administrator created. A new password is required on first sign-in.');
    return 0;
})->purpose('Create an administrator without overwriting existing accounts');

Artisan::command('apao:renewal-reminders {--dry-run}', function (\App\Services\RenewalReminderService $service) {
 $counts=$service->run((bool)$this->option('dry-run'));
 $this->info(json_encode($counts));return $counts['failed']?1:0;
})->purpose('Send 30, 15, 7 and expiration-day renewal reminders');
\Illuminate\Support\Facades\Schedule::command('apao:renewal-reminders')->dailyAt('08:00')->timezone('Asia/Manila')->withoutOverlapping();

Artisan::command('apao:prune-audit-logs {--dry-run}', function () {
    $cutoff=now()->subDays(90);
    $query=\Illuminate\Support\Facades\DB::table('audit_logs')->where('created_at','<',$cutoff);
    if ($this->option('dry-run')) {
        $this->info($query->count().' audit logs older than 90 days would be deleted.');
        return 0;
    }
    $deleted=0;
    do {
        $ids=(clone $query)->orderBy('id')->limit(1000)->pluck('id');
        if ($ids->isEmpty()) break;
        $deleted+=\Illuminate\Support\Facades\DB::table('audit_logs')->whereIn('id',$ids)->where('created_at','<',$cutoff)->delete();
    } while ($ids->count()===1000);
    $this->info($deleted.' audit logs older than 90 days deleted.');
    return 0;
})->purpose('Keep the latest 90 days of audit logs');
\Illuminate\Support\Facades\Schedule::command('apao:prune-audit-logs')->dailyAt('03:00')->timezone('Asia/Manila')->withoutOverlapping();

Artisan::command('apao:randomize-dummy-afp {--dry-run}', function () {
    $db=\Illuminate\Support\Facades\DB::class;
    $query=$db::table('personnel')->where('pistol_serial_number','like','AFP-DUMMY-%');
    if ($this->option('dry-run')) { $this->info($query->count().' dummy personnel AFP numbers would be replaced.'); return 0; }
    $changed=$db::transaction(function () use ($db,$query) {
        $records=(clone $query)->orderBy('id')->lockForUpdate()->get(['id','item_number','afp_serial_number']);
        $used=array_fill_keys($db::table('personnel')->whereNotNull('afp_serial_number')->pluck('afp_serial_number')->map(fn($value)=>strtoupper($value))->all(),true);
        foreach($records as $record){
            do {$number='AFP'.random_int(100000,999999);}while(isset($used[$number]));
            $used[$number]=true;
            $db::table('personnel')->where('id',$record->id)->update(['afp_serial_number'=>$number,'updated_at'=>now()]);
            $db::table('inspections')->where('personnel_id',$record->id)->update(['afp_serial_number'=>$number,'updated_at'=>now()]);
            \App\Services\RecordAudit::record(['name'=>'System maintenance','role'=>'system'],'personnel_afp_updated','personnel','Dummy personnel record #'.$record->id,['afp_serial_number'=>$record->afp_serial_number],['afp_serial_number'=>$number]);
        }
        return $records->count();
    });
    $this->info($changed.' dummy personnel now have unique AFP + 6-digit numbers.');return 0;
})->purpose('Assign random unique AFP numbers only to imported AFP-DUMMY pistol records');

Artisan::command('apao:use-afp-pistol-serial {--dry-run}', function () {
    $db=\Illuminate\Support\Facades\DB::class;
    $records=$db::table('personnel')->orderBy('id')->get(['id','afp_serial_number','pistol_serial_number']);
    $numbers=$records->pluck('afp_serial_number')->all();
    if (count(array_unique($numbers))!==count($numbers) || in_array(null,$numbers,true) || in_array('',$numbers,true)) {
        $this->error('Every personnel needs a unique nonempty AFP number.');return 1;
    }
    if ($db::table('firearms')->whereIn('serial_number',$numbers)->exists()) {
        $this->error('An AFP number conflicts with an additional firearm serial.');return 1;
    }
    $changed=$records->filter(fn($r)=>$r->pistol_serial_number!==$r->afp_serial_number);
    if ($this->option('dry-run')) {$this->info($changed->count().' personnel pistol serials would be replaced by AFP numbers.');return 0;}
    $db::transaction(function () use ($db,$changed) {
        $db::table('personnel')->whereIn('id',$changed->pluck('id'))->update(['pistol_serial_number'=>null]);
        foreach($changed as $r) {
            $db::table('personnel')->where('id',$r->id)->update(['pistol_serial_number'=>$r->afp_serial_number,'updated_at'=>now()]);
            \App\Services\RecordAudit::record(['name'=>'System maintenance','role'=>'system'],'personnel_pistol_serial_updated','personnel','Personnel record #'.$r->id,['pistol_serial_number'=>$r->pistol_serial_number],['pistol_serial_number'=>$r->afp_serial_number]);
        }
    });
    $this->info($changed->count().' personnel pistol serials now use AFP numbers.');return 0;
})->purpose('Use each personnel AFP number as their original pistol serial');
