
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td { border: 1px solid #333; padding: 5px; text-align: left;}
        th { background: #eee; }
        .header { text-align: center; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $empresa->nombre ?? 'EMPRESA' }}</h2>
        <p>{{ $empresa->direccion ?? '' }} | NIT: {{ $empresa->nit ?? '' }}</p>
        <h3>Reporte de Ventas</h3>
        <p>Desde: <strong>{{ $fechaInicio }}</strong> Hasta: <strong>{{ $fechaFin }}</strong></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Comprobante</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $i => $venta)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i') }}</td>
                <td>{{ $venta->cliente->nombre ?? 'N/A' }}</td>
                <td>{{ $venta->tipo_comprobante }} {{ $venta->serie_comprobante }}-{{ $venta->num_comprobante }}</td>
                <td>{{ number_format($venta->total, 2) }}</td>
                <td>{{ $venta->estado }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4" class="total" style="text-align:right;">TOTAL</td>
                <td class="total">{{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
