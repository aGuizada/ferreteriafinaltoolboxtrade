<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Caja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        h1, h2 {
            color: #2c3e50;
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
        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resumen de Caja</h1>
        <p><strong>Fecha de Apertura:</strong> {{ $resumenCaja['fechaApertura'] }}</p>
        
        <h2>Movimientos</h2>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumenCaja['movimientos'] as $movimiento)
                <tr>
                    <td>{{ $movimiento['concepto'] }}</td>
                    <td>${{ $movimiento['monto'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Resumen</h2>
        <table>
            <tr>
                <th>Total Ingresos</th>
                <td class="total">${{ number_format($resumenCaja['totalIngresos'], 2) }}</td>
            </tr>
            <tr>
                <th>Total Egresos</th>
                <td class="total">${{ number_format($resumenCaja['totalEgresos'], 2) }}</td>
            </tr>
            <tr>
                <th>Saldo en Caja</th>
                <td class="total">${{ $resumenCaja['saldoCaja'] }}</td>
            </tr>
        </table>
    </div>
</body>
</html>