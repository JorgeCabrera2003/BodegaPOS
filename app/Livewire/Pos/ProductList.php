<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductList extends Component
{
    public string $search = '';
    public ?int $selectedCategoryId = null;

    public function selectCategory(?int $categoryId)
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function addToCart(int $productId)
    {
        $this->dispatch('product-added-to-cart', productId: $productId);
    }

    // Listener para el scanner de código de barras
    #[On('barcode-scanned')]
    public function handleBarcode(string $barcode)
    {
        $product = Product::active()->where('barcode', $barcode)->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->dispatch('notify', message: 'Producto no encontrado', type: 'error');
        }
    }

    #[On('sale-completed')]
    public function refreshProducts()
    {
        // El método vacío forzará un re-render del componente, cargando el stock actualizado de la BD.
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->get();

        $products = Product::active()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->selectedCategoryId, function ($query) {
                $query->where('category_id', $this->selectedCategoryId);
            })
            ->take(50) // Límite para rendimiento en la caja
            ->get();

        return view('livewire.pos.product-list', [
            'categories' => $categories,
            'products'   => $products,
        ]);
    }
}
