<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'image_path',
        'category_id',
        'supplier_id',
        'base_price_usd',
        'cost_price_usd',
        'stock_quantity',
        'min_stock_alert',
        'max_stock',
        'is_active',
        'apply_igtf',
    ];

    protected $casts = [
        'base_price_usd'  => 'decimal:2',
        'cost_price_usd'  => 'decimal:2',
        'stock_quantity'  => 'integer',
        'min_stock_alert' => 'integer',
        'max_stock'       => 'integer',
        'is_active'       => 'boolean',
        'apply_igtf'      => 'boolean',
    ];

    // ─── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'barcode', 'base_price_usd', 'stock_quantity', 'is_active', 'apply_igtf'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Producto {$this->name} fue {$eventName}")
            ->useLogName('products');
    }

    // ─── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Precio en VES calculado dinámicamente según la tasa activa en Redis.
     */
    public function getPriceVesAttribute(): float
    {
        return app(CurrencyService::class)->usdToVes((float) $this->base_price_usd);
    }

    /**
     * Verifica si el stock está en alerta.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    // ─── Relaciones ─────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
