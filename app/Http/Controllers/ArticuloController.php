<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Inventario;
use App\Articulo;
use App\Imports\ArticuloImport;

use App\Precio;

class ArticuloController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->join('industrias', 'articulos.idindustria', '=', 'industrias.id')
                ->join('marcas', 'articulos.idmarca', '=', 'marcas.id')
                ->join('grupos', 'articulos.idgrupo', '=', 'grupos.id')
                ->join('medidas', 'articulos.idmedida', '=', 'medidas.id')

                ->select(
                    'articulos.id',
                    'articulos.idcategoria',
                    'articulos.idproveedor',
                    //aumente 7 julio
                    'articulos.idindustria',
                    //aumente 7 julio
                    'articulos.idmarca',
                    //aumente 7 julio
                    'articulos.idgrupo',
                    //aumente 7 julio
                    'articulos.idmedida',
                    'articulos.codigo',
                    'articulos.nombre',
                    'articulos.nombre_generico',
                    'articulos.costo_compra',
                    //aumente12julio
                    'articulos.vencimiento',
                    'articulos.unidad_envase',
                    'articulos.precio_list_unid',
                    'articulos.precio_costo_unid',
                    'articulos.precio_costo_paq',

                    'categorias.nombre as nombre_categoria',
                    'industrias.nombre as nombre_industria',
                    'marcas.nombre as nombre_marca',
                    'grupos.nombre_grupo',
                    'medidas.descripcion_medida',
                    //aumente 5 julio

                    'articulos.precio_uno',
                    'articulos.precio_dos',
                    'articulos.precio_tres',
                    'articulos.precio_cuatro',

                    'articulos.precio_venta',
                    'articulos.stock',
                    'personas.nombre as nombre_proveedor',
                    'articulos.descripcion',
                    'articulos.condicion',
                    'articulos.fotografia',
                    // agregado el 26.01,2024

                    'articulos.codigo_alfanumerico',
                    'articulos.descripcion_fabrica'
                )
                ->distinct()
                ->orderBy('articulos.id', 'desc')->paginate(6);
        } else {
            $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                    ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                    ->join('personas', 'proveedores.id', '=', 'personas.id')
                    ->join('industrias', 'articulos.idindustria', '=', 'industrias.id')
                    ->join('marcas', 'articulos.idmarca', '=', 'marcas.id')
                    ->join('grupos', 'articulos.idgrupo', '=', 'grupos.id')
                    ->join('medidas', 'articulos.idmedida', '=', 'medidas.id')
                    ->select(
                        'articulos.id',
                        'articulos.idcategoria',
                        'articulos.idproveedor',
                        //aumente 7 julio
                        'articulos.idindustria',
                        //aumente 7 julio
                        'articulos.idmarca',
                        //aumente 7 julio
                        'articulos.idgrupo',
                        //aumente 7 julio
                        'articulos.idmedida',
                        'articulos.codigo',
                        'articulos.nombre',
                        'articulos.nombre_generico',
                        'articulos.costo_compra',
                        //aumente12julio
                        'articulos.vencimiento',
                        'articulos.unidad_envase',
                        'articulos.precio_list_unid',
                        'articulos.precio_costo_unid',
                        'articulos.precio_costo_paq',
    
                        'categorias.nombre as nombre_categoria',
                        'industrias.nombre as nombre_industria',
                        'marcas.nombre as nombre_marca',
                        'grupos.nombre_grupo',
                        'medidas.descripcion_medida',
                        //aumente 5 julio
    
                        'articulos.precio_uno',
                        'articulos.precio_dos',
                        'articulos.precio_tres',
                        'articulos.precio_cuatro',
    
                        'articulos.precio_venta',
                        'articulos.stock',
                        'personas.nombre as nombre_proveedor',
                        'articulos.descripcion',
                        'articulos.condicion',
                        'articulos.fotografia',
                        // agregado el 26.01,2024
    
                        'articulos.codigo_alfanumerico',
                        'articulos.descripcion_fabrica'
                )
                ->where('articulos.nombre', 'like', '%' . $buscar . '%')
                ->orWhere('articulos.descripcion', 'like', '%' . $buscar . '%')
                ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
                ->orWhere('categorias.nombre', 'like', '%' . $buscar . '%')
                ->orWhere('industrias.nombre', 'like', '%' . $buscar . '%')
                ->orWhere('marcas.nombre', 'like', '%' . $buscar . '%')
                ->distinct()
                ->orderBy('articulos.id', 'desc')->paginate(6);
        }


        return [
            'pagination' => [
                'total' => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page' => $articulos->perPage(),
                'last_page' => $articulos->lastPage(),
                'from' => $articulos->firstItem(),
                'to' => $articulos->lastItem(),
            ],
            'articulos' => $articulos
        ];
    }
    public function index2(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->join('industrias', 'articulos.idindustria', '=', 'industrias.id')
                ->join('marcas', 'articulos.idmarca', '=', 'marcas.id')
                ->join('grupos', 'articulos.idgrupo', '=', 'grupos.id')
                ->join('medidas', 'articulos.idmedida', '=', 'medidas.id')

                ->select(
                    'articulos.id',
                    'articulos.idcategoria',
                    'articulos.idproveedor',
                    //aumente 7 julio
                    'articulos.idindustria',
                    //aumente 7 julio
                    'articulos.idmarca',
                    //aumente 7 julio
                    'articulos.idgrupo',
                    //aumente 7 julio
                    'articulos.idmedida',
                    'articulos.codigo',
                    'articulos.nombre',
                    'articulos.nombre_generico',
                    'articulos.costo_compra',
                    //aumente12julio
                    'articulos.vencimiento',
                    'articulos.unidad_envase',
                    'articulos.precio_list_unid',
                    'articulos.precio_costo_unid',
                    'articulos.precio_costo_paq',

                    'categorias.nombre as nombre_categoria',
                    'industrias.nombre as nombre_industria',
                    'marcas.nombre as nombre_marca',
                    'grupos.nombre_grupo',
                    'medidas.descripcion_medida',
                    //aumente 5 julio

                    'articulos.precio_uno',
                    'articulos.precio_dos',
                    'articulos.precio_tres',
                    'articulos.precio_cuatro',

                    'articulos.precio_venta',
                    'articulos.stock',
                    'personas.nombre as nombre_proveedor',
                    'articulos.descripcion',
                    'articulos.condicion',
                    'articulos.fotografia',
                    // agregado el 26.01,2024

                    'articulos.codigo_alfanumerico',
                    'articulos.descripcion_fabrica'
                )
                ->distinct()
                ->orderBy('articulos.id', 'desc');
        } else {
            $articulos = Articulo::join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->join('industrias', 'articulos.idindustria', '=', 'industrias.id')
                ->join('marcas', 'articulos.idmarca', '=', 'marcas.id')
                ->join('grupos', 'articulos.idgrupo', '=', 'grupos.id')
                ->select(
                    'articulos.id',
                    'articulos.idcategoria',
                    'articulos.codigo',
                    'articulos.nombre',

                    'articulos.unidad_envase',
                    'articulos.precio_list_unid',
                    'articulos.precio_costo_unid',
                    'articulos.precio_costo_paq',

                    'categorias.nombre as nombre_categoria',
                    'industrias.nombre as nombre_industria',
                    'marcas.nombre as nombre_marca',
                    'grupos.nombre_grupo',
                    //aumente 5 julio

                    'articulos.precio_uno',
                    'articulos.precio_dos',
                    'articulos.precio_tres',
                    'articulos.precio_cuatro',

                    'articulos.precio_venta',
                    'articulos.stock',
                    'personas.nombre as nombre_proveedor',
                    'articulos.descripcion',
                    'articulos.condicion',
                    'articulos.fotografia',
                    // agregado el 26.01,2024

                    'articulos.codigo_alfanumerico',
                    'articulos.descripcion_fabrica'
                )
                ->where('articulos.' . $criterio, 'like', '%' . $buscar . '%')
                ->distinct()
                ->orderBy('articulos.id', 'desc');
        }
        $articulos=$articulos->get();

        return ['articulos' => $articulos
        ];
    }

    public function buscadorGlobal(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $idProveedor = $request->input('idProveedor');
        $filtro = $request->input('buscar');

        $campos = [
            'articulos.id',
            'articulos.codigo',
            'articulos.nombre',
            'articulos.precio_costo_unid',
            'articulos.precio_costo_paq',
            'articulos.unidad_envase',
            'personas.nombre as nombre_proveedor',
            'categorias.nombre as nombre_categoria'
        ];

        $query = Articulo::select($campos)
            ->join('categorias', 'articulos.idcategoria','=','categorias.id')
            ->join('proveedores', 'articulos.idproveedor','=','proveedores.id')
            ->join('personas', 'proveedores.id','=','personas.id')
            ->where('idproveedor', $idProveedor)
            ->orderBy('articulos.id', 'desc');

        if ($filtro) {
            $query->where(function($q) use ($filtro) {
                $q->where('articulos.nombre', 'like', '%' . $filtro . '%')
                ->orWhere('articulos.codigo', 'like', '%' . $filtro . '%');
            });
        }

        $productos = $query->paginate(10);

        return response()->json([ "articulos" => $productos]);
    }

    public function listarArticuloVenta(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
    
        $buscar = $request->buscar;
        $idAlmacen = $request->idAlmacen;
        $fechaActual = now();
    
        $subquery = DB::table('inventarios')
            ->join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
            ->select('inventarios.idarticulo', 'inventarios.idalmacen', DB::raw('SUM(inventarios.saldo_stock) as saldo_stock'))
            ->where('inventarios.fecha_vencimiento', '>', $fechaActual)
            ->where('inventarios.idalmacen', '=', $idAlmacen)
            ->where('inventarios.saldo_stock', '>', 0)
            ->groupBy('inventarios.idarticulo', 'inventarios.idalmacen');
    
        $articulosConSaldo = DB::table(DB::raw("({$subquery->toSql()}) as inventarios"))
            ->mergeBindings($subquery)
            ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
            ->join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
            ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
            ->join('personas', 'proveedores.id', '=', 'personas.id')
            ->join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
            ->join('marcas', 'articulos.idmarca', '=', 'marcas.id') // Nuevo join con la tabla marcas
            ->select(
                'articulos.id',
                'articulos.nombre',
                'articulos.precio_uno',
                'articulos.precio_dos',
                'articulos.precio_tres',
                'articulos.precio_cuatro',
                'articulos.fotografia',
                'articulos.unidad_envase',
                'almacens.nombre_almacen',
                'inventarios.saldo_stock',
                'articulos.codigo',
                'articulos.precio_venta',
                'articulos.condicion',
                'categorias.nombre as nombre_categoria',
                'marcas.nombre as nombre_marca' // Nuevo campo para la marca
            )
            ->orderBy('articulos.nombre')
            ->orderBy('almacens.nombre_almacen');
    
        if ($buscar != '') {
            $articulosConSaldo->where(function($query) use ($buscar) {
                $query->where('articulos.codigo', 'like', '%' . $buscar . '%')
                      ->orWhere('articulos.nombre', 'like', '%' . $buscar . '%');
            });
        }
    
        $articulosConSaldo = $articulosConSaldo->get();
    
        return ['articulos' => $articulosConSaldo];
    }

   


    public function listarPdf()
    {
        return Excel::download(new ProductExport, 'articulos.xlsx');
    }
    public function buscarArticulo(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $articulos = Articulo::where('codigo', '=', $filtro)
            ->select('id', 'codigo', 'nombre', 'precio_costo_unid', 'fotografia', 'unidad_envase', 'descripcion')->orderBy('nombre', 'asc')->take(1)->get();

        return ['articulos' => $articulos];
    }
    public function buscarArticuloVenta(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
    
        $filtro = $request->filtro;
        $idAlmacen = $request->idalmacen;
        $articulos = Articulo::join('medidas', 'articulos.idmedida', '=', 'medidas.id')
            ->join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
            ->join('inventarios', 'inventarios.idarticulo', '=', 'articulos.id')
            ->join('marcas', 'articulos.idmarca', '=', 'marcas.id')
            ->join('industrias', 'articulos.idindustria', '=', 'industrias.id')
            ->join('grupos', 'articulos.idgrupo', '=', 'grupos.id')
            ->select(
                'articulos.id',
                'articulos.nombre',
                'articulos.stock',
                'articulos.precio_costo_unid',
                'articulos.precio_costo_paq',
                'medidas.descripcion_medida as medida',
                'medidas.codigoClasificador as codigoClasificador',
                'articulos.precio_venta',
                'categorias.codigoProductoSin',
                'articulos.codigo',
                'articulos.precio_uno',
                'articulos.precio_dos',
                'articulos.precio_tres',
                'articulos.precio_cuatro',
                'articulos.fotografia',
                'articulos.condicion',
                'articulos.descripcion',
                'articulos.idcategoria',
                'articulos.idproveedor',
                'articulos.idindustria',
                'industrias.nombre as nombre_industria',
                'articulos.idmarca',
                'marcas.nombre as nombre_marca',
                
                'grupos.nombre_grupo as nombre_grupo',
                
                'medidas.descripcion_medida as nombre_medida',
                'categorias.nombre as nombre_categoria',
                'unidad_envase',
                'inventarios.fecha_vencimiento',
                DB::raw('(SELECT SUM(inventarios.saldo_stock) FROM inventarios WHERE inventarios.idarticulo = articulos.id AND inventarios.fecha_vencimiento > NOW() AND inventarios.idalmacen = ?) as saldo_stock')
            )
            ->where('articulos.codigo', '=', $filtro)
            ->where('inventarios.idalmacen', '=', $idAlmacen)
            // ->where('inventarios.saldo_stock', '>', 0)
            ->whereDate('inventarios.fecha_vencimiento', '>', now()) // Filtrar los lotes no vencidos
            ->orderBy('inventarios.fecha_vencimiento', 'asc')
            ->addBinding($idAlmacen, 'select')
            ->orderBy('articulos.nombre', 'asc')->take(1)->get();
        
        Log::info('ARTICULO:', [
            'DATA' => $articulos,
        ]);
    
        return ['articulos' => $articulos];
    }
    public function store(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = new Articulo();
        $articulo->idcategoria = $request->idcategoria;
        $articulo->idmarca = $request->idmarca;
        $articulo->idindustria = $request->idindustria;
        $articulo->idgrupo = $request->idgrupo; //aumente 12 junio
        $articulo->vencimiento = $request->fechaVencimientoSeleccion; 
        $articulo->idmedida = $request->idmedida; //new

        $articulo->codigo = $request->codigo;
        $articulo->nombre = $request->nombre;

        $articulo->nombre_generico = $request->nombre_generico; //aumete 12julio

        $articulo->unidad_envase = $request->unidad_envase;
        $articulo->precio_venta = $request->precio_venta;

        $articulo->precio_uno = $request->precio_uno;
        $articulo->precio_dos = $request->precio_dos;
        $articulo->precio_tres = $request->precio_tres;
        $articulo->precio_cuatro = $request->precio_cuatro;

        $articulo->costo_compra = $request->costo_compra; //new

        $articulo->stock = $request->stock;
        $articulo->idproveedor = $request->idproveedor;
        $articulo->precio_costo_unid = $request->precio_costo_unid;
        $articulo->precio_costo_paq = $request->precio_costo_paq;
        $articulo->descripcion = $request->descripcion;
        $articulo->codigo_alfanumerico = $request->codigo_alfanumerico;
        $articulo->descripcion_fabrica = $request->descripcion_fabrica;


        //$articulo->fecha_vencimiento = $request->fecha_vencimiento;
        $articulo->condicion = '1';
        $nombreimagen = '';
        if ($request->hasFile('fotografia')) {
            if ($request->hasFile('fotografia')) {
                $imagen = $request->file("fotografia");
                $nombreimagen = Str::slug($request->nombre) . "." . $imagen->guessExtension();
                $ruta = public_path("img/articulo/");

                // Crear el directorio si no existe
                if (!File::isDirectory($ruta)) {
                    File::makeDirectory($ruta, 0755, true);
                }

                // Copiar la imagen al directorio
                copy($imagen->getRealPath(), $ruta . $nombreimagen);

                $articulo->fotografia = $nombreimagen;
            }
        }
        Log::info('DATOS REGISTRO ARTICULO:', [
            'idcategoria' => $request->idcategoria,
            'idmarca' => $request->idmarca,
            'idindustria' => $request->idindustria,
            'idgrupo' => $request->idgrupo,
            'idproveedor' => $request->idproveedor,
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'nombre_generico' => $request->nombre_generico,
            'precio_venta' => $request->precio_venta,
            'stock' => $request->stock,
            'descripcion' => $request->descripcion,
            'precio_uno' => $request->precio_uno,
            'precio_dos' => $request->precio_dos,
            'precio_tres' => $request->precio_tres,
            'precio_cuatro' => $request->precio_cuatro,
            'fotografia' => $nombreimagen,

        ]);
        $articulo->save();
        return ['idArticulo' => $articulo->id];
    }    
    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
    
        try {
            DB::beginTransaction();
    
            // Primero, busca el artículo existente
            $articulo = Articulo::findOrFail($request->id);
    
            // Verificar si el código ya existe en otro artículo
            $existingArticulo = Articulo::where('codigo', $request->codigo)
                ->where('id', '!=', $articulo->id)
                ->first();
    
            if ($existingArticulo) {
                return response()->json([
                    'error' => true,
                    'message' => 'El código ' . $request->codigo . ' ya está en uso por otro artículo.'
                ], 400);
            }
    
            // Resto del código de actualización
            $articulo->fill($request->all());
    
            // Manejo de la fotografía
            if ($request->hasFile('fotografia')) {
                $imagen = $request->file("fotografia");
                $nombreimagen = Str::slug($request->nombre) . "." . $imagen->guessExtension();
                $ruta = public_path("img/articulo/");
    
                // Eliminar imagen anterior si existe
                if ($articulo->fotografia && File::exists($ruta . $articulo->fotografia)) {
                    File::delete($ruta . $articulo->fotografia);
                }
    
                // Copiar la imagen al directorio
                copy($imagen->getRealPath(), $ruta . $nombreimagen);
                $articulo->fotografia = $nombreimagen;
            }
    
            $articulo->save();
    
            DB::commit();
    
            return response()->json([
                'error' => false,
                'message' => 'Artículo actualizado correctamente'
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al actualizar el artículo: ' . $e->getMessage()
            ], 500);
        }
    }
    public function desactivar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '0';
        $articulo->save();
    }

    public function activar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '1';
        $articulo->save();
    }
    //--------LISTADO PARA PEDIDO PROVEEDOR---------------
    public function listPedProve(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        Log::info('Data', [
            'idProveedor' => $request->idProveedor,
            'buscar' => $request->buscar,
            'criterio' => $request->criterio,
        ]);

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $idProveedor = $request->idProveedor;

        $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
            ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
            ->join('personas', 'proveedores.id', '=', 'personas.id')
            ->select(
                'articulos.id',
                //'articulos.idcategoria', 
                'articulos.codigo',
                'articulos.nombre as articulo',
                //'categorias.nombre as nombre_categoria', 
                'articulos.precio_costo_unid as precio',
                'articulos.precio_costo_paq',
                //'articulos.stock', 
                'personas.nombre as nombre_proveedor',
                'articulos.fotografia',
                'articulos.descripcion',
                //'articulos.fecha_vencimiento', 
                //'articulos.condicion', 
                'articulos.unidad_envase as unidad_x_paquete',
            )
            ->where('proveedores.id', '=', $idProveedor);
        //->orderBy('articulos.id', 'desc')->paginate(10);
        if (!empty($buscar)) {
            $articulos = $articulos->where(function ($query) use ($criterio, $buscar) {
                $query->where('articulos.' . $criterio, 'like', '%' . $buscar . '%');
            });
        }
        $articulos = $articulos->orderBy('articulos.id', 'desc')->paginate(6);
        return [
            'pagination' => [
                'total' => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page' => $articulos->perPage(),
                'last_page' => $articulos->lastPage(),
                'from' => $articulos->firstItem(),
                'to' => $articulos->lastItem(),
            ],
            'articulos' => $articulos
        ];
    }

    public function importar(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:xlsx,xls',
            ]);

            $archivo = $request->file('archivo');

            $import = new ArticuloImport();
            Excel::import($import, $archivo);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                return response()->json(['errors' => $errors], 422);
            } else {
                return response()->json(['mensaje' => 'Importación exitosa'], 200);
            }
        } catch (Exception $e) {
            Log::error('Error en la importación: ' . $e->getMessage());

            return response()->json(['error' => 'Error en la importación', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function editarCostoUnidadPaquete(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $articulo = Articulo::findOrFail($request->newData['id']);
            $articulo->precio_costo_unid = $request->newData['precio_costo_unid'];
            $articulo->precio_costo_paq = $request->newData['precio_costo_paq'];
            $articulo->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Articulo actualizado correctamente',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar articulo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editarPrecioCompraVenta(Request $request){
        $articulo = Articulo::findOrFail($request->id);
        $articulo->precio_costo_unid = $request->precio_costo_unidad;
        $articulo->precio_costo_paq = $request->precio_costo_paquete;
        $articulo->precio_uno = $request->precio_uno;
        $articulo->precio_dos = $request->precio_dos;
        $articulo->precio_tres = $request->precio_tres;
        $articulo->precio_cuatro = $request->precio_cuatro;
        $articulo->save();
    }
}