<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique()->nullable()->comment('Código interno del producto');
            $table->string('barcode', 100)->unique()->nullable()->comment('Código de barras EAN-13/UPC (futuro scanner)');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Precio SIEMPRE en USD (moneda dura) para protección contra devaluación
            $table->decimal('base_price_usd', 10, 2)->comment('Precio base en dólares');
            $table->decimal('cost_price_usd', 10, 2)->nullable()->comment('Precio de costo en USD');

            // Inventario
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(5)->comment('Umbral para alerta de stock bajo');
            $table->integer('max_stock')->nullable()->comment('Stock máximo deseado');

            $table->boolean('is_active')->default(true);
            $table->boolean('apply_igtf')->default(false)->comment('Si aplica IGTF en pago en divisas');
            $table->timestamps();
            $table->softDeletes();

            // Índices B-Tree para búsquedas rápidas en el POS
            $table->index('barcode');
            $table->index('sku');
            $table->index('is_active');
            $table->index('stock_quantity');
            $table->fullText('name', 'products_name_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
