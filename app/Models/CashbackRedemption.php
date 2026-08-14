<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackRedemption extends Model
{
    protected $table = 'cashback_redemptions';

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'branch_id',
        'identification',
        'bank',
        'account_type',
        'account_number',
        'monto',
        'estado',
        'telegram_enviado_at',
        'telegram_error',
        'observacion',
        'pagado_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'telegram_enviado_at' => 'datetime',
        'pagado_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
