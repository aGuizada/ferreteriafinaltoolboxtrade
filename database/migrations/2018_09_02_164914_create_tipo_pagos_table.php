<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTipoPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_pagos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('codigoClasificador');
            $table->string('nombre_tipo_pago', 250)->nullable();
            $table->timestamps();
        });
        DB::table('tipo_pagos')->insert([
            [
                'codigoClasificador' => 1,
                'nombre_tipo_pago' => 'EFECTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigoClasificador' => 2,
                'nombre_tipo_pago' => 'TARJETA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigoClasificador' => 3,
                'nombre_tipo_pago' => 'CHEQUE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigoClasificador' => 4,
                'nombre_tipo_pago' => 'QR SIMPLE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_pagos');
    }
}
