<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
final class RecordAudit {
 public static function record(array $user,string $action,string $module,string $description,array $old=[],array $new=[]): void {
  $redact=static fn(array $values)=>array_diff_key($values,array_flip(['password','remember_token','photo','signature','otp']));
  DB::table('audit_logs')->insert(['user_id'=>$user['id']??null,'user_name'=>$user['name']??'System',
   'user_role'=>$user['role']??'system','action'=>$action,'module'=>$module,'subject'=>$description,
   'description'=>$description,'old_values'=>json_encode($redact($old),JSON_THROW_ON_ERROR),
   'new_values'=>json_encode($redact($new),JSON_THROW_ON_ERROR),'ip_address'=>request()->ip(),'created_at'=>now()]);
 }
}
