<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExchangeRate extends Model
{
    use LogsActivity;

    /**
     * Sin updated_at — tabla append-only (inmutable).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'currency_code',
        'rate',
        'source',
        'notes',
    ];

    protected $casts = [
        'rate'       => 'decimal:6',
        'created_at' => 'datetime',
    ];

    // ─── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rate', 'source', 'notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Tasa de cambio {$eventName}")
            ->useLogName('exchange_rates');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Obtiene la tasa más reciente registrada.
     */
    public function scopeLatestRate($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ─── Relaciones ─────────────────────────────────────────────────────────────

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Obtiene la tasa activa desde Redis (cache) o base de datos.
     */
    public static function getActiveRate(): float
    {
        return (float) cache()->remember('active_bcv_rate', now()->addHours(12), function () {
            return static::latestRate()->value('rate') ?? 1.0;
        });
    }
}
