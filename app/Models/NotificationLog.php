<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationLog extends Model {
 protected $table='notification_logs';
 protected $guarded=[];
 protected function casts(): array {return ['expiration_date'=>'date','date_sent'=>'datetime'];}
 public function setExpirationDateAttribute($value): void { $this->attributes['expiration_date']=$value ? \Illuminate\Support\Carbon::parse($value)->toDateString() : null; }
 public function personnel(){return $this->belongsTo(Personnel::class);}
 public function firearm(){return $this->belongsTo(Firearm::class);}
 public function user(){return $this->belongsTo(User::class);}
}
