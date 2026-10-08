<?php
namespace Tests\Unit;

use App\Services\BrevoService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BrevoTransportTest extends TestCase
{
    public function test_email_transport_uses_configured_sender_and_recipient(): void
    {
        config(['apao.environment.BREVO_API_KEY'=>'test-key',
            'apao.environment.BREVO_SENDER_EMAIL'=>'sender@example.test']);
        Http::fake(['api.brevo.com/*'=>Http::response(['messageId'=>'test-message'],201)]);
        $id=BrevoService::brevo_send_transactional_email('recipient@example.test','Recipient','Reminder','<p>Reminder</p>');
        $this->assertSame('test-message',$id);
        $this->assertFileExists(config('apao.brevo_ca_bundle'));
        Http::assertSent(fn($request)=>$request['to'][0]['email']==='recipient@example.test'
            && $request['sender']['email']==='sender@example.test');
    }

    public function test_brevo_rejection_does_not_report_success(): void
    {
        config(['apao.environment.BREVO_API_KEY'=>'test-key',
            'apao.environment.BREVO_SENDER_EMAIL'=>'sender@example.test']);
        Http::fake(['api.brevo.com/*'=>Http::response(['message'=>'Unauthorized'],401)]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Brevo rejected the email request.');
        BrevoService::brevo_send_transactional_email('recipient@example.test','Recipient','Reminder','<p>Reminder</p>');
    }
}
