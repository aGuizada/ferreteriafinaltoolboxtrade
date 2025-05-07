


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resibo de Venta</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
          .empresa {
            font-size: 11px;
            text-align: center;
            margin: 5px 0;
            line-height: 1.3;
        }
            .info-venta {
            margin: 5px 0;
            padding: 3px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
          .footer {
            text-align: center;
            margin-top: 5px;
            font-size: 9px;
            font-style: italic;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #888; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
        .totals { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RESIBO DE VENTA</h2>
        <p>No. {{ $venta->id }}</p>
        @if($empresa->logo)
            <img src="{{ storage_path('app/public/logos/' . $empresa->logo) }}" alt="Logo" height="60">
        @endif
    </div>

  <div class="empresa">
        <strong>{{ strtoupper($empresa->nombre) }}</strong><br>
        {{ $empresa->direccion }}<br>
        Tel: {{ $empresa->telefono }} | Email: {{ $empresa->email }}<br>
        NIT: {{ $empresa->nit }}
    </div>

    

  <div class="info-venta">
        <strong>Fecha:</strong> {{ isset($venta->created_at) ? \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') : date('d/m/Y H:i') }}<br>
        <strong>Cliente:</strong> {{ isset($persona->nombre) ? strtoupper(substr($persona->nombre, 0, 25)) : 'CONSUMIDOR FINAL' }}<br>
        <strong>Doc:</strong> {{ $persona->num_documento ?? 'N/A' }}
    </div>

    

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($venta->detalles as $detalle)
                @php
                    $subtotal = $detalle->cantidad * $detalle->precio;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ strtoupper(substr($detalle->producto->nombre, 0, 30)) }}</td>
                    <td style="text-align:center;">{{ $detalle->cantidad }}</td>
                    <td style="text-align:right;">{{ number_format($detalle->precio, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="3" style="text-align:right;">TOTAL</td>
                <td style="text-align:right;">{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

  
    <div>
        <strong>TIPO DE PAGO:</strong> {{ $venta->tipoPago->nombre_tipo_pago ?? 'N/A' }}
    </div>

    <br><br>
    <div style="text-align:center;">
        <em>¡GRACIAS POR SU COMPRA!</em>
    </div>
        <div class="footer">

        {{ $empresa->horario_atencion ?? 'Atendemos de Lunes a Viernes 8:00-18:00' }}
    </div>
</body>
</html>
