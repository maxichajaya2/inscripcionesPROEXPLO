<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProExplo 2026 - Plataforma en Mantenimiento</title>
    <style>
        /* Inspirado en tu AppLayout y estilos de inscripciones */
        :root {
            --pro-orange-gradient: linear-gradient(#ea7317 0%, #a04307 100%);
            --pro-orange-glow: rgba(249, 115, 22, 0.5);
            --pro-dark: #1e293b; /* Slate 800 */
            --pro-bg: radial-gradient(circle at top right, #ffffff 0%, #f1f5f9 100%);
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--pro-bg);
            color: var(--pro-dark);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .main-container {
            max-width: 700px;
            width: 100%;
            text-align: center;
            animation: fadeInDown 0.8s ease-out forwards;
        }

        /* Estilo de la tarjeta principal similar a tus botones de macroSeccion */
        .card-mantenimiento {
            position: relative;
            background: white;
            padding: 3.5rem 2rem;
            border-radius: 2rem; /* rounded-3xl */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* Borde superior animado como tu 'border-anim-orange' */
        .card-mantenimiento::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: var(--pro-orange-gradient);
        }

        .logo-container {
            margin-bottom: 2.5rem;
        }

        .logo-img {
            max-width: 280px;
            height: auto;
            /* Filtro sutil para que resalte */
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 900; /* font-black */
            color: #ea580c; /* pro-orange */
            margin: 0 0 1.5rem 0;
            text-transform: uppercase;
            letter-spacing: -0.025em;
        }

        .message-content {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #475569; /* slate-600 */
            margin-bottom: 2.5rem;
        }

        .message-content strong {
            color: #1e293b;
            font-weight: 700;
        }

        /* El decorador naranja de tu título */
        .divider {
            display: inline-block;
            width: 80px;
            height: 6px;
            border-radius: 9999px;
            background: #f97316;
            box-shadow: 0 0 15px var(--pro-orange-glow);
            margin-bottom: 2rem;
        }

        .info-box {
            background: #fff7ed; /* Naranja muy clarito */
            border-left: 4px solid #f97316;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: left;
            display: inline-block;
            max-width: 90%;
        }

        .info-box p {
            margin: 0;
            font-size: 0.95rem;
            color: #9a3412; /* orange-900 */
            font-weight: 500;
        }

        .footer {
            margin-top: 3rem;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 640px) {
            h1 { font-size: 1.6rem; }
            .card-mantenimiento { padding: 2.5rem 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="card-mantenimiento">
            <div class="logo-container">
                <img src="https://inscripciones.proexplo.com.pe/images/logo-proexplo.webp" alt="PROEXPLO 2026" class="logo-img">
            </div>

            <div class="divider"></div>

            <h1>Plataforma en Mantenimiento</h1>
            <h2>Optimizando el sistema de inscripciones</h2>

            <div class="message-content">
                <p>
                    Estimados participantes y colaboradores, nos encontramos realizando <strong>actualizaciones importantes</strong>
                    en nuestra plataforma de inscripciones para<strong> ProExplo 2026</strong>.
                </p>
            </div>

            <div class="info-box">
                <p>
                    ⚠️ <strong>Nota:</strong> Si necesita asistencia urgente con su inscripción, por favor contáctenos a
                    <a href="mailto:inscripciones@iimp.org.pe" style="color: #ea580c; text-decoration: none; font-weight: 700;">inscripciones@iimp.org.pe</a>
                </p>
            </div>
        </div>

        <div class="footer">
            © {{ date('Y') }} Instituto de Ingenieros de Minas del Perú
        </div>
    </div>

</body>
</html>
