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
        Schema::table('input_items', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('notes');
            $table->decimal('unit_price_with_discount', 12, 2)->default(0)->after('discount');
            $table->decimal('profit_percent', 12, 2)->default(0)->after('unit_price_with_discount');
            $table->decimal('sales_price', 12, 2)->default(0)->after('profit_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_items', function (Blueprint $table) {
            $table->dropColumn('discount', 'unit_price_with_discount', 'profit_percent', 'sales_price');
        });
    }
};
