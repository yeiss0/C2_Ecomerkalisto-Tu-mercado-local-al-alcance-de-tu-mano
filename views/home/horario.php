<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios de Atención - EcomerKalisto</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
        .horario-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 80px 20px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .horario-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(213, 255, 106, 0.08) 0%, transparent 60%);
            animation: pulse-bg 4s ease-in-out infinite;
        }
        @keyframes pulse-bg {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        .horario-hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            color: #fff;
            position: relative;
            z-index: 1;
        }
        .horario-hero h1 span {
            color: #D5FF6A;
        }
        .horario-hero p {
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }
        .horario-container {
            max-width: 700px;
            margin: -30px auto 60px;
            padding: 0 20px;
        }
        .horario-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .horario-card-header {
            background: linear-gradient(135deg, #D5FF6A, #b8e832);
            padding: 30px;
            text-align: center;
        }
        .horario-card-header .clock-icon {
            font-size: 3rem;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .horario-card-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1a1a2e;
            margin: 0;
        }
        .horario-body {
            padding: 0;
        }
        .day-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 30px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        .day-row:last-child {
            border-bottom: none;
        }
        .day-row:hover {
            background: #f9fff0;
        }
        .day-row.today {
            background: linear-gradient(90deg, #f0ffe0, #ffffff);
            border-left: 4px solid #D5FF6A;
        }
        .day-name {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }
        .day-name .day-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #D5FF6A;
            box-shadow: 0 0 6px rgba(213,255,106,0.6);
        }
        .day-row.today .day-name {
            color: #1a7a00;
        }
        .day-hours {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #1a1a2e;
            font-size: 1rem;
        }
        .day-hours i {
            color: #D5FF6A;
            font-size: 0.9rem;
        }
        .today-badge {
            background: #D5FF6A;
            color: #1a1a2e;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .horario-footer {
            background: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .horario-footer p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .horario-footer p i {
            color: #D5FF6A;
            margin-right: 5px;
        }
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            max-width: 700px;
            margin: 30px auto 60px;
            padding: 0 20px;
        }
        .info-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px rgba(0,0,0,0.12);
        }
        .info-card i {
            font-size: 2rem;
            color: #D5FF6A;
            margin-bottom: 12px;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(180,220,50,0.4));
        }
        .info-card h4 {
            font-size: 0.85rem;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .info-card p {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: #D5FF6A;
            color: #1a1a2e;
            font-weight: 700;
            border-radius: 50px;
            text-decoration: none;
            margin: 0 auto 20px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        .back-btn:hover {
            background: #c0e855;
            transform: translateY(-2px);
            color: #1a1a2e;
            text-decoration: none;
        }
        .nav-horario {
            background: #1a1a2e;
            padding: 15px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-horario .logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }
        .nav-horario .logo span {
            color: #D5FF6A;
        }
        @media (max-width: 500px) {
            .nav-horario {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .horario-hero h1 { font-size: 2rem; }
            .horario-hero p { font-size: 0.9rem; }
            .horario-card-header h2 { font-size: 1.3rem; }
            .day-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body style="background: #f4f6f0; font-family: 'Poppins', sans-serif;">

    <!-- Navbar simple -->
    <nav class="nav-horario">
        <a href="<?= BASE_URL ?>/" class="logo"><i class="fas fa-shopping-basket"></i> Ecomer<span>Kalisto</span></a>
        <a href="<?= BASE_URL ?>/" class="back-btn" style="margin: 0;"><i class="fas fa-arrow-left"></i> Volver a la tienda</a>
    </nav>

    <!-- Hero -->
    <div class="horario-hero">
        <h1><i class="fas fa-clock" style="color: #D5FF6A; margin-right: 12px;"></i>Nuestro <span>Horario</span></h1>
        <p>Estamos aquí para atenderte todos los días de la semana</p>
    </div>

    <!-- Tarjeta de horarios -->
    <div class="horario-container">
        <div class="horario-card">
            <div class="horario-card-header">
                <div class="clock-icon"><i class="fas fa-store"></i></div>
                <h2>Horarios de Atención</h2>
            </div>
            <div class="horario-body" id="schedule-body">
                <!-- Se rellena con JS para resaltar el día actual -->
            </div>
            <div class="horario-footer">
                <p><i class="fas fa-info-circle"></i> Horario válido para pedidos a domicilio y recogida en tienda</p>
            </div>
        </div>
    </div>

    <!-- Info cards -->
    <div class="info-cards">
        <div class="info-card">
            <i class="fas fa-phone-alt"></i>
            <h4>Teléfono</h4>
            <p id="h-telefono">+57 313 253 1923</p>
        </div>
        <div class="info-card">
            <i class="fas fa-map-marker-alt"></i>
            <h4>Dirección</h4>
            <p id="h-direccion">Cl. 14 #4a-84, Ubaté</p>
        </div>
        <div class="info-card">
            <i class="fab fa-whatsapp"></i>
            <h4>WhatsApp</h4>
            <p id="h-whatsapp">+57 313 768 9660</p>
        </div>
    </div>
    </div>

    <script>
        const days = [
            'Domingo', 'Lunes', 'Martes', 'Miércoles',
            'Jueves', 'Viernes', 'Sábado'
        ];
        const today = new Date().getDay(); // 0=Dom, 1=Lun...

        const container = document.getElementById('schedule-body');

        async function loadConfigAndSchedule() {
            let scheduleStr = "9:00 AM — 9:00 PM";
            try {
                const res = await fetch(BASE_URL + '/api/get_config.php?t=' + Date.now());
                const config = await res.json();
                
                if (config.horario) scheduleStr = config.horario;
                
                if (config.contacto_telefono) {
                    document.getElementById('h-telefono').innerText = config.contacto_telefono;
                    document.getElementById('h-whatsapp').innerText = config.contacto_telefono;
                }
                
                if (config.contacto_direccion) {
                    document.getElementById('h-direccion').innerText = config.contacto_direccion;
                }
            } catch(e) {
                console.error("Error loading config", e);
            }
            
            let html = '';
            days.forEach((day, index) => {
                const isToday = index === today;
                html += `
                    <div class="day-row ${isToday ? 'today' : ''}">
                        <div class="day-name">
                            <span class="day-dot"></span>
                            <span>${day}</span>
                            ${isToday ? '<span class="today-badge">Hoy</span>' : ''}
                        </div>
                        <div class="day-hours">
                            <i class="fas fa-clock"></i>
                            ${scheduleStr}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        loadConfigAndSchedule();
    </script>
</body>
</html>
