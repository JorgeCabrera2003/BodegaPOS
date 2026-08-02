<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registered_by')->constrained('users')->comment('Usuario que registró el gasto');
            $table->foreignId('exchange_rate_id')->constrained('exchange_rates')->comment('Tasa al momento del registro');

            $table->enum('category', [
                'supplier',    // Pago a proveedor
                'utilities',   // Servicios (luz, agua, internet, CANTV)
                'payroll',     // Nómina / sueldos
                'rent',        // Alquiler del local
                'maintenance', // Mantenimiento y reparaciones
                'transport',   // Flete y transporte
                'tax',         // Impuestos y tasas (SENIAT, Alcaldía)
                'other',       // Otros
            ]);

            $table->string('description', 300)->comment('Descripción detallada del gasto');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            $table->decimal('amount_usd', 10, 2)->default(0)->comment('Monto en USD');
            $table->decimal('amount_ves', 15, 2)->default(0)->comment('Monto en VES');

            $table->string('receipt_path')->nullable()->comment('Comprobante/factura escaneada');
            $table->date('expense_date')->comment('Fecha del gasto (puede ser diferente al registro)');
            $table->boolean('is_recurring')->default(false)->comment('Gasto recurrente mensual');

            $table->timestamps();

            $table->index('category');
            $table->index('expense_date');
            $table->index('registered_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
