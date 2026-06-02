<!DOCTYPE html>
<html lang="es">

<head>
    <title>Escanear QR - ProgresoFit</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/escanearQr.css">
    <!-- Librería de lectura QR por cámara (sin instalar nada) -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>

<body>
    <?php require 'parts/header.view.php'; ?>
    <main>
        <div class="scanner-container">
            <h2>Escanear QR de acceso</h2>
            <p class="scanner-subtitulo">Apuntá la cámara al código QR del alumno</p>

            <!-- Área de la cámara -->
            <div id="qr-reader"></div>

            <!-- Resultado del escaneo -->
            <div id="resultado" class="resultado" hidden>
                <div id="resultado-icono" class="resultado-icono"></div>
                <p id="resultado-tipo" class="resultado-tipo"></p>
                <p id="resultado-mensaje" class="resultado-mensaje"></p>
                <div id="resultado-aforo" class="resultado-aforo"></div>
                <button id="btn-nuevo-scan" class="btn-nuevo-scan">Escanear otro QR</button>
            </div>
        </div>
    </main>
    <?php require 'parts/footer.view.php'; ?>

    <script>
        const JWT         = <?= json_encode($jwt) ?>;
        const GIMNASIO_ID = <?= (int) $gimnasioId ?>;
        const API_URL     = '/api-proxy/escanear'; // usamos proxy PHP para evitar CORS

        let escaneando = true;

        const html5QrCode = new Html5Qrcode("qr-reader");

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        function mostrarResultado(tipo, mensaje, aforo, esError) {
            html5QrCode.stop().catch(() => {});
            escaneando = false;

            const div       = document.getElementById('resultado');
            const icono     = document.getElementById('resultado-icono');
            const tipoEl    = document.getElementById('resultado-tipo');
            const mensajeEl = document.getElementById('resultado-mensaje');
            const aforoEl   = document.getElementById('resultado-aforo');

            div.removeAttribute('hidden');
            div.className = 'resultado ' + (esError ? 'resultado-error' : 'resultado-ok');

            icono.textContent     = esError ? '✗' : (tipo === 'ENTRADA' ? '↑' : '↓');
            tipoEl.textContent    = esError ? 'Error' : tipo;
            mensajeEl.textContent = mensaje;

            if (aforo) {
                aforoEl.textContent = `Ocupación: ${aforo.ocupacion_actual} / ${aforo.capacidad_maxima} personas`;
                aforoEl.removeAttribute('hidden');
            } else {
                aforoEl.setAttribute('hidden', '');
            }
        }

        async function onScanExitosa(tokenQR) {
            if (!escaneando) return;
            escaneando = false; // evitar doble disparo

            try {
                const resp = await fetch('/escanear-proxy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-JWT': JWT
                    },
                    body: JSON.stringify({ token: tokenQR, gimnasio_id: GIMNASIO_ID })
                });

                const data = await resp.json();

                if (resp.ok) {
                    mostrarResultado(data.tipo, data.tipo === 'ENTRADA' ? 'Entrada registrada' : 'Salida registrada', data, false);
                } else {
                    mostrarResultado(null, data.error || 'Error al procesar el QR', null, true);
                }
            } catch (e) {
                mostrarResultado(null, 'Error de conexión con el servidor', null, true);
            }
        }

        function onScanError() {
            // silencioso — la cámara sigue buscando
        }

        html5QrCode.start(
            { facingMode: "environment" }, // cámara trasera del celular
            config,
            onScanExitosa,
            onScanError
        ).catch(() => {
            // Si no hay cámara trasera, probar con cualquier cámara
            html5QrCode.start(
                { facingMode: "user" },
                config,
                onScanExitosa,
                onScanError
            ).catch(err => {
                document.getElementById('qr-reader').innerHTML =
                    '<p class="scanner-error">No se pudo acceder a la cámara. Verificá los permisos del navegador.</p>';
            });
        });

        document.getElementById('btn-nuevo-scan').addEventListener('click', () => {
            document.getElementById('resultado').setAttribute('hidden', '');
            escaneando = true;
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanExitosa,
                onScanError
            ).catch(() => {
                html5QrCode.start({ facingMode: "user" }, config, onScanExitosa, onScanError);
            });
        });
    </script>
</body>

</html>
