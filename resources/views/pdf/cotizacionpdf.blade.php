<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma de Cotización</title>
    <style>
           body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .header-info div {
            width: 45%;
        }

        .header-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        #logo {
            width: 120px;
            margin-right: 20px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .invoice-details {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details th,
        .invoice-details td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .invoice-details th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .invoice-details .total-row td {
            font-weight: bold;
            text-align: right;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer-message {
            margin-top: 20px;
        }

        .date-time {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
            color: #333;
        }

        .customer-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .customer-details p {
            margin: 0;
            font-size: 14px;
            width: 48%; /* To ensure the details appear side by side */
        }
    </style>
</head>
<body>

    <header>
        <h1>Proforma de Cotización</h1>
        
    </header>

    <div class="container">
        <!-- Header Information (Client and Company) -->
        <div class="customer-details">
       
            <div>
                <p><strong>Nombre:</strong> {{ $venta[0]->nombre }}</p>
                <p><strong>Número de documento:</strong> {{ $venta[0]->num_documento }}</p>
                <p><strong>Fecha:</strong> {{ $fechaVenta }} <br>
                <strong>Hora:</strong> {{ $horaVenta }}</p>
           
            </div>
         
        </div>

        <!-- Proforma Title -->
        <div class="invoice-title">Detalles de la Cotización</div>

        <!-- Cotización Details (Items) -->
        <table class="invoice-details">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Descuento</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->articulo }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>{{ $detalle->precio }}</td>
                        <td>{{ $detalle->descuento }}</td>
                        <td>{{ $detalle->cantidad * $detalle->precio - $detalle->descuento }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total -->
        <div class="total">
            <p>Total : {{ $venta[0]->total }}</p>
        </div>

        <!-- Footer -->
        <footer>
            <p class="footer-message">Gracias por su interés. Si tiene alguna pregunta, no dude en contactarnos.</p>
        </footer>
    </div>

</body>
</html>
