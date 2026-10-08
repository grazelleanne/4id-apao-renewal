<?php
namespace App\Http\Controllers;
use App\Services\UserManagementService;
final class UserController extends ActionController
{
    public function data(){return $this->action(fn()=>UserManagementService::users_data());}
    public function store(){return $this->action(fn()=>UserManagementService::users_store($this->account()));}
    public function update(){return $this->action(fn()=>UserManagementService::users_update($this->account()));}
}
