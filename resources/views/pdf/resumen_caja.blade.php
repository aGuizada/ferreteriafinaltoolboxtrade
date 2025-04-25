<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Caja #{{ $resumenCaja['id'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 16px;
        }
        .info-caja {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .info-caja p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .movimientos-section {
            margin: 15px 0;
        }
        .movimientos-section h3 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            color: #333;
        }
        .highlight-qr {
            background-color: #e3f2fd; /* Azul claro */
        }
        .resumen {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .qr-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f0f8ff;
            border-left: 5px solid #0066cc;
            border-radius: 3px;
        }
        .qr-section h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .firma-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .firma {
            width: 45%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RESUMEN DE CIERRE DE CAJA</h1>
        <h2>Caja #{{ $resumenCaja['id'] }}</h2>
    </div>

    <div class="info-caja">
        <p><strong>Fecha Apertura:</strong> {{ \Carbon\Carbon::parse($resumenCaja['fechaApertura'])->format('d/m/Y H:i:s') }}</p>
        <p><strong>Fecha Cierre:</strong> {{ $resumenCaja['fechaCierre'] ? \Carbon\Carbon::parse($resumenCaja['fechaCierre'])->format('d/m/Y H:i:s') : 'Caja abierta' }}</p>
    </div>

    <div class="movimientos-section">
        <h3>Movimientos de Caja</h3>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumenCaja['movimientos'] as $movimiento)
                <tr @if($movimiento['concepto'] == 'Pagos con QR') class="highlight-qr" @endif>
                    <td>{{ $movimiento['concepto'] }}</td>
                    <td class="total">{{ number_format($movimiento['monto'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sección específica para pagos QR -->
    <div class="qr-section">
        <h3>Detalle de Pagos con QR</h3>
        <p>Total de pagos recibidos mediante QR: <strong>Bs. {{ number_format($resumenCaja['pagosQR'] ?? 0, 2) }}</strong></p>
        <p>Los pagos mediante QR están incluidos en el saldo de caja y en los totales de movimientos.</p>
    </div>

    <!-- Resumen de ventas por método de pago -->
    <div class="movimientos-section">
        <h3>Ventas por Método de Pago</h3>
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Cantidad</th>
                    <th>Total (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $ventasPorMetodo = [
                        1 => ['nombre' => 'Efectivo', 'cantidad' => 0, 'total' => 0],
                        2 => ['nombre' => 'Transferencia', 'cantidad' => 0, 'total' => 0],
                        3 => ['nombre' => 'Tarjeta', 'cantidad' => 0, 'total' => 0],
                        4 => ['nombre' => 'QR', 'cantidad' => 0, 'total' => 0],
                    ];
                    
                    foreach($resumenCaja['ventas'] as $venta) {
                        $idMetodo = $venta['idtipo_pago'];
                        if (isset($ventasPorMetodo[$idMetodo])) {
                            $ventasPorMetodo[$idMetodo]['cantidad']++;
                            $ventasPorMetodo[$idMetodo]['total'] += $venta['total'];
                        }
                    }
                @endphp
                
                @foreach($ventasPorMetodo as $id => $metodo)
                <tr @if($id == 4) class="highlight-qr" @endif>
                    <td>{{ $metodo['nombre'] }}</td>
                    <td>{{ $metodo['cantidad'] }}</td>
                    <td class="total">{{ number_format($metodo['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="resumen">
        <table>
            <tbody>
                <tr>
                    <td><strong>Total Ingresos:</strong></td>
                    <td class="total">Bs. {{ number_format($resumenCaja['totalIngresos'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Egresos:</strong></td>
                    <td class="total">Bs. {{ number_format($resumenCaja['totalEgresos'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Saldo Final en Caja:</strong></td>
                    <td class="total">Bs. {{ number_format($resumenCaja['saldoCaja'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="firma-section">
        <div class="firma">
            <p>Cajero</p>
        </div>
        <div class="firma">
            <p>Supervisor</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>