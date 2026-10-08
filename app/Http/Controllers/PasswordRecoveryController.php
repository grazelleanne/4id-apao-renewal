<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

final class PasswordRecoveryController extends Controller
{
    public function send(Request $request)
    {
        $input=$request->validate(['email'=>'required|email|max:255']);
        $email=strtolower(trim($input['email']));
        $key='recovery-send:'.hash('sha256',$email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key,3)) return response()->json(['message'=>'Please wait before requesting another code.'],429);
        RateLimiter::hit($key,300);
        $request->session()->forget('password_recovery');
        $user=User::whereRaw('LOWER(email)=?',[$email])->where('is_active',true)->first();
        if ($user) {
            $code=(string)random_int(100000,999999);
            BrevoService::brevo_send_transactional_email($email,$user->name,'APAO password recovery',
                '<p>Your verification code is <strong>'.e($code).'</strong>. It expires in five minutes.</p>');
            DB::table('password_resets_otp')->updateOrInsert(['email'=>$email],
                ['otp'=>Hash::make($code),'expires_at'=>now()->addMinutes(5),'created_at'=>now()]);
        }
        return response()->json(['success'=>true,'message'=>'If the account is active, a code has been sent.']);
    }

    public function verify(Request $request)
    {
        $input=$request->validate(['email'=>'required|email','code'=>'required|digits:6']);
        $email=strtolower(trim($input['email']));
        $key='recovery-verify:'.hash('sha256',$email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key,5)) return response()->json(['message'=>'Too many attempts. Request a new code later.'],429);
        RateLimiter::hit($key,300);
        $otp=DB::table('password_resets_otp')->where('email',$email)->first();
        if (!$otp || now()->greaterThan($otp->expires_at) || !Hash::check($input['code'],$otp->otp)) return response()->json(['message'=>'Invalid or expired verification code.'],422);
        $request->session()->put('password_recovery',['email'=>$email,'hash'=>$otp->otp]);
        return response()->json(['success'=>true]);
    }

    public function reset(Request $request)
    {
        $input=$request->validate(['email'=>'required|email','password'=>'required|string|confirmed|max:4096']);
        if (!password_is_strong($input['password'])) return response()->json(['message'=>'Use at least 8 characters, uppercase, lowercase, a number and a symbol.'],422);
        $email=strtolower(trim($input['email']));
        $verified=$request->session()->get('password_recovery');
        $changed=DB::transaction(function() use($verified,$email,$input) {
            $otp=DB::table('password_resets_otp')->where('email',$email)->lockForUpdate()->first();
            if (!$verified || $verified['email']!==$email || !$otp || !hash_equals($verified['hash'],$otp->otp) || now()->greaterThan($otp->expires_at)) return false;
            $user=User::whereRaw('LOWER(email)=?',[$email])->where('is_active',true)->lockForUpdate()->first();
            if (!$user) return false;
            $user->password=$input['password']; $user->session_version++; $user->must_change_password=false; $user->save();
            DB::table('password_resets_otp')->where('email',$email)->delete();
            return true;
        });
        if (!$changed) return response()->json(['message'=>'Verify a valid code before resetting your password.'],422);
        $request->session()->forget('password_recovery'); $request->session()->regenerate();
        return response()->json(['success'=>true]);
    }
}
