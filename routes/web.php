<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{AuthController,DashboardController,PersonnelController,InspectionController,UserController,ProfileController,NotificationController,AuditController,ParController};

Route::get('/login',[AuthController::class,'form'])->name('login');
Route::post('/login',[AuthController::class,'login'])->name('login.post');
Route::get('/login/captcha',[AuthController::class,'captcha'])->name('login.captcha');
Route::post('/logout',[AuthController::class,'logout'])->name('logout');
Route::get('/session/status',[AuthController::class,'status'])->name('session.status');
Route::redirect('/register','/login')->name('register');
Route::view('/forgot-password','forgot_password')->name('forgot.password');
Route::post('/forgot-password/send-otp',[\App\Http\Controllers\PasswordRecoveryController::class,'send']);
Route::post('/forgot-password/verify-otp',[\App\Http\Controllers\PasswordRecoveryController::class,'verify']);
Route::post('/forgot-password/reset',[\App\Http\Controllers\PasswordRecoveryController::class,'reset']);

Route::middleware('account:super_admin,admin,staff')->group(function(){
    Route::get('/',[DashboardController::class,'index']);
    Route::get('/dashboard',[DashboardController::class,'index']);
    Route::get('/personnel/{item}/image/{kind}',[PersonnelController::class,'image'])->whereNumber('item')->whereIn('kind',['photo','signature']);
    Route::get('/personnel',[PersonnelController::class,'index']);
    Route::get('/personnel/{item}/pdf',[PersonnelController::class,'pdf'])->whereNumber('item');
    Route::get('/inspection/{item}/pdf',[InspectionController::class,'print'])->whereNumber('item');
    Route::get('/par',[ParController::class,'index']);
    Route::get('/par/{item}/pdf',[ParController::class,'print'])->whereNumber('item')->name('staff.par.pdf');
});
Route::prefix('admin')->name('admin.')->middleware('account:super_admin,admin')->group(function(){
    Route::get('/dashboard',[DashboardController::class,'admin'])->name('dashboard');
    foreach (['personnel','inspection','reports','archive','users','audit'] as $module) {
        Route::get('/'.$module,[DashboardController::class,'admin'])->defaults('module',$module)->name($module);
    }
    Route::match(['get','post'],'/first-password',[AuthController::class,'firstPassword'])->name('first-password');
    Route::get('/dashboard-data',[DashboardController::class,'data'])->name('dashboard.data');
    Route::get('/personnel-data',[PersonnelController::class,'data'])->name('personnel.data');
    Route::post('/personnel',[PersonnelController::class,'store'])->name('personnel.store');
    Route::put('/personnel-data/{item}',[PersonnelController::class,'update'])->whereNumber('item');
    Route::delete('/personnel-data/{item}',[PersonnelController::class,'archive'])->whereNumber('item');
    Route::get('/archive-data',[DashboardController::class,'archive'])->name('archive.data');
    Route::post('/archive/restore',[PersonnelController::class,'restore'])->name('archive.restore');
    Route::get('/users-data',[UserController::class,'data'])->name('users.data');
    Route::post('/users',[UserController::class,'store'])->name('users.store');
    Route::put('/users/update',[UserController::class,'update'])->name('users.update');
    Route::get('/audit-data',[AuditController::class,'index'])->name('audit.data');
    Route::get('/notifications',[NotificationController::class,'index'])->name('notifications');
    Route::post('/notifications/read',[NotificationController::class,'read'])->name('notifications.read');
    Route::put('/profile',[ProfileController::class,'update'])->name('profile.update');
    Route::put('/profile/password',[ProfileController::class,'password'])->name('profile.password');
    Route::get('/inspection-data',[InspectionController::class,'data']);
    Route::get('/personnel/{item}/renewal-history',[InspectionController::class,'history'])->whereNumber('item');
    Route::get('/inspection/{item}/detail',[InspectionController::class,'detail'])->whereNumber('item');
    Route::get('/inspection/{item}/print',[InspectionController::class,'print'])->whereNumber('item');
    Route::post('/inspection/save',[InspectionController::class,'save']);
    Route::post('/inspection/notify-staff',[InspectionController::class,'notify']);
});
Route::prefix('staff')->name('staff.')->middleware('account:staff,super_admin,admin')->group(function(){
    Route::get('/par-data',[ParController::class,'data'])->name('par.data');
    Route::post('/par/{item}/reprint',[ParController::class,'reprint'])->whereNumber('item')->name('par.reprint');
    Route::post('/par/{item}/save',[ParController::class,'save'])->whereNumber('item')->name('par.save');
    Route::get('/dashboard',[DashboardController::class,'staff'])->name('dashboard');
    Route::get('/dashboard-data',[PersonnelController::class,'data'])->name('dashboard.data');
    Route::match(['get','post'],'/first-password',[AuthController::class,'firstPassword'])->name('first-password');
    Route::post('/personnel',[PersonnelController::class,'store'])->name('personnel.store');
    Route::post('/personnel/availability',[PersonnelController::class,'availability'])->name('personnel.availability');
    Route::post('/personnel/{item}/notify',[PersonnelController::class,'notify'])->whereNumber('item');
    Route::post('/ics/{item}/send-inspection',[InspectionController::class,'submit'])->whereNumber('item');
    Route::get('/notifications',[NotificationController::class,'index'])->name('notifications');
    Route::post('/notifications/read',[NotificationController::class,'read'])->name('notifications.read');
    Route::put('/profile',[ProfileController::class,'update'])->name('profile.update');
    Route::put('/profile/password',[ProfileController::class,'password'])->name('profile.password');
});
