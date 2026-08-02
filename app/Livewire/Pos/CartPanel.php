<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Services\CurrencyService;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPanel extends Component
{
    public array $cart = [];
    public array $payments = [];
    
    // Info del cliente (opcional)
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerId = '';

    // Modales y estados
    public bool $showPaymentModal = false;
    public string $currentPaymentMethod = 'cash_usd';
    public float $currentPaymentAmount = 0;
    public string $currentPaymentReference = '';

    #[On('product-added-to-cart')]
    public function addProduct(int $productId)
    {
        $product = Product::active()->find($productId);
        if (! $product) return;

        if ($product->stock_quantity <= 0) {
            Notification::make()->danger()->title('Agotado')->body("El producto {$product->name} no tiene stock.")->send();
            return;
        }

        $existingKey = collect($this->cart)->search(fn ($item) => $item['product_id'] === $productId);

        if ($existingKey !== false) {
            if ($this->cart[$existingKey]['quantity'] >= $product->stock_quantity) {
                Notification::make()->warning()->title('Stock insuficiente')->body('No puedes agregar más unidades de este producto.')->send();
                return;
            }
            $this->cart[$existingKey]['quantity']++;
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'unit_price_usd' => (float) $product->base_price_usd,
                'quantity' => 1,
                'stock' => $product->stock_quantity,
                'apply_igtf' => $product->apply_igtf,
            ];
        }
    }

    public function incrementQuantity(int $index)
    {
        if ($this->cart[$index]['quantity'] < $this->cart[$index]['stock']) {
            $this->cart[$index]['quantity']++;
        }
    }

    public function decrementQuantity(int $index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity']--;
        } else {
            $this->removeItem($index);
        }
    }

    public function removeItem(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Re-indexar
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->payments = [];
    }

    // ─── Cálculos en tiempo real ────────────────────────────────────────────────

    public function getSubtotalUsdProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['unit_price_usd'] * $item['quantity']);
    }

    public function getIgtfUsdProperty(): float
    {
        $currencyService = app(CurrencyService::class);
        $hasDivisasPayment = collect($this->payments)->pluck('method')->intersect(['cash_usd', 'zelle', 'binance'])->isNotEmpty();
        
        return $hasDivisasPayment ? $currencyService->calculateIgtf($this->subtotal_usd) : 0.0;
    }

    public function getTotalUsdProperty(): float
    {
        return app(CurrencyService::class)->roundUsd($this->subtotal_usd + $this->igtf_usd);
    }

    public function getTotalVesProperty(): float
    {
        return app(CurrencyService::class)->usdToVes($this->total_usd);
    }

    public function getPaidUsdProperty(): float
    {
        return collect($this->payments)->sum('amount_usd');
    }

    public function getRemainingUsdProperty(): float
    {
        return max(0, app(CurrencyService::class)->roundUsd($this->total_usd - $this->paid_usd));
    }

    public function getChangeUsdProperty(): float
    {
        return max(0, app(CurrencyService::class)->roundUsd($this->paid_usd - $this->total_usd));
    }

    // ─── Lógica de Pagos ───────────────────────────────────────────────────────

    public function openPaymentModal()
    {
        if (empty($this->cart)) return;
        $this->currentPaymentAmount = $this->remaining_usd;
        $this->showPaymentModal = true;
    }

    public function addPayment()
    {
        $this->validate([
            'currentPaymentAmount' => 'required|numeric|min:0.01',
            'currentPaymentMethod' => 'required|string',
        ]);

        $currency = app(CurrencyService::class);
        $amountUsd = (float) $this->currentPaymentAmount;

        // Si el método es en Bs, recalculamos el equivalente exacto
        if (in_array($this->currentPaymentMethod, ['cash_ves', 'pagomovil', 'pos_terminal', 'transfer_ves'])) {
            $amountVes = $amountUsd; // El input lo tomaremos como VES si es método local en la vista, pero para simplificar, asumimos que el cajero digita en USD equivalente y el sistema calcula. 
            // En una app real de VZLA, el cajero puede digitar el monto en Bs y se convierte.
            // Para mantenerlo simple: el input SIEMPRE representa el monto en USD que está cubriendo.
            $amountVes = $currency->usdToVes($amountUsd);
        } else {
            $amountVes = $currency->usdToVes($amountUsd);
        }

        $this->payments[] = [
            'method' => $this->currentPaymentMethod,
            'amount_usd' => $amountUsd,
            'amount_ves' => $amountVes,
            'reference' => $this->currentPaymentReference,
        ];

        $this->currentPaymentAmount = $this->remaining_usd;
        $this->currentPaymentReference = '';

        if ($this->remaining_usd <= 0) {
            // Ya se cubrió el total
        }
    }

    public function removePayment(int $index)
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function completeSale(SaleService $saleService)
    {
        if ($this->remaining_usd > 0) {
            Notification::make()->danger()->title('Pago Incompleto')->body('Falta por cobrar monto para completar la venta.')->send();
            return;
        }

        try {
            $sale = $saleService->processSale(
                cartItems: $this->cart,
                payments: $this->payments,
                cashierId: auth()->id() ?? 1,
                customerInfo: [
                    'name' => $this->customerName,
                    'phone' => $this->customerPhone,
                    'id_number' => $this->customerId,
                ]
            );

            Notification::make()->success()->title('Venta Completada')->body("Venta #{$sale->id} registrada.")->send();
            $this->clearCart();
            $this->showPaymentModal = false;

            // Emitimos evento para refrescar catálogo
            $this->dispatch('sale-completed');

            // Aquí se emitiría un evento para imprimir factura
            // $this->dispatch('print-receipt', saleId: $sale->id);

        } catch (\Exception $e) {
            Notification::make()->danger()->title('Error')->body($e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.pos.cart-panel');
    }
}
