<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use LogsActivity;

    protected $fillable = [
        'registered_by',
        'exchange_rate_id',
        'category',
        'description',
        'supplier_id',
        'amount_usd',
        'amount_ves',
        'receipt_path',
        'expense_date',
        'is_recurring',
    ];

    protected $casts = [
        'amount_usd'   => 'decimal:2',
        'amount_ves'   => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public static array $categoryLabels = [
        'supplier'    => 'Proveedor',
        'utilities'   => 'Servicios Públicos',
        'payroll'     => 'Nómina',
        'rent'        => 'Alquiler',
        'maintenance' => 'Mantenimiento',
        'transport'   => 'Transporte/Flete',
        'tax'         => 'Impuestos',
        'other'       => 'Otros',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'description', 'amount_usd', 'amount_ves', 'expense_date'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Gasto '{$this->description}' fue {$eventName}")
            ->useLogName('expenses');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }
}
