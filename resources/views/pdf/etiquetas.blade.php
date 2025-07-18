<!DOCTYPE html>
<html lang="es">
<head>
    
    <title>Etiquetas de Productos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .etiqueta {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-after: always;
        }
        .nombre {
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .precio {
            font-size: 2em;
            color: #1a8917;
        }
    </style>
</head>
<body>
@if(isset($productos) && count($productos))
    @foreach($productos as $producto)
        <div class="etiqueta">
            <div class="nombre">{{ $producto['nombre'] }}</div>
            <div class="precio">{{ $producto['precio_fecha'] }}</div>
        </div>
    @endforeach
@endif
</body>
</html>
