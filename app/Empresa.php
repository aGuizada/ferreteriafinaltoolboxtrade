<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'id', 'nombre', 'direccion', 'telefono', 'email', 'nit', 'licencia'
    ];
    public function sucursal(){
        return $this->belongsTo('App\Sucursales');
    }
}
