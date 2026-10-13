<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
final class RecordWriteLock {
 public function handle(Request $request,Closure $next) {
  if($request->isMethod('GET')||DB::getDriverName()!=='mysql')return $next($request);
  // Shared by imports and both legacy and normalized record writers.
  $result=DB::selectOne("SELECT GET_LOCK('apao_record_write',10) AS acquired");
  if((int)$result->acquired!==1)return response()->json(['message'=>'Record updates are busy. Please retry.'],503);
  try{return $next($request);}finally{DB::selectOne("SELECT RELEASE_LOCK('apao_record_write') AS released");}
 }
}
