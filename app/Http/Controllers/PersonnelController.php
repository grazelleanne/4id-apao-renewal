<?php
namespace App\Http\Controllers;
use App\Services\PersonnelService;
final class PersonnelController extends ActionController
{
    public function data(){return $this->action(fn()=>PersonnelService::personnel_data());}
    public function store(){return $this->action(fn()=>PersonnelService::personnel_store($this->account()));}
    public function availability(){return $this->action(fn()=>PersonnelService::personnel_availability());}
    public function update(int $item){return $this->action(fn()=>PersonnelService::personnel_change($item,$this->account(),false));}
    public function archive(int $item){return $this->action(fn()=>PersonnelService::personnel_change($item,$this->account(),true));}
    public function restore(){return $this->action(fn()=>PersonnelService::personnel_restore($this->account()));}
    public function image(int $item,string $kind){return $this->action(fn()=>PersonnelService::personnel_image_response($item,$kind));}
    public function notify(int $item){return $this->action(fn()=>PersonnelService::staff_notify_personnel($item,$this->account()));}
    public function pdf(int $item){return $this->action(fn()=>PersonnelService::personnel_pdf($item));}
    public function index(){return $this->action(fn()=>PersonnelService::personnel_list());}
}
