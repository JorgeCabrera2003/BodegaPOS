<?php
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Find products without an image
$products = Product::whereNull('image_path')->orWhere('image_path', '')->get();

$count = 0;
foreach ($products as $product) {
    $name = urlencode($product->name);
    // Use ui-avatars to generate a clean placeholder with initials and random colors
    $url = "https://ui-avatars.com/api/?name={$name}&background=random&color=fff&size=400&font-size=0.33&bold=true&format=png";
    
    try {
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ]
        ]);
        
        $imageContent = file_get_contents($url, false, $context);
        
        if ($imageContent) {
            $filename = 'products/' . Str::slug($product->name) . '_' . Str::random(4) . '.png';
            Storage::disk('public')->put($filename, $imageContent);
            
            $product->update(['image_path' => $filename]);
            $count++;
            echo "Generated image for: {$product->name}\n";
        } else {
            echo "Failed to generate for: {$product->name}\n";
        }
    } catch (\Exception $e) {
        echo "Error on {$product->name}: " . $e->getMessage() . "\n";
    }
}
echo "Total generated: $count products.\n";
