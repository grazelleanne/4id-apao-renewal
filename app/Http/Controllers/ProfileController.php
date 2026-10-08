<?php
namespace App\Http\Controllers;
use App\Services\ProfileService;
final class ProfileController extends ActionController
{
    public function update(){return $this->action(fn()=>ProfileService::profile_update($this->account()));}
    public function password(){return $this->action(fn()=>ProfileService::profile_change_password($this->account()));}
}
