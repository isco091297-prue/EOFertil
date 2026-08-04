<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackCampaignScope extends Model
{
    protected $table = 'cashback_campaign_scopes';

    protected $fillable = [

        'cashback_campaign_id',

        'warehouse_id',

        'zone_id',

        'branch_id',
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
