<div style="display: flex; flex-direction: column; height: 100%;">
    <!-- Buscador usando UI de Filament -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
        <div style="flex: 1;">
            <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre, SKU o escanear código..."
                    autofocus
                    id="pos-search-input"
                    style="font-size: 1.125rem; padding: 0.75rem;"
                />
            </x-filament::input.wrapper>
        </div>
    </div>

    <!-- Filtro de Categorías -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem;">
        <x-filament::button 
            wire:click="selectCategory(null)"
            color="{{ $selectedCategoryId === null ? 'primary' : 'gray' }}"
            style="border-radius: 9999px;"
        >
            Todas
        </x-filament::button>
        @foreach($categories as $category)
            <x-filament::button 
                wire:click="selectCategory({{ $category->id }})"
                color="{{ $selectedCategoryId === $category->id ? 'primary' : 'gray' }}"
                style="border-radius: 9999px;"
            >
                {{ $category->name }}
            </x-filament::button>
        @endforeach
    </div>

    <!-- Grilla de Productos -->
    <div style="flex: 1; overflow-y: auto; padding-bottom: 5rem;">
        @if($products->isEmpty())
            <div style="text-align: center; padding: 3rem 0;">
                <x-filament::icon
                    icon="heroicon-o-inbox"
                    style="width: 3rem; height: 3rem; margin: 0 auto; color: var(--gray-400);"
                />
                <h3 style="margin-top: 0.5rem; font-weight: 500; color: var(--gray-900);">No hay productos</h3>
                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: var(--gray-500);">Prueba con otra búsqueda.</p>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;">
                @foreach($products as $product)
                    <div 
                        wire:click="addToCart({{ $product->id }})"
                        style="background: white; border: 1px solid var(--gray-200); border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 0.75rem; display: flex; flex-direction: column; position: relative; cursor: pointer; user-select: none;"
                    >
                        <!-- Stock Badge -->
                        @if($product->stock_quantity <= 0)
                            <div style="position: absolute; top: 0.5rem; right: 0.5rem; z-index: 10;">
                                <x-filament::badge color="danger">Agotado</x-filament::badge>
                            </div>
                        @elseif($product->is_low_stock)
                            <div style="position: absolute; top: 0.5rem; right: 0.5rem; z-index: 10;">
                                <x-filament::badge color="warning">Quedan {{ $product->stock_quantity }}</x-filament::badge>
                            </div>
                        @endif

                        <!-- Imagen -->
                        <div style="height: 6rem; background: var(--gray-50); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" style="object-fit: contain; height: 100%; width: 100%; border-radius: 0.5rem;">
                            @else
                                <x-filament::icon
                                    icon="heroicon-o-photo"
                                    style="width: 3rem; height: 3rem; color: var(--gray-300);"
                                />
                            @endif
                        </div>

                        <!-- Detalles -->
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <h3 style="font-size: 0.875rem; font-weight: 500; color: var(--gray-900); line-height: 1.2; margin-bottom: 0.5rem;" title="{{ $product->name }}">
                                {{ \Illuminate\Support\Str::limit($product->name, 40) }}
                            </h3>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">Bs. {{ number_format($product->price_ves, 2, ',', '.') }}</div>
                                <div style="font-size: 1.125rem; font-weight: 700; color: rgb(var(--primary-600));">${{ number_format($product->base_price_usd, 2) }}</div>
                            </div>
                        </div>

                        <!-- Bloqueo si no hay stock -->
                        @if($product->stock_quantity <= 0)
                            <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.6); border-radius: 0.75rem;"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Script de Scanner Global -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            let barcode = '';
            let timer;
            
            document.addEventListener('keypress', function(e) {
                if (e.target.tagName === 'INPUT' && e.target.id !== 'pos-search-input' && e.target.type !== 'text') return;
                
                if (e.key === 'Enter') {
                    if (barcode.length > 3) {
                        @this.dispatch('barcode-scanned', { barcode: barcode });
                    }
                    barcode = '';
                    
                    if(e.target.id === 'pos-search-input') {
                        e.target.value = '';
                        @this.set('search', '');
                    }
                } else {
                    barcode += e.key;
                    clearTimeout(timer);
                    timer = setTimeout(() => { barcode = ''; }, 500);
                }
            });
        });
    </script>
</div>
