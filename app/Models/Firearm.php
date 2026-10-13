<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Firearm extends Model {
 protected $table='firearms';
 protected $guarded=[];
 protected function casts(): array {return ['expiration_date'=>'date'];}
 public function personnel(){return $this->belongsTo(Personnel::class);}
 
}
