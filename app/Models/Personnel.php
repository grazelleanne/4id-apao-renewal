<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Personnel extends Model
{
    protected $table='personnel';
    protected $guarded=[];
    protected $hidden=['photo','signature'];
    protected function casts(): array { return ['date_of_birth'=>'date','date_of_validity'=>'date','date_approved'=>'date','is_archived'=>'boolean','qty_ammo'=>'integer']; }
    public function inspections(): HasMany { return $this->hasMany(Inspection::class); }
    public function receipts(): HasMany { return $this->hasMany(PropertyAcknowledgementReceipt::class); }
    public function renewals(): HasMany { return $this->hasMany(RenewalTransaction::class); }
}
