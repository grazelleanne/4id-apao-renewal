<?php
namespace App\Services;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
final class RecordValidation {
 public const SERIAL_PATTERN='/^[A-Za-z0-9][A-Za-z0-9\/-]{2,99}$/';
 public static function validate(string $module,array $data,?int $id=null,bool $legacyImport=false): array {
  $serial=['required','string','max:100','regex:'.self::SERIAL_PATTERN];
  $rules=match($module){
   'personnel'=>[
    'item_number'=>['required','integer','min:1',Rule::unique('personnel')->ignore($id)],
    'first_name'=>'required|string|max:255','last_name'=>'required|string|max:255','middle_name'=>'nullable|string|max:255',
    'rank'=>'required|string|max:255','unit'=>'required|string|max:255','issued_by'=>'required|string|max:255',
    'email'=>['required','email:rfc',static function($attribute,$value,$fail){if(!filter_var($value,FILTER_VALIDATE_EMAIL))$fail('Enter a valid email address.');},'max:255',Rule::unique('personnel')->ignore($id)],
    'date_of_birth'=>'required|date_format:Y-m-d|before_or_equal:today|after_or_equal:1900-01-01',
    'date_of_validity'=>'nullable|date_format:Y-m-d',
    'import_status'=>['nullable','string',static function($attribute,$value,$fail){if(!in_array(strtolower(trim($value)),['active','for renewal','expired'],true))$fail('Status must be Active, For Renewal, or Expired.');}],
    'afp_serial_number'=>['required','string','max:255',Rule::unique('personnel')->ignore($id)],
    'atp_serial_number'=>[...$serial,Rule::unique('personnel')->ignore($id),Rule::unique('firearms','atp_serial_number')],
    'pistol_serial_number'=>[...$serial,Rule::unique('personnel')->ignore($id),Rule::unique('firearms','serial_number')],
    'pistol_nomenclature'=>'required|string|max:255','pistol_type'=>'required|string|max:100','qty_ammo'=>'required|integer|min:0|max:1000000',
   ],
   'firearms'=>[
    'personnel_id'=>['required','integer',Rule::exists('personnel','id')->whereNull('archived_at')],
    'atp_serial_number'=>[...$serial,Rule::unique('firearms')->ignore($id),Rule::unique('personnel','atp_serial_number')],
    'serial_number'=>[...$serial,Rule::unique('firearms')->ignore($id),Rule::unique('personnel','pistol_serial_number')],
    'nomenclature'=>'required|string|max:255','type'=>'required|string|max:100','expiration_date'=>'nullable|date_format:Y-m-d',
   ],
   'ammunition'=>[
    'personnel_id'=>['required','integer',Rule::exists('personnel','id')->whereNull('archived_at')],
    'lot_number'=>['required','string','max:100',Rule::unique('ammunition')->where(fn($q)=>$q->where('personnel_id',$data['personnel_id']??0)->where('caliber',$data['caliber']??''))->ignore($id)],
    'caliber'=>'required|string|max:100','quantity'=>'required|integer|min:0|max:1000000',
   ],default=>throw new \InvalidArgumentException('Unknown record module.')
  };
  if($legacyImport && $module==='personnel'){
   $rules['issued_by']='nullable|string|max:255';
   $rules['atp_serial_number']=['nullable','string','max:100','regex:'.self::SERIAL_PATTERN,Rule::unique('personnel')->ignore($id),Rule::unique('firearms','atp_serial_number')];
  }
  foreach($data as $key=>$value)if(is_string($value))$data[$key]=trim(strip_tags($value));
  if($module==='personnel') $data['pistol_serial_number']=$data['afp_serial_number']??'';
  if(isset($data['email']))$data['email']=strtolower($data['email']);
  return Validator::make($data,$rules,['atp_serial_number.unique'=>'This ATP Serial Number already exists.'])->validate();
 }
}
