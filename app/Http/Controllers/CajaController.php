<?php

namespace App\Http\Controllers;

use PDF;
use App\Caja;
use App\TransaccionesCaja;
use Illuminate\Http\Request;
use App\ArqueoCaja;
use App\User;
use Carbon\Carbon;  
use App\Venta;
use App\Ingreso;
use Illuminate\Support\Facades\DB;
use App\TipoPago;
use Illuminate\Http\JsonResponse;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        $buscar = $request->buscar;
        $criterio = $request->criterio;
    
        if ($buscar==''){
            $cajas = Caja::join('sucursales', 'cajas.idsucursal', '=', 'sucursales.id')
            ->join('users', 'cajas.idusuario', '=', 'users.id')
            ->select('cajas.id', 'cajas.idsucursal', 'sucursales.nombre as nombre_sucursal',
             'cajas.idusuario', 'users.usuario as usuario', 'cajas.fechaApertura', 
             'cajas.fechaCierre', 'saldoInicial', 
             'depositos', 
             'salidas', 
             'ventas',
             'ventasCredito',
             'pagosEfectivoVentas', 
             'pagosEfecivocompras', 
             'compras', 
             'comprasContado',
             'saldoFaltante', 
             'PagoCuotaEfectivo', 
             'saldoCaja', 
             'estado',
             'cuotasventasCredito')
            ->where('cajas.idsucursal', '=', \Auth::user()->idsucursal)
            ->orderBy('cajas.id', 'desc')->paginate(6);
        }
        else{
            $cajas = Caja::join('sucursales', 'cajas.idsucursal', '=', 'sucursales.id')
            ->join('users', 'cajas.idusuario', '=', 'users.id')
            ->select('cajas.id', 'cajas.idsucursal', 'sucursales.nombre as nombre_sucursal', 'cajas.idusuario', 'users.usuario as usuario', 'cajas.fechaApertura', 'cajas.fechaCierre', 'cajas.saldoInicial', 'cajas.depositos', 'cajas.salidas', 'cajas.ventas','cajas.pagosEfectivoVentas', 'cajas.compras', 'cajas.pagosEfecivocompras', 'cajas.saldoFaltante', 'cajas.PagoCuotaEfectivo', 'cajas.saldoCaja', 'cajas.estado')
            ->where('cajas.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('cajas.id', 'desc')->paginate(6);
        }
    
        foreach ($cajas as $caja) {
            // Pagos QR
            $caja->pagosQR = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 4)
                ->sum('total');
        
            // Ventas adelantadas (solo tipo_venta = 3)
            $caja->ventasAdelantadas = Venta::where('idcaja', $caja->id)
                ->where('idtipo_venta', 3)
                ->sum('total');
        
            // Ventas contado (tipo_pago = 1 y NO son adelantadas)
            $caja->ventasContado = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 1)
                ->where('idtipo_venta', '!=', 3)
                ->sum('total');
        
            // Ventas crédito (tipo_pago = 2)
            $caja->ventasCreditoCalculado = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 2)
                ->sum('total');
        
            // Ventas totales (suma de todas las ventas)
            $caja->ventasTotalesCalculadas = Venta::where('idcaja', $caja->id)
                ->sum('total');
        
            // Corregir saldo de caja
            $caja->saldoCaja = floatval($caja->saldoInicial)
                + floatval($caja->depositos)
                + floatval($caja->ventasContado)
                + floatval($caja->pagosQR)
                + floatval($caja->ventasAdelantadas)
                + floatval($caja->PagoCuotaEfectivo)
                - floatval($caja->salidas)
                - floatval($caja->compras);
        
            // Para depuración
            $caja->calculoDetallado = [
                'saldoInicial' => floatval($caja->saldoInicial),
                'depositos' => floatval($caja->depositos),
                'ventasContado' => floatval($caja->ventasContado),
                'pagosQR' => floatval($caja->pagosQR),
                'ventasAdelantadas' => floatval($caja->ventasAdelantadas),
                'PagoCuotaEfectivo' => floatval($caja->PagoCuotaEfectivo),
                'salidas' => floatval($caja->salidas),
                'compras' => floatval($caja->compras),
                'saldoCajaFinal' => $caja->saldoCaja,
                'ventasTotales' => $caja->ventasTotalesCalculadas
            ];
        }
        
        return [
            'pagination' => [
                'total'        => $cajas->total(),
                'current_page' => $cajas->currentPage(),
                'per_page'     => $cajas->perPage(),
                'last_page'    => $cajas->lastPage(),
                'from'         => $cajas->firstItem(),
                'to'           => $cajas->lastItem(),
            ],
            'cajas' => $cajas
        ];
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $caja = new Caja();
        $caja->idsucursal = \Auth::user()->idsucursal;
        $caja->idusuario = \Auth::user()->id;
        $caja->fechaApertura = now()->setTimezone('America/La_Paz');
        $caja->saldoInicial = $request->saldoInicial;
        $caja->saldoCaja = $request->saldoInicial;
        $caja->ventasContado = 0;
        $caja->pagosQR = 0;
        $caja->depositos = 0;
        $caja->salidas = 0;
        $caja->ventas = 0;
        $caja->compras = 0;
        $caja->estado = '1';
        $caja->save();
    
        return response()->json([
            'status' => 'success', 
            'caja' => $caja,
            'message' => 'Caja aperturada correctamente con saldo inicial de ' . $caja->saldoInicial
        ], 200);
    }

    public function depositar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $caja = Caja::findOrFail($request->id);
        $caja->depositos = ($request->depositos)+($caja->depositos);
        $caja->saldoCaja = $caja->saldoCaja + $request->depositos;
        $caja->save();

        $transacciones = new TransaccionesCaja();
        $transacciones->idcaja = $request->id;
        $transacciones->idusuario = \Auth::user()->id; 
        $transacciones->fecha = now()->setTimezone('America/La_Paz');
        $transacciones->transaccion = $request->transaccion;
        $transacciones->importe = ($request->depositos);
        $transacciones->save();
    }

    public function retirar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $caja = Caja::findOrFail($request->id);
        if ($request->salidas > $caja->saldoCaja) {
            return response()->json(['error' => 'Saldo insuficiente para realizar el retiro.'], 400);
        }

        $caja->salidas = ($request->salidas)+($caja->salidas);
        $caja->saldoCaja = $caja->saldoCaja - $request->salidas;
        $caja->save();

        $transacciones = new TransaccionesCaja();
        $transacciones->idcaja = $request->id;
        $transacciones->idusuario = \Auth::user()->id; 
        $transacciones->fecha = now()->setTimezone('America/La_Paz');
        $transacciones->transaccion = $request->transaccion;
        $transacciones->importe = ($request->salidas);
        $transacciones->save();
    }

    public function arqueoCaja(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $arqueoCaja = new ArqueoCaja();
        $arqueoCaja->idcaja = $request->idcaja;
        $arqueoCaja->idusuario = \Auth::user()->id; 
        $arqueoCaja->billete200 = $request->billete200;
        $arqueoCaja->billete100 = $request->billete100;
        $arqueoCaja->billete50 = $request->billete50;
        $arqueoCaja->billete20 = $request->billete20;
        $arqueoCaja->billete10 = $request->billete10;
        $arqueoCaja->moneda5 = $request->moneda5;
        $arqueoCaja->moneda2 = $request->moneda2;
        $arqueoCaja->moneda1 = $request->moneda1;
        $arqueoCaja->moneda050 = $request->moneda050;
        $arqueoCaja->moneda020 = $request->moneda020;
        $arqueoCaja->moneda010 = $request->moneda010;
        $arqueoCaja->save();
    }

    public function cerrar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        DB::beginTransaction();
        try {
            $caja = Caja::findOrFail($request->id);
    
            if ($caja->estado == '0') {
                return response()->json(['error' => 'La caja ya está cerrada'], 400);
            }
    
            // Recalcular todos los movimientos de caja
            $ventasContado = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 1)
                ->where('idtipo_venta', '!=', 3)
                ->sum('total');
    
            $pagosQR = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 4)
                ->sum('total');
    
            $ventasAdelantadas = Venta::where('idcaja', $caja->id)
                ->where('idtipo_venta', 3)
                ->sum('total');
    
            $ventasTotales = Venta::where('idcaja', $caja->id)->sum('total');
            $pagosCuotas = $caja->PagoCuotaEfectivo;
    
            // Convertimos todo a float para evitar errores de tipo
            $saldoInicial = (float) $caja->saldoInicial;
            $depositos = (float) $caja->depositos;
            $pagosEfectivoVentas = (float) $caja->pagosEfectivoVentas;
            $salidas = (float) $caja->salidas;
            $compras = (float) $caja->compras;
            $saldoReal = (float) $request->saldoReal;
    
            // Saldo que debería haber en el sistema
            $saldoSistema = $saldoInicial
                          + $depositos
                          + $ventasContado
                          + $pagosQR
                          + $ventasAdelantadas
                          + $pagosEfectivoVentas
                          - $salidas
                          - $compras;
    
            // Diferencia entre lo que debería haber y lo que realmente hay
            $saldoFaltante = $saldoSistema - $saldoReal;
    
            // Actualizar datos de la caja
            $caja->fechaCierre = now()->setTimezone('America/La_Paz');
            $caja->estado = '0';
            $caja->ventasContado = $ventasContado;
            $caja->pagosQR = $pagosQR;
            $caja->ventasAdelantadas = $ventasAdelantadas;
            $caja->ventas = $ventasTotales;
            $caja->saldoCaja = $saldoSistema;
            $caja->saldoFaltante = $saldoFaltante;
            $caja->save();
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'saldoInicial' => $saldoInicial,
                'ventasContado' => $ventasContado,
                'pagosQR' => $pagosQR,
                'ventasAdelantadas' => $ventasAdelantadas,
                'ventasTotales' => $ventasTotales,
                'saldoCaja' => $saldoSistema,
                'saldoReal' => $saldoReal,
                'saldoFaltante' => $saldoFaltante
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al cerrar la caja: ' . $e->getMessage()], 500);
        }
    }
    
    public function obtenerSaldoCaja($id)
    {
        if (!request()->ajax()) return redirect('/');
    
        $caja = Caja::findOrFail($id);
        
        // Calcular ventas al contado (excluyendo adelantadas)
        $ventasContado = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 1)
            ->where('idtipo_venta', '!=', 3)
            ->sum('total');
    
        // Calcular pagos QR
        $pagosQR = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 4)
            ->sum('total');
    
        // Calcular ventas adelantadas
        $ventasAdelantadas = Venta::where('idcaja', $caja->id)
            ->where('idtipo_venta', 3)
            ->sum('total');
    
        // Calcular saldo exactamente como en la tabla
        $saldo = (float)$caja->saldoInicial
               + (float)$caja->depositos
               + (float)$ventasContado
               + (float)$pagosQR
               + (float)$ventasAdelantadas
               + (float)$caja->PagoCuotaEfectivo
               - (float)$caja->salidas
               - (float)$caja->compras;
    
        return response()->json(['saldo' => $saldo]);
    }

    public function resumen($id)
    {
        try {
            $caja = Caja::findOrFail($id);
        
            if (!$caja->estado) {
                throw new \Exception('La caja ya está cerrada');
            }
        
            // Calcular ventas al contado (excluyendo adelantadas)
            $ventasContado = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 1)
                ->where('idtipo_venta', '!=', 3)
                ->sum('total');
        
            // Calcular pagos QR
            $pagosQR = Venta::where('idcaja', $caja->id)
                ->where('idtipo_pago', 4)
                ->sum('total');
        
            // Calcular ventas adelantadas
            $ventasAdelantadas = Venta::where('idcaja', $caja->id)
                ->where('idtipo_venta', 3)
                ->sum('total');
        
            // Calcular ventas totales
            $ventasTotales = Venta::where('idcaja', $caja->id)->sum('total');
        
            // Calcular pagos de cuotas
            $pagosCuotas = $caja->PagoCuotaEfectivo;
        
            // Calcular saldo de caja
            $saldoCaja = (float)$caja->saldoInicial 
                       + (float)$caja->depositos 
                       + (float)$ventasContado 
                       + (float)$pagosQR 
                       + (float)$ventasAdelantadas
                       + (float)$pagosCuotas
                       - (float)$caja->salidas 
                       - (float)$caja->compras;
        
            // Calcular total de ingresos
            $totalIngresos = (float)$caja->saldoInicial 
                           + (float)$caja->depositos 
                           + (float)$ventasContado 
                           + (float)$pagosQR 
                           + (float)$ventasAdelantadas
                           + (float)$pagosCuotas;
        
            // Calcular total de egresos
            $totalEgresos = (float)$caja->salidas + (float)$caja->compras;
        
            // Obtener ventas para el detalle
            $ventas = Venta::where('idcaja', $caja->id)
                ->select('id', 'created_at as fecha', 'total', 'idtipo_pago', 'idtipo_venta')
                ->orderBy('created_at', 'desc')
                ->get();
        
            return response()->json([
                'id' => $caja->id,
                'fechaApertura' => $caja->fechaApertura,
                'saldoInicial' => (float)$caja->saldoInicial,
                'ventasTotales' => $ventasTotales,
                'ventas' => $ventas,
                'ventasContado' => (float)$ventasContado,
                'pagosQR' => (float)$pagosQR,
                'ventasAdelantadas' => (float)$ventasAdelantadas,
                'pagosCuotas' => (float)$pagosCuotas,
                'totalIngresos' => $totalIngresos,
                'totalEgresos' => $totalEgresos,
                'saldoCaja' => $saldoCaja,
                'movimientos' => [
                    ['concepto' => 'Saldo Inicial', 'monto' => (float)$caja->saldoInicial],
                    ['concepto' => 'Ventas al Contado', 'monto' => (float)$ventasContado],
                    ['concepto' => 'Pagos con QR', 'monto' => (float)$pagosQR],
                    ['concepto' => 'Ventas Adelantadas', 'monto' => (float)$ventasAdelantadas],
                    ['concepto' => 'Pagos de Cuotas', 'monto' => (float)$pagosCuotas],
                    ['concepto' => 'Depósitos', 'monto' => (float)$caja->depositos],
                    ['concepto' => 'Compras', 'monto' => (float)$caja->compras],
                    ['concepto' => 'Salidas', 'monto' => (float)$caja->salidas],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function generarPDF($id)
{
    try {
        // Obtener los datos del resumen
        $caja = Caja::findOrFail($id);
        
        // Calcular todos los valores necesarios (igual que en el método resumen)
        $ventasContado = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 1)
            ->where('idtipo_venta', '!=', 3)
            ->sum('total');
        
        $pagosQR = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 4)
            ->sum('total');
        
        $ventasAdelantadas = Venta::where('idcaja', $caja->id)
            ->where('idtipo_venta', 3)
            ->sum('total');
        
        $ventasTotales = Venta::where('idcaja', $caja->id)->sum('total');
        $pagosCuotas = $caja->PagoCuotaEfectivo;
        
        $totalIngresos = (float)$caja->saldoInicial 
                       + (float)$caja->depositos 
                       + (float)$ventasContado 
                       + (float)$pagosQR 
                       + (float)$ventasAdelantadas
                       + (float)$pagosCuotas;
        
        $totalEgresos = (float)$caja->salidas + (float)$caja->compras;
        $saldoCaja = $totalIngresos - $totalEgresos;
        
        $ventas = Venta::where('idcaja', $caja->id)
            ->select('id', 'created_at as fecha', 'total', 'idtipo_pago', 'idtipo_venta')
            ->orderBy('created_at', 'desc')
            ->get();

        // Preparar los datos para la vista
        $resumenCaja = [
            'id' => $caja->id,
            'fechaApertura' => $caja->fechaApertura,
            'fechaCierre' => $caja->fechaCierre,
            'saldoInicial' => (float)$caja->saldoInicial,
            'ventasTotales' => $ventasTotales,
            'ventas' => $ventas,
            'ventasContado' => (float)$ventasContado,
            'pagosQR' => (float)$pagosQR,
            'ventasAdelantadas' => (float)$ventasAdelantadas,
            'pagosCuotas' => (float)$pagosCuotas,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $saldoCaja,
            'esCajaCerrada' => !$caja->estado,
            'movimientos' => [
                ['concepto' => 'Saldo Inicial', 'monto' => (float)$caja->saldoInicial],
                ['concepto' => 'Ventas al Contado', 'monto' => (float)$ventasContado],
                ['concepto' => 'Pagos con QR', 'monto' => (float)$pagosQR],
                ['concepto' => 'Ventas Adelantadas', 'monto' => (float)$ventasAdelantadas],
                ['concepto' => 'Pagos de Cuotas', 'monto' => (float)$pagosCuotas],
                ['concepto' => 'Depósitos', 'monto' => (float)$caja->depositos],
                ['concepto' => 'Compras', 'monto' => (float)$caja->compras],
                ['concepto' => 'Salidas', 'monto' => (float)$caja->salidas],
            ]
        ];

        // Generar el PDF
        $pdf = PDF::loadView('pdf.resumen_caja', ['resumenCaja' => $resumenCaja]);
        return $pdf->download('resumen_caja_'.$id.'.pdf');

    } catch (\Exception $e) {
        abort(404, 'No se encontró la caja o hubo un error al generar el PDF: '.$e->getMessage());
    }
}

    public function obtenerResumenCaja($id)
    {
        if (!request()->ajax()) return redirect('/');
    
        $caja = Caja::findOrFail($id);
    
        if (!$caja->estado) {
            return response()->json(['error' => 'La caja ya está cerrada'], 400);
        }
    
        // Calcular ventas al contado (excluyendo adelantadas)
        $ventasContado = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 1)
            ->where('idtipo_venta', '!=', 3)
            ->sum('total');
    
        // Calcular pagos QR
        $pagosQR = Venta::where('idcaja', $caja->id)
            ->where('idtipo_pago', 4)
            ->sum('total');
    
        // Calcular ventas adelantadas
        $ventasAdelantadas = Venta::where('idcaja', $caja->id)
            ->where('idtipo_venta', 3)
            ->sum('total');
    
        // Obtener ventas del día
        $ventas = Venta::where('idcaja', $caja->id)
            ->select('id', 'created_at as fecha', 'total', 'idtipo_pago', 'idtipo_venta')
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Calcular total de ingresos
        $totalIngresos = $caja->saldoInicial 
                       + $caja->depositos 
                       + $ventasContado 
                       + $pagosQR 
                       + $ventasAdelantadas
                       + $caja->PagoCuotaEfectivo;
    
        // Calcular total de egresos
        $totalEgresos = $caja->salidas + $caja->compras;
    
        // Calcular saldo de caja
        $saldoCaja = $totalIngresos - $totalEgresos;
    
        return [
            'id' => $caja->id,
            'fechaApertura' => $caja->fechaApertura,
            'ventas' => $ventas,
            'ventasContado' => $ventasContado,
            'pagosQR' => $pagosQR,
            'ventasAdelantadas' => $ventasAdelantadas,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $saldoCaja,
            'movimientos' => [
                ['concepto' => 'Saldo Inicial', 'monto' => $caja->saldoInicial],
                ['concepto' => 'Ventas al Contado', 'monto' => $ventasContado],
                ['concepto' => 'Pagos con QR', 'monto' => $pagosQR],
                ['concepto' => 'Ventas Adelantadas', 'monto' => $ventasAdelantadas],
                ['concepto' => 'Pagos de Cuotas', 'monto' => $caja->PagoCuotaEfectivo],
                ['concepto' => 'Depósitos', 'monto' => $caja->depositos],
                ['concepto' => 'Compras', 'monto' => $caja->compras],
                ['concepto' => 'Salidas', 'monto' => $caja->salidas],
            ]
        ];
    }
}