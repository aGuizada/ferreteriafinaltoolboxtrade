<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $fillable = [
        'idcategoria', 'idmarca', 'idindustria', 'idgrupo', 'idmedida', 'idproveedor', 'codigo', 'nombre', 'costo_compra', 'nombre_generico',
        'unidad_envase',
        'precio_list_unid',
        'precio_costo_unid',
        'precio_costo_paq',
        'precio_venta',
        'precio_uno',
        'precio_dos',
        'precio_tres',
        'precio_cuatro',
        'stock', 'descripcion',
        'condicion',
        'fotografia',
        //agregado el 26 de 01 de 2024
        'codigo_alfanumerico',
        'descripcion_fabrica',

    ];
    public function categoria()
    {
        return $this->belongsTo('App\Categoria');
    }
    public function precios()
    {
        return $this->hasOne(Precio::class);
    }
    public function inventario()
    {
        return $this->hasMany('App\Inventario');
    }
    public function medida()
    {
        return $this->belongsTo('App\Medida');
    }
 //Relationships Muchos a Muchos  agregado el 26 de 01 de 2024
    public function promociones() {
    	return $this->belongsToMany(Promocion::class,'promociones_articulos');
    }
}
