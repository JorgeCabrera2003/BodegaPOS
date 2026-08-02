<?php
use App\Models\Product;

$images = [
    'HPAN-001' => 'products/harina_pan.png',
    'MARY-001' => 'products/arroz_mary.png',
    'COCA-001' => 'products/coca_cola.png',
    'POL-001' => 'products/cerveza_polar.png',
    'ACE-001' => 'products/detergente_ace.png',
    'SUSY-001' => 'products/galletas_susy.png',
    'QPAISA-500' => 'products/queso_paisa.png',
    'MAV-500' => 'products/mantequilla_mavesa.png',
];

foreach ($images as $sku => $imagePath) {
    Product::where('sku', $sku)->update(['image_path' => $imagePath]);
}

echo "✅ Imágenes enlazadas correctamente en la base de datos.\n";
