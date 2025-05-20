<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 12px;
            line-height: 1.5;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-align: center;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            font-size: 11px;
            margin: 5px 0 0;
        }

        .info-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .info-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 11px;
        }

        .info-box h3 {
            margin: 0 0 5px 0;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-box p {
            margin: 6px 0;
            clear: both;
        }

        .info-box p strong {
            margin-right: 5px;
            display: inline-block;
            min-width: 5px;
        }

        .validez-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .validez-box h3 {
            margin: 0 0 1px 0;
            font-size: 13px;
        }

        .validez-box p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }

        table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        .item-name {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
        }

        .nota-box {
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .footer {
            border-top: 1px dashed #ddd;
            margin-top: 15px;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }

        .footer p {
            margin: 3px 0;
        }

        .cotizacion-info {
            margin-left: 360px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>COTIZACIÓN N° {{ $venta[0]->id }}</h1>
            <p>Fecha: {{ $fechaVenta }} | Hora: {{ $horaVenta }}</p>
        </div>

        <div class="info-container">
            <div class="info-box cliente-info">
                <h3>CLIENTE</h3>
                <p><strong>Nombre:</strong> <span>{{ $venta[0]->nombre }}</span></p>
                <p><strong>{{ $venta[0]->tipo_documento }}:</strong> <span>{{ $venta[0]->num_documento }}</span></p>
                <p><strong>Atendido por:</strong> <span>{{ $venta[0]->usuario }}</span></p>
                 
            </div>
            <div class="info-box cotizacion-info">
                <h3>INFORMACIÓN DE COTIZACIÓN</h3>
                  <p><strong>Forma de pago:</strong> <span>{{ $venta[0]->forma_pago ?? 'Contado' }}</span></p>
                <p><strong>T. entrega:</strong> <span>{{ $venta[0]->tiempo_entrega ?? 'Inmediato' }}</span></p>
                <p><strong>L. entrega:</strong> <span>{{ $venta[0]->lugar_entrega ?? 'En tienda' }}</span></p>
            </div>
        </div>

        <div class="validez-box">
            <h3>VALIDEZ DE LA OFERTA</h3>
            <p><strong>Días de validez:</strong> {{ $diasValidez }} | <strong>Válido hasta:</strong> {{ $fechaValidez }}</p>
        </div>
        

<table>
    <thead>
        <tr>
            <th width="40%">ARTÍCULO</th>
            <th width="10%">CANT.</th>
            <th width="15%">PRECIO</th>
            <th width="20%">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($detalles as $detalle)
        <tr>
            <td class="item-name">{{ $detalle->articulo }}</td>
            <td class="align-right">{{ $detalle->cantidad }}</td>
            <td class="align-right">{{ number_format($detalle->precio, 2) }}</td>
            <td class="align-right">{{ number_format(($detalle->cantidad * $detalle->precio) - $detalle->descuento, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3" class="align-right">TOTAL</td>
            <td class="align-right">{{ number_format($venta[0]->total, 2) }}</td>
        </tr>
    </tbody>
</table>

        <div class="nota-box">
            <p><strong>Nota:</strong> {{ $venta[0]->nota ?: 'Sin observaciones adicionales' }}</p>
        </div>

        <div class="footer">
            <p>Esta cotización no constituye una factura.</p>
            <p>Los precios están sujetos a cambios una vez vencida la validez.</p>
            <p>Gracias por su preferencia.</p>
        </div>
    </div>
</body>
</html>
