<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
final class PersonnelNotice extends Mailable {
 public function __construct(public string $noticeSubject,public string $body,public bool $isHtml=false) {}
 public function build(): static {
  $this->subject($this->noticeSubject);
  return $this->isHtml?$this->html($this->body):$this->text('emails.renewal_notice',['body'=>$this->body]);
 }
}
