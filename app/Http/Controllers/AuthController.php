<?php
namespace App\Http\Controllers;
use App\Services\AuthService;
use App\Support\SessionState;

final class AuthController extends ActionController
{
    public function form() {
        if (current_user()) return redirect('/dashboard');
        return $this->action(fn()=>AuthService::show_login());
    }
    public function login() { return $this->action(fn()=>AuthService::login()); }
    public function captcha() {
        $left=random_int(1,9); $right=random_int(1,9);
        SessionState::$data['captcha']=$left+$right;
        return response()->json(['question'=>"$left + $right = ?"]);
    }
    public function logout() {
        if ($user=current_user()) audit($user,'logout',$user['email']);
        SessionState::$data=[];
        session()->invalidate(); session()->regenerateToken();
        return redirect('/login',303);
    }
    public function status() {
        if (!current_user()) return response()->json(['authenticated'=>false],401);
        return $this->action(function() { require_user([],true); json_response(['authenticated'=>true]); });
    }
    public function firstPassword() {
        $user=$this->account();
        if (request()->isMethod('post')) return $this->action(fn()=>AuthService::staff_first_password($user));
        if (!$user['must_change_password']) return redirect($user['role']==='staff'?'/staff/dashboard':'/admin/dashboard');
        return view('staff_first_password',['user'=>(object)$user]);
    }
}
