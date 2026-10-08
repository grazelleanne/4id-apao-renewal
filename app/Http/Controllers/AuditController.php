<?php
namespace App\Http\Controllers;
use App\Services\AuditService;
final class AuditController extends ActionController
{
    public function index(){return $this->action(fn()=>AuditService::audit_data());}
}
