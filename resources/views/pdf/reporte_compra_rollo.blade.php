<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compra - Rollo</title>
    <style>
        /* Estilos optimizados para impresión térmica 80mm */
        @page { margin: 0; }
        body { 
            font-family: 'Arial Narrow', Arial, sans-serif;
            font-size: 10px;
            color: #000;
            width: 76mm;
            margin: 2mm auto;
            padding: 0;
            line-height: 1.3;
        }
        .header { 
            text-align: center;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #000;
        }
        .empresa {
            font-size: 10px;
            text-align: center;
            margin: 3px 0;
            font-weight: bold;
        }
        .titulo-recibo {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .info-compra {
            margin: 5px 0;
            padding: 3px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9px;
        }
        th {
            font-weight: bold;
            padding: 2px 1px;
            text-align: left;
            border-bottom: 1px solid #000;
        }
        td {
            padding: 2px 1px;
            border-bottom: 1px dashed #ddd;
        }
        .align-right {
            text-align: right;
        }
        .align-center {
            text-align: center;
        }
        .totals {
            font-weight: bold;
            border-top: 1px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 5px;
            font-size: 9px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="titulo-recibo">REPORTE DE COMPRA</div>
        <div>No. {{ str_pad($ingreso->id ?? '00000', 5, '0', STR_PAD_LEFT) }}</div>
    </div>


    <!-- Información de la compra -->
    <div class="info-compra">
        <strong>Fecha:</strong> {{ $ingreso->created_at }}<br>
        <strong>Proveedor:</strong> {{ $ingreso->nombre }}<br>
        <strong>Usuario:</strong> {{ $ingreso->usuario }}
    </div>

    <!-- Detalles de productos -->
    <table>
        <thead>
            <tr>
                <th>Artículo</th>
                <th class="align-center">Cant.</th>
                <th class="align-right">Precio</th>
                <th class="align-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td>{{ $detalle->articulo }}</td>
                <td class="align-center">{{ $detalle->cantidad }}</td>
                <td class="align-right">{{ number_format($detalle->precio, 2) }}</td>
                <td class="align-right">{{ number_format($detalle->cantidad * $detalle->precio, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <div class="footer">
        <p>Total: {{ number_format($ingreso->total, 2) }}</p>
    </div>
</body>
</html>