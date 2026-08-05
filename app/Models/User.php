<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'privacy_accepted',
        'privacy_accepted_at',
        'responsibility_accepted',
        'responsibility_accepted_at',
        'last_login',
        'cashback_total',
        'cashback_claimed',
        'cashback_available',

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

            'privacy_accepted' => 'boolean',

            'privacy_accepted_at' => 'datetime',

            'responsibility_accepted' => 'boolean',
            'responsibility_accepted_at' => 'datetime',

            'is_active' => 'boolean',

            'cashback_total' => 'decimal:2',

            'cashback_claimed' => 'decimal:2',

            'cashback_available' => 'decimal:2',

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
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }
}
