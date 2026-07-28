<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'zone_id',
        'code',
        'name',
        'address',
        'phone',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
