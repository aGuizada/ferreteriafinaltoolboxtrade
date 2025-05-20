<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta</title>
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
        .discount-row { color: #ff0000; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PROFORMA </h2>
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
                <th class="text-center">Cant</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $subtotalSinDescuento = 0;
                $totalConDescuento = $venta->total ?? 0;
                $descuento = $venta->descuento ?? 0;
            @endphp
            @foreach($venta->detalles as $detalle)
                @php
                    $subtotal = $detalle->cantidad * $detalle->precio;
                    $subtotalSinDescuento += $subtotal;
                @endphp
                <tr>
                    <td>{{ strtoupper(substr($detalle->producto->nombre, 0, 30)) }}</td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td class="text-right">{{ number_format($detalle->precio, 2) }}</td>
                    <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if($descuento > 0)
            <tr>
                <td colspan="3" class="text-right">SUBTOTAL</td>
                <td class="text-right">{{ number_format($subtotalSinDescuento, 2) }}</td>
            </tr>
            <tr class="discount-row">
                <td colspan="3" class="text-right">DESCUENTO</td>
                <td class="text-right">-{{ number_format($descuento, 2) }}</td>
            </tr>
            @endif
            <tr class="totals">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalConDescuento, 2) }}</td>
            </tr>
            @if(isset($venta->monto_recibido))
            <tr>
                <td colspan="3" class="text-right">RECIBIDO</td>
                <td class="text-right">{{ number_format($venta->monto_recibido, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">CAMBIO</td>
                <td class="text-right">{{ number_format($venta->monto_recibido - $totalConDescuento, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <div>
        <strong>TIPO DE PAGO:</strong> {{ $venta->tipoPago->nombre_tipo_pago ?? 'N/A' }}
    </div>

    <br><br>
</body>
</html>