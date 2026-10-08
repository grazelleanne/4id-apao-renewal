<?php
declare(strict_types=1);

use App\Models\User;
use App\Support\SessionState;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 2));
require_once __DIR__.'/domain.php';

function env_value(string $key, string $default = ''): string
{
    return (string) config('apao.environment.'.$key, $default);
}
function db(): PDO
{
    $pdo = DB::connection()->getPdo();
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
function request_data(): array { return request()->all(); }
function request_expects_json(): bool { return request()->expectsJson() || request()->isJson(); }
function require_post(array $allowedMethods = ['POST']): void
{
    if (!in_array(request()->method(), $allowedMethods, true)) json_response(['success'=>false,'message'=>'Method not allowed.'], 405);
    // Laravel's web middleware validates CSRF before the controller runs.
}
function json_response(array $payload, int $status = 200): never
{
    throw new HttpResponseException(response()->json($payload,$status,[],JSON_INVALID_UTF8_SUBSTITUTE));
}
function app_redirect(string $path): never
{
    throw new HttpResponseException(redirect()->to($path,303));
}
function app_header(string $header, bool $replace = true, ?int $status = null): void
{
    [$name,$value]=array_pad(explode(':',$header,2),2,'');
    $headers=request()->attributes->get('apao_response_headers',[]);
    $headers[trim($name)]=trim($value);
    request()->attributes->set('apao_response_headers',$headers);
    if ($status) app_status($status);
}
function app_status(?int $status = null): int
{
    if ($status !== null) request()->attributes->set('apao_response_status',$status);
    return request()->attributes->get('apao_response_status',200);
}
function finish_response(): never
{
    $content=(string) ob_get_contents();
    throw new HttpResponseException(response($content,app_status(),request()->attributes->get('apao_response_headers',[])));
}
function page_error(string $message, int $status = 500): never
{
    throw new HttpResponseException(response(page('Request error','<h1>'.h($message).'</h1><p><a href="/login">Return</a></p>'),$status));
}
function render_view(string $name, array $data = []): string
{
    return view($name,array_merge([
        'user'=>(object) (current_user()??[]),
        'initialDashboardData'=>['personnel'=>[]],
        'initialActiveTab'=>'registration','initialFocusItem'=>null,
    ],$data))->render();
}
function regenerate_app_session(bool $destroy = true): void { session()->regenerate($destroy); }
function persist_app_session(): void { session()->put('apao',SessionState::$data); }
function current_user(): ?array
{
    return is_array(SessionState::$data['user']??null)?SessionState::$data['user']:null;
}
function require_user(array $roles = [], bool $allowTemporaryPassword = false): array
{
    $user=current_user();
    if (!$user) {
        if (request_expects_json()) json_response(['success'=>false,'message'=>'Please sign in again.'],401);
        app_redirect('/login');
    }
    $fresh=User::find($user['id']);
    $expired=time()-(int) (SessionState::$data['_last_activity']??0) > max(900,(int) env_value('APP_SESSION_LIFETIME','7200'));
    if ($expired || !$fresh || !$fresh->is_active || (int) $fresh->session_version !== (int) ($user['session_version']??0)) {
        SessionState::$data=[];
        regenerate_app_session();
        if (request_expects_json()) json_response(['success'=>false,'message'=>'Your session is no longer valid. Please sign in again.'],401);
        app_redirect('/login');
    }
    $user=['id'=>$fresh->id,'name'=>$fresh->name,'email'=>$fresh->email,'role'=>$fresh->role,
        'session_version'=>(int) $fresh->session_version,'must_change_password'=>(bool) $fresh->must_change_password];
    SessionState::$data['user']=$user;
    SessionState::$data['_last_activity']=time();
    if ($roles && !in_array($user['role'],$roles,true)) {
        if (request_expects_json()) json_response(['success'=>false,'message'=>'Access denied.'],403);
        page_error('Access denied.',403);
    }
    if (!$allowTemporaryPassword && $user['must_change_password']) {
        $redirect=$user['role']==='staff'?'/staff/first-password':'/admin/first-password';
        if (request_expects_json()) json_response(['success'=>false,'message'=>'Create your new password before accessing the system.','redirect'=>$redirect],403);
        app_redirect($redirect);
    }
    return $user;
}
