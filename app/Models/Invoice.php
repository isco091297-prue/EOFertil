<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashback_campaign_id',
        'user_id',
        'branch_id',
        'numero_factura_original',
        'numero_factura_normalizado',
        'fecha_factura',
        'total_factura',
        'total_productos_participantes',
        'porcentaje_cashback',
        'cashback_generado',
        'foto_factura',
        'ocr_result',
        'origen',
        'estado',
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'total_factura' => 'decimal:2',
        'total_productos_participantes' => 'decimal:2',
        'porcentaje_cashback' => 'decimal:2',
        'cashback_generado' => 'decimal:2',
        'ocr_result' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function cashbackCampaign(): BelongsTo
    {
        return $this->belongsTo(CashbackCampaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }
}
