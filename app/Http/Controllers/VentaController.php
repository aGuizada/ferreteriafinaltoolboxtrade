<?php

namespace App\Http\Controllers;
use NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Venta;
use App\Persona;
use App\Articulo;
use App\Inventario;
use App\DetalleVenta;
use App\User;
use App\CreditoVenta;
use App\CuotasCredito;
use App\Empresa;
use App\Caja;
use App\Rol;
use Illuminate\Support\Facades\Log;
use App\Notifications\NotifyAdmin;
use FPDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade as PDF;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
class VentaController extends Controller
{
    public function __construct()
    {
        session_start();
    }

    /**
     * Lista de ventas con paginación
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
    
        $buscar = $request->buscar;
        $usuario = \Auth::user();
    
        $query = Venta::join('users', 'ventas.idusuario', '=', 'users.id')
            ->join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->select(
                'ventas.id',
                'ventas.tipo_comprobante',
                'ventas.serie_comprobante',
                'ventas.num_comprobante',
                'ventas.fecha_hora',
                'ventas.impuesto',
                'ventas.total',
                'ventas.estado',
                'ventas.idtipo_venta', // Añadido para identificar ventas a crédito
                'users.usuario',
                'personas.nombre as razonSocial',
                'personas.num_documento as documentoid'
            )
            ->orderBy('ventas.id', 'desc');
    
        // Filtrar por usuario si no es administrador
        if ($usuario->idrol != 1) { // Asumiendo que el rol 1 es el de administrador
            $query->where('ventas.idusuario', $usuario->id);
        }
    
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('ventas.num_comprobante', 'like', '%' . $buscar . '%')
                  ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
                  ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('ventas.fecha_hora', 'like', '%' . $buscar . '%')
                  ->orWhere('users.usuario', 'like', '%' . $buscar . '%');
            });
        }
    
        $ventas = $query->paginate(10);
    
        return [
            'pagination' => [
                'total' => $ventas->total(),
                'current_page' => $ventas->currentPage(),
                'last_page' => $ventas->lastPage(),
                'from' => $ventas->firstItem(),
                'to' => $ventas->lastItem(),
            ],
            'ventas' => $ventas,
            'usuario' => $usuario
        ];
    }
    public function indexCredito()
{
    $ventas = Venta::with('cliente', 'usuario')
        ->where('tipo_venta', 'credito')
        ->orderBy('id', 'desc')
        ->paginate(10);

    return Inertia::render('Ventas/Credito', [
        'ventas' => $ventas,
        'clientes' => Cliente::all(),
    ]);
}


    /**
     * Obtiene la cabecera de una venta específica
     */
    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
    
        $id = $request->id;
        
        // Consulta base con columnas que sabemos que existen
        $venta = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->join('users', 'ventas.idusuario', '=', 'users.id')
            ->select(
                'ventas.id',
                'ventas.tipo_comprobante',
                'ventas.serie_comprobante',
                'ventas.num_comprobante',
                'ventas.fecha_hora',
                'ventas.impuesto',
                'ventas.total',
                'ventas.estado',
                'ventas.idtipo_venta', // Importante para identificar el tipo de venta
                'ventas.idtipo_pago',
                // Omitimos los campos que no existen
                'personas.nombre',
                'personas.num_documento',
                'users.usuario'
            )
            ->where('ventas.id', '=', $id)
            ->first();
    
        $datos = ['venta' => $venta];
    
        // Si es una venta a crédito, obtener información adicional del crédito
        if ($venta && $venta->idtipo_venta == 2) {
            $credito = CreditoVenta::where('idventa', $id)->first();
            if ($credito) {
                $cuotas = CuotasCredito::where('idcredito', $credito->id)
                          ->orderBy('numero_cuota')
                          ->get();
                
                $datos['credito'] = $credito;
                $datos['cuotas'] = $cuotas;
            }
        }
    
        // Si es una venta adelantada, intentar obtener datos adicionales si existen
        if ($venta && $venta->idtipo_venta == 3) {
            // Solo si has migrado los campos
            if (Schema::hasColumn('ventas', 'direccion_entrega')) {
                $datosAdelantados = DB::table('ventas')
                    ->select('direccion_entrega', 'telefono_contacto', 'fecha_entrega', 'observaciones')
                    ->where('id', $id)
                    ->first();
                
                if ($datosAdelantados) {
                    $datos['datosAdelantados'] = $datosAdelantados;
                }
            }
        }
    
