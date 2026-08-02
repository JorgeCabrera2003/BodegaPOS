<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();

            // Split Payment: múltiples pagos por venta
            $table->enum('payment_method', [
                'cash_usd',      // Efectivo dólares
                'cash_ves',      // Efectivo bolívares
                'pagomovil',     // Pago Móvil Interbancario
                'pos_terminal',  // Punto de venta (tarjeta)
                'zelle',         // Zelle (dólares digitales)
                'binance',       // Binance Pay / USDT
                'transfer_ves',  // Transferencia bancaria en bolívares
            ]);

            $table->decimal('amount_usd', 10, 2)->default(0)->comment('Monto equivalente en USD');
            $table->decimal('amount_ves', 15, 2)->default(0)->comment('Monto en bolívares');

            // Campos de validación para métodos digitales
            $table->string('reference_number', 100)->nullable()->comment('Número de referencia (Pago Móvil, Zelle, etc.)');
            $table->string('bank_name', 100)->nullable()->comment('Banco emisor (para Pago Móvil)');
            $table->string('phone_number', 20)->nullable()->comment('Teléfono del pagador (Pago Móvil)');
            $table->string('id_number', 20)->nullable()->comment('Cédula del pagador (Pago Móvil)');

            $table->boolean('is_verified')->default(false)->comment('Confirmación manual del pago digital');
            $table->timestamps();

            $table->index('sale_id');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
