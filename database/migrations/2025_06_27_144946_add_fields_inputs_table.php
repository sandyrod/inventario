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
            $table->date('dateinput')
                ->default(DB::raw('(CURRENT_DATE)'))
                ->comment('Fecha de la nota')
                ->after('description');
            $table->date('datepaid')
                ->nullable()
                ->comment('Fecha en la que se debe pagar')
                ->after('dateinput');
            $table->enum('statuspaid', ['pendiente', 'pagado'])
                ->default('pendiente')
                ->comment('Estado del pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->dropColumn(['dateinput', 'datepaid', 'statuspaid']);
        });
    }
};
