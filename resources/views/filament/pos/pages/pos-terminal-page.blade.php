<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; height: 100%;">
        
        <!-- Panel Izquierdo: Buscador y Lista de Productos -->
        <div style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1rem; height: calc(100vh - 10rem); overflow-y: hidden;">
            @livewire('pos.product-list')
        </div>

        <!-- Panel Derecho: Carrito y Pago -->
        <div style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1rem; height: calc(100vh - 10rem); display: flex; flex-direction: column; justify-content: space-between;">
            @livewire('pos.cart-panel')
        </div>

    </div>

    <style>
        /* CSS para responsividad rápida */
        @media (max-width: 768px) {
            div[style*="grid-template-columns: 2fr 1fr"] {
                grid-template-columns: 1fr !important;
                height: auto !important;
            }
            div[style*="height: calc(100vh - 10rem)"] {
                height: 80vh !important;
            }
        }
    </style>
</x-filament-panels::page>
