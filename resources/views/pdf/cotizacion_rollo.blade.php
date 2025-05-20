
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COTIZACIÓN (ROLLO 80mm)</title>
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
        .titulo-cotizacion {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .info-cotizacion {
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
        .validez {
            margin-top: 5px;
            padding: 3px;
            border: 1px dashed #000;
            text-align: center;
            font-weight: bold;
        }
        .notas {
            margin-top: 5px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        @if(isset($logoPath) && file_exists($logoPath))
            <img src="{{ $logoPath }}" class="logo" alt="Logo">
        @endif
        <div class="titulo-cotizacion">COTIZACIÓN</div>
        <div>No. {{ str_pad($venta[0]->id ?? '00000', 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <!-- Datos de la empresa -->
    <div class="empresa">
        <div>{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</div>
        <div>{{ $empresa->direccion ?? 'DIRECCIÓN' }}</div>
        <div>Tel: {{ $empresa->telefono ?? 'N/A' }} | NIT: {{ $empresa->nit ?? 'N/A' }}</div>
    </div>

    <!-- Información de la cotización -->
    <div class="info-cotizacion">
        <strong>Fecha:</strong> {{ $fechaVenta }} {{ $horaVenta }}<br>
        <strong>Cliente:</strong> {{ isset($venta[0]->nombre) ? strtoupper(substr($venta[0]->nombre, 0, 25)) : 'CONSUMIDOR FINAL' }}<br>
        <strong>Doc:</strong> {{ $venta[0]->num_documento ?? 'N/A' }}<br>
        <strong>Vendedor:</strong> {{ $venta[0]->usuario ?? 'N/A' }}
    </div>

    <!-- Detalles de productos -->
    <table>
        <thead>
            <tr>
                <th width="45%">Artículo</th>
                <th width="15%" class="align-center">Cant.</th>
                <th width="20%" class="align-right">P. Unit.</th>
                <th width="20%" class="align-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td>{{ substr($detalle->articulo, 0, 20) }}</td>
                <td class="align-center">{{ $detalle->cantidad }}</td>
                <td class="align-right">{{ number_format($detalle->precio, 2) }}</td>
                <td class="align-right">{{ number_format($detalle->cantidad * $detalle->precio, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="3" class="align-right">TOTAL Bs.</td>
                <td class="align-right">{{ number_format($venta[0]->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Información de validez -->
    <div class="validez">
        VÁLIDO HASTA: {{ $fechaValidez }} ({{ $diasValidez }} días)
    </div>

    <!-- Condiciones de entrega y pago -->
    <div class="notas">
        <strong>Condiciones:</strong><br>

        <p><strong>T. entrega:</strong> <span>{{ $venta[0]->tiempo_entrega ?? 'Inmediato' }}</span></p>
        <p><strong>L. entrega:</strong> <span>{{ $venta[0]->lugar_entrega ?? 'En tienda' }}</span></p>      <strong>Notas:</strong> {{ $venta[0]->nota ?? 'Cotización sujeta a disponibilidad de stock' }}
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <div>¡Gracias por su preferencia!</div>
        <div>Esta cotización no constituye una factura</div>
        <div>Los precios están sujetos a cambios una vez vencida la validez</div>

    </div>
</body>
</html>
