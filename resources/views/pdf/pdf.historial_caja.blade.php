<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Caja #{{ $resumenCaja['id'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
        .resumen {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .total {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .highlight-qr {
            background-color: #e3f2fd; /* Azul claro */
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>HISTORIAL DE MOVIMIENTOS DE CAJA</h1>
        <h2>Caja #{{ $resumenCaja['id'] }}</h2>
    </div>

    <div class="info-caja">
        <p><strong>Fecha Apertura:</strong> {{ \Carbon\Carbon::parse($resumenCaja['fechaApertura'])->format('d/m/Y H:i:s') }}</p>
        <p><strong>Fecha Cierre:</strong> {{ $resumenCaja['fechaCierre'] ? \Carbon\Carbon::parse($resumenCaja['fechaCierre'])->format('d/m/Y H:i:s') : 'Caja abierta' }}</p>
        <p><strong>Estado:</strong> {{ $resumenCaja['fechaCierre'] ? 'CERRADA' : 'ABIERTA' }}</p>
    </div>

    <h3>Resumen de Movimientos</h3>
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
                <td class="monto">{{ number_format($movimiento['monto'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Sección específica para pagos QR -->
    <div class="qr-section">
        <h3>Detalle de Pagos con QR</h3>
        <p>Total de pagos recibidos mediante QR: <strong>Bs. {{ number_format($resumenCaja['pagosQR'], 2) }}</strong></p>
        <p>Los pagos mediante QR están incluidos en el saldo de caja y en los totales de movimientos.</p>
    </div>

    <h3>Ventas Registradas</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Total (Bs.)</th>
                <th>Tipo de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumenCaja['ventas'] as $venta)
            <tr @if($venta['idtipo_pago'] == 4) class="highlight-qr" @endif>
                <td>{{ $venta['id'] }}</td>
                <td>{{ \Carbon\Carbon::parse($venta['fecha'])->format('d/m/Y H:i:s') }}</td>
                <td>{{ number_format($venta['total'], 2) }}</td>
                <td>
                    @switch($venta['idtipo_pago'])
                        @case(1)
                            Efectivo
                            @break
                        @case(2)
                            Transferencia
                            @break
                        @case(3)
                            Tarjeta
                            @break
                        @case(4)
                            <strong>QR</strong>
                            @break
                        @default
                            Otro
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Transacciones de Caja</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Descripción</th>
                <th>Importe (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumenCaja['transacciones'] as $transaccion)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaccion['fecha'])->format('d/m/Y H:i:s') }}</td>
                <td>{{ $transaccion['usuario'] }}</td>
                <td>{{ $transaccion['transaccion'] }}</td>
                <td>{{ number_format($transaccion['importe'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

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
                    <td><strong>Saldo en Caja:</strong></td>
                    <td class="total">Bs. {{ number_format($resumenCaja['saldoCaja'], 2) }}</td>
                </tr>
                @if($resumenCaja['fechaCierre'])
                <tr>
                    <td><strong>Saldo Faltante:</strong></td>
                    <td class="total">Bs. {{ number_format($resumenCaja['saldoFaltante'], 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>