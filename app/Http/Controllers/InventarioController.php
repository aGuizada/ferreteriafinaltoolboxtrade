<?php

namespace App\Http\Controllers;

use App\Articulo;
use App\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductosBajoStockExport;
use App\Exports\ProductosPorVencerseExport;
use App\Exports\ProductosVencidosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventarioImport;
use Exception;

class InventarioController extends Controller
{
    public function registrarInventario(Request $request)
    {
        if (!$request->has('inventarios')) {
            return response()->json(['error' => 'No se enviaron inventarios'], 400);
        }
    
        DB::beginTransaction();
        try {
            $inventarios = $request->input('inventarios');
    
            foreach ($inventarios as $inventario) {
                // Verificar si el artículo existe
                $articulo = Articulo::find($inventario['idarticulo']);
                if (!$articulo) {
                    Log::warning("Artículo no encontrado: " . $inventario['idarticulo']);
                    continue;
                }
    
                // Asignar fecha de vencimiento, usando un valor por defecto si no está presente
                $fechaVencimiento = isset($inventario['fecha_vencimiento']) 
                    ? date('Y-m-d', strtotime($inventario['fecha_vencimiento'])) 
                    : '2099-01-01';
    
                // Verificar si ya existe un registro de inventario con el mismo artículo, almacén y fecha de vencimiento
                $inventarioExistente = Inventario::where('idarticulo', $inventario['idarticulo'])
                    ->where('idalmacen', $inventario['idalmacen'])
                    ->whereDate('fecha_vencimiento', $fechaVencimiento)
                    ->first();
    
                if ($inventarioExistente) {
                    // Si existe, actualizar los valores de saldo_stock y cantidad
                    $cantidad = $inventario['cantidad'] ?? 0;
                    $inventarioExistente->saldo_stock += $cantidad;
                    $inventarioExistente->cantidad += $cantidad;
                    $inventarioExistente->save();
                } else {
                    // Si no existe, crear un nuevo registro
                    $cantidad = $inventario['cantidad'] ?? 0;
                    Inventario::create([
                        'idalmacen' => $inventario['idalmacen'],
                        'idarticulo' => $inventario['idarticulo'],
                        'fecha_vencimiento' => $fechaVencimiento,
                        'saldo_stock' => $cantidad,
                        'cantidad' => $cantidad
                    ]);
                }
            }
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Inventarios guardados exitosamente'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar inventarios: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar inventarios'
            ], 500);
        }
    }
    

    
    public function store(Request $request)
    {
        Log::info("al menos llegaa xddd");

        if ($request->has('inventarios')) {
            $inventarios = $request->input('inventarios');
            Log::info("llego");
            foreach ($inventarios as $inventario) {
                // Verificar si el idarticulo existe en la tabla de articulos
                $articulo = Articulo::find($inventario['idarticulo']);
                Log::info("llego2");

                if ($articulo) {
                    // El idarticulo existe, obtener todos los inventarios con el mismo idarticulo
                    $inventariosExistente = Inventario::where('idarticulo', $inventario['idarticulo'])
                        ->whereDate('fecha_vencimiento', $inventario['fecha_vencimiento'])
                        ->get();
                    Log::info("El articulo existe");

                    $foundInventory = false;
                    foreach ($inventariosExistente as $invExistente) {
                        Log::info("El inventario");
                        $fechaVencimiento = new \DateTime($invExistente->fecha_vencimiento);

                        // Comparar solo la fecha de vencimiento (día, mes y año)
                        if ($fechaVencimiento->format('Y-m-d') === date('Y-m-d', strtotime($inventario['fecha_vencimiento'])) && $invExistente->idalmacen == intval($inventario['idalmacen']) ) {
                            // Verificar si el índice 'cantidad' está presente antes de usarlo
                            Log::info("se comparo");

                            if (isset($inventario['cantidad'])) {
                                // Si la fecha de vencimiento coincide, sumar el saldo_stock
                                $invExistente->saldo_stock += $inventario['cantidad'];
                                $invExistente->cantidad += $inventario['cantidad'];
                                $invExistente->save();
                                $foundInventory = true;
                                break;
                            } else {
                                // Manejar el caso en el que el índice 'cantidad' no está presente
                                Log::info("El índice 'cantidad' no está presente en el array");
                            }
                        }
                    }

                    if (!$foundInventory) {
                        // Si no hay coincidencias de fecha de vencimiento, registrar normalmente
                        $newInventario = new Inventario();
                        $newInventario->idalmacen = $inventario['idalmacen'];
                        $newInventario->idarticulo = $inventario['idarticulo'];
                        $newInventario->fecha_vencimiento = $inventario['fecha_vencimiento'];

                        // Verificar si el índice 'cantidad' está presente antes de usarlo
                        if (isset($inventario['cantidad'])) {
                            $newInventario->saldo_stock = $inventario['cantidad'];
                            $newInventario->cantidad = $inventario['cantidad'];
                        } else {
                            // Asignar un valor por defecto en caso de que el índice 'cantidad' no esté presente
                            $newInventario->saldo_stock = 0;
                            $newInventario->cantidad = 0;

                            Log::info("El índice 'cantidad' no está presente en el array");
                        }

                        $newInventario->save();
                    }
                } else {
                    // El idarticulo no existe, registrar normalmente
                    Log::info("No existe el articulo");
                }
            }

            // Aquí puedes retornar una respuesta al cliente para indicar que los datos fueron guardados exitosamente
            return response()->json(['message' => 'Inventarios guardados exitosamente'], 200);
        }

        // Si no se enviaron inventarios en la solicitud, puedes retornar una respuesta de error
        return response()->json(['error' => 'No se enviaron inventarios'], 400);
    }

    public function productosPorVencer(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        // Obtener la fecha actual
        $fechaActual = now()->toDateString();

        $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
            ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
            ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
            ->join('personas', 'proveedores.id', '=', 'personas.id')
            ->select(
                'inventarios.id',
                'inventarios.fecha_vencimiento',
                'inventarios.saldo_stock',
                'almacens.nombre_almacen',
                'almacens.ubicacion',
                'articulos.codigo',
                'articulos.nombre as nombre_producto',
                'articulos.unidad_envase',
                'personas.nombre as nombre_proveedor',
                // Calcular los días restantes para vencerse
                DB::raw('DATEDIFF(inventarios.fecha_vencimiento, "' . $fechaActual . '") AS dias_restantes'),
                // Indicar si el producto está vencido o no
                DB::raw('IF(inventarios.fecha_vencimiento < "' . $fechaActual . '", 0, 1) AS vencido')
            )
            ->whereRaw('DATEDIFF(inventarios.fecha_vencimiento, "' . $fechaActual . '") <= 30')
            ->orderBy('inventarios.id', 'desc');

        if (!empty($buscar)) {
            $inventarios->where('inventarios.' . $criterio, 'like', '%' . $buscar . '%');
        }

        $inventarios = $inventarios->paginate(6);

        return [
            'pagination' => [
                'total' => $inventarios->total(),
                'current_page' => $inventarios->currentPage(),
                'per_page' => $inventarios->perPage(),
                'last_page' => $inventarios->lastPage(),
                'from' => $inventarios->firstItem(),
                'to' => $inventarios->lastItem(),
            ],
            'inventarios' => $inventarios
        ];
    }

    public function listarReportePorVencerExcel()
    {
        return Excel::download(new ProductosPorVencerseExport, 'articulosPorVencer.xlsx');
    }

    public function productosVencidos(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(
                    'inventarios.id',
                    'inventarios.fecha_vencimiento',
                    'inventarios.saldo_stock',

                    'almacens.nombre_almacen',
                    'almacens.ubicacion',

                    'articulos.codigo',
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',

                    'personas.nombre as nombre_proveedor',

                )
                ->whereDate('inventarios.fecha_vencimiento', '<=', DB::raw('CURDATE()'))
                ->orderBy('inventarios.id', 'desc')->paginate(6);
        } else {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(

                    'inventarios.id',
                    'inventarios.fecha_vencimiento',
                    'inventarios.saldo_stock',

                    'almacens.nombre_almacen',
                    'almacens.ubicacion',

                    'articulos.codigo',
                    'articulos.nombre as nombre_producto',
                    'articulos.precio_costo_unid',

                    'personas.nombre as nombre_proveedor',
                )
                ->whereDate('inventarios.fecha_vencimiento', '<=', DB::raw('CURDATE()'))
                ->where('inventarios.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('inventarios.id', 'desc')->paginate(6);
        }


        return [
            'pagination' => [
                'total' => $inventarios->total(),
                'current_page' => $inventarios->currentPage(),
                'per_page' => $inventarios->perPage(),
                'last_page' => $inventarios->lastPage(),
                'from' => $inventarios->firstItem(),
                'to' => $inventarios->lastItem(),
            ],
            'inventarios' => $inventarios
        ];
    }

    public function listarReporteVencidosExcel()
    {
        return Excel::download(new ProductosVencidosExport, 'articulosVencidos.xlsx');
    }

    public function productosBajoStock(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(
                    'inventarios.id',
                    'inventarios.fecha_vencimiento',
                    'inventarios.saldo_stock',

                    'almacens.nombre_almacen',
                    'almacens.ubicacion',

                    'articulos.codigo',
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.stock',

                    'personas.nombre as nombre_proveedor',

                )
                ->whereRaw('articulos.stock > inventarios.saldo_stock')
                ->orderBy('inventarios.id', 'desc')->paginate(6);
        } else {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(

                    'inventarios.id',
                    'inventarios.fecha_vencimiento',
                    'inventarios.saldo_stock',

                    'almacens.nombre_almacen',
                    'almacens.ubicacion',

                    'articulos.codigo',
                    'articulos.nombre as nombre_producto',
                    'articulos.precio_costo_unid',

                    'personas.nombre as nombre_proveedor',
                )
                ->whereRaw('articulos.stock > inventarios.saldo_stock')
                ->where('inventarios.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('inventarios.id', 'desc')->paginate(6);
        }


        return [
            'pagination' => [
                'total' => $inventarios->total(),
                'current_page' => $inventarios->currentPage(),
                'per_page' => $inventarios->perPage(),
                'last_page' => $inventarios->lastPage(),
                'from' => $inventarios->firstItem(),
                'to' => $inventarios->lastItem(),
            ],
            'inventarios' => $inventarios
        ];
    }

    public function listarReporteBajoStockExcel()
    {
        return Excel::download(new ProductosBajoStockExport, 'articulosBajoStock.xlsx');
    }
    //-------------------aumente el listado mejorado------

    ////////////////--Lista or item y lotes-/////////////////
    public function indexItemLote(Request $request, $tipo)
    {
        if (!$request->ajax())
            return redirect('/');
        $idAlmacen = $request->idAlmacen;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        if ($tipo === 'item') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'almacens.nombre_almacen',
                    'inventarios.cantidad',
                    DB::raw('SUM(inventarios.saldo_stock) as saldo_stock_total')
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->groupBy('articulos.nombre', 'almacens.nombre_almacen', 'articulos.unidad_envase', 'inventarios.cantidad',)
                ->orderBy('articulos.nombre')
                ->orderBy('almacens.nombre_almacen');
            //->get();
        } else if ($tipo === 'lote') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->select(
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.precio_costo_unid',
                    'inventarios.saldo_stock',
                    'inventarios.cantidad',
                    DB::raw('DATE_FORMAT(inventarios.created_at, "%Y-%m-%d") as fecha_ingreso'), // Formato deseado
                    'inventarios.fecha_vencimiento',

                    'almacens.nombre_almacen',
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)

                ->orderBy('articulos.nombre');
        }

        if (!empty($buscar)) {
            $inventarios = $inventarios->where(function ($query) use ($criterio, $buscar, $tipo) {
                $query->where('articulos.' . $criterio, 'like', '%' . $buscar . '%');
                if ($tipo === 'lote') {
                    $query->orWhere('articulos.' . $criterio, 'like', '%' . $buscar . '%');
                }
            });
        }
        $inventarios = $inventarios->paginate(10);

        return [
            'pagination' => [
                'total' => $inventarios->total(),
                'current_page' => $inventarios->currentPage(),
                'per_page' => $inventarios->perPage(),
                'last_page' => $inventarios->lastPage(),
                'from' => $inventarios->firstItem(),
                'to' => $inventarios->lastItem(),
            ],
            'inventarios' => $inventarios];
    }
    //LISTA PARA OBTENER EL SALDO_STOCK POR ITEM POR EL ALMACEN,NOMBRE Y ID DE ARTICULO
    public function indexsaldostock(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $idAlmacen = $request->idAlmacen;
        $idArticulo = $request->idArticulo;
        if ($idAlmacen !== null && $idArticulo !== null) {
            $invenstock = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
                ->join('personas', 'proveedores.id', '=', 'personas.id')
                ->select(
                    'articulos.nombre as nombre_producto',
                    //'articulos.unidad_envase',
                    'almacens.nombre_almacen',
                    DB::raw('SUM(inventarios.saldo_stock) as saldo_stock_totaldos')
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->where('inventarios.idarticulo', '=', $idArticulo)
                ->groupBy('articulos.nombre', 'almacens.nombre_almacen')
                ->orderBy('articulos.nombre')
                ->orderBy('almacens.nombre_almacen')->get();

            return ['invenstock' => $invenstock];
        } else {
            // Si falta alguno de los valores, regresar respuesta vacía
            return ['invenstock' => []];
        }
    }
    public function reporteAlmacenes(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $idAlmacen = $request->idAlmacen;

        $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
            ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
            ->join('proveedores', 'articulos.idproveedor', '=', 'proveedores.id')
            ->join('personas', 'proveedores.id', '=', 'personas.id')
            ->select(
                'articulos.nombre as nombre_producto',
                'articulos.unidad_envase',
                'almacens.nombre_almacen',
                DB::raw('SUM(inventarios.saldo_stock) as saldo_stock_total')
            )
            ->where('inventarios.idalmacen', '=', $idAlmacen)
            ->groupBy('articulos.nombre', 'almacens.nombre_almacen', 'articulos.unidad_envase')
            ->orderBy('articulos.nombre')
            ->orderBy('almacens.nombre_almacen');
        //->get();
        //---------------------------------------

        $inventarios = $inventarios->get();

        if ($inventarios->isEmpty()) {
            return response()->json(['mensaje' => 'No existe articulos en el almacen seleccionado']);
        }
        //---------------------------------
        return  response()->json(['inventarios' => $inventarios]);
    }

    public function importar(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:csv,txt',
            ]);

            $archivo = $request->file('archivo');

            $import = new InventarioImport();
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
}
