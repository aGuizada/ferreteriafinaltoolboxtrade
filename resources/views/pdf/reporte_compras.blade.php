<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Compras</title>
    <style>
        /* Estilos para el PDF */
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Reporte de Compras</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N° Comprobante</th>
                <th>Proveedor</th>
                <th>Usuario</th>
                <th>Tipo Comp.</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $compra)
            <tr>
                <td>{{ $compra->fecha_hora }}</td>
                <td>{{ $compra->num_comprobante }}</td>
                <td>{{ $compra->proveedor }}</td>
                <td>{{ $compra->usuario }}</td>
                <td>{{ $compra->tipo_comprobante }}</td>
                <td>{{ $compra->estado }}</td>
                <td>{{ number_format($compra->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>