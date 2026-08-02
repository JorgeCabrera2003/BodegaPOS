<?php
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Support\Str;

// 1. Crear Categorías
$catAlimentos = Category::firstOrCreate(['name' => 'Alimentos'], ['slug' => 'alimentos', 'description' => 'Víveres y alimentos en general', 'is_active' => true]);
$catBebidas = Category::firstOrCreate(['name' => 'Bebidas'], ['slug' => 'bebidas', 'description' => 'Bebidas alcohólicas y no alcohólicas', 'is_active' => true]);
$catLimpieza = Category::firstOrCreate(['name' => 'Limpieza'], ['slug' => 'limpieza', 'description' => 'Artículos de limpieza del hogar', 'is_active' => true]);
$catSnacks = Category::firstOrCreate(['name' => 'Snacks & Golosinas'], ['slug' => 'snacks', 'description' => 'Chucherías varias', 'is_active' => true]);

// 2. Crear un Proveedor Base
$supplier = Supplier::firstOrCreate(
    ['rif' => 'J-12345678-9'],
    ['name' => 'Distribuidora Mayorista VZLA', 'contact_name' => 'Pedro Pérez', 'phone' => '0414-1234567', 'is_active' => true]
);

// 3. Crear Productos de prueba
$productos = [
    [
        'name' => 'Harina PAN Blanco 1Kg',
        'sku' => 'HPAN-001',
        'barcode' => '7591016000010',
        'category_id' => $catAlimentos->id,
        'base_price_usd' => 1.20,
        'stock_quantity' => 150,
        'min_stock_alert' => 20,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Arroz Mary Tradicional 1Kg',
        'sku' => 'MARY-001',
        'barcode' => '7591111000020',
        'category_id' => $catAlimentos->id,
        'base_price_usd' => 1.15,
        'stock_quantity' => 200,
        'min_stock_alert' => 30,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Coca-Cola 2 Litros Retornable',
        'sku' => 'COCA-001',
        'barcode' => '7591234567891',
        'category_id' => $catBebidas->id,
        'base_price_usd' => 1.50,
        'stock_quantity' => 45,
        'min_stock_alert' => 10,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Cerveza Polar Pilsen Lata 355ml',
        'sku' => 'POL-001',
        'barcode' => '7599876543210',
        'category_id' => $catBebidas->id,
        'base_price_usd' => 0.85,
        'stock_quantity' => 500,
        'min_stock_alert' => 50,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Detergente en Polvo Ace 1Kg',
        'sku' => 'ACE-001',
        'barcode' => '7591122334455',
        'category_id' => $catLimpieza->id,
        'base_price_usd' => 2.50,
        'stock_quantity' => 30,
        'min_stock_alert' => 15,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Galletas Susy',
        'sku' => 'SUSY-001',
        'barcode' => '7592233445566',
        'category_id' => $catSnacks->id,
        'base_price_usd' => 0.60,
        'stock_quantity' => 80,
        'min_stock_alert' => 10,
        'apply_igtf' => true,
    ],
    [
        'name' => 'Queso Paisa (Porción 500g)',
        'sku' => 'QPAISA-500',
        'barcode' => '000000010001',
        'category_id' => $catAlimentos->id,
        'base_price_usd' => 4.20,
        'stock_quantity' => 5, // bajo stock para probar UI
        'min_stock_alert' => 10,
        'apply_igtf' => false, // exento
    ],
    [
        'name' => 'Mantequilla Mavesa 500g',
        'sku' => 'MAV-500',
        'barcode' => '7593344556677',
        'category_id' => $catAlimentos->id,
        'base_price_usd' => 2.10,
        'stock_quantity' => 0, // agotado para probar UI
        'min_stock_alert' => 5,
        'apply_igtf' => true,
    ]
];

foreach ($productos as $prod) {
    Product::firstOrCreate(
        ['sku' => $prod['sku']],
        [
            'name' => $prod['name'],
            'barcode' => $prod['barcode'],
            'description' => 'Producto de prueba',
            'category_id' => $prod['category_id'],
            'supplier_id' => $supplier->id,
            'base_price_usd' => $prod['base_price_usd'],
            // El Observer y CurrencyService asignarán el price_ves automáticamente
            'price_ves' => app(\App\Services\CurrencyService::class)->usdToVes($prod['base_price_usd']),
            'stock_quantity' => $prod['stock_quantity'],
            'min_stock_alert' => $prod['min_stock_alert'],
            'is_active' => true,
            'apply_igtf' => $prod['apply_igtf'],
        ]
    );
}

echo "✅ Productos de prueba creados exitosamente.\n";
