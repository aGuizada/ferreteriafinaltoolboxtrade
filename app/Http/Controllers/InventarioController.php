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

                // Crear un nuevo registro siempre
                $cantidad = $inventario['cantidad'] ?? 0;
                Inventario::create([
                    'idalmacen' => $inventario['idalmacen'],
                    'idarticulo' => $inventario['idarticulo'],
                    'fecha_vencimiento' => $fechaVencimiento,
                    'saldo_stock' => $cantidad,
                    'cantidad' => $cantidad
                ]);
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
        if (!$request->has('inventarios')) {
            return response()->json(['error' => 'No se enviaron inventarios'], 400);
        }

        DB::beginTransaction();
        try {
            $inventarios = $request->input('inventarios');
            
            foreach ($inventarios as $inventario) {
                $articulo = Articulo::find($inventario['idarticulo']);
                
                if ($articulo) {
                    // Crear nuevo registro siempre
                    $newInventario = new Inventario();
                    $newInventario->idalmacen = $inventario['idalmacen'];
                    $newInventario->idarticulo = $inventario['idarticulo'];
                    $newInventario->fecha_vencimiento = $inventario['fecha_vencimiento'] ?? '2099-01-01';
                    $newInventario->saldo_stock = $inventario['cantidad'] ?? 0;
                    $newInventario->cantidad = $inventario['cantidad'] ?? 0;
                    $newInventario->save();
                } else {
                    Log::warning("Artículo no encontrado: " . $inventario['idarticulo']);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Inventarios guardados exitosamente'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar inventarios: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar inventarios'
            ], 500);
        }
    }

    public function generarReporteInversion(Request $request)
    {
        $idAlmacen = $request->idAlmacen;
        $tipo = $request->tipo;
    
        if ($tipo === 'item') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->select(
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.precio_costo_unid',
                    DB::raw('SUM(inventarios.saldo_stock) as saldo_stock_total'),
                    DB::raw('SUM(inventarios.saldo_stock * articulos.precio_costo_unid) as inversion_total')
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->where('inventarios.saldo_stock', '>', 0) // Solo productos con stock
                ->groupBy('articulos.nombre', 'articulos.unidad_envase', 'articulos.precio_costo_unid')
                ->orderBy('articulos.nombre')
                ->get();
        } else {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->select(
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.precio_costo_unid',
                    'inventarios.saldo_stock',
                    DB::raw('(inventarios.saldo_stock * articulos.precio_costo_unid) as inversion_lote'),
                    'inventarios.fecha_vencimiento'
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->where('inventarios.saldo_stock', '>', 0) // Solo lotes con stock
                ->orderBy('articulos.nombre')
                ->get();
        }
    
        // Calcular total de la inversión
        $totalInversion = $inventarios->sum(function ($item) use ($tipo) {
            return $tipo === 'item' ? $item->inversion_total : $item->inversion_lote;
        });
    
        // ⚡️ CORREGIDO: apuntamos a la carpeta 'pdf'
        $pdf = \PDF::loadView('pdf.reporte-inversion', [
            'inventarios' => $inventarios,
            'tipo' => $tipo,
            'totalInversion' => $totalInversion,
            'fecha' => now()->format('d/m/Y')
        ]);
    
        return $pdf->download('reporte_inversion.pdf');
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
        $incluirVencimiento = $request->input('incluir_vencimiento', false);
    
        if ($tipo === 'item') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->select(
                    'articulos.id as idarticulo',
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.precio_costo_unid',
                    'almacens.nombre_almacen',
                    DB::raw('SUM(inventarios.cantidad) as cantidad'),
                    DB::raw('SUM(inventarios.saldo_stock) as saldo_stock_total'),
                    DB::raw('SUM(inventarios.saldo_stock * articulos.precio_costo_unid) as inversion_total')
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->groupBy('articulos.id', 'articulos.nombre', 'articulos.unidad_envase', 'articulos.precio_costo_unid', 'almacens.nombre_almacen')
                ->orderBy('articulos.nombre');
    
        } else if ($tipo === 'lote') {
            $inventarios = Inventario::join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                ->select(
                    'inventarios.id',
                    'articulos.id as idarticulo',
                    'articulos.nombre as nombre_producto',
                    'articulos.unidad_envase',
                    'articulos.precio_costo_unid',
                    'inventarios.saldo_stock',
                    'inventarios.cantidad',
                    DB::raw('(inventarios.saldo_stock * articulos.precio_costo_unid) as inversion_lote'),
                    'inventarios.fecha_vencimiento',
                    'almacens.nombre_almacen',
                )
                ->where('inventarios.idalmacen', '=', $idAlmacen)
                ->orderBy('articulos.nombre');
        }
    
        if (!empty($buscar)) {
            $inventarios = $inventarios->where('articulos.'.$criterio, 'like', '%' . $buscar . '%');
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
            'inventarios' => $inventarios
        ];
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
    public function verHistorial($idArticulo)
    {
        try {
            Log::info("Solicitud de historial recibida para ID: " . $idArticulo);
            
            $historial = DB::table('inventarios')
                ->join('almacens', 'inventarios.idalmacen', '=', 'almacens.id')
                ->select(
                    'inventarios.fecha_vencimiento as fecha',
                    'inventarios.cantidad as cantidadComprada',
                    DB::raw('(inventarios.cantidad - inventarios.saldo_stock) as cantidadAgotada'),
                    'almacens.nombre_almacen',
                    DB::raw('DATE_FORMAT(inventarios.created_at, "%Y-%m-%d") as fecha_ingreso')
                )
                ->where('inventarios.idarticulo', $idArticulo)
                ->orderBy('inventarios.created_at', 'desc')
                ->get();
    
            if ($historial->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No se encontró historial para este producto',
                    'historial' => []
                ], 200);
            }
            
            return response()->json([
                'success' => true,
                'historial' => $historial
            ], 200);
        } catch (Exception $e) {
            Log::error('Error al obtener el historial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
