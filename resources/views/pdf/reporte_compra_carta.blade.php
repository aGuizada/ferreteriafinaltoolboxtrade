<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Compra - Carta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header, .footer {
            text-align: center;
        }
        .details {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .details th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Compra</h2>
        <p>Fecha: {{ $ingreso->created_at }}</p>
    </div>
    <table class="details">
        <tr>
            <th>Proveedor:</th>
            <td>{{ $ingreso->nombre }}</td>
        </tr>
        <tr>
            <th>Usuario:</th>
            <td>{{ $ingreso->usuario }}</td>
        </tr>
        <tr>
            <th>Comprobante:</th>
            <td>{{ $ingreso->tipo_comprobante }} {{ $ingreso->serie_comprobante }}-{{ $ingreso->num_comprobante }}</td>
        </tr>
    </table>
    <table class="details">
        <thead>
            <tr>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td>{{ $detalle->articulo }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>{{ number_format($detalle->precio, 2) }}</td>
                <td>{{ number_format($detalle->cantidad * $detalle->precio, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>Total: {{ number_format($ingreso->total, 2) }}</p>
    </div>
</body>
</html>