<?php
namespace App\Services;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
final class PersonnelEmailService {
 public static function send(array $personnel,array $user,string $name,string $subject,string $body,string $html): void {
  $log=NotificationLog::create(['user_id'=>$user['id']??null,'personnel_id'=>$personnel['id'],
   'record_key'=>'manual:'.Str::uuid(),'recipient'=>$personnel['email'],'subject'=>$subject,'message'=>$body,
   'expiration_date'=>($personnel['date_of_validity']??null)?:null,'days_before'=>null,'status'=>'Sending']);
  try {
   if(config('mail.default')==='smtp')Mail::to($personnel['email'],$name)->send(new \App\Mail\PersonnelNotice($subject,$html,true));
   else BrevoService::brevo_send_transactional_email($personnel['email'],$name,$subject,$html);
   $log->update(['status'=>'Sent','date_sent'=>now()]);
  }catch(\Throwable $e){$log->update(['status'=>'Failed','error'=>'Email delivery failed. Verify mail configuration and recipient.']);throw $e;}
 }
}
