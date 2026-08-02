<?php
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Get products that still have the old placeholder images
$products = Product::where('image_path', 'LIKE', 'products/placeholder_%')->orWhereNull('image_path')->get();
$count = 0;

$keywords = [
    'Pasta' => 'pasta,food',
    'Harina' => 'flour,corn',
    'Azúcar' => 'sugar,crystals',
    'Café' => 'coffee,beans',
    'Atún' => 'canned,tuna',
    'Sardinas' => 'sardines,can',
    'Leche' => 'milk,carton',
    'Yogurt' => 'yogurt',
    'Margarina' => 'margarine,butter',
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
    
    // Using loremflickr to get a random realistic photo matching the keyword
    // Using 400x400 to keep it square like a POS product image
    $url = "https://loremflickr.com/400/400/" . urlencode($search) . "/all";
    
    try {
        $context = stream_context_create([
            "http" => ["header" => "User-Agent: Mozilla/5.0\r\n"]
        ]);
        
        // This blocks until the image is downloaded
        $imageContent = file_get_contents($url, false, $context);
        
        if ($imageContent) {
            // Delete old placeholder if it exists
            if ($product->image_path && Str::startsWith($product->image_path, 'products/placeholder_')) {
                Storage::disk('public')->delete($product->image_path);
            }

            $filename = 'products/real_' . Str::slug($product->name) . '_' . Str::random(4) . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $product->update(['image_path' => $filename]);
            $count++;
            echo "Downloaded real image for: {$product->name} (using $search)\n";
        }
    } catch (\Exception $e) {
        echo "Error on {$product->name}: " . $e->getMessage() . "\n";
    }
}
echo "Total updated with real photos: $count\n";
