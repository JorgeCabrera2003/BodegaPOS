<?php
use App\Models\Product;
$products = Product::where('image_path', 'LIKE', 'products/placeholder_%')->orWhereNull('image_path')->get();
$list = [];
foreach($products as $p) {
    $list[] = $p->id . '|' . $p->name;
}
echo implode("\n", $list);
