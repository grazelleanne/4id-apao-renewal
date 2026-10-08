<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('apao:create-admin {email} {name}', function () {
    $email=strtolower(trim($this->argument('email')));
    $name=trim($this->argument('name'));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL) || !$name || strlen($name)>255) {
        $this->error('Provide a valid email and name.'); return 1;
    }
    if (User::where('email',$email)->exists()) {
        $this->error('That account already exists. Use Manage Users to update it.'); return 1;
    }
    $password=$this->secret('New administrator password');
    if (!is_string($password) || !password_is_strong($password) || $password!==$this->secret('Confirm password')) {
        $this->error('Passwords must match and contain 8+ characters, uppercase, lowercase, a number and a symbol.'); return 1;
    }
    User::create(['email'=>$email,'name'=>$name,'password'=>$password,'role'=>'admin',
        'is_active'=>true,'session_version'=>1,'must_change_password'=>true]);
    $this->info('Administrator created. A new password is required on first sign-in.');
    return 0;
})->purpose('Create an administrator without overwriting existing accounts');
