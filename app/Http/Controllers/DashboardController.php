<?php
namespace App\Http\Controllers;
use App\Services\DashboardService;
use App\Services\PersonnelService;

final class DashboardController extends ActionController
{
    public function index() { return redirect($this->account()['role']==='staff'?'/staff/dashboard':'/admin/dashboard'); }
    public function admin(string $module='dashboard') {
        $data=['user'=>(object)$this->account()];
        if ($module==='reports') $data['initialPersonnel']=PersonnelService::personnel_rows();
        return view('admin_'.$module,$data);
    }
    public function staff() {
        return view('staff_dashboard',[
            'user'=>(object)$this->account(),'initialDashboardData'=>['personnel'=>PersonnelService::personnel_rows()],
            'initialActiveTab'=>request()->string('tab','registration')->toString(),
            'initialFocusItem'=>request()->query('item'),
        ]);
    }
    public function data() { return $this->action(fn()=>DashboardService::dashboard_data()); }
    public function archive() { return $this->action(fn()=>DashboardService::archive_data()); }
}
