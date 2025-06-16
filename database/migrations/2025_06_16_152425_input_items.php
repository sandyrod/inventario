<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('input_items', function (Blueprint $table) {
            $table->id();
            
            // Clave foránea a la compra (input)
            $table->foreignId('input_id')->constrained('inputs')->onDelete('cascade');
            
            // Clave foránea al producto
            $table->foreignId('product_id')->constrained('products');
            
            // Datos del item
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2)->storedAs('quantity * unit_price');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices para mejorar performance
            $table->index(['input_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_items');
    }
};
