<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo General de Crédito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 16px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 120px;
            font-weight: bold;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .cuotas-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
        }
        .cuotas-table th, .cuotas-table td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        .cuotas-table th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>RECIBO GENERAL DE CRÉDITO</h2>
    </div>

    <!-- Información General -->
    <table class="info-table">
        <tr>
            <td>Fecha:</td>
            <td>{{ now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Cliente:</td>
            <td>{{ $venta->cliente->nombre ?? 'SIN NOMBRE' }}</td>
        </tr>
        <tr>
            <td>Número de Venta:</td>
            <td>{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Fecha de Venta:</td>
            <td>{{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Total de Cuotas:</td>
            <td>{{ $cuotas->count() }}</td>
        </tr>
    </table>

    <!-- Resumen Financiero -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monto Total del Crédito</td>
                <td class="text-right">${{ number_format($credito->monto_total, 2) }}</td>
            </tr>
            <tr>
                <td>Monto Pagado</td>
                <td class="text-right">${{ number_format($credito->monto_pagado, 2) }}</td>
            </tr>
            <tr>
                <td>Saldo Pendiente</td>
                <td class="text-right">${{ number_format($credito->saldo_pendiente, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="info-table">
        <tr>
            <td>Cuotas Pagadas:</td>
            <td>{{ $cuotas->where('pagado', true)->count() }}</td>
        </tr>
    </table>

    <!-- Detalle de Cuotas -->
    <h3 style="margin: 5px 0; font-size: 13px;">TODAS LAS CUOTAS</h3>
    <table class="cuotas-table">
        <thead>
            <tr>
                <th style="width: 20%;">N° Cuota</th>
                <th style="width: 25%;">Fecha de Pago</th>
                <th style="width: 25%;" class="text-right">Monto</th>
                <th style="width: 30%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotas as $cuota)
                <tr>
                    <td>{{ $cuota->numero_cuota }}</td>
                    <td>{{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}</td>
                    <td class="text-right">${{ number_format($cuota->monto, 2) }}</td>
                    <td class="text-center">{{ $cuota->pagado ? 'Pagado' : 'Pendiente' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen Final -->
    <table class="info-table">
        <tr>
            <td>Total de cuotas:</td>
            <td>{{ $cuotas->count() }}</td>
        </tr>
        <tr>
            <td>Cuotas pagadas:</td>
            <td>{{ $cuotas->where('pagado', true)->count() }}</td>
        </tr>
        <tr>
            <td>Cuotas pendientes:</td>
            <td>{{ $cuotas->where('pagado', false)->count() }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>** Documento generado por toolboxtrade **</p>
    </div>
</body>
</html>