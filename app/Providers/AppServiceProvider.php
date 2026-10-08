<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*',function($view){
            $defaults=['initialDashboardData'=>['personnel'=>[]],'initialActiveTab'=>'registration','initialFocusItem'=>null];
            foreach ($defaults as $key=>$value) if (!array_key_exists($key,$view->getData())) $view->with($key,$value);
        });
        if (!class_exists('Js',false)) class_alias(\Illuminate\Support\Js::class,'Js');
    }
}
