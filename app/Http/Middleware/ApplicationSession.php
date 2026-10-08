<?php
namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SessionState;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ApplicationSession
{
    public function handle(Request $request, Closure $next)
    {
        SessionState::$data=$request->session()->get('apao',[]);
        try { $response=$next($request); }
        catch (HttpResponseException $exception) { $response=$exception->getResponse(); }
        finally {
            $request->session()->put('apao',SessionState::$data);
            $id=SessionState::$data['user']['id']??null;
            if ($id && Auth::id() !== $id) {
                $user=User::find($id);
                if ($user) Auth::login($user);
            } elseif (!$id && Auth::check()) Auth::logout();
            SessionState::$data=[];
        }
        return $response;
    }
}
