<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ammunition extends Model {
 protected $table='ammunition';
 protected $guarded=[];
 protected function casts(): array {return ['quantity'=>'integer'];}
 public function personnel(){return $this->belongsTo(Personnel::class);}
 
}
