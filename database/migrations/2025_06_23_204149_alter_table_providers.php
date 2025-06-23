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
        Schema::table('inputs', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')
                  ->default(1)
                  ->after('user_id'); // Colócalo después del campo que prefieras
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->dropColumn('provider_id');
        });
    }
};
