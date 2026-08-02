<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'product_name',
        'historical_unit_price_usd',
        'historical_unit_price_ves',
        'subtotal_usd',
        'subtotal_ves',
    ];

    protected $casts = [
        'quantity'                   => 'integer',
        'historical_unit_price_usd'  => 'decimal:2',
        'historical_unit_price_ves'  => 'decimal:2',
        'subtotal_usd'               => 'decimal:2',
        'subtotal_ves'               => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
