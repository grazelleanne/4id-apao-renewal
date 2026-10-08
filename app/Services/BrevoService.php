<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class BrevoService
{
    public static function brevo_send_transactional_email(string $recipientEmail,string $recipientName,string $subject,string $html): string
    {
        $key=trim(env_value('BREVO_API_KEY'));
        $sender=trim(env_value('BREVO_SENDER_EMAIL'));
        if (!$key || preg_match('/[\r\n]/',$key) || !filter_var($sender,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Brevo is not configured.');
        $payload=['sender'=>['name'=>env_value('BREVO_SENDER_NAME','APAO Renewal System'),'email'=>$sender],
            'to'=>[['name'=>$recipientName,'email'=>$recipientEmail]],'subject'=>$subject,'htmlContent'=>$html,'tags'=>['apao-renewal']];
        if (filter_var($reply=env_value('BREVO_REPLY_TO_EMAIL'),FILTER_VALIDATE_EMAIL)) $payload['replyTo']=['email'=>$reply];
        $response=Http::withOptions(['verify'=>config('apao.brevo_ca_bundle') ?: true])
            ->withHeaders(['api-key'=>$key])->acceptJson()->connectTimeout(5)->timeout(20)
            ->post('https://api.brevo.com/v3/smtp/email',$payload);
        if (!$response->successful()) {
            Log::warning('Brevo rejected the email request.',['status'=>$response->status()]);
            throw new RuntimeException('Brevo rejected the email request.');
        }
        return (string) $response->json('messageId','');
    }
}
