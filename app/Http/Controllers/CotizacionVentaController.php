<?php

namespace App\Http\Controllers;

use App\CotizacionVenta;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Venta;
use App\DetalleCotizacionVenta;
use App\User;
use App\Empresa;
use App\Caja;
use Illuminate\Support\Facades\Log;
use App\Notifications\NotifyAdmin;
use Illuminate\Support\Facades\Auth;
use TheSeer\Tokenizer\Exception;
use FPDF;


class CotizacionVentaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        $buscar = $request->buscar;
    
        $cotizacion_venta = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->join('almacens','cotizacion_venta.idalmacen','=','almacens.id')
            ->select(
                'cotizacion_venta.id',
                'cotizacion_venta.idcliente',
                'cotizacion_venta.idusuario',
                'cotizacion_venta.idalmacen',
                'almacens.nombre_almacen',
                'cotizacion_venta.fecha_hora',
                'cotizacion_venta.impuesto',
                'cotizacion_venta.total',
                'cotizacion_venta.estado',
                'cotizacion_venta.plazo_entrega',
                'cotizacion_venta.tiempo_entrega',
                'cotizacion_venta.lugar_entrega',
                'cotizacion_venta.forma_pago',
                'cotizacion_venta.nota',
                'cotizacion_venta.validez',
                'cotizacion_venta.condicion',
                'personas.nombre',
                'personas.num_documento',
                'personas.telefono',
                'users.usuario'
            );
    
        if ($buscar != '') {
            $cotizacion_venta->where(function($query) use ($buscar) {
                $query->where('cotizacion_venta.id', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.fecha_hora', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.total', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.estado', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.plazo_entrega', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.tiempo_entrega', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.lugar_entrega', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.forma_pago', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.nota', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.validez', 'like', '%' . $buscar . '%')
                    ->orWhere('cotizacion_venta.condicion', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.telefono', 'like', '%' . $buscar . '%')
                    ->orWhere('users.usuario', 'like', '%' . $buscar . '%')
                    ->orWhere('almacens.nombre_almacen', 'like', '%' . $buscar . '%');
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
    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $id = $request->id;
        $cotizacion = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->select(
                'cotizacion_venta.id',
                'cotizacion_venta.idcliente',
                'cotizacion_venta.tiempo_entrega',


                'cotizacion_venta.fecha_hora',
                'cotizacion_venta.impuesto',
                'cotizacion_venta.total',

                'personas.nombre',


                'users.usuario'
            )
            ->where('cotizacion_venta.id', '=', $id)
            ->orderBy('cotizacion_venta.id', 'desc')->take(1)->get();

        return ['cotizacion' => $cotizacion];
    }
    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $IDcotizacion = $request->idcotizacion;
        $detalles = DetalleCotizacionVenta::join('articulos', 'detalle_cotizacion.idarticulo', '=', 'articulos.id')
            ->join('cotizacion_venta', 'detalle_cotizacion.idcotizacion', '=', 'cotizacion_venta.id')
            ->join('medidas','articulos.idmedida','=','medidas.id')
            ->select(

                'detalle_cotizacion.cantidad',
                'detalle_cotizacion.precio as precioseleccionado',
                'detalle_cotizacion.descuento',
                'detalle_cotizacion.idarticulo',

                'articulos.nombre as articulo',
                'articulos.codigo',
                'articulos.unidad_envase',

                'medidas.descripcion_medida as medida',
                'cotizacion_venta.total as prectotal',
                // 'cotizacion_venta.idcliente'	

            )
            ->where('detalle_cotizacion.idcotizacion', '=', $IDcotizacion)
            ->orderBy('detalle_cotizacion.id', 'desc')->get();

        return ['detalles' => $detalles];
    }

    public function pdf(Request $request, $id)
    {
        $venta = CotizacionVenta::join('personas', 'cotizacion_venta.idcliente', '=', 'personas.id')
            ->join('users', 'cotizacion_venta.idusuario', '=', 'users.id')
            ->select(
                'cotizacion_venta.id',
                'cotizacion_venta.created_at',
                'cotizacion_venta.impuesto',
                'cotizacion_venta.total',
                'personas.nombre',
                'personas.tipo_documento',
                'personas.num_documento',
                'personas.direccion',
                'personas.email',
                'personas.telefono',
                'users.usuario'
            )
            ->where('cotizacion_venta.id', '=', $id)
            ->orderBy('cotizacion_venta.id', 'desc')->take(1)->get();

        $detalles = DetalleCotizacionVenta::join('articulos', 'detalle_cotizacion.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_cotizacion.cantidad',
                'detalle_cotizacion.precio',
                'detalle_cotizacion.descuento',
                'articulos.nombre as articulo'
            )
            ->where('detalle_cotizacion.idcotizacion', '=', $id)
            ->orderBy('detalle_cotizacion.id', 'desc')->get();

        // $numventa = Venta::select('num_comprobante')->where('id', $id)->get();

        $pdf = \PDF::loadView('pdf.cotizacionpdf', ['venta' => $venta, 'detalles' => $detalles]);
        return $pdf->download('cotizacion' . '.pdf');

    }
    public function store(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        try {
            DB::beginTransaction();

            $descu = '';
            $valorMaximoDescuentoEmpresa = Empresa::first();
            $valorMaximo = $valorMaximoDescuentoEmpresa->valorMaximoDescuento;
            $detalles = $request->data; //Array de detalles

            foreach ($detalles as $ep => $det) {
                $descu = $det['descuento'];
            }

            if ($descu > $valorMaximoDescuentoEmpresa->valorMaximoDescuento) {
                return [
                    'id' => -1,
                    'valorMaximo' => $valorMaximo
                ];
            } else {
                //----------------REGISTRO DESTE AQUI----------
                $fechaActual = now()->setTimezone('America/La_Paz');
                $nuevaFecha = $fechaActual->addDays($request->n_validez);



                $cotizacionventa = new CotizacionVenta();
                $cotizacionventa->idcliente = $request->idcliente;
                $cotizacionventa->idusuario = Auth::user()->id;

                $cotizacionventa->fecha_hora = now()->setTimezone('America/La_Paz');
                $cotizacionventa->impuesto = $request->impuesto;
                $cotizacionventa->total = $request->total;
                $cotizacionventa->estado = $request->estado;
                $cotizacionventa->idalmacen = $request->idalmacen;
                $cotizacionventa->validez = $nuevaFecha;
                $cotizacionventa->plazo_entrega = $request->n_validez;
                $cotizacionventa->tiempo_entrega = $request->tiempo_entrega;

                $cotizacionventa->lugar_entrega = $request->lugar_entrega;
                $cotizacionventa->forma_pago = $request->forma_pago;
                $cotizacionventa->nota = $request->nota;
                $cotizacionventa->condicion = '1';

                Log::info('DATOS REGISTRO COTIZACION_VENTA:', [
                    'idcliente' => $request->idcliente,
                    'idusuario' => $request->idusuario,
                    'fecha_hora' => $request->fecha_hora,
                    'impuesto' => $request->impuesto,
                    'total' => $request->total,
                    'estado' => $request->estado,
                    'validez' => $request->nuevaFecha,
                    'observacion' => $request->observacion,
                    'tiempo_entrega' => $request->tiempo_entrega,

                    'lugar_entrega' => $request->lugar_entrega,
                    'forma_pago' => $request->forma_pago,
                    'nota' => $request->nota,
                    'condicion' => $request->condicion,
                ]);
 
                $cotizacionventa->save();

                $detalles = $request->data; //Array de detalles

                Log::info('PRODUCTOS ARTICULOS PEDIDO PROVEEDOR:', [
                    'DATA' => $detalles,
                ]);

                foreach ($detalles as $det) {
                    $detalle = new DetalleCotizacionVenta();
                    $detalle->idcotizacion = $cotizacionventa->id;
                    $detalle->idarticulo = $det['idarticulo'];
                    $detalle->cantidad = $det['cantidad'];
                    $detalle->precio = $det['precioseleccionado'];
                    $detalle->descuento = $det['descuento'];
                    $detalle->save();
                }



                DB::commit();
                return [
                    'id' => $cotizacionventa->id
                ];



            }
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    public function editar(Request $request)
    {
        try {
            DB::beginTransaction();

            $fechaActual = now()->setTimezone('America/La_Paz');
            $nuevaFecha = $fechaActual->addDays($request->n_validez);

            // Encuentra el pedido de proveedor por su ID
            $cotizacionventa = CotizacionVenta::find($request->idcotizacionv);
            //Log::info($request->forma_pago);

            // Actualiza los campos de COTIZACION con los nuevos valores
            $cotizacionventa->idcliente = $request->idcliente;
            $cotizacionventa->idusuario = \Auth::user()->id;

            $cotizacionventa->fecha_hora = now()->setTimezone('America/La_Paz');
            $cotizacionventa->impuesto = $request->impuesto;
            $cotizacionventa->total = $request->total;
            $cotizacionventa->estado = $request->estado;

            $cotizacionventa->validez = $nuevaFecha; //---SAVER HASTA QUE FECHA ES LA ENTEGA
            $cotizacionventa->plazo_entrega = $request->n_validez;//-NUMERO DE DIAS PARA LA VALIDEZ
            $cotizacionventa->tiempo_entrega = $request->tiempo_entrega;

            $cotizacionventa->lugar_entrega = $request->lugar_entrega;
            $cotizacionventa->forma_pago = $request->forma_pago;
            $cotizacionventa->nota = $request->nota;
            $cotizacionventa->condicion = '1';

            Log::info('DATOS EDITADOS DE COTIZACION_VENTA:', [
                'idcliente' => $request->idcliente,
                'idusuario' => $request->idusuario,
                'fecha_hora' => $request->fecha_hora,
                'impuesto' => $request->impuesto,
                'total' => $request->total,
                'estado' => $request->estado,
                'validez' => $request->nuevaFecha,
                'observacion' => $request->observacion,
                'tiempo_entrega' => $request->tiempo_entrega,

                'lugar_entrega' => $request->lugar_entrega,
                'forma_pago' => $request->forma_pago,
                'nota' => $request->nota,
                'condicion' => $request->condicion,
            ]);
            // Guarda los cambios en el pedido de proveedor
            $cotizacionventa->save();

            // Elimina los detalles del pedido existentes
            DetalleCotizacionVenta::where('idcotizacion', $cotizacionventa->id)->delete();

            // Agrega los nuevos detalles del pedido
            $detalles = $request->data; // Array de detalles
            Log::info('PRODUCTOS ARTICULOS COTIZACION VENTA:', [
                'DATA!!' => $detalles,
            ]);
            foreach ($detalles as $det) {

                $detalle = new DetalleCotizacionVenta();
                $detalle->idcotizacion = $cotizacionventa->id;
                $detalle->idarticulo = $det['idarticulo'];
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio = $det['precioseleccionado'];
                $detalle->descuento = $det['descuento'];
                $detalle->save();
            }

            DB::commit(); // Confirmar los cambios en la base de datos
        } catch (Exception $e) {
            Log::error("Error al editar el pedido: " . $e->getMessage());
            DB::rollBack();
        }
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $cotizacionventa = CotizacionVenta::findOrFail($request->id);
        $cotizacionventa->condicion = '0';

        $cotizacionventa->save();
    }
    //


    public function activar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $cotizacionventa = CotizacionVenta::findOrFail($request->id);
        $cotizacionventa->condicion = '1';
        $cotizacionventa->save();
    }

    public function delete(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        try {
            DB::beginTransaction();

            $cotizacionVenta = CotizacionVenta::findOrFail($request->id);
            $cotizacionVenta->delete();

            // También puedes eliminar los detalles relacionados con la cotización de venta si es necesario
            DetalleCotizacionVenta::where('idcotizacion', $request->id)->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cotización de venta eliminada exitosamente.',
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Ocurrió un error al eliminar la cotización de venta.',
            ];
        }
    }

   

}