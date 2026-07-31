<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'cashback_campaign_id',
        'tipo',
        'movimiento',
        'valor',
        'saldo_despues',
        'descripcion',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'saldo_despues' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashbackCampaign(): BelongsTo
    {
        return $this->belongsTo(CashbackCampaign::class);
    }
}
