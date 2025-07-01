<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('paymentforms')->insert([
            'name' => 'Efectivo Bs'
        ]);
        DB::table('paymentforms')->insert([
            'name' => 'Biopago'
        ]);
        DB::table('paymentforms')->insert([
            'name' => 'Punto de Venta'
        ]);
        DB::table('paymentforms')->insert([
            'name' => 'Pago movil'
        ]);
        DB::table('paymentforms')->insert([
            'name' => 'Efectivo $'
        ]);
        DB::table('paymentforms')->insert([
            'name' => 'Transferencia'
        ]);

        DB::table('paymentterms')->insert([
            'name' => 'Contado'
        ]);
        DB::table('paymentterms')->insert([
            'name' => 'Credito'
        ]);
    }
}
