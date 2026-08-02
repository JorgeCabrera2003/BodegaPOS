<?php

namespace App\Notifications;

use App\Models\Product;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->warning()
            ->title('⚠️ Stock Bajo: ' . $this->product->name)
            ->body(
                "El producto **{$this->product->name}** tiene solo " .
                "**{$this->product->stock_quantity}** unidades en inventario. " .
                "Umbral mínimo: {$this->product->min_stock_alert}."
            )
            ->icon('heroicon-o-exclamation-triangle')
            ->getDatabaseMessage();
    }
}
