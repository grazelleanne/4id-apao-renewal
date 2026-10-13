<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DataImportLog extends Model {
 protected $table='data_import_logs';
 protected $guarded=[];
 public $timestamps=false; protected function casts(): array {return ['errors'=>'array','date_uploaded'=>'datetime'];}
 public function user(){return $this->belongsTo(User::class);}
}
