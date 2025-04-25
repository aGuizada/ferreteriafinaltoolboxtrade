<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    
    protected $fillable = [
        'idcliente',
        'idusuario',
        'idtipo_pago',
        'idtipo_venta',
        'idcaja',
        'tipo_comprobante',
        'serie_comprobante',
        'num_comprobante',
        'fecha_hora',
        'impuesto',
        'total',
        'estado',
        'monto_recibido',
        'cambio',
        // Campos para ventas adelantadas
        'direccion_entrega',
        'telefono_contacto',
        'fecha_entrega',
        'observaciones'
    ];

    public function cliente()
    {
        return $this->belongsTo('App\Persona', 'idcliente');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function tipoPago()
    {
        return $this->belongsTo('App\TipoPago', 'idtipo_pago');
    }

    public function caja()
    {
        return $this->belongsTo('App\Caja', 'idcaja');
    }

    public function detalles()
    {
        return $this->hasMany('App\DetalleVenta', 'idventa');
    }

    public function credito()
    {
        return $this->hasOne('App\CreditoVenta', 'idventa');
    }
}