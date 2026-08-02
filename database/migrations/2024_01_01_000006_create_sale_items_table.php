<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->integer('quantity');
            $table->string('product_name', 200)->comment('Nombre histórico del producto al momento de la venta');

            // Precios HISTÓRICOS — no cambian si el producto se actualiza después
            $table->decimal('historical_unit_price_usd', 10, 2)->comment('Precio unitario USD al momento de venta');
            $table->decimal('historical_unit_price_ves', 15, 2)->comment('Precio unitario VES al momento de venta');
            $table->decimal('subtotal_usd', 10, 2);
            $table->decimal('subtotal_ves', 15, 2);

            $table->timestamps();

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