        return $datos;
    }
    /**
     * Obtiene los detalles de una venta específica
     */
    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $id = $request->id;
        $detalles = DetalleVenta::join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ventas.cantidad',
                'detalle_ventas.precio',
                'detalle_ventas.descuento',
                'articulos.nombre as articulo',
                'articulos.unidad_envase'
            )
            ->where('detalle_ventas.idventa', '=', $id)
            ->orderBy('detalle_ventas.id', 'desc')->get();

        return ['detalles' => $detalles];
    }

    /**
     * Registra una nueva venta
     */
 
     public function store(Request $request)
     {
         if (!$request->ajax()) return redirect('/');
     
         // Validar campos obligatorios (añadir descuento)
         $request->validate([
             'idcliente' => 'required|integer',
             'idtipo_pago' => 'required|integer',
             'idtipo_venta' => 'required|integer|in:1,2,3',
             'tipo_comprobante' => 'required|string',
             'serie_comprobante' => 'required|string',
             'num_comprobante' => 'required|string',
             'impuesto' => 'required|numeric',
             'total' => 'required|numeric',
             'descuento' => 'nullable|numeric|min:0', // Añadido descuento
             'idAlmacen' => 'required|integer',
             'data' => 'required|array|min:1'
         ], [
             'tipo_comprobante.required' => 'El tipo de comprobante es obligatorio.',
             'serie_comprobante.required' => 'La serie de comprobante es obligatoria.',
             'num_comprobante.required' => 'El número de comprobante es obligatorio.',
             'descuento.min' => 'El descuento no puede ser negativo' // Mensaje para descuento
         ]);
     
         try {
             DB::beginTransaction();
     
             if (!$this->validarCajaAbierta()) {
                 return ['id' => -1, 'caja_validado' => 'Debe tener una caja abierta'];
             }
     
             if (!is_array($request->data)) {
                 throw new \Exception("Los datos de los productos no son válidos");
             }
     
             if (empty($request->data)) {
                 throw new \Exception("No se han proporcionado productos para la venta");
             }
     
             // Validar que el descuento no sea mayor al total
             if ($request->descuento && $request->descuento > $request->total) {
                 throw new \Exception("El descuento no puede ser mayor al total de la venta");
             }
     
             // Aplicar descuento al total si existe
             $totalConDescuento = $request->descuento 
                 ? $request->total - $request->descuento
                 : $request->total;
     
             // Modificar el request para pasar el total con descuento
             $request->merge(['total' => $totalConDescuento]);
     
             if ($request->tipo_comprobante === "RESIVO") {
                 $venta = $this->crearVentaResivo($request);
             } else {
                 $venta = $this->crearVenta($request);
             }
     
             // Asignar el descuento a la venta
             $venta->descuento = $request->descuento ?? 0;
             $venta->save();
     
             $this->actualizarCaja($request);
             $this->registrarDetallesVenta($venta, $request->data, $request->idAlmacen);
             $this->notificarAdministradores();
     
             DB::commit();
             
             // Respuesta diferenciada según tipo de venta
             if ($venta->idtipo_venta == 2) { // Si es venta a crédito
                 return response()->json([
                     'id' => $venta->id,
                     'tipo' => 'credito',
                     'pdf_url' => url("/venta/descargarPlanPagos/{$venta->id}"),
                     'message' => 'Venta a crédito registrada correctamente',
                     'descuento_aplicado' => $venta->descuento // Opcional: devolver descuento aplicado
                 ]);
             } else { // Venta al contado
                 return response()->json([
                     'id' => $venta->id,
                     'tipo' => 'contado',
                     'message' => 'Venta registrada correctamente',
                     'descuento_aplicado' => $venta->descuento // Opcional: devolver descuento aplicado
                 ]);
             }
         } catch (\Exception $e) {
             DB::rollBack();
             \Log::error('Error al registrar venta: ' . $e->getMessage() . ' - Línea: ' . $e->getLine() . ' - Archivo: ' . $e->getFile());
             return ['error' => $e->getMessage()];
         }
     }

    /**
     * Valida si hay una caja abierta
     */
    private function validarCajaAbierta()
    {
        $user = Auth::user();
    
        if ($user->idrol == 1) {
            return Caja::where('estado', '1')->exists();
        }
    
        if (!isset($user->idsucursal)) {
            return false;
        }
    
        return Caja::where('estado', '1')
            ->where('idsucursal', $user->idsucursal)
            ->exists();
    }

    /**
     * Crea una venta regular
     */
    private function crearVenta($request)
    {
        try {
            DB::beginTransaction();

            // Validate credit sale data
            if ($request->idtipo_venta == 2) {
                if (!$request->numero_cuotas || !$request->tiempo_dias_cuota || !$request->total_credito) {
                    \Log::error('Credit data validation failed:', [
                        'numero_cuotas' => $request->numero_cuotas,
                        'tiempo_dias_cuota' => $request->tiempo_dias_cuota,
                        'total_credito' => $request->total_credito,
                        'cuotaspago' => $request->cuotaspago
                    ]);
                    throw new \Exception('Datos de crédito incompletos');
                }
                
                if (empty($request->cuotaspago)) {
                    throw new \Exception('No se proporcionaron cuotas para el crédito');
                }
            }

            // Get last receipt number
            $ultimaVenta = DB::table('ventas')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();
                
            $ultimoNumero = $ultimaVenta ? intval($ultimaVenta->num_comprobante) : 0;
            $nuevoNumero = str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);
            
            $venta = new Venta();
            $venta->idcliente = $request->idcliente;
            $venta->idtipo_pago = $request->idtipo_pago;
            $venta->idtipo_venta = $request->idtipo_venta;
            $venta->tipo_comprobante = $request->tipo_comprobante;
            $venta->serie_comprobante = $request->serie_comprobante;
            $venta->num_comprobante = $nuevoNumero;
            $venta->impuesto = $request->impuesto;
            $venta->total = $request->total;
            $venta->idusuario = \Auth::user()->id;
            $venta->fecha_hora = now()->setTimezone('America/La_Paz');
            $venta->idcaja = Caja::latest()->first()->id;
            
            // Para ventas al contado, guardar monto recibido y cambio
            if ($request->idtipo_venta == 1 && isset($request->monto_recibido)) {
                $venta->monto_recibido = $request->monto_recibido;
                $venta->cambio = $request->cambio;
            }
            
            // Establecer el estado según el tipo de venta
            if ($request->idtipo_venta == 2) { // Crédito
                $venta->estado = 'Pendiente';
            } else  if ($request->idtipo_venta == 3) { // Adelantada
                $venta->estado = 'Pendiente';
                
                // Guardar datos de entrega para ventas adelantadas
                $venta->direccion_entrega = $request->direccion_entrega;
                $venta->telefono_contacto = $request->telefono_contacto;
                $venta->fecha_entrega = $request->fecha_entrega;
                $venta->observaciones = $request->observaciones;
                
                // Si es pago en efectivo, guardar monto y cambio
                if ($request->idtipo_pago == 1 && isset($request->monto_recibido)) {
                    $venta->monto_recibido = $request->monto_recibido;
                    $venta->cambio = $request->cambio;
                }
            
            } else { // Contado
                $venta->estado = 'Registrado';
            }
            
            $venta->save();
            
            // Para ventas a crédito, guardar información del crédito
            if ($request->idtipo_venta == 2) {
                $creditoventa = new CreditoVenta();
                $creditoventa->idventa = $venta->id;
                $creditoventa->idcliente = $request->idcliente;
                $creditoventa->numero_cuotas = intval($request->numero_cuotas);
                $creditoventa->tiempo_dias_cuota = intval($request->tiempo_dias_cuota);
                $creditoventa->total = floatval($request->total_credito);
                $creditoventa->estado = 'Pendiente';
                
                // Get first unpaid installment date
                $primeraCuota = collect($request->cuotaspago)->first();
                if (!$primeraCuota || !isset($primeraCuota['fecha_pago'])) {
                    throw new \Exception('Primera cuota no proporcionada correctamente');
                }
                
                $creditoventa->proximo_pago = $primeraCuota['fecha_pago'];
                $creditoventa->save();

                // Save installments
                foreach ($request->cuotaspago as $index => $cuota) {
                    $cuotaCredito = new CuotasCredito();
                    $cuotaCredito->idcredito = $creditoventa->id;
                    $cuotaCredito->numero_cuota = $index + 1;
                    $cuotaCredito->fecha_pago = $cuota['fecha_pago'];
                    $cuotaCredito->precio_cuota = floatval($cuota['precio_cuota']);
                    $cuotaCredito->saldo_restante = floatval($cuota['saldo_restante']);
                    $cuotaCredito->estado = 'Pendiente';
                    $cuotaCredito->save();
                }
            }
            
            DB::commit();
            return $venta;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en crearVenta: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }
    protected function crearVentaSegunTipo(Request $request)
{
    $venta = new Venta();
    $venta->idcliente = $request->idcliente;
    $venta->idusuario = Auth::id();
    $venta->idcaja = Caja::where('estado', 1)->first()->id;
    $venta->tipo_comprobante = $request->tipo_comprobante ?? 'RECIBO';
    $venta->serie_comprobante = $request->serie_comprobante ?? '001';
    $venta->num_comprobante = $this->generarNumeroComprobante();
    $venta->fecha_hora = now();
    $venta->impuesto = $request->impuesto ?? 0;
    $venta->total = $this->calcularTotal($request->data);
    $venta->idtipo_venta = $request->idtipo_venta;
    $venta->idtipo_pago = $request->idtipo_pago;
    $venta->estado = $request->idtipo_venta == 1 ? 'Registrado' : 'Pendiente';
    
    // Campos específicos para venta adelantada
    if ($request->idtipo_venta == 3) {
        $venta->direccion_entrega = $request->direccion_entrega;
        $venta->telefono_contacto = $request->telefono_contacto;
        $venta->fecha_entrega = $request->fecha_entrega;
        $venta->observaciones = $request->observaciones;
    }
    
    $venta->save();

    // Si es venta a crédito, crear el crédito
    if ($request->idtipo_venta == 2) {
        $this->crearCreditoVenta($venta, $request);
    }

    return $venta;
}






