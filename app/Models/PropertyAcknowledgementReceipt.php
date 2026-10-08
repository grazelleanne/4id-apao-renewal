<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PropertyAcknowledgementReceipt extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['equipment_items'=>'array','issued_date'=>'date','valid_until'=>'date','firearm_unit_cost'=>'decimal:2','ammunition_unit_cost'=>'decimal:2']; }
    public function personnel(){return $this->belongsTo(Personnel::class);}
    public function previous(){return $this->belongsTo(self::class,'previous_par_id');}
}
