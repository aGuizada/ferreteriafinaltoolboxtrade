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
             'ventasContado',
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
        
        $caja->estado = '1';
        $caja->save();
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
        $caja = Caja::findOrFail($request->id);
        $caja->fechaCierre = now()->setTimezone('America/La_Paz');
        $caja->estado = '0';
        $caja->saldoFaltante = ($request->saldoFaltante)-($caja->saldoCaja);
        $caja->save();
    }
    public function obtenerSaldoCaja($id)
    {
        if (!request()->ajax()) return redirect('/');
    
        $caja = Caja::findOrFail($id);
        return response()->json(['saldo' => floatval($caja->saldoCaja)]);
    }
    public function resumen($id)
    {
        $caja = Caja::findOrFail($id);

        if (!$caja->estado) {
            return response()->json(['error' => 'La caja ya está cerrada'], 400);
        }

        $fechaApertura = Carbon::parse($caja->fechaApertura);

        // Obtener ventas del día
        $ventas = Venta::where('idcaja', $caja->id)
        ->select('id', 'created_at as fecha', 'total')
        ->orderBy('created_at', 'desc')
        ->get();
        // Calcular total de ingresos
        $totalIngresos = $caja->saldoInicial + $caja->depositos + $caja->ventas;

        // Calcular total de egresos
        $totalEgresos = $caja->salidas + $caja->compras;

        // Detalle de movimientos
        $movimientos = [
            ['concepto' => 'Saldo Inicial', 'monto' => $caja->saldoInicial],
            ['concepto' => 'Ventas al Contado', 'monto' => $caja->ventasContado],
            ['concepto' => 'Pagos de Ventas a Crédito', 'monto' => $caja->pagosEfectivoVentas],
            ['concepto' => 'Depósitos', 'monto' => $caja->depositos],
            ['concepto' => 'Compras al Contado', 'monto' => $caja->comprasContado],
            ['concepto' => 'Pagos de Compras a Crédito', 'monto' => $caja->pagosEfecivocompras],
            ['concepto' => 'Salidas', 'monto' => $caja->salidas],
        ];

        return response()->json([
            'id' => $caja->id,
            'fechaApertura' => $caja->fechaApertura,
            'ventas' => $ventas,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $caja->saldoCaja,
            'movimientos' => $movimientos
        ]);
    }
    public function generarPDF($id)
    {
        // Obtén los datos del resumen de caja
        $resumenCaja = $this->obtenerResumenCaja($id);
    
        // Verifica si la respuesta contiene errores
        if (isset($resumenCaja['error'])) {
            return response()->json(['error' => $resumenCaja['error']], 400);
        }
    
        // Genera el PDF
        $pdf = PDF::loadView('pdf.resumen_caja', ['resumenCaja' => $resumenCaja]);
    
        // Descarga el PDF
        return $pdf->download('resumen_caja.pdf');
    }
    
public function obtenerResumenCaja($id)
{
    if (!request()->ajax()) return redirect('/');

    $caja = Caja::findOrFail($id);

    if (!$caja->estado) {
        return response()->json(['error' => 'La caja ya está cerrada'], 400);
    }

    // Obtener ventas del día
    $ventas = Venta::where('idcaja', $caja->id)
        ->select('id', 'created_at as fecha', 'total')
        ->orderBy('created_at', 'desc')
        ->get();

    // Calcular total de ingresos
    $totalIngresos = $caja->saldoInicial + $caja->depositos + $caja->ventas;

    // Calcular total de egresos
    $totalEgresos = $caja->salidas + $caja->compras;

    // Detalle de movimientos
    $movimientos = [
        ['concepto' => 'Saldo Inicial', 'monto' => $caja->saldoInicial],
        ['concepto' => 'Ventas al Contado', 'monto' => $caja->ventasContado],
        ['concepto' => 'Pagos de Ventas a Crédito', 'monto' => $caja->pagosEfectivoVentas],
        ['concepto' => 'Depósitos', 'monto' => $caja->depositos],
        ['concepto' => 'Compras al Contado', 'monto' => $caja->comprasContado],
        ['concepto' => 'Pagos de Compras a Crédito', 'monto' => $caja->pagosEfecivocompras],
        ['concepto' => 'Salidas', 'monto' => $caja->salidas],
    ];

    return [
        'id' => $caja->id,
        'fechaApertura' => $caja->fechaApertura,
        'ventas' => $ventas,
        'totalIngresos' => $totalIngresos,
        'totalEgresos' => $totalEgresos,
        'saldoCaja' => $caja->saldoCaja,
        'movimientos' => $movimientos
    ];
}


}
