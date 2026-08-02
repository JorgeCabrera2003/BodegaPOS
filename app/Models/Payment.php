<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount_usd',
        'amount_ves',
        'reference_number',
        'bank_name',
        'phone_number',
        'id_number',
        'is_verified',
    ];

    protected $casts = [
        'amount_usd'  => 'decimal:2',
        'amount_ves'  => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    /**
     * Etiquetas legibles para métodos de pago venezolanos.
     */
    public static array $methodLabels = [
        'cash_usd'     => 'Efectivo USD',
        'cash_ves'     => 'Efectivo Bs.',
        'pagomovil'    => 'Pago Móvil',
        'pos_terminal' => 'Punto de Venta',
        'zelle'        => 'Zelle',
        'binance'      => 'Binance Pay',
        'transfer_ves' => 'Transferencia Bs.',
    ];

    /**
     * Métodos que pagan en divisas (aplican IGTF).
     */
    public static array $foreignCurrencyMethods = [
        'cash_usd', 'zelle', 'binance',
    ];

    /**
     * Métodos que requieren número de referencia.
     */
    public static array $requiresReference = [
        'pagomovil', 'pos_terminal', 'zelle', 'binance', 'transfer_ves',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['payment_method', 'amount_usd', 'amount_ves', 'reference_number'])
            ->logOnlyDirty()
            ->useLogName('payments');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return static::$methodLabels[$this->payment_method] ?? $this->payment_method;
    }

    public function isForeignCurrency(): bool
    {
        return in_array($this->payment_method, static::$foreignCurrencyMethods);
    }
}
