<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignUserRanking extends Model
{
    protected $fillable = [

        'cashback_campaign_id',

        'user_id',

        'warehouse_id',

        'zone_id',

        'branch_id',

        'sales_total',

        'cashback_total',

        'invoice_count',

        'position',

    ];

    protected $casts = [

        'sales_total' => 'decimal:2',

        'cashback_total' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            CashbackCampaign::class,
            'cashback_campaign_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(
            Zone::class
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class
        );
    }
}
