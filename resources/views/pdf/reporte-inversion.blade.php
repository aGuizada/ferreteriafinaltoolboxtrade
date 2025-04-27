<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Inversión en Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin-bottom: 5px; }
        .header p { margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
        .footer { margin-top: 30px; text-align: right; font-size: 0.9em; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inversión en Inventario</h1>
        <p>Generado el: {{ $fecha }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                @if($tipo === 'item')
                    <th>Unidad X Paq.</th>
                    <th>Stock Total</th>
                    <th>Precio Unitario</th>
                    <th>Inversión</th>
                @else
                    <th>Stock</th>
                    <th>Unidad X Paq.</th>
                    <th>Precio Unitario</th>
                    <th>Inversión</th>
                    <th>Fecha Venc.</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($inventarios as $item)
                <tr>
                    <td>{{ $item->nombre_producto }}</td>
                    @if($tipo === 'item')
                        <td>{{ $item->unidad_envase }}</td>
                        <td>{{ number_format($item->saldo_stock_total, 2) }}</td>
                        <td>${{ number_format($item->precio_costo_unid, 2) }}</td>
                        <td>${{ number_format($item->inversion_total, 2) }}</td>
                    @else
                        <td>{{ number_format($item->saldo_stock, 2) }}</td>
                        <td>{{ $item->unidad_envase }}</td>
                        <td>${{ number_format($item->precio_costo_unid, 2) }}</td>
                        <td>${{ number_format($item->inversion_lote, 2) }}</td>
                        <td>{{ $item->fecha_vencimiento }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $tipo === 'item' ? 4 : 4 }}" class="total">TOTAL INVERSIÓN:</td>
                <td>${{ number_format($totalInversion, 2) }}</td>
                @if($tipo === 'lote')<td></td>@endif
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Sistema de Inventarios - {{ date('Y') }}
    </div>
</body>
</html>