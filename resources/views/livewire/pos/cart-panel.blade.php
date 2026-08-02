<div style="display: flex; flex-direction: column; height: 100%; position: relative;">
    <!-- Header Carrito -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
            <x-filament::icon icon="heroicon-o-shopping-cart" style="width: 1.5rem; height: 1.5rem; color: rgb(var(--primary-600));" />
            Orden Actual
        </h2>
        @if(!empty($cart))
            <x-filament::button wire:click="clearCart" color="danger" variant="text" size="sm" icon="heroicon-o-trash">
                Vaciar
            </x-filament::button>
        @endif
    </div>

    <!-- Lista de Items -->
    <div style="flex: 1; overflow-y: auto; padding-right: 0.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
        @forelse($cart as $index => $item)
            <div style="display: flex; justify-content: space-between; align-items: flex-start; background: var(--gray-50); border-radius: 0.5rem; padding: 0.75rem; border: 1px solid var(--gray-100);">
                <div style="flex: 1; padding-right: 0.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--gray-800); line-height: 1.2;">{{ $item['name'] }}</h4>
                    <div style="color: rgb(var(--primary-600)); font-weight: 500; font-size: 0.875rem; margin-top: 0.25rem;">${{ number_format($item['unit_price_usd'], 2) }}</div>
                </div>
                
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                    <div style="font-weight: 700; color: var(--gray-900);">${{ number_format($item['unit_price_usd'] * $item['quantity'], 2) }}</div>
                    
                    <div style="display: flex; align-items: center; background: white; border: 1px solid var(--gray-200); border-radius: 0.5rem; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <button wire:click="decrementQuantity({{ $index }})" style="padding: 0.25rem 0.5rem; background: var(--gray-100); border: none; cursor: pointer;">
                            <x-filament::icon icon="heroicon-o-minus" style="width: 1rem; height: 1rem; color: var(--gray-700);" />
                        </button>
                        <span style="padding: 0.25rem 0.75rem; font-size: 0.875rem; font-weight: 700; min-width: 2.5rem; text-align: center;">{{ $item['quantity'] }}</span>
                        <button wire:click="incrementQuantity({{ $index }})" style="padding: 0.25rem 0.5rem; background: var(--gray-100); border: none; cursor: pointer;">
                            <x-filament::icon icon="heroicon-o-plus" style="width: 1rem; height: 1rem; color: var(--gray-700);" />
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--gray-400);">
                <x-filament::icon icon="heroicon-o-shopping-bag" style="width: 4rem; height: 4rem; margin-bottom: 0.5rem; opacity: 0.2;" />
                <p>El carrito está vacío</p>
                <p style="font-size: 0.75rem; margin-top: 0.25rem;">Escanea un producto o búscalo.</p>
            </div>
        @endforelse
    </div>

    <!-- Totales -->
    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--gray-300);">
        <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">
            <span>Subtotal</span>
            <span>${{ number_format($this->subtotal_usd, 2) }}</span>
        </div>
        
        @if($this->igtf_usd > 0)
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: rgb(var(--warning-600)); margin-bottom: 0.25rem;">
                <span>IGTF (3%)</span>
                <span>+ ${{ number_format($this->igtf_usd, 2) }}</span>
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--gray-200);">
            <span style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900);">Total</span>
            <div style="text-align: right;">
                <div style="font-size: 0.875rem; color: var(--gray-500); font-weight: 500;">Bs. {{ number_format($this->total_ves, 2, ',', '.') }}</div>
                <div style="font-size: 1.875rem; font-weight: 900; color: rgb(var(--primary-600));">${{ number_format($this->total_usd, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Botón Pagar -->
    <div style="margin-top: 1rem;">
        <x-filament::button 
            wire:click="openPaymentModal"
            disabled="{{ empty($cart) }}"
            size="xl"
            color="primary"
            icon="heroicon-o-banknotes"
            style="width: 100%; justify-content: center; font-size: 1.125rem; font-weight: 700;"
        >
            PROCESAR PAGO
        </x-filament::button>
    </div>

    <!-- ─── Modal de Pago ────────────────────────────────────────────────── -->
    @if($showPaymentModal)
        <div style="position: fixed; inset: 0; background: rgba(17, 24, 39, 0.75); z-index: 50; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 1rem;">
            <div style="background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 48rem; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
                
                <div style="padding: 1rem; border-bottom: 1px solid var(--gray-200); background: var(--gray-50); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--gray-800);">Completar Venta</h3>
                    <button wire:click="$set('showPaymentModal', false)" style="background: transparent; border: none; cursor: pointer; color: var(--gray-500);">
                        <x-filament::icon icon="heroicon-o-x-mark" style="width: 1.5rem; height: 1.5rem;" />
                    </button>
                </div>

                <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        
                        <!-- Columna Izquierda: Formulario -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Cliente -->
                            <div style="background: var(--gray-50); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--gray-200);">
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--gray-700); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Cliente (Opcional)</label>
                                <div style="margin-bottom: 0.5rem;">
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" wire:model="customerName" placeholder="Nombre" />
                                    </x-filament::input.wrapper>
                                </div>
                                <div>
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" wire:model="customerId" placeholder="Cédula/RIF" />
                                    </x-filament::input.wrapper>
                                </div>
                            </div>

                            <!-- Agregar Pago -->
                            <div style="background: rgb(var(--info-50)); padding: 1rem; border-radius: 0.75rem; border: 1px solid rgb(var(--info-200));">
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: rgb(var(--info-800)); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Añadir Pago</label>
                                
                                <div style="margin-bottom: 0.75rem;">
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model.live="currentPaymentMethod">
                                            <option value="cash_usd">💵 Efectivo USD (Aplica IGTF)</option>
                                            <option value="cash_ves">💵 Efectivo Bs.</option>
                                            <option value="pagomovil">📱 Pago Móvil</option>
                                            <option value="pos_terminal">💳 Punto de Venta</option>
                                            <option value="zelle">🇺🇸 Zelle (Aplica IGTF)</option>
                                            <option value="binance">🟡 Binance Pay (Aplica IGTF)</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>

                                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                                    <div style="flex: 1;">
                                        <x-filament::input.wrapper prefix="$">
                                            <x-filament::input type="number" wire:model="currentPaymentAmount" step="0.01" style="font-weight: 700; font-size: 1.125rem;" />
                                        </x-filament::input.wrapper>
                                    </div>
                                    <x-filament::button wire:click="addPayment" color="info" size="lg">
                                        Añadir
                                    </x-filament::button>
                                </div>

                                @if(in_array($currentPaymentMethod, ['pagomovil', 'pos_terminal', 'zelle', 'binance']))
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" wire:model="currentPaymentReference" placeholder="Nro de Referencia" />
                                    </x-filament::input.wrapper>
                                @endif
                                
                                @error('currentPaymentAmount') <span style="color: rgb(var(--danger-500)); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha: Resumen -->
                        <div style="display: flex; flex-direction: column;">
                            <div style="background: var(--gray-900); color: white; padding: 1.25rem; border-radius: 1rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; overflow: hidden; margin-bottom: 1rem;">
                                <span style="color: var(--gray-400); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Por Cobrar</span>
                                <span style="font-size: 3rem; font-weight: 900; color: white;">${{ number_format($this->remaining_usd, 2) }}</span>
                                <span style="color: rgb(var(--primary-400)); font-weight: 500; margin-top: 0.25rem;">Bs. {{ number_format(app(\App\Services\CurrencyService::class)->usdToVes($this->remaining_usd), 2, ',', '.') }}</span>
                            </div>

                            <!-- Lista de pagos registrados -->
                            <div style="flex: 1; overflow-y: auto; margin-bottom: 1rem; border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 0.75rem; background: var(--gray-50);">
                                <h4 style="font-size: 0.75rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">Pagos Registrados</h4>
                                @forelse($payments as $index => $payment)
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; padding: 0.5rem 0; border-bottom: 1px solid var(--gray-100);">
                                        <div>
                                            <span style="font-weight: 700; color: var(--gray-800);">{{ \App\Models\Payment::$methodLabels[$payment['method']] ?? $payment['method'] }}</span>
                                            @if($payment['reference'])
                                                <div style="font-size: 0.75rem; color: var(--gray-500);">Ref: {{ $payment['reference'] }}</div>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <span style="font-weight: 700;">${{ number_format($payment['amount_usd'], 2) }}</span>
                                            <button wire:click="removePayment({{ $index }})" style="background: transparent; border: none; cursor: pointer; color: rgb(var(--danger-500));">
                                                <x-filament::icon icon="heroicon-o-x-circle" style="width: 1.25rem; height: 1.25rem;" />
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div style="color: var(--gray-400); text-align: center; font-size: 0.875rem; padding: 1rem 0;">Ningún pago registrado</div>
                                @endforelse
                            </div>
                            
                            @if($this->change_usd > 0)
                                <div style="background: rgb(var(--success-100)); border: 1px solid rgb(var(--success-200)); padding: 0.75rem; border-radius: 0.75rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <span style="font-weight: 700; color: rgb(var(--success-800)); text-transform: uppercase; font-size: 0.875rem;">Vuelto a entregar</span>
                                    <span style="font-weight: 900; color: rgb(var(--success-700)); font-size: 1.25rem;">${{ number_format($this->change_usd, 2) }}</span>
                                </div>
                            @endif

                            <x-filament::button 
                                wire:click="completeSale"
                                disabled="{{ $this->remaining_usd > 0 }}"
                                color="success"
                                size="xl"
                                icon="heroicon-o-check-circle"
                                style="width: 100%; justify-content: center; font-size: 1.125rem; font-weight: 700;"
                            >
                                COMPLETAR VENTA
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
