<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Notifications\LowStockNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SaleService — Procesa ventas completas de forma atómica.
 *
 * Principio SRP: Solo se ocupa de persistir transacciones de venta.
 * Usa DB::transaction() para garantizar integridad ACID.
 */
class SaleService
{
    public function __construct(
        protected CurrencyService $currency
    ) {}

    /**
     * Procesa una venta completa con múltiples items y pagos.
     *
     * @param array $cartItems   [['product_id', 'quantity', 'unit_price_usd'], ...]
     * @param array $payments    [['method', 'amount_usd', 'amount_ves', 'reference', ...], ...]
     * @param int   $cashierId
     * @param array $customerInfo ['name', 'phone', 'id_number'] (opcional)
     * @return Sale
     */
    public function processSale(
        array $cartItems,
        array $payments,
        int $cashierId,
        array $customerInfo = []
    ): Sale {
        return DB::transaction(function () use ($cartItems, $payments, $cashierId, $customerInfo) {

            $rate         = $this->currency->getActiveRate();
            $exchangeRate = ExchangeRate::latestRate()->first();

            if (! $exchangeRate) {
                throw new \RuntimeException('No hay tasa de cambio BCV registrada en el sistema.');
            }

            // ── 1. Calcular totales ───────────────────────────────────────────
            $subtotalUsd = 0.0;

            foreach ($cartItems as $item) {
                $subtotalUsd += (float) $item['unit_price_usd'] * (int) $item['quantity'];
            }

            // Determinar si aplica IGTF (al menos un pago en divisas)
            $hasForwardCurrencyPayment = collect($payments)
                ->pluck('method')
                ->intersect(Payment::$foreignCurrencyMethods)
                ->isNotEmpty();

            $igtfUsd   = $hasForwardCurrencyPayment ? $this->currency->calculateIgtf($subtotalUsd) : 0.0;
            $totalUsd  = $this->currency->roundUsd($subtotalUsd + $igtfUsd);
            $totalVes  = $this->currency->usdToVes($totalUsd, $rate);

            $amountPaidUsd = collect($payments)->sum('amount_usd');
            $changeUsd     = $this->currency->roundUsd(max(0, $amountPaidUsd - $totalUsd));

            // ── 2. Crear cabecera de venta ────────────────────────────────────
            $sale = Sale::create([
                'cashier_id'          => $cashierId,
                'exchange_rate_id'    => $exchangeRate->id,
                'customer_name'       => $customerInfo['name'] ?? null,
                'customer_phone'      => $customerInfo['phone'] ?? null,
                'customer_id_number'  => $customerInfo['id_number'] ?? null,
                'subtotal_usd'        => $subtotalUsd,
                'igtf_usd'            => $igtfUsd,
                'total_usd'           => $totalUsd,
                'total_ves'           => $totalVes,
                'amount_paid_usd'     => $amountPaidUsd,
                'change_usd'          => $changeUsd,
                'status'              => 'completed',
            ]);

            // ── 3. Crear ítems y descontar inventario ─────────────────────────
            foreach ($cartItems as $item) {
                $product     = Product::lockForUpdate()->findOrFail($item['product_id']);
                $unitPriceUsd = (float) $item['unit_price_usd'];
                $qty          = (int) $item['quantity'];

                SaleItem::create([
                    'sale_id'                    => $sale->id,
                    'product_id'                 => $product->id,
                    'quantity'                   => $qty,
                    'product_name'               => $product->name,
                    'historical_unit_price_usd'  => $unitPriceUsd,
                    'historical_unit_price_ves'  => $this->currency->usdToVes($unitPriceUsd, $rate),
                    'subtotal_usd'               => $this->currency->roundUsd($unitPriceUsd * $qty),
                    'subtotal_ves'               => $this->currency->usdToVes($unitPriceUsd * $qty, $rate),
                ]);

                // Descontar stock — el Observer disparará alerta si baja del umbral
                $product->decrement('stock_quantity', $qty);
            }

            // ── 4. Registrar pagos ────────────────────────────────────────────
            foreach ($payments as $paymentData) {
                $amountUsd = (float) ($paymentData['amount_usd'] ?? 0);
                $amountVes = (float) ($paymentData['amount_ves'] ?? 0);

                // Si el monto VES no viene, lo calculamos
                if ($amountVes === 0.0 && $amountUsd > 0) {
                    $amountVes = $this->currency->usdToVes($amountUsd, $rate);
                }

                Payment::create([
                    'sale_id'          => $sale->id,
                    'payment_method'   => $paymentData['method'],
                    'amount_usd'       => $amountUsd,
                    'amount_ves'       => $amountVes,
                    'reference_number' => $paymentData['reference'] ?? null,
                    'bank_name'        => $paymentData['bank_name'] ?? null,
                    'phone_number'     => $paymentData['phone_number'] ?? null,
                    'id_number'        => $paymentData['id_number'] ?? null,
                ]);
            }

            Log::info("Venta #{$sale->id} procesada por cajero #{$cashierId}. Total: \${$totalUsd}");

            return $sale->load(['items.product', 'payments', 'exchangeRate', 'cashier']);
        });
    }
}
