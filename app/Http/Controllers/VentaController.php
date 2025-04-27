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
                'per_page' => $ventas->perPage(),
                'last_page' => $ventas->lastPage(),
                'from' => $ventas->firstItem(),
                'to' => $ventas->lastItem(),
            ],
            'ventas' => $ventas,
            'usuario' => $usuario
        ];
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
    
        try {
            DB::beginTransaction();
    
            if (!$this->validarCajaAbierta()) {
                return ['id' => -1, 'caja_validado' => 'Debe tener una caja abierta'];
            }
    
            // Validar que data sea un array y no esté vacío (CORRECCIÓN: Paréntesis cerrado)
            if (!is_array($request->data)) {  // <-- Se agregó ) para cerrar la condición
                throw new \Exception("Los datos de los productos no son válidos");
            }
    
            if (empty($request->data)) {
                throw new \Exception("No se han proporcionado productos para la venta");
            }
    
            if ($request->tipo_comprobante === "RESIVO") { // Nota: Corregí "RESIVO" a "RESIVO" si ese es el valor correcto
                $venta = $this->crearVentaResivo($request);
            } else {
                $venta = $this->crearVenta($request);
            }
    
            $this->actualizarCaja($request);
            $this->registrarDetallesVenta($venta, $request->data, $request->idAlmacen);
            $this->notificarAdministradores();
    
            DB::commit();
            return ['id' => $venta->id];
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
        $ultimaCaja = Caja::latest()->first();
        return $ultimaCaja && $ultimaCaja->estado == '1';
    }

    /**
     * Crea una venta regular
     */
    private function crearVenta($request)
    {
        $venta = new Venta();
        
        // Asignación básica de datos
        $venta->idcliente = $request->idcliente;
        $venta->idtipo_pago = $request->idtipo_pago;
        $venta->idtipo_venta = $request->idtipo_venta;
        $venta->tipo_comprobante = $request->tipo_comprobante;
        $venta->serie_comprobante = $request->serie_comprobante;
        $venta->num_comprobante = $request->num_comprobante;
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
        
        // Si es venta a crédito, registrar información adicional
        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($venta, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }
        
        return $venta;
    }
    /**
     * Crea una venta con recibo
     */
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

        // Actualizar saldo de caja según el tipo de pago
        if ($request->idtipo_pago == 1) { // Pago al contado
            $cajaActual->ventasContado += $request->total;
            $cajaActual->saldoCaja += $request->total;
        } elseif ($request->idtipo_pago == 4) { // Pago QR
            $cajaActual->pagosQR += $request->total;
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
            
            // Actualizar estado
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

        // Si el pago es en efectivo (tipo 1)
        if ($request->idtipo_pago == 1) {
            // Si es venta a crédito
            if ($request->idtipo_venta == 2) {
                // Sumar a ventas crédito
                $ultimaCaja->ventas += $request->primer_precio_cuota ?? 0;
                $ultimaCaja->pagosEfectivoVentas += $request->primer_precio_cuota ?? 0;
                $ultimaCaja->ventasCredito += $request->primer_precio_cuota ?? 0;
                $ultimaCaja->saldoCaja += $request->primer_precio_cuota ?? 0;
            } 
            // Si es venta adelantada (pago en efectivo)
            else if ($request->idtipo_venta == 3) {
                // Sumar a ventas (pagos en efectivo)
                $ultimaCaja->ventasContado += $request->total;
                $ultimaCaja->ventas += $request->total;
                $ultimaCaja->pagosEfectivoVentas += $request->total;
                $ultimaCaja->saldoCaja += $request->total;
            } 
            // Si es venta al contado
            else {
                // Sumar a ventas contado
                $ultimaCaja->ventasContado += $request->total;
                $ultimaCaja->ventas += $request->total;
                $ultimaCaja->pagosEfectivoVentas += $request->total;
                $ultimaCaja->saldoCaja += $request->total;
            }
        } 
        // Si es otro medio de pago (no efectivo)
        else {
            if ($request->idtipo_venta == 1 || $request->idtipo_venta == 3) {
                // Actualizar solo el total de ventas para ventas al contado o adelantadas no efectivo
                $ultimaCaja->ventas += $request->total;
            } else {
                // Para créditos con otro medio de pago
                $ultimaCaja->ventas += $request->primer_precio_cuota ?? 0;
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
     * Revierte el inventario en caso de anulación
     */
    private function revertirInventario()
    {
        $idAlmacen = $_SESSION['sidAlmacen'];
        $detalles = $_SESSION['sdetalle'];

        foreach ($detalles as $detalle) {
            $inventario = Inventario::where('idalmacen', $idAlmacen)
                ->where('idarticulo', $detalle['idarticulo'])
                ->firstOrFail();
            $inventario->saldo_stock += $detalle['cantidad'];
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

        // Obtener el rol del usuario autenticado
        $rolUsuario = Auth::user()->idrol;

        // Verificar si el usuario es administrador
        if ($rolUsuario !== 1) {
            return response()->json([
                'error' => 'Sólo los administradores pueden anular ventas.'
            ], 403);
        }

        // Buscar la venta a anular
        $venta = Venta::findOrFail($request->id);

        // Anular la venta
        $venta->estado = 'Anulado';
        $venta->save();

        return response()->json([
            'mensaje' => 'Venta anulada correctamente.'
        ]);
    }

    /**
     * Imprime un recibo en formato de rollo térmico
     */
    public function imprimirResivoRollo($id) {
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

            if ($venta->detalles->isNotEmpty()) {
                // Configuración para recibo de rollo
                $pdf = new FPDF('P', 'mm', array(80, 297)); // Ancho de 80mm, alto variable
                $pdf->SetAutoPageBreak(true, 10);
                $pdf->SetMargins(5, 10, 5);
                $pdf->AddPage();

                if ($empresa->logo) {
                    $logoPath = storage_path('app/public/logos/' . $empresa->logo);
                    if (file_exists($logoPath)) {
                        $logoWidth = 30; // Ancho del logo en mm
                        $xPosition = (80 - $logoWidth) / 2; // Calcular posición X para centrar
                        $pdf->Image($logoPath, $xPosition, 2, $logoWidth);
                        $pdf->Ln(20);
                    }
                }

                // Establecer fuente monoespaciada
                $pdf->SetFont('Courier', 'B', 12);

                // Encabezado
                $pdf->Cell(0, 10, utf8_decode(strtoupper('RECIBO DE VENTA')), 0, 1, 'C');
                $pdf->SetFont('Courier', '', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('No. ' . $id)), 0, 1, 'C');

                // Información de la empresa
                $pdf->SetFont('Courier', 'B', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->nombre)), 0, 1, 'C');
                $pdf->SetFont('Courier', '', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->direccion)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('TELÉFONO: ' . $empresa->telefono)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('EMAIL: ' . $empresa->email)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('NIT: ' . $empresa->nit)), 0, 1, 'C');
               
                $pdf->Ln(5);

                // Fecha y hora
                $pdf->Cell(0, 5, utf8_decode(strtoupper('FECHA: ' . date('d/m/Y', strtotime($venta->created_at)))), 0, 1);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('HORA: ' . date('H:i:s', strtotime($venta->created_at)))), 0, 1);

                // Detalles del cliente
                $pdf->Ln(2);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('CLIENTE: ' . $persona->nombre)), 0, 1);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('DOC: ' . $persona->num_documento)), 0, 1);

                // Línea separadora
                $pdf->Cell(0, 2, '', 'T', 1);

                // Encabezados de la tabla
                $pdf->SetFont('Courier', 'B', 8);
                $pdf->Cell(40, 5, utf8_decode(strtoupper('PRODUCTO')), 0, 0);
                $pdf->Cell(10, 5, utf8_decode(strtoupper('CANT')), 0, 0, 'R');
                $pdf->Cell(20, 5, utf8_decode(strtoupper('PRECIO')), 0, 1, 'R');

                $pdf->SetFont('Courier', '', 8);

                // Detalles de los productos
                $total = 0;
                foreach ($venta->detalles as $detalle) {
                    $precioVenta = $detalle->cantidad * $detalle->precio;
                    $total += $precioVenta;

                    $pdf->Cell(40, 5, utf8_decode(strtoupper(substr($detalle->producto->nombre, 0, 20))), 0, 0);
                    $pdf->Cell(10, 5, utf8_decode($detalle->cantidad), 0, 0, 'R');
                    $pdf->Cell(20, 5, utf8_decode(number_format($precioVenta, 2)), 0, 1, 'R');
                }

                // Línea separadora
                $pdf->Cell(0, 2, '', 'T', 1);

                // Total
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->Cell(50, 6, utf8_decode(strtoupper('TOTAL')), 0, 0);
                $pdf->Cell(20, 6, utf8_decode(number_format($total, 2)), 0, 1, 'R');

                $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
                $totalTexto = strtoupper($formatter->format($total)) . ' BOLIVIANOS';
                $pdf->SetFont('Courier', 'B', 8);
                $pdf->Cell(0, 5, 'SON: ' . $totalTexto, 0, 1);
                
                // Tipo de pago
                $tipoPago = $venta->tipoPago;
                $nombreTipoPago = $tipoPago ? $tipoPago->nombre_tipo_pago : 'N/A';
                $pdf->SetFont('Courier', '', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('TIPO DE PAGO: ' . $nombreTipoPago)), 0, 1);

                // Mensaje de agradecimiento
                $pdf->Ln(5);
                $pdf->SetFont('Courier', 'I', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('¡GRACIAS POR SU COMPRA!')), 0, 1, 'C');

                // Guardar el archivo PDF generado
                $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
                $pdfPath = public_path('docs/recibo_rollo_' . $nombreLimpio . '_' . $id . '.pdf');
                $pdf->Output($pdfPath, 'F');

                // Descargar el archivo PDF generado
                return response()->download($pdfPath);
            } else {
                return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Error al imprimir el recibo en rollo: ' . $e->getMessage());
            return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN ROLLO'], 500);
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
                return response()->json(['error' => 'NO SE ENCONTRÓ LA VENTA'], 500);
            }

            $persona = Persona::find($venta->idcliente);
            if (!$persona) {
                return response()->json(['error' => 'NO SE ENCONTRÓ EL CLIENTE'], 500);
            }

            $empresa = Empresa::first();
            if (!$empresa) {
                return response()->json(['error' => 'NO SE ENCONTRÓ LA EMPRESA'], 404);
            }

            if ($venta->detalles->isNotEmpty()) {
                $pdf = new FPDF('P', 'mm', 'Letter');
                $pdf->SetMargins(20, 10, 20);
                $pdf->SetAutoPageBreak(true, 20);
                $pdf->AddPage();

                // Logo
                if ($empresa->logo) {
                    $logoPath = storage_path('app/public/logos/' . $empresa->logo);
                    if (file_exists($logoPath)) {
                        $logoWidth = 40; // Ancho del logo en mm
                        $pdf->Image($logoPath, 160, 10, $logoWidth);
                    }
                }

                // Título RECIBO DE VENTA
                $pdf->SetFont('Courier', 'B', 14);
                $pdf->Cell(0, 10, 'RECIBO DE VENTA', 0, 1, 'C');
                $pdf->SetFont('Courier', '', 10);
                $pdf->Cell(0, 5, utf8_decode('No. ' . $id), 0, 1, 'C');

                // Información de la empresa
                $pdf->SetFont('Courier', 'B', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->nombre)), 0, 1, 'C');
                $pdf->SetFont('Courier', '', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->direccion)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('TELÉFONO: ' . $empresa->telefono)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('EMAIL: ' . $empresa->email)), 0, 1, 'C');
                $pdf->Cell(0, 5, utf8_decode(strtoupper('NIT: ' . $empresa->nit)), 0, 1, 'C');
               
                $pdf->Ln(5);

                $fecha = date('d/m/Y', strtotime($venta->created_at));
                $hora = date('H:i:s', strtotime($venta->created_at));
                $pdf->Cell(50, 6, utf8_decode('FECHA: ' . strtoupper($fecha)), 0, 0, 'L');
                $pdf->Cell(50, 6, utf8_decode('HORA: ' . strtoupper($hora)), 0, 1, 'L');

                $clienteInfo = utf8_decode('CLIENTE: ' . strtoupper($persona->nombre) . '                 DOCUMENTO: ' . strtoupper($persona->num_documento));
                $pdf->Cell(0, 6, $clienteInfo, 0, 1, 'L');

                // Tabla de productos
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(90, 7, utf8_decode('PRODUCTO'), 1, 0, 'C', true);
                $pdf->Cell(25, 7, utf8_decode('CANTIDAD'), 1, 0, 'C', true);
                $pdf->Cell(35, 7, utf8_decode('PRECIO UNIT.'), 1, 0, 'C', true);
                $pdf->Cell(35, 7, utf8_decode('SUBTOTAL'), 1, 1, 'C', true);

                $pdf->SetFont('Courier', '', 9);
                $total = 0;
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    $subtotal = $detalle->cantidad * $detalle->precio;
                    $total += $subtotal;
                    
                    $pdf->Cell(90, 6, utf8_decode(strtoupper(substr($producto->nombre, 0, 30))), 1, 0);
                    $pdf->Cell(25, 6, utf8_decode(strtoupper($detalle->cantidad)), 1, 0, 'C');
                    $pdf->Cell(35, 6, utf8_decode(strtoupper(number_format($detalle->precio, 2))), 1, 0, 'R');
                    $pdf->Cell(35, 6, utf8_decode(strtoupper(number_format($subtotal, 2))), 1, 1, 'R');
                }

                $pdf->SetFont('Courier', 'B', 10);
                $pdf->Cell(150, 7, utf8_decode('TOTAL'), 1, 0, 'R');
                $pdf->Cell(35, 7, utf8_decode(strtoupper(number_format($total, 2))), 1, 1, 'R');

                $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
                $totalTexto = strtoupper($formatter->format($total)) . ' BOLIVIANOS';
                $pdf->SetFont('Courier', 'B', 8);
                
                // Dividir el texto en dos líneas si es muy largo
                if (strlen($totalTexto) > 90) {
                    $primeraParte = substr($totalTexto, 0, 90);
                    $ultimoEspacio = strrpos($primeraParte, ' ');
                    if ($ultimoEspacio !== false) {
                        $primeraParte = substr($totalTexto, 0, $ultimoEspacio);
                        $segundaParte = substr($totalTexto, $ultimoEspacio + 1);
                        $pdf->Cell(0, 5, 'SON:', 0, 1);
                        $pdf->MultiCell(0, 5, $primeraParte . "\n" . $segundaParte, 0, 'L');
                    } else {
                        $pdf->MultiCell(0, 5, 'SON: ' . $totalTexto, 0, 'L');
                    }
                } else {
                    $pdf->Cell(0, 5, 'SON: ' . $totalTexto, 0, 1);
                }

                // Tipo de pago
                $tipoPago = $venta->tipoPago;
                $nombreTipoPago = $tipoPago ? $tipoPago->nombre_tipo_pago : 'N/A';
                $pdf->SetFont('Courier', '', 8);
                $pdf->Cell(0, 5, utf8_decode(strtoupper('TIPO DE PAGO: ' . $nombreTipoPago)), 0, 1);

                // Firma
                $pdf->SetFont('Courier', '', 10);
                $anchoFirma = $pdf->GetPageWidth() / 2 - 20;
                $pdf->Cell($anchoFirma, 6, '_________________________', 0, 0, 'C');
                $pdf->Cell($anchoFirma, 6, '_________________________', 0, 1, 'C');
                $pdf->Cell($anchoFirma, 6, 'FIRMA DEL CLIENTE', 0, 0, 'C');
                $pdf->Cell($anchoFirma, 6, 'FIRMA AUTORIZADA', 0, 1, 'C');
                $pdf->Ln(10);

                // Nota de agradecimiento
                $pdf->SetFont('Courier', 'I', 10);
                $pdf->Cell(0, 7, utf8_decode('¡GRACIAS POR SU COMPRA!'), 0, 1, 'C');

                // Dibujar el margen
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(1);
                $margenVertical = 10;
                $margenHorizontal = 10;
                $pdf->Rect($margenHorizontal, $margenVertical, $pdf->GetPageWidth() - ($margenHorizontal * 2), $pdf->GetY() + $margenVertical);

                $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
                $pdfPath = public_path('docs/recibo_carta_' . $nombreLimpio . '_' . $id . '.pdf');
                $pdf->Output($pdfPath, 'F');

                return response()->download($pdfPath);
            } else {
                return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error al imprimir el recibo en carta: ' . $e->getMessage());
            return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN CARTA'], 500);
        }
    }

    /**
     * Obtiene el último número de comprobante
     */
    public function obtenerUltimoComprobante(Request $request) {
        $idsucursal = $request->idsucursal;
    
        $ultimoComprobanteVentas = Venta::join('users', 'ventas.idusuario', '=', 'users.id')
            ->select('ventas.num_comprobante')
            ->where('users.idsucursal', $idsucursal)
            ->orderBy('ventas.num_comprobante', 'desc')
            ->limit(1)
            ->first();

        $lastComprobanteVentas = $ultimoComprobanteVentas ? $ultimoComprobanteVentas->num_comprobante : 0;
    
        return response()->json(['last_comprobante' => $lastComprobanteVentas]);
    }

    /**
     * Crea un registro de crédito para la venta
     */
    private function crearCreditoVenta($venta, $request)
    {
        $creditoventa = new CreditoVenta();
        $creditoventa->idventa = $venta->id;
        $creditoventa->idcliente = $request->idcliente;
        $creditoventa->numero_cuotas = $request->numero_cuotasCredito;
        $creditoventa->tiempo_dias_cuota = $request->tiempo_dias_cuotaCredito;
        $creditoventa->total = $request->totalCredito;
        $creditoventa->estado = $request->estadoCredito;

        $primerCuotaNoPagada = null;
        foreach ($request->cuotaspago as $cuota) {
            if ($cuota['estado'] !== 'Pagado') {
                $primerCuotaNoPagada = $cuota;
                break;
            }
        }
        $creditoventa->proximo_pago = $primerCuotaNoPagada['fecha_pago'];

        $creditoventa->save();

        return $creditoventa;
    }

    /**
     * Registra las cuotas del crédito
     */


}