<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SystemNotification extends Model { protected $table='notifications'; protected $guarded=[]; protected function casts(): array { return ['read_by_admin'=>'boolean','read_by_staff'=>'boolean']; } }
