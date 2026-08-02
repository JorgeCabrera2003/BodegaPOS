<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->checkLowStock($product);
    }

    public function updated(Product $product): void
    {
        if ($product->isDirty('stock_quantity')) {
            $this->checkLowStock($product);
        }
    }

    private function checkLowStock(Product $product): void
    {
        if (
            $product->stock_quantity <= $product->min_stock_alert &&
            $product->stock_quantity >= 0
        ) {
            User::role(['super_admin', 'admin'])
                ->get()
                ->each(fn (User $admin) => $admin->notify(new LowStockNotification($product)));
        }
    }
}
