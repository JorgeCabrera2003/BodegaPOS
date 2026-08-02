<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de tasas de cambio — INMUTABLE (append-only).
     * Nunca se hacen UPDATE en esta tabla.
     * Cada fila representa el tipo de cambio oficial BCV en un instante exacto.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3)->default('USD')->comment('Moneda origen: USD');
            $table->decimal('rate', 15, 6)->comment('Tasa BCV: 1 USD = X VES');
            $table->enum('source', ['BCV', 'manual'])->default('BCV');
            $table->string('notes')->nullable()->comment('Notas opcionales (ej: "Tasa mañana")');
            $table->timestamp('created_at')->useCurrent()->comment('Momento exacto del registro');
            // NO updated_at — esta tabla es append-only

            $table->index('created_at');
            $table->index('currency_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
