<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

final class AccountAccess
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $temporary=$request->is('*/first-password');
        $request->attributes->set('apao_user',require_user($roles,$temporary));
        return $next($request);
    }
}
