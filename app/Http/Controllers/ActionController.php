<?php
namespace App\Http\Controllers;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ActionController extends Controller
{
    protected function action(Closure $handler)
    {
        $level=ob_get_level();
        ob_start();
        try { $handler(); finish_response(); }
        catch (HttpResponseException $exception) { return $exception->getResponse(); }
        finally { while (ob_get_level()>$level) ob_end_clean(); }
    }
    protected function account(): array { return request()->attributes->get('apao_user',[]); }
}
