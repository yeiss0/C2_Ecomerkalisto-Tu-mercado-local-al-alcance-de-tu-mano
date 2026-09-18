<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - EcoMerkaListo</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .contact-header { background: #D5FF6A; color: #2c3e50; padding: 60px 0; margin-bottom: 50px; text-align: center; }
        .nav-back { position: absolute; top: 20px; left: 20px; padding: 10px; text-decoration: none; color: #2c3e50; font-weight: 600; opacity: 0.8; }
        .nav-back:hover { opacity: 1; color: #1a252f; }
        .contact-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: 100%; transition: transform 0.3s ease; text-align: center; }
        .contact-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .contact-icon { width: 80px; height: 80px; background: rgba(46, 204, 113, 0.1); color: #2ecc71; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px; }
    </style>
</head>
<body>

    <div class="contact-header position-relative">
        <a href="<?= BASE_URL ?>/" class="nav-back"><i class="fas fa-arrow-left"></i> Volver a la Tienda</a>
        <div class="container">
            <h1 class="fw-bold mb-3">¿Cómo podemos ayudarte?</h1>
            <p class="lead opacity-75">Estamos aquí para responder tus dudas y recibir tus sugerencias.</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row g-4 justify-content-center">
            
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Correo Electrónico</h4>
                    <p class="text-muted mb-4">Escríbenos y te responderemos lo más pronto posible.</p>
                    <a href="mailto:inversionesmercalisto@gmail.com" class="fs-5 text-dark fw-bold text-decoration-none" style="word-break: break-all;">inversionesmercalisto@gmail.com</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-card border-success" style="border: 2px solid #2ecc71;">
                    <div class="contact-icon" style="background: #2ecc71; color: white;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h4 class="fw-bold mb-3">WhatsApp / Teléfono</h4>
                    <p class="text-muted mb-4">La forma más rápida de comunicarte con nosotros.</p>
                    <a href="https://wa.me/573132531923" target="_blank" class="fs-5 text-success fw-bold text-decoration-none">+57 313 253 1923</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Ubicación</h4>
                    <p class="text-muted mb-4">Visítanos en nuestra sede principal.</p>
                    <span class="fs-5 text-dark fw-bold">Cl. 14 #4a-84<br><small class="text-muted">Ubaté, Cundinamarca</small></span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
