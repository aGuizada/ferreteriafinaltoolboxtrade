<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Inversión en Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin-bottom: 5px; }
        .header p { margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
        .footer { margin-top: 30px; text-align: right; font-size: 0.9em; color: #555; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
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
                            <td>Bs{{ number_format($item->precio_costo_unid, 2) }}</td>
                            <td>Bs{{ number_format($item->inversion_total, 2) }}</td>
                        @else
                            <td>{{ number_format($item->saldo_stock, 2) }}</td>
                            <td>{{ $item->unidad_envase }}</td>
                            <td>Bs{{ number_format($item->precio_costo_unid, 2) }}</td>
                            <td>Bs{{ number_format($item->inversion_lote, 2) }}</td>
                            <td>{{ $item->fecha_vencimiento }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $tipo === 'item' ? 4 : 5 }}" class="total">TOTAL INVERSIÓN:</td>
                    <td>Bs{{ number_format($totalInversion, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>