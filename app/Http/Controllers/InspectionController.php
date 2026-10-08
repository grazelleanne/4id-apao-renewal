<?php
namespace App\Http\Controllers;
use App\Services\InspectionService;
final class InspectionController extends ActionController
{
    public function data(){return $this->action(fn()=>InspectionService::inspection_data());}
    public function detail(int $item){return $this->action(fn()=>InspectionService::inspection_detail($item));}
    public function save(){return $this->action(fn()=>InspectionService::inspection_save($this->account()));}
    public function notify(){return $this->action(fn()=>InspectionService::inspection_notify_staff($this->account()));}
    public function submit(int $item){return $this->action(fn()=>InspectionService::ics_send_for_inspection($item,$this->account()));}
    public function history(int $item){return $this->action(fn()=>InspectionService::personnel_renewal_history($item));}
    public function print(int $item){return $this->action(fn()=>InspectionService::inspection_pdf($item));}
}
