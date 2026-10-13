<?php
namespace App\Services;
use App\Models\{Personnel,Firearm,NotificationLog};
use Illuminate\Support\Facades\{Mail,Cache};
use Illuminate\Support\Carbon;
final class RenewalReminderService {
 public function run(bool $dryRun=false): array {
  $counts=['due'=>0,'sent'=>0,'failed'=>0,'skipped'=>0];
  $lock=Cache::lock('apao-renewal-reminders',3600);
  if (!$lock->get()) throw new \RuntimeException('Another reminder check is running.');
  try {
   foreach ([30,15,7,0] as $days) {
    $date=Carbon::today('Asia/Manila')->addDays($days)->toDateString();
    Personnel::whereNull('archived_at')->whereDate('date_of_validity',$date)->chunkById(100,function($people)use($days,$date,$dryRun,&$counts){
     foreach($people as $p) $this->send($p,null,$date,$days,$dryRun,$counts);
    });
    Firearm::whereDate('expiration_date',$date)->whereHas('personnel',fn($q)=>$q->whereNull('archived_at'))->with('personnel')->chunkById(100,function($records)use($days,$date,$dryRun,&$counts){
     foreach($records as $f) $this->send($f->personnel,$f,$date,$days,$dryRun,$counts);
    });
   }
  } finally {$lock->release();}
  return $counts;
 }
 private function send(Personnel $p,?Firearm $f,string $date,int $days,bool $dryRun,array &$counts): void {
  $counts['due']++;
  $key=['record_key'=>($f?'firearm:'.$f->id:'personnel:'.$p->id),'expiration_date'=>$date,'days_before'=>$days];
  $existing=NotificationLog::where($key)->first();
  if ($existing && in_array($existing->status,['Sent','Sending'],true)) {$counts['skipped']++;return;}
  if($dryRun)return;
  $name=trim(implode(' ',array_filter([$p->rank,$p->first_name,$p->middle_name,$p->last_name])));
  $subject=$days===0?'Action Required: Firearm Authorization Expires Today':'Firearm Renewal Reminder: '.$days.' Days Remaining';
  $body='Personnel: '.$name."\nFirearm: ".($f?->nomenclature??$p->pistol_nomenclature).
   "\nSerial number: ".($f?->serial_number??$p->pistol_serial_number).
   "\nATP serial number: ".($f?->atp_serial_number??$p->atp_serial_number??'Not recorded').
   "\nExpiration date: ".$date."\nRequired action: Contact the 10th APAO and submit the required renewal documents before expiration.";
  $log=NotificationLog::firstOrCreate($key,['personnel_id'=>$p->id,'firearm_id'=>$f?->id,
   'recipient'=>$p->email??'','subject'=>$subject,'message'=>$body,'status'=>'Pending','error'=>null]);
  if (!NotificationLog::whereKey($log->id)->whereIn('status',['Pending','Failed'])->update(['status'=>'Sending','recipient'=>$p->email??'','subject'=>$subject,'message'=>$body,'error'=>null])) { $counts['skipped']++; return; }
  $log->refresh();
  try {
   if(!filter_var($p->email,FILTER_VALIDATE_EMAIL))throw new \RuntimeException('Personnel has no valid email address.');
   if(config('mail.default')==='smtp') {
    Mail::to($p->email,$name)->send(new \App\Mail\PersonnelNotice($subject,$body));
   } else {
    BrevoService::brevo_send_transactional_email($p->email,$name,$subject,nl2br(htmlspecialchars($body,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8')));
   }
   $log->update(['status'=>'Sent','date_sent'=>now()]);$counts['sent']++;
  } catch(\Throwable $e){
   $log->update(['status'=>'Failed','error'=>'Email delivery failed. Verify the recipient and email provider configuration.']);
   report($e);$counts['failed']++;
  }
 }
}
