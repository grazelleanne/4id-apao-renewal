<?php
namespace App\Http\Controllers;
use App\Services\NotificationService;
final class NotificationController extends ActionController
{
    public function index(){return $this->action(fn()=>NotificationService::notifications_data($this->account()));}
    public function read(){return $this->action(fn()=>NotificationService::notifications_mark_read($this->account()));}
}
