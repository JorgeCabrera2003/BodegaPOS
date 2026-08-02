<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'cashier_id',
        'exchange_rate_id',
        'customer_name',
        'customer_phone',
        'customer_id_number',
        'subtotal_usd',
        'igtf_usd',
        'total_usd',
        'total_ves',
        'amount_paid_usd',
        'change_usd',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal_usd'   => 'decimal:2',
        'igtf_usd'       => 'decimal:2',
        'total_usd'      => 'decimal:2',
        'total_ves'      => 'decimal:2',
        'amount_paid_usd'=> 'decimal:2',
        'change_usd'     => 'decimal:2',
        'created_at'     => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_usd', 'total_ves', 'cashier_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Venta #{$this->id} fue {$eventName}")
            ->useLogName('sales');
    }

    // ─── Relaciones ─────────────────────────────────────────────────────────────

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
