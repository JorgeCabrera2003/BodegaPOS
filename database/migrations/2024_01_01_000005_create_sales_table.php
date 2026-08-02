<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cashier_id')->constrained('users')->comment('Cajero que procesó la venta');
            $table->foreignId('exchange_rate_id')->constrained('exchange_rates')->comment('Tasa vigente en el momento EXACTO de la venta');

            // Datos del cliente (opcionales para ventas rápidas)
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_id_number', 20)->nullable()->comment('Cédula/RIF del cliente');

            // Totales — guardados históricos, NO calculados en vuelo
            $table->decimal('subtotal_usd', 10, 2)->default(0);
            $table->decimal('igtf_usd', 10, 2)->default(0)->comment('IGTF 3% si aplica');
            $table->decimal('total_usd', 10, 2)->default(0);
            $table->decimal('total_ves', 15, 2)->default(0)->comment('Total en bolívares al momento de la venta');

            $table->decimal('amount_paid_usd', 10, 2)->default(0)->comment('Total recibido del cliente en equivalente USD');
            $table->decimal('change_usd', 10, 2)->default(0)->comment('Vuelto en USD');

            $table->enum('status', ['pending', 'completed', 'refunded', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cashier_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
