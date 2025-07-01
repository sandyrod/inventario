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
        Schema::table('outputs', function (Blueprint $table) {
            $table->unsignedBigInteger('paymentterm_id')
                  ->default(1)
                  ->after('description');
            $table->unsignedBigInteger('paymentform_id')
                  ->default(1)
                  ->after('paymentterm_id');
            $table->unsignedBigInteger('status_id')
                  ->default(1)
                  ->after('paymentform_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropColumn('paymentterm_id','paymentform_id', 'status_id');
        });
    }
};