public function descargarPlanPagos($id)
{
    // Obtener los datos necesarios
    $venta = Venta::with('cliente', 'credito.cuotas')->findOrFail($id);
    $cliente = $venta->cliente;
    $credito = $venta->credito;
    $cuotasPagadas = $credito->cuotas->where('estado', 'Pagado');
    $totalCuotas = $credito->cuotas->sum('precio_cuota');

    // Cargar la vista Blade como HTML
    $pdfContent = view('pdf.recibo_general_creditos', [
        'venta' => $venta,
        'cliente' => $cliente,
        'credito' => $credito,
        'cuotasPagadas' => $cuotasPagadas,
        'totalCuotas' => $totalCuotas,
    ])->render();

    // Generar el PDF usando DomPDF
    $pdf = PDF::loadHTML($pdfContent);

    // Descargar el PDF generado
    return $pdf->download("plan_pagos_{$id}.pdf");
}


         private function crearVentaResivo($request)
    {
        $ventaResivo = new Venta();
        
        // Campos básicos de la venta
        $ventaResivo->idcliente = $request->idcliente;
        $ventaResivo->idtipo_pago = $request->idtipo_pago;
        $ventaResivo->idtipo_venta = $request->idtipo_venta;
        $ventaResivo->tipo_comprobante = $request->tipo_comprobante;
        $ventaResivo->serie_comprobante = $request->serie_comprobante;
        $ventaResivo->num_comprobante = $request->num_comprobante;
        $ventaResivo->impuesto = $request->impuesto;
        $ventaResivo->total = $request->total;
        $ventaResivo->idusuario = \Auth::user()->id;
        $ventaResivo->fecha_hora = now()->setTimezone('America/La_Paz');
        $ventaResivo->idcaja = Caja::latest()->first()->id;
        
        // Para ventas adelantadas (tipo 3), añadir campos específicos
        if ($request->idtipo_venta == 3) {
            $ventaResivo->direccion_entrega = $request->direccion_entrega;
            $ventaResivo->telefono_contacto = $request->telefono_contacto;
            $ventaResivo->fecha_entrega = $request->fecha_entrega;
            $ventaResivo->observaciones = $request->observaciones;
            $ventaResivo->estado = 'Pendiente';
        } 
        // Para ventas a crédito (tipo 2)
        else if ($request->idtipo_venta == 2) {
            $ventaResivo->estado = 'Pendiente';
        } 
        // Para ventas al contado (tipo 1)
        else {
            $ventaResivo->estado = 'Registrado';
        }
        
        $ventaResivo->save();
        
        // Si es venta a crédito, registrar información de crédito
        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($ventaResivo, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }
        
        return $ventaResivo;
    }
    public function registrar(Request $request)
{
    DB::beginTransaction();
    try {
        // Crear la venta
        $venta = new Venta();
        $venta->idcliente = $request->idcliente;
        $venta->tipo_comprobante = $request->tipo_comprobante;
        $venta->serie_comprobante = $request->serie_comprobante;
        $venta->num_comprobante = $request->num_comprobante;
        $venta->impuesto = $request->impuesto;
        $venta->total = $request->total;
        $venta->idAlmacen = $request->idAlmacen;
        $venta->idtipo_pago = $request->idtipo_pago;
        $venta->idtipo_venta = $request->idtipo_venta;
        
        // Obtener la caja abierta actual
        $cajaActual = Caja::where('estado', '1')
            ->where('idsucursal', \Auth::user()->idsucursal)
            ->firstOrFail();
        
        // Asociar la venta a la caja actual
        $venta->idcaja = $cajaActual->id;
        $venta->save();

        if ($request->idtipo_pago == 1) { // Pago al contado
            $cajaActual->ventasContado += $request->total;
            $cajaActual->saldoCaja += $request->total;
        } elseif ($request->idtipo_pago == 4) { // Pago QR
            $cajaActual->pagosQR += $request->total;
            $cajaActual->saldoCaja += $request->total;
        } elseif ($request->idtipo_pago == 3) { // Pago por Transferencia
            $cajaActual->pagosTransferencia += $request->total;
            $cajaActual->saldoCaja += $request->total;
        }
        $cajaActual->save();

        // Guardar detalles de venta
        foreach ($request->data as $detalle) {
            $ventaDetalle = new VentaDetalle();
            $ventaDetalle->idventa = $venta->id;
            $ventaDetalle->idarticulo = $detalle['idarticulo'];
            $ventaDetalle->cantidad = $detalle['cantidad'];
            $ventaDetalle->precio = $detalle['precio'];
            $ventaDetalle->descuento = $detalle['descuento'] ?? 0;
            $ventaDetalle->save();

            // Actualizar stock
            $articulo = Articulo::findOrFail($detalle['idarticulo']);
            $articulo->stock -= $detalle['cantidad'];
            $articulo->save();
        }

        DB::commit();

        return response()->json([
            'status' => 'success', 
            'id' => $venta->id,
            'message' => 'Venta registrada correctamente',
            'caja' => [
                'ventasContado' => $cajaActual->ventasContado,
                'pagosQR' => $cajaActual->pagosQR,
                'saldoCaja' => $cajaActual->saldoCaja
            ]
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al registrar venta: ' . $e->getMessage());
        return response()->json([
            'error' => 'No se pudo registrar la venta: ' . $e->getMessage()
        ], 500);
    }
}
private function registrarCuotasCredito($creditoVenta, $cuotasPago)
{
    if (empty($cuotasPago) || !is_array($cuotasPago)) {
        throw new \Exception('No se proporcionaron cuotas para el crédito');
    }

    foreach ($cuotasPago as $index => $cuota) {
        $cuotaCredito = new CuotasCredito();
        $cuotaCredito->idcredito = $creditoVenta->id;
        $cuotaCredito->numero_cuota = $cuota['numero_cuota'] ?? ($index + 1);
        $cuotaCredito->fecha_pago = $cuota['fecha_pago'];
        $cuotaCredito->precio_cuota = floatval($cuota['precio_cuota']);
        $cuotaCredito->saldo_restante = floatval($cuota['saldo_restante']);
        $cuotaCredito->estado = $cuota['estado'] ?? 'Pendiente';
        $cuotaCredito->save();
    }
}
public function confirmarEntrega(Request $request)
{
    if (!$request->ajax()) {
        return redirect('/');
    }

    try {
        DB::beginTransaction();
        
        // Buscar la venta
        $venta = Venta::findOrFail($request->id);
        
        // Verificar que la venta tenga estado Pendiente
        if ($venta->estado !== 'Pendiente') {
            return response()->json([
                'error' => 'Esta venta no está en estado Pendiente.'
            ], 400);
        }
        
        // Si es venta adelantada, registrar en caja
        if ($venta->idtipo_venta == 3) {
            $ultimaCaja = Caja::latest()->first();
            
            // Registrar según el tipo de pago
            if ($venta->idtipo_pago == 1) { // Efectivo
                $ultimaCaja->ventasAdelantadas += $venta->total;
                $ultimaCaja->ventas += $venta->total;
                $ultimaCaja->pagosEfectivoVentas += $venta->total;
                $ultimaCaja->saldoCaja += $venta->total;
            } elseif ($venta->idtipo_pago == 4) { // QR
                $ultimaCaja->pagosQR += $venta->total;
                $ultimaCaja->ventasAdelantadas += $venta->total;
                $ultimaCaja->saldoCaja += $venta->total;
                $ultimaCaja->ventas += $venta->total;
            } elseif ($venta->idtipo_pago == 3) { // Transferencia
                $ultimaCaja->pagosTransferencia += $venta->total;
                $ultimaCaja->ventasAdelantadas += $venta->total;
                $ultimaCaja->saldoCaja += $venta->total;
                $ultimaCaja->ventas += $venta->total;
            } elseif ($venta->idtipo_pago == 2) { // Tarjeta
                $ultimaCaja->pagosTarjeta += $venta->total;
                $ultimaCaja->ventasAdelantadas += $venta->total;
                $ultimaCaja->saldoCaja += $venta->total;
                $ultimaCaja->ventas += $venta->total;
            }
            
            $ultimaCaja->save();
        }
        
        // Actualizar estado de la venta
        $venta->estado = 'Entregado';
        $venta->save();
        
        DB::commit();
        
        return response()->json([
            'mensaje' => 'Entrega confirmada correctamente.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al confirmar entrega: ' . $e->getMessage());
        return response()->json([
            'error' => 'Error al confirmar entrega: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Actualiza la caja con la nueva venta
     */
   

     private function actualizarCaja($request)
     {
         $ultimaCaja = Caja::latest()->first();
     
         // Si es venta a crédito, NO sumar nada a caja (a menos que haya abono inicial)
         if ($request->idtipo_venta == 2) {
             // Si hay abono inicial (primer_precio_cuota), solo sumar ese monto
             $abonoInicial = $request->primer_precio_cuota ?? 0;
             if ($abonoInicial > 0) {
                 $ultimaCaja->ventasCredito += $abonoInicial;
                 $ultimaCaja->ventas += $abonoInicial;
                 if ($request->idtipo_pago == 1) {
                     $ultimaCaja->pagosEfectivoVentas += $abonoInicial;
                     $ultimaCaja->saldoCaja += $abonoInicial;
                 }
                 // Si el abono inicial es por otro medio de pago, puedes agregar lógica aquí si lo necesitas
             }
             // Si no hay abono inicial, no sumar nada a caja
             $ultimaCaja->save();
             return;
         }
     
         // Si es venta adelantada (tipo 3), no registrar en caja hasta la entrega
         if ($request->idtipo_venta == 3) {
             return;
         }
     
         // Si el pago es en efectivo (tipo 1)
         if ($request->idtipo_pago == 1) {
             $ultimaCaja->ventasContado += $request->total;
             $ultimaCaja->ventas += $request->total;
             $ultimaCaja->pagosEfectivoVentas += $request->total;
             $ultimaCaja->saldoCaja += $request->total;
         } 
         // Si es otro medio de pago (no efectivo)
         else {
             if ($request->idtipo_pago == 4) { // QR
                 $ultimaCaja->pagosQR += $request->total;
                 $ultimaCaja->saldoCaja += $request->total;
                 $ultimaCaja->ventas += $request->total;
             } elseif ($request->idtipo_pago == 3) { // Transferencia
                 $ultimaCaja->pagosTransferencia += $request->total;
                 $ultimaCaja->saldoCaja += $request->total;
                 $ultimaCaja->ventas += $request->total;
             } elseif ($request->idtipo_pago == 2) { // Tarjeta
                 $ultimaCaja->pagosTarjeta += $request->total;
                 $ultimaCaja->saldoCaja += $request->total;
                 $ultimaCaja->ventas += $request->total;
             }
         }
     
         $ultimaCaja->save();
     }

    
public function obtenerCuotas(Request $request)
{
    if (!$request->ajax())
        return redirect('/');

    $id = $request->id;
    
    // Verificar si la venta existe
    $venta = Venta::find($id);
    if (!$venta) {
        return response()->json(['error' => 'Venta no encontrada'], 404);
    }
    
    // Verificar si tiene crédito
    $credito = CreditoVenta::where('idventa', $id)->first();
    if (!$credito) {
        return response()->json(['error' => 'Esta venta no tiene crédito asociado'], 404);
    }
    
    // Obtener las cuotas
    $cuotas = CuotasCredito::where('idcredito', $credito->id)
        ->orderBy('numero_cuota')
        ->get();
    
    return response()->json(['cuotas' => $cuotas, 'credito' => $credito]);
}
    /**
     * Registra los detalles de la venta
     */
    private function registrarDetallesVenta($venta, $detalles, $idAlmacen)
    {
        foreach ($detalles as $ep => $det) {
            $this->actualizarInventario($idAlmacen, $det);
            $detalleVenta = new DetalleVenta();
            $detalleVenta->idventa = $venta->id;
            $detalleVenta->idarticulo = $det['idarticulo'];
            $detalleVenta->cantidad = $det['cantidad'];
            $detalleVenta->precio = $det['precio'];
            $detalleVenta->descuento = $det['descuento'] ?? 0;
            $detalleVenta->save();
        }
        $_SESSION['sidAlmacen'] = $idAlmacen;
        $_SESSION['sdetalle'] = $detalles;
    }

    /**
     * Actualiza el inventario con la nueva venta
     */
    private function actualizarInventario($idAlmacen, $detalle)
    {
        $cantidadRestante = $detalle['cantidad'];
        $fechaActual = now();
        $inventarios = Inventario::where('idalmacen', $idAlmacen)
            ->where('idarticulo', $detalle['idarticulo'])
            ->where('fecha_vencimiento','>',$fechaActual)
            ->orderBy('id')
            ->get();

        foreach ($inventarios as $inventario) {
            if ($cantidadRestante <= 0) {
                break;
            }

            if ($inventario->saldo_stock >= $cantidadRestante) {
                $inventario->saldo_stock -= $cantidadRestante;
                $cantidadRestante = 0;
            } else {
                $cantidadRestante -= $inventario->saldo_stock;
                $inventario->saldo_stock = 0;
            }

            $inventario->save();
        }
    }



    /**
     * Notifica a los administradores sobre la venta
     */
    private function notificarAdministradores()
    {
        $fechaActual = date('Y-m-d');
        $numVentas = Venta::whereDate('created_at', $fechaActual)->count();
        $numIngresos = 0; // Simplificado ya que no parece ser crítico

        $arreglosDatos = [
            'ventas' => ['numero' => $numVentas, 'msj' => 'Ventas'],
            'ingresos' => ['numero' => $numIngresos, 'msj' => 'Ingresos']
        ];

        $allUsers = User::all();

        foreach ($allUsers as $notificar) {
            User::findOrFail($notificar->id)->notify(new NotifyAdmin($arreglosDatos));
        }
    }

    /**
     * Anula una venta
     */

     public function desactivar(Request $request)
     {
         if (!$request->ajax()) {
             return redirect('/');
         }
 
         try {
             DB::beginTransaction();
 
             // Obtener el rol del usuario autenticado
             $usuario = Auth::user();
 
             // Verificar si el usuario tiene permiso (no es rol 2)
             if ($usuario->idrol === 2) {
                 return response()->json([
                     'error' => 'No tiene permisos para anular ventas'
                 ], 403);
             }
 
             // Buscar la venta a anular
             $venta = Venta::findOrFail($request->id);
 
             // Si ya está anulada, no hacer nada
             if ($venta->estado === 'Anulado') {
                 return response()->json([
                     'mensaje' => 'La venta ya está anulada'
                 ]);
             }
 
             // Revertir movimientos en caja
             $this->revertirCaja($venta);
 
             // Revertir inventario
             $this->revertirInventarioVenta($venta);
 
             // Anular la venta
             $venta->estado = 'Anulado';
             $venta->save();
 
             DB::commit();
 
             return response()->json([
                 'mensaje' => 'Venta anulada correctamente y movimientos revertidos'
             ]);
 
         } catch (\Exception $e) {
             DB::rollBack();
             return response()->json([
                 'error' => 'Error al anular la venta: ' . $e->getMessage()
             ], 500);
         }
     }
 
   
  
     private function revertirCaja($venta)
     {
         \Log::info("Iniciando reversión de caja para venta ID: {$venta->id}, idcaja: {$venta->idcaja}");
     
         $caja = Caja::find($venta->idcaja);
         if (!$caja) {
             \Log::error("No se encontró la caja con id: {$venta->idcaja} para la venta: {$venta->id}");
             return;
         }
     
         \Log::info("Caja encontrada: {$caja->id}. tipo_pago: {$venta->idtipo_pago}, tipo_venta: {$venta->idtipo_venta}, total: {$venta->total}");
     
         // Efectivo
         if ($venta->idtipo_pago == 1) {
             if ($venta->idtipo_venta == 2) { // Crédito
                 $primerCuota = CreditoVenta::where('idventa', $venta->id)->first();
                 $primerMonto = 0;
                 if ($primerCuota) {
                     $cuota = CuotasCredito::where('idcredito', $primerCuota->id)
                         ->orderBy('numero_cuota')
                         ->first();
                     $primerMonto = $cuota ? $cuota->precio_cuota : 0;
                 }
                 $caja->ventas -= $primerMonto;
                 $caja->pagosEfectivoVentas -= $primerMonto;
                 $caja->ventasCredito -= $primerMonto;
                 $caja->saldoCaja -= $primerMonto;
                 \Log::info("Revertido crédito efectivo: -{$primerMonto} en ventas, pagosEfectivoVentas, ventasCredito, saldoCaja");
             } else if ($venta->idtipo_venta == 3) { // Adelantada
                 $caja->ventasAdelantadas -= $venta->total;
                 $caja->ventas -= $venta->total;
                 $caja->pagosEfectivoVentas -= $venta->total;
                 $caja->saldoCaja -= $venta->total;
                 \Log::info("Revertido adelantada efectivo: -{$venta->total} en ventasAdelantadas, ventas, pagosEfectivoVentas, saldoCaja");
             } else { // Contado
                 $caja->ventasContado -= $venta->total;
                 $caja->ventas -= $venta->total;
                 $caja->pagosEfectivoVentas -= $venta->total;
                 $caja->saldoCaja -= $venta->total;
                 \Log::info("Revertido contado efectivo: -{$venta->total} en ventasContado, ventas, pagosEfectivoVentas, saldoCaja");
             }
         }
         // Otros medios de pago (ejemplo: QR, tarjeta, transferencia, etc.)
         else {
             if ($venta->idtipo_pago == 4) { // QR
                 $caja->pagosQR -= $venta->total;
                 if ($venta->idtipo_venta == 3) {
                     $caja->ventasAdelantadas -= $venta->total;
                 }
                 $caja->saldoCaja -= $venta->total;
                 $caja->ventas -= $venta->total;
                 \Log::info("Revertido QR: -{$venta->total} en pagosQR, saldoCaja, ventas");
             } else if ($venta->idtipo_pago == 2) { // Tarjeta
                 $caja->pagosTarjeta -= $venta->total;
                 if ($venta->idtipo_venta == 3) {
                     $caja->ventasAdelantadas -= $venta->total;
                 }
                 $caja->saldoCaja -= $venta->total;
                 $caja->ventas -= $venta->total;
                 \Log::info("Revertido tarjeta: -{$venta->total} en pagosTarjeta, saldoCaja, ventas");
             } else if ($venta->idtipo_pago == 3) { // Transferencia
                 $caja->pagosTransferencia -= $venta->total;
                 if ($venta->idtipo_venta == 3) {
                     $caja->ventasAdelantadas -= $venta->total;
                 }
                 $caja->saldoCaja -= $venta->total;
                 $caja->ventas -= $venta->total;
                 \Log::info("Revertido transferencia: -{$venta->total} en pagosTransferencia, saldoCaja, ventas");
             } else {
                 // Otros medios de pago no especificados
                 if ($venta->idtipo_venta == 1 || $venta->idtipo_venta == 3) {
                     $caja->ventas -= $venta->total;
                     \Log::info("Revertido otro medio de pago: -{$venta->total} en ventas");
                 } else { // Crédito con otro medio de pago
                     $primerCuota = CreditoVenta::where('idventa', $venta->id)->first();
                     $primerMonto = 0;
                     if ($primerCuota) {
                         $cuota = CuotasCredito::where('idcredito', $primerCuota->id)
                             ->orderBy('numero_cuota')
                             ->first();
                         $primerMonto = $cuota ? $cuota->precio_cuota : 0;
                     }
                     $caja->ventas -= $primerMonto;
                     \Log::info("Revertido crédito otro medio de pago: -{$primerMonto} en ventas");
                 }
             }
         }
     
         // Evitar negativos
         $campos = ['ventas', 'ventasContado', 'pagosEfectivoVentas', 'ventasCredito', 'saldoCaja', 'pagosQR', 'pagosTarjeta', 'pagosTransferencia', 'ventasAdelantadas'];
         foreach ($campos as $campo) {
             if (isset($caja->$campo) && $caja->$campo < 0) {
                 \Log::warning("El campo {$campo} quedó negativo ({$caja->$campo}), se ajusta a 0");
                 $caja->$campo = 0;
             }
         }
     
         $caja->save();
         \Log::info("Caja actualizada tras reversión de venta {$venta->id}: " . json_encode($caja->toArray()));
     }
     
 
 
     /**
      * Revierte el inventario de una venta anulada
      */
     private function revertirInventarioVenta($venta)
     {
         // Obtener detalles de la venta
         $detalles = DetalleVenta::where('idventa', $venta->id)->get();
 
         // Si la venta tiene almacén asociado
         $idAlmacen = $venta->idAlmacen ?? ($_SESSION['sidAlmacen'] ?? null);
 
         foreach ($detalles as $detalle) {
             // Buscar inventario por almacén y artículo
             $inventario = Inventario::where('idalmacen', $idAlmacen)
                 ->where('idarticulo', $detalle->idarticulo)
                 ->first();
 
             if ($inventario) {
                 $inventario->saldo_stock += $detalle->cantidad;
                 $inventario->save();
             }
         }
     }

    /**
     * Imprime un recibo en formato de rollo térmico
     */
    public function imprimirResivoRollo($id)
    {
        try {
            \Log::info("Iniciando generación de recibo en rollo para venta ID: $id");
            
            $venta = Venta::with('detalles.producto')->find($id);
            if (!$venta) {
                \Log::error("Venta no encontrada: $id");
                return response()->json(['error' => 'NO SE ENCONTRÓ LA VENTA'], 404);
            }
            \Log::info("Venta encontrada", ['venta_id' => $venta->id]);
    
            // Calcular total con descuento
            $totalConDescuento = $venta->total;
            $descuento = $venta->descuento ?? 0;
            
            $persona = Persona::find($venta->idcliente);
            if (!$persona) {
                \Log::error("Cliente no encontrado para venta: $id");
                return response()->json(['error' => 'NO SE ENCONTRÓ EL CLIENTE'], 404);
            }
    
            $empresa = Empresa::first();
            if (!$empresa) {
                \Log::error("No hay empresa configurada");
                return response()->json(['error' => 'NO SE ENCONTRÓ LA EMPRESA'], 404);
            }
    
            if ($venta->detalles->isEmpty()) {
                \Log::error("Venta sin detalles", ['venta_id' => $venta->id]);
                return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 404);
            }
    
            // Verifica el logo
            $logoPath = null;
            if ($empresa->logo) {
                $logoPath = storage_path('app/public/logos/' . $empresa->logo);
                if (!file_exists($logoPath)) {
                    \Log::warning("Logo no encontrado en: $logoPath");
                    $logoPath = null;
                }
            }
    
            \Log::info("Generando PDF...");
            $pdf = \PDF::loadView('pdf.recibo_venta_rollo', [
                'venta' => $venta,
                'persona' => $persona,
                'empresa' => $empresa,
                'logoPath' => $logoPath,
                'totalConDescuento' => $totalConDescuento,
                'descuento' => $descuento
            ])->setPaper([0, 0, 226.77, 600], 'portrait');
    
            \Log::info("PDF generado correctamente");
            
            $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
            return $pdf->download("recibo_rollo_{$nombreLimpio}_{$id}.pdf");
    
        } catch (\Exception $e) {
            \Log::error('Error al imprimir el recibo en rollo: ' . $e->getMessage() . 
                       ' - Line: ' . $e->getLine() . 
                       ' - File: ' . $e->getFile());
            return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN ROLLO: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Imprime un recibo en formato carta
     */
    public function imprimirResivoCarta($id)
    {
        try {
            $venta = Venta::with('detalles.producto')->find($id);
            if (!$venta) {
                return response()->json(['error' => 'NO SE ENCONTRÓ LA VENTA'], 404);
            }
    
            $persona = Persona::find($venta->idcliente);
            if (!$persona) {
                return response()->json(['error' => 'NO SE ENCONTRÓ EL CLIENTE'], 404);
            }
    
            $empresa = Empresa::first();
            if (!$empresa) {
                return response()->json(['error' => 'NO SE ENCONTRÓ LA EMPRESA'], 404);
            }
    
            if ($venta->detalles->isEmpty()) {
                return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 404);
            }
    
            // Calcular total con descuento
            $totalConDescuento = $venta->total;
            $descuento = $venta->descuento ?? 0;
    
            $pdf = \PDF::loadView('pdf.recibo_venta_carta', [
                'venta' => $venta,
                'persona' => $persona,
                'empresa' => $empresa,
                'totalConDescuento' => $totalConDescuento,
                'descuento' => $descuento
            ])->setPaper('letter', 'portrait');
    
            $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
            return $pdf->download("recibo_carta_{$nombreLimpio}_{$id}.pdf");
    
        } catch (\Exception $e) {
            \Log::error('Error al imprimir el recibo en carta: ' . $e->getMessage());
            return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN CARTA'], 500);
        }
    }

    /**
     * Obtiene el último número de comprobante
     */
    public function obtenerUltimoComprobante(Request $request) {
        try {
            DB::beginTransaction();
            
            // Get the last receipt number with table lock to prevent race conditions
            $ultimaVenta = DB::table('ventas')
                ->lockForUpdate()
                ->orderBy('id', 'desc')  // Changed to order by ID to ensure proper sequence
                ->first();
                
            // If there's a previous sale, increment the number, otherwise start from 1
            if ($ultimaVenta) {
                $ultimoNumero = intval($ultimaVenta->num_comprobante);
                $siguienteNumero = $ultimoNumero + 1;
            } else {
                $siguienteNumero = 1;
            }
            
            // Format number with leading zeros (5 digits)
            $numeroFormateado = str_pad($siguienteNumero, 5, '0', STR_PAD_LEFT);
            
            DB::commit();
            
            return response()->json([
                'last_comprobante' => $numeroFormateado,
                'numero' => $siguienteNumero
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al obtener último comprobante: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener último comprobante',
                'last_comprobante' => '00001'
            ]);
        }
    }

    /**
     * Crea un registro de crédito para la venta
     */
    private function crearCreditoVenta($venta, $request)
    {
        // Permitir ambos nombres de campo para compatibilidad
        $numeroCuotas = $request->numero_cuotas ?? $request->numero_cuotasCredito ?? null;
        $tiempoDiasCuota = $request->tiempo_dias_cuota ?? null;
        $totalCredito = $request->total ?? $request->total_credito ?? null;
    
        // Validar que los datos requeridos no sean nulos
        if (!$numeroCuotas || !$tiempoDiasCuota || !$totalCredito) {
            throw new \Exception('Datos de crédito incompletos: número de cuotas, tiempo de días por cuota o total no proporcionados.');
        }
    
        $creditoventa = new CreditoVenta();
        $creditoventa->idventa = $venta->id;
        $creditoventa->idcliente = $request->idcliente;
        $creditoventa->numero_cuotas = intval($numeroCuotas);
        $creditoventa->tiempo_dias_cuota = intval($tiempoDiasCuota);
        $creditoventa->total = floatval($totalCredito);
        $creditoventa->estado = $request->estado ?? 'Pendiente';
    
        // Validar y asignar el próximo pago
        $primerCuotaNoPagada = collect($request->cuotaspago)->firstWhere('estado', '!=', 'Pagado');
        if (!$primerCuotaNoPagada || !isset($primerCuotaNoPagada['fecha_pago'])) {
            throw new \Exception('No se proporcionó una fecha válida para el próximo pago.');
        }
        $creditoventa->proximo_pago = $primerCuotaNoPagada['fecha_pago'];
    
        $creditoventa->save();
    
        return $creditoventa;
    }
    public function topClientes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topVendedores = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->select(
                'ventas.idcliente',
                'personas.nombre as nombreCliente',
                DB::raw('COUNT(*) as cantidadCompras'),
                DB::raw('SUM(ventas.total) as totalGastado')
            )
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('ventas.idcliente', 'personas.nombre')
            ->orderByDesc('cantidadCompras')
            ->limit(10)
            ->get();

        return response()->json(['topClientes' => $topVendedores]);
    }

    public function topProductos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topProductos = DetalleVenta::join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ventas.idarticulo',
                'articulos.nombre as nombreArticulo',
                DB::raw('SUM(detalle_ventas.cantidad) as cantidadTotal'),
                DB::raw('COUNT(*) as vecesVendido')
            )
            ->join('ventas', 'detalle_ventas.idventa', '=', 'ventas.id')
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('detalle_ventas.idarticulo', 'articulos.nombre')
            ->orderByDesc('cantidadTotal')
            ->limit(10)
            ->get();

        return response()->json(['topProductos' => $topProductos]);
    }    public function topVendedores(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topVendedores = Venta::join('personas', 'ventas.idusuario', '=', 'personas.id')
            ->select(
                'ventas.idusuario',
                'personas.nombre as nombreUsuario',
                DB::raw('COUNT(*) as cantidadVentas'),
                DB::raw('SUM(ventas.total) as totalVentas')
            )
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('ventas.idusuario', 'personas.nombre')
            ->orderByDesc('cantidadVentas')
            ->limit(10)
            ->get();

        return response()->json(['topVendedores' => $topVendedores]);
    }
    public function formularioReporte()
    {
        return view('reportes.ventas_form');
    }
    
    // Genera el PDF de ventas entre fechas
    public function generarReportePDF(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $usuarioIds = $request->input('usuario_ids', []);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $usuarioIds = $request->input('usuario_ids', []);
    
        if (!$fechaInicio || !$fechaFin) {
            $query = \App\Venta::with('cliente')->orderBy('fecha_hora', 'asc');
            if (!empty($usuarioIds)) {
                $query->whereIn('idusuario', $usuarioIds);
            }
            $ventas = $query->get();
            $fechaInicio = \App\Venta::min('fecha_hora') ? date('Y-m-d', strtotime(\App\Venta::min('fecha_hora'))) : null;
            $fechaFin = \App\Venta::max('fecha_hora') ? date('Y-m-d', strtotime(\App\Venta::max('fecha_hora'))) : null;
        } else {
            $query = \App\Venta::with('cliente')
                ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->orderBy('fecha_hora', 'asc');
            if (!empty($usuarioIds)) {
                $query->whereIn('idusuario', $usuarioIds);
            }
            $ventas = $query->get();
        }
    
        $total = $ventas->sum('total');
        $empresa = \App\Empresa::first();
    
        $pdf = \PDF::loadView('pdf.ventas_pdf', [
            'ventas' => $ventas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'total' => $total,
            'empresa' => $empresa,
        ])->setPaper('A4', 'portrait');
    
        return $pdf->download('reporte_ventas_' . ($fechaInicio ?? 'todas') . '_al_' . ($fechaFin ?? 'todas') . '.pdf');
    }
    
    public function exportarExcel(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $usuarioIds = $request->input('usuario_ids', []);
    
            \Log::info('Exportando Excel', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'usuario_ids' => $usuarioIds
            ]);
    
            return \Excel::download(
                new \App\Exports\VentasExport($fechaInicio, $fechaFin, $usuarioIds),
                'ventas.xlsx'
            );
        } catch (\Exception $e) {
            \Log::error('Error al exportar Excel: ' . $e->getMessage());
            // Opcional: puedes retornar el error al frontend
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}