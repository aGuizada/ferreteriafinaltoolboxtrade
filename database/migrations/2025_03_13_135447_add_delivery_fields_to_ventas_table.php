<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryFieldsToVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'direccion_entrega')) {
                $table->string('direccion_entrega')->nullable();
            }
            if (!Schema::hasColumn('ventas', 'telefono_contacto')) {
                $table->string('telefono_contacto')->nullable();
            }
            if (!Schema::hasColumn('ventas', 'fecha_entrega')) {
                $table->date('fecha_entrega')->nullable();
            }
            if (!Schema::hasColumn('ventas', 'observaciones')) {
                $table->text('observaciones')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'direccion_entrega')) {
                $table->dropColumn('direccion_entrega');
            }
            if (Schema::hasColumn('ventas', 'telefono_contacto')) {
                $table->dropColumn('telefono_contacto');
            }
            if (Schema::hasColumn('ventas', 'fecha_entrega')) {
                $table->dropColumn('fecha_entrega');
            }
            if (Schema::hasColumn('ventas', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }
}