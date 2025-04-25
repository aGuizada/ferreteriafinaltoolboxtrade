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
            line-height: 1.5; /* Aumentado para mayor separación entre líneas */
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
            margin-bottom: 15px; /* Aumentado el margen inferior */
        }
        
        .info-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 8px; /* Aumentado el padding */
            font-size: 11px;
        }
        
        .info-box h3 {
            margin: 0 0 8px 0; /* Aumentado el margen inferior */
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        /* Corregir la superposición de texto en los datos del cliente */
        .info-box p {
            margin: 6px 0; /* Aumentado el margen entre párrafos */
            clear: both; /* Asegurar que cada párrafo comience en una nueva línea */
        }
        
        .info-box p strong {
            margin-right: 5px; /* Espacio después de la etiqueta */
            display: inline-block;
            min-width: 65px; /* Ancho mínimo para alinear los valores */
        }
        
        .validez-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .validez-box h3 {
            margin: 0 0 5px 0;
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
            padding: 6px; /* Aumentado el padding */
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
        
        /* Corregir el espaciado en las firmas */
        .firmas {
            display: flex;
            justify-content: space-between;
            margin-top: 30px; /* Aumentado */
            margin-bottom: 15px;
        }
        
        .firma {
            width: 45%;
            text-align: center;
        }
        
        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 40px; /* Aumentado para dar más espacio para la firma */
            padding-top: 5px;
            font-size: 12px;
        }
        
        .footer {
            border-top: 1px dashed #ddd;
            margin-top: 15px;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #666;
            line-height: 1.4; /* Mejorar espaciado entre líneas */
        }
        
        .footer p {
            margin: 3px 0; /* Espaciado entre párrafos del footer */
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>COTIZACIÓN N° {{ $venta[0]->id }}</h1>
            <p>Fecha: {{ $fechaVenta }} | Hora: {{ $horaVenta }}</p>
        </div>
        
        <!-- Información de cliente y cotización -->
        <div class="info-container">
            <div class="info-box">
                <h3>CLIENTE</h3>
                <p><strong>Nombre:</strong> <span>{{ $venta[0]->nombre }}</span></p>
                <p><strong>{{ $venta[0]->tipo_documento }}:</strong> <span>{{ $venta[0]->num_documento }}</span></p>
                <p><strong>Teléfono:</strong> <span>{{ $venta[0]->telefono }}</span></p>
                <p><strong>Dirección:</strong> <span>{{ $venta[0]->direccion }}</span></p>
            </div>
        </div>
        <div class="info-container1">
        <div class="info-box">
                <h3>INFORMACIÓN DE COTIZACIÓN</h3>
                <p><strong>Atendido por:</strong> <span>{{ $venta[0]->usuario }}</span></p>
                <p><strong>Forma de pago:</strong> <span>{{ $venta[0]->forma_pago ?? 'Contado' }}</span></p>
                <p><strong>T. entrega:</strong> <span>{{ $venta[0]->tiempo_entrega ?? 'Inmediato' }}</span></p>
                <p><strong>L. entrega:</strong> <span>{{ $venta[0]->lugar_entrega ?? 'En tienda' }}</span></p>
            </div>
            </div>
        <!-- Validez -->
        <div class="validez-box">
            <h3>VALIDEZ DE LA OFERTA</h3>
            <p><strong>Días de validez:</strong> {{ $diasValidez }} | <strong>Válido hasta:</strong> {{ $fechaValidez }}</p>
        </div>
        
        <!-- Tabla de productos -->
        <table>
            <thead>
                <tr>
                    <th width="40%">ARTÍCULO</th>
                    <th width="10%">CANT.</th>
                    <th width="15%">PRECIO</th>
                    <th width="15%">DESC.</th>
                    <th width="20%">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                <tr>
                    <td class="item-name">{{ $detalle->articulo }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ number_format($detalle->precio, 2) }}</td>
                    <td>{{ number_format($detalle->descuento, 2) }}</td>
                    <td>{{ number_format(($detalle->cantidad * $detalle->precio) - $detalle->descuento, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" style="text-align: right">TOTAL</td>
                    <td>{{ number_format($venta[0]->total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Nota -->
        <div class="nota-box">
            <p><strong>Nota:</strong> {{ $venta[0]->nota ?: 'Sin observaciones adicionales' }}</p>
        </div>
       
        
        <!-- Pie de página -->
        <div class="footer">
            <p>Esta cotización no constituye una factura.</p>
            <p>Los precios están sujetos a cambios una vez vencida la validez.</p>
            <p>Gracias por su preferencia.</p>
        </div>
    </div>
</body>
</html>