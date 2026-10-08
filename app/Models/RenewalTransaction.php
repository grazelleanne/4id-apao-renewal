<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RenewalTransaction extends Model
{
    protected $guarded=[];
    public function personnel(){return $this->belongsTo(Personnel::class);}
}
