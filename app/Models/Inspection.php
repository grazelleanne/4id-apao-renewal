<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inspection extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['date_registered'=>'date','next_renewal_date'=>'date','inspected_at'=>'datetime']; }
    public function personnel(){return $this->belongsTo(Personnel::class);}
}
