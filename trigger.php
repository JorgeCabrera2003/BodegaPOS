<?php
$obs = app(\App\Observers\ProductObserver::class);
$obs->created(\App\Models\Product::where('sku', 'MAV-500')->first());
$obs->created(\App\Models\Product::where('sku', 'QPAISA-500')->first());
echo "Notifications triggered.\n";
