<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response=$next($request);
        foreach ([
            'X-Content-Type-Options'=>'nosniff','X-Frame-Options'=>'SAMEORIGIN',
            'Referrer-Policy'=>'strict-origin-when-cross-origin',
            'Permissions-Policy'=>'camera=(self), microphone=(), geolocation=()',
            'Content-Security-Policy'=>"default-src 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'",
            'Cache-Control'=>'no-store, private','Pragma'=>'no-cache',
        ] as $key=>$value) $response->headers->set($key,$value);
        if ($request->secure()) $response->headers->set('Strict-Transport-Security','max-age=31536000');
        return $response;
    }
}
