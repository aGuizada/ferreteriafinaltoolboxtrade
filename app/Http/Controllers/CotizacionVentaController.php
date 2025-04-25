<?php

namespace App\Http\Controllers;

use App\CotizacionVenta;
use App\DetalleCotizacionVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CotizacionVentaController extends Controller
{
    /**
     * Mostrar listado de cotizaciones
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        $buscar = $request->buscar;
    
        $cotizacion_venta = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->join('almacens', 'cotizacion_venta.idalmacen', '=', 'almacens.id')
            ->select(
                'cotizacion_venta.id', 'cotizacion_venta.idcliente', 'cotizacion_venta.idalmacen',
                'cotizacion_venta.fecha_hora', 'cotizacion_venta.total', 'cotizacion_venta.nota',
                'cotizacion_venta.validez', 'cotizacion_venta.plazo_entrega', 'cotizacion_venta.condicion',
                'personas.nombre', 'personas.num_documento', 'users.usuario'
            );
    
        if ($buscar) {
            $cotizacion_venta->where(function($query) use ($buscar) {
                $query->where('cotizacion_venta.id', 'like', '%'.$buscar.'%')
                    ->orWhere('personas.nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('personas.num_documento', 'like', '%'.$buscar.'%')
                    ->orWhere('cotizacion_venta.fecha_hora', 'like', '%'.$buscar.'%');
            });
        }
    
        $cotizacion_venta = $cotizacion_venta->orderBy('cotizacion_venta.id', 'desc')->paginate(10);
    
        return [
            'pagination' => [
                'total' => $cotizacion_venta->total(),
                'current_page' => $cotizacion_venta->currentPage(),
                'per_page' => $cotizacion_venta->perPage(),
                'last_page' => $cotizacion_venta->lastPage(),
                'from' => $cotizacion_venta->firstItem(),
                'to' => $cotizacion_venta->lastItem(),
            ],
            'cotizacion_venta' => $cotizacion_venta
        ];
    }

    /**
     * Registrar nueva cotización
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            // Calcular fecha de validez
            $fechaActual = now()->setTimezone('America/La_Paz');
            $nuevaFecha = $fechaActual->addDays($request->n_validez);

            // Crear la cotización
            $cotizacionventa = new CotizacionVenta();
            $cotizacionventa->idcliente = $request->idcliente;
            $cotizacionventa->idusuario = Auth::user()->id;
            $cotizacionventa->idalmacen = $request->idalmacen;
            $cotizacionventa->fecha_hora = now()->setTimezone('America/La_Paz');
            $cotizacionventa->impuesto = $request->impuesto;
            $cotizacionventa->total = $request->total;
            $cotizacionventa->estado = $request->estado;
            $cotizacionventa->validez = $nuevaFecha;
            $cotizacionventa->plazo_entrega = $request->n_validez;
            $cotizacionventa->tiempo_entrega = $request->tiempo_entrega;
            $cotizacionventa->lugar_entrega = $request->lugar_entrega;
            $cotizacionventa->forma_pago = $request->forma_pago;
            $cotizacionventa->nota = $request->nota;
            $cotizacionventa->condicion = '1';
            $cotizacionventa->save();

            // Guardar los detalles
            foreach ($request->data as $det) {
                $detalle = new DetalleCotizacionVenta();
                $detalle->idcotizacion = $cotizacionventa->id;
                $detalle->idarticulo = $det['idarticulo'];
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio = $det['precioseleccionado'];
                $detalle->descuento = $det['descuento'] ?? 0;
                $detalle->save();
            }

            DB::commit();
            return ['id' => $cotizacionventa->id];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['id' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualizar cotización existente
     */
    public function editar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            // Calcular fecha de validez
            $fechaActual = now()->setTimezone('America/La_Paz');
            $nuevaFecha = $fechaActual->addDays($request->n_validez);

            // Actualizar la cotización
            $cotizacionventa = CotizacionVenta::findOrFail($request->idcotizacionv);
            $cotizacionventa->idcliente = $request->idcliente;
            $cotizacionventa->fecha_hora = now()->setTimezone('America/La_Paz');
            $cotizacionventa->total = $request->total;
            $cotizacionventa->validez = $nuevaFecha;
            $cotizacionventa->plazo_entrega = $request->n_validez;
            $cotizacionventa->nota = $request->nota;
            $cotizacionventa->save();

            // Eliminar y recrear detalles
            DetalleCotizacionVenta::where('idcotizacion', $cotizacionventa->id)->delete();

            foreach ($request->data as $det) {
                $detalle = new DetalleCotizacionVenta();
                $detalle->idcotizacion = $cotizacionventa->id;
                $detalle->idarticulo = $det['idarticulo'];
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio = $det['precioseleccionado'];
                $detalle->descuento = $det['descuento'] ?? 0;
                $detalle->save();
            }

            DB::commit();
            return ['id' => $cotizacionventa->id];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtener datos de la cabecera de una cotización
     */
    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $cotizacion = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->select(
                'cotizacion_venta.id', 'cotizacion_venta.idcliente', 'cotizacion_venta.fecha_hora',
                'cotizacion_venta.total', 'cotizacion_venta.validez', 'cotizacion_venta.plazo_entrega',
                'personas.nombre', 'personas.num_documento', 'personas.telefono', 'users.usuario'
            )
            ->where('cotizacion_venta.id', '=', $request->id)
            ->first();

        return ['cotizacion' => [$cotizacion]];
    }

    /**
     * Obtener detalles de una cotización
     */
    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $detalles = DetalleCotizacionVenta::join('articulos', 'detalle_cotizacion.idarticulo', '=', 'articulos.id')
            ->join('medidas', 'articulos.idmedida', '=', 'medidas.id')
            ->select(
                'detalle_cotizacion.idarticulo', 'detalle_cotizacion.cantidad',
                'detalle_cotizacion.precio as precioseleccionado', 'detalle_cotizacion.descuento',
                'articulos.nombre as nombre_articulo', 'articulos.codigo', 'articulos.unidad_envase',
                'medidas.descripcion_medida as medida',
                DB::raw('detalle_cotizacion.precio * detalle_cotizacion.cantidad as prectotal')
            )
            ->where('detalle_cotizacion.idcotizacion', '=', $request->idcotizacion)
            ->get();

        return ['detalles' => $detalles];
    }

    /**
     * Generar PDF de la cotización
     */
    public function pdf($id)
    {
        $venta = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->select(
                'cotizacion_venta.id', 'cotizacion_venta.created_at', 'cotizacion_venta.impuesto',
                'cotizacion_venta.total', 'cotizacion_venta.validez', 'cotizacion_venta.plazo_entrega',
                'personas.nombre', 'personas.tipo_documento', 'personas.num_documento',
                'personas.direccion', 'personas.email', 'personas.telefono', 'users.usuario'
            )
            ->where('cotizacion_venta.id', '=', $id)
            ->get();
    
        $detalles = DetalleCotizacionVenta::join('articulos', 'detalle_cotizacion.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_cotizacion.cantidad', 'detalle_cotizacion.precio',
                'detalle_cotizacion.descuento', 'articulos.nombre as articulo'
            )
            ->where('detalle_cotizacion.idcotizacion', '=', $id)
            ->get();
    
        $fechaVenta = $venta[0]->created_at->format('d/m/Y');
        $horaVenta = $venta[0]->created_at->format('H:i');
        $fechaValidez = Carbon::parse($venta[0]->validez)->format('d/m/Y');
        $diasValidez = $venta[0]->plazo_entrega;
    
        $pdf = \PDF::loadView('pdf.cotizacionpdf', [
            'venta' => $venta,
            'detalles' => $detalles,
            'fechaVenta' => $fechaVenta,
            'horaVenta' => $horaVenta,
            'fechaValidez' => $fechaValidez,
            'diasValidez' => $diasValidez
        ]);
        
        return $pdf->download('cotizacion-' . $id . '.pdf');
    }

    /**
     * Desactivar cotización
     */
    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $cotizacionventa = CotizacionVenta::findOrFail($request->id);
        $cotizacionventa->condicion = '0';
        $cotizacionventa->save();
        
        return ['success' => true];
    }

    /**
     * Activar cotización
     */
    public function activar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $cotizacionventa = CotizacionVenta::findOrFail($request->id);
        $cotizacionventa->condicion = '1';
        $cotizacionventa->save();
        
        return ['success' => true];
    }
}