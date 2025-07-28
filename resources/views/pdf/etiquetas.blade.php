<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Estilos críticos para ajustar al tamaño */
        body, html {
            width: {{ $ancho_pt }}pt !important;
            height: {{ $alto_pt }}pt !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            font-family: 'Helvetica', Arial, sans-serif;
        }

        .contenedor-etiqueta {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transform: scale(0.95); /* Pequeño margen interno */
            transform-origin: center;
        }

        .texto-principal {
            font-size: 6pt; /* Tamaño reducido */
            font-weight: bold;
            text-align: center;
            line-height: 1.1;
            max-width: 100%;
            word-break: break-word;
        }

        .codigo-barras {
            max-width: 100%;
            max-height: 20pt; /* Altura controlada */
            margin-top: 2pt;
        }

        .texto-secundario {
            font-size: 4pt;
            text-align: center;
            margin-top: 1pt;
        }
    </style>
</head>
<body>
    @foreach($productos as $producto)
    @php
        // Obtener la fecha actual en formato Ymd (AñoMesDía)
        $fecha = now()->format('Ymd');
        // Formatear el precio: reemplazar punto decimal por guion
        $precioFormateado = str_replace('.', '-', number_format($producto['precio'] ?? 0, 2, '.', ''));
        // Combinar fecha y precio
        $precioConFecha = $fecha . $precioFormateado;
    @endphp
    <div class="contenedor-etiqueta">
        <div class="texto-principal">{{ $producto['nombre'] }}</div>
        
        <!-- Código de barras -->
        @if(isset($producto['codigo_barras']))
        <img class="codigo-barras" src="data:image/png;base64,{{ $producto['codigo_barras'] }}" alt="Código de barras">
        @endif
        
        <div class="texto-secundario">{{ $producto['codigo'] ?? '' }}</div>
        <div class="texto-principal">{{ $precioConFecha }}</div>
    </div>
    @endforeach
</body>
</html>