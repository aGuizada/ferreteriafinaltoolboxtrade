<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta (Rollo)</title>
    <style>
        /* Estilos optimizados para impresión térmica 80mm */
        @page { margin: 0;  }
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
        .logo {
            max-height: 30px;
            margin-bottom: 3px;
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
        .info-venta {
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
        .qr-code {
            margin: 3px auto;
            text-align: center;
        }
        .discount-row {
            color: #ff0000;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        @if(isset($logoPath) && file_exists($logoPath))
            <img src="{{ $logoPath }}" class="logo" alt="Logo">
        @endif
        <div class="titulo-recibo">PROFORMA</div>
        <div>No. {{ str_pad($venta->id ?? '00000', 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <!-- Datos de la empresa -->
    <div class="empresa">
        <div>{{ strtoupper($empresa->nombre ?? 'TIENDA') }}</div>
        <div>{{ $empresa->direccion ?? 'DIRECCIÓN' }}</div>
        <div>Tel: {{ $empresa->telefono ?? 'N/A' }} | NIT: {{ $empresa->nit ?? 'N/A' }}</div>
    </div>

    <!-- Información de la venta -->
    <div class="info-venta">
        <strong>Fecha:</strong> {{ isset($venta->created_at) ? \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') : date('d/m/Y H:i') }}<br>
        <strong>Cliente:</strong> {{ isset($persona->nombre) ? strtoupper(substr($persona->nombre, 0, 25)) : 'CONSUMIDOR FINAL' }}<br>
        <strong>Doc:</strong> {{ $persona->num_documento ?? 'N/A' }}
    </div>

    <!-- Detalles de productos -->
    <table>
        <thead>
            <tr>
                <th width="60%">Descripción</th>
                <th width="15%" class="align-center">Cant.</th>
                <th width="25%" class="align-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $subtotalSinDescuento = 0;
                $totalConDescuento = $venta->total ?? 0;
                $descuento = $venta->descuento ?? 0;
            @endphp
            @if(isset($venta->detalles) && count($venta->detalles) > 0)
                @foreach($venta->detalles as $detalle)
                    @php
                        $subtotal = $detalle->cantidad * $detalle->precio;
                        $subtotalSinDescuento += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ isset($detalle->producto->nombre) ? strtoupper(substr($detalle->producto->nombre, 0, 20)) : 'PRODUCTO' }}</td>
                        <td class="align-center">{{ $detalle->cantidad ?? 1 }}</td>
                        <td class="align-right">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="align-center">No hay detalles de venta</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            @if($descuento > 0)
            <tr>
                <td colspan="2" class="align-right">SUBTOTAL Bs.</td>
                <td class="align-right">{{ number_format($subtotalSinDescuento, 2) }}</td>
            </tr>
            <tr class="discount-row">
                <td colspan="2" class="align-right">DESCUENTO Bs.</td>
                <td class="align-right">-{{ number_format($descuento, 2) }}</td>
            </tr>
            @endif
            <tr class="totals">
                <td colspan="2" class="align-right">TOTAL Bs.</td>
                <td class="align-right">{{ number_format($totalConDescuento, 2) }}</td>
            </tr>
            @if(isset($venta->monto_recibido))
            <tr>
                <td colspan="2" class="align-right">Recibido Bs.</td>
                <td class="align-right">{{ number_format($venta->monto_recibido, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="align-right">Cambio Bs.</td>
                <td class="align-right">{{ number_format($venta->monto_recibido - $totalConDescuento, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- Método de pago -->
    <div style="margin-top: 3px;">
        <strong>Forma de pago:</strong> {{ $venta->tipoPago->nombre_tipo_pago ?? 'EFECTIVO' }}
    </div>

    <!-- Código QR (opcional) -->
    @if(isset($empresa->qr) && file_exists(storage_path('app/public/qr/' . $empresa->qr)))
    <div class="qr-code">
        <img src="{{ storage_path('app/public/qr/' . $empresa->qr) }}" width="50" alt="Código QR">
        <div>Escanee para verificar</div>
    </div>
    @endif


</body>
</html>