<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProExplo 2026 - Ajustes Técnicos</title>
    <style>
        :root {
            --pro-orange: #ea580c;
            --pro-orange-gradient: linear-gradient(#ea7317 0%, #a04307 100%);
            --pro-bg: radial-gradient(circle at top right, #ffffff 0%, #f1f5f9 100%);
        }

        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: var(--pro-bg);
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            width: 100%;
            text-align: center;
            border-top: 8px solid var(--pro-orange);
            animation: fadeIn 0.6s ease-out;
        }

        .mascota-container {
            margin-bottom: 20px;
        }

        .mascota-img {
            max-width: 150px;
            height: auto;
        }

        h1 {
            color: var(--pro-orange);
            font-size: 2rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        p {
            line-height: 1.8;
            color: #475569;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-refresh {
            display: inline-block;
            background: var(--pro-orange-gradient);
            color: white;
            padding: 12px 30px;
            border-radius: 99px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            transition: transform 0.2s;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
        }

        .btn-refresh:hover {
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="mascota-container">
            <img src="https://inscripciones.proexplo.com.pe/images/logo-proexplo.webp" alt="Mascota ProExplo" class="mascota-img">
        </div>

        <h1>Optimización en curso</h1>

        <p>
            Estamos realizando unos <strong>ajustes técnicos rápidos</strong> para asegurar que tu experiencia en la plataforma de <strong>ProExplo 2026</strong> sea perfecta.
            <br><br>
            No te preocupes, nuestro equipo de soporte ya está trabajando en ello y regresaremos en breve.
        </p>

        <a href="javascript:location.reload();" class="btn-refresh">
            🔄 Reintentar ahora
        </a>

        <div style="margin-top: 2rem; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
            Instituto de Ingenieros de Minas del Perú
        </div>
    </div>

</body>
</html>
