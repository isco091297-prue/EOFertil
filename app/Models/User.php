<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [

        'role_id',

        'warehouse_id',

        'zone_id',

        'branch_id',

        'first_name',

        'last_name',

        'identification',

        'phone',

        'username',

        'email',

        'password',

        'bank',

        'account_type',

        'account_number',

        'is_active',

        'last_login',
        'welcome_completed_at',

    ];

    protected $hidden = [

        'password',

        'remember_token',

    ];

    protected function casts(): array
    {
        return [

            'password' => 'hashed',

            'last_login' => 'datetime',
            'welcome_completed_at' => 'datetime',

            'is_active' => 'boolean',

        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
