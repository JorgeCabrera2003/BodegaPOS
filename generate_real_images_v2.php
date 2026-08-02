<?php
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$originalImages = [
    'products/arroz_mary.png',
    'products/cerveza_polar.png',
    'products/coca_cola.png',
    'products/detergente_ace.png',
    'products/galletas_susy.png',
    'products/harina_pan.png',
    'products/mantequilla_mavesa.png',
    'products/queso_paisa.png',
];

$products = Product::whereNotIn('image_path', $originalImages)->orWhereNull('image_path')->get();
$count = 0;

$keywords = [
    'Pasta' => 'pasta',
    'Harina' => 'flour,corn',
    'Azúcar' => 'sugar',
    'Café' => 'coffee,beans',
    'Atún' => 'canned,tuna',
    'Sardinas' => 'sardines',
    'Leche' => 'milk,carton',
    'Yogurt' => 'yogurt',
    'Margarina' => 'margarine',
    'Pan' => 'bread,sliced',
    'Chocolate' => 'chocolate,bar',
    'Papas' => 'potato,chips',
    'Refresco' => 'soda,beverage',
    'Agua' => 'water,bottle',
    'Jugo' => 'juice,drink',
    'Lavaplatos' => 'dish,soap',
    'Cloro' => 'bleach,bottle',
    'Desinfectante' => 'cleaner,bottle',
    'Jabón' => 'soap,bar',
    'Desodorante' => 'deodorant',
    'Crema Dental' => 'toothpaste',
    'Champú' => 'shampoo,bottle',
    'Papel' => 'toilet,paper'
];

foreach ($products as $product) {
    $search = 'supermarket,product'; // fallback
    foreach($keywords as $k => $v) {
        if(stripos($product->name, $k) !== false) {
            $search = $v;
            break;
        }
    }
    
    $url = "https://loremflickr.com/400/400/" . urlencode($search) . "/all";
    
    try {
        $context = stream_context_create([
            "http" => ["header" => "User-Agent: Mozilla/5.0\r\n"]
        ]);
        
        $imageContent = @file_get_contents($url, false, $context);
        
        if ($imageContent) {
            // cleanup old avatar
            if ($product->image_path && !in_array($product->image_path, $originalImages)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $filename = 'products/real_' . Str::slug($product->name) . '_' . Str::random(4) . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $product->update(['image_path' => $filename]);
            $count++;
            echo "Downloaded real image for: {$product->name} (using $search)\n";
        }
    } catch (\Exception $e) {
        echo "Error on {$product->name}\n";
    }
}
echo "Total updated with real photos: $count\n";
