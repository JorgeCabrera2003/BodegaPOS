<?php
use App\Models\Product;

$map = [
    'Arroz Mary' => 'products/arroz_mary.png',
    'Cerveza Polar' => 'products/cerveza_polar.png',
    'Coca-Cola' => 'products/coca_cola.png',
    'Detergente en Polvo Ace' => 'products/detergente_ace.png',
    'Galletas Susy' => 'products/galletas_susy.png',
    'Harina PAN' => 'products/harina_pan.png',
    'Mantequilla Mavesa' => 'products/mantequilla_mavesa.png',
    'Queso Paisa' => 'products/queso_paisa.png',
];

$count = 0;
foreach ($map as $keyword => $imagePath) {
    $updated = Product::where('name', 'LIKE', '%' . $keyword . '%')->update(['image_path' => $imagePath]);
    if ($updated > 0) {
        $count++;
        echo "Updated: $keyword -> $imagePath\n";
    }
}
echo "Total updated: $count products.\n";
