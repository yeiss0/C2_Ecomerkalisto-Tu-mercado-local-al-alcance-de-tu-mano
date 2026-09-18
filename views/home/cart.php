<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - EcoMerkaListo</title>
    <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .cart-header { background: #D5FF6A; padding: 20px 0; text-align: center; font-weight: bold; font-size: 1.5rem; color: #2c3e50; }
        .cart-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee; }
        .cart-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .cart-summary { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-checkout { background-color: #2ecc71; color: white; width: 100%; font-weight: bold; border: none; padding: 12px; border-radius: 8px; }
        .btn-checkout:hover { background-color: #27ae60; }
        .nav-back { padding: 15px; display: block; text-decoration: none; color: #2c3e50; font-weight: 600; }
    </style>
</head>
<body>

    <a href="<?= BASE_URL ?>/" class="nav-back"><i class="fas fa-arrow-left"></i> Volver a la Tienda</a>

    <div class="cart-header">
        <i class="fas fa-shopping-cart"></i> Tu Carrito de Compras
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body" id="cart-items-container">
                        <div class="text-center py-5 text-muted" id="empty-cart-msg">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <h4>Tu carrito está vacío</h4>
                            <p>¡Agrega algunos productos para empezar!</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4 class="mb-4">Resumen del Pedido</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (<span id="total-items">0</span> items)</span>
                        <strong id="subtotal-price">$0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span>Envío</span>
                        <strong class="text-success">Calcular en el siguiente paso</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5 fw-bold">Total</span>
                        <span class="fs-5 fw-bold text-success" id="total-price">$0</span>
                    </div>
                    
                    <p class="text-danger fw-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-circle"></i> Mínimo para que te lleven el domicilio es de $50.000
                    </p>
                    
                    <h5 class="mt-4 mb-3">Datos de Envío</h5>
                    <div class="mb-3">
                        <input type="text" id="chk-nombre" class="form-control" placeholder="Tu nombre completo" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" id="chk-email" class="form-control" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($_SESSION['usuario_email'] ?? '', ENT_QUOTES); ?>" required readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                    </div>
                    <div class="mb-3">
                        <input type="tel" id="chk-telefono" class="form-control" placeholder="Celular / WhatsApp" required>
                    </div>
                    <div class="mb-3">
                        <select id="chk-entrega" class="form-select" onchange="toggleDireccion()">
                            <option value="Domicilio">Envío a Domicilio</option>
                            <option value="Recogida">Pasar a recoger</option>
                        </select>
                    </div>
                    <div class="mb-3" id="div-direccion">
                        <input type="text" id="chk-direccion" class="form-control" placeholder="Dirección completa">
                    </div>
                    <div class="mb-3">
                        <select id="chk-pago" class="form-select">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Datáfono">Datáfono</option>
                        </select>
                    </div>

                    <button class="btn btn-checkout mt-2" onclick="checkout()">Realizar pedido</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        function renderCart() {
            const container = document.getElementById('cart-items-container');
            
            if(cart.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted" id="empty-cart-msg">
                        <i class="fas fa-box-open fa-3x mb-3"></i>
                        <h4>Tu carrito está vacío</h4>
                        <p>¡Agrega algunos productos para empezar!</p>
                    </div>`;
                document.getElementById('total-items').innerText = '0';
                document.getElementById('subtotal-price').innerText = '$0';
                document.getElementById('total-price').innerText = '$0';
                return;
            }
            
            let html = '';
            let totalQty = 0;
            let totalPrice = 0;
            
            cart.forEach((item, index) => {
                totalQty += item.qty;
                totalPrice += (item.precio * item.qty);
                html += `
                    <div class="cart-item">
                        <div class="d-flex align-items-center">
                            <img src="${item.imagen}" alt="${item.nombre}" class="me-3">
                            <div>
                                <h6 class="mb-0">${item.nombre}</h6>
                                <small class="text-muted">$${parseFloat(item.precio).toLocaleString('es-CO')} c/u</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${index}, -1)">-</button>
                            <span class="mx-3 fw-bold">${item.qty}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${index}, 1)">+</button>
                            <button class="btn btn-sm btn-danger ms-4" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            document.getElementById('total-items').innerText = totalQty;
            document.getElementById('subtotal-price').innerText = '$' + totalPrice.toLocaleString('es-CO');
            document.getElementById('total-price').innerText = '$' + totalPrice.toLocaleString('es-CO');
        }
        
        function updateQty(index, change) {
            cart[index].qty += change;
            if(cart[index].qty <= 0) {
                cart.splice(index, 1);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        
        function removeItem(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        
        function toggleDireccion() {
            const dir = document.getElementById('div-direccion');
            if(document.getElementById('chk-entrega').value === 'Recogida') {
                dir.style.display = 'none';
            } else {
                dir.style.display = 'block';
            }
        }
        
        async function fetchLatestPrices() {
            try {
                const res = await fetch(BASE_URL + '/api/get_productos.php?t=' + new Date().getTime());
                const allProducts = await res.json();
                
                cart.forEach(item => {
                    const serverProduct = allProducts.find(p => p.id == item.id);
                    if (serverProduct) {
                        item.precio = serverProduct.precio;
                        item.nombre = serverProduct.nombre;
                        item.imagen = serverProduct.url_imagen;
                    }
                });
                localStorage.setItem('cart', JSON.stringify(cart));
                renderCart();
            } catch(e) {
                renderCart();
            }
        }
        
        async function checkout() {
            if(cart.length === 0) return alert("Tu carrito está vacío.");
            
            // Validate minimum amount for delivery
            let totalPrice = cart.reduce((acc, item) => acc + (item.precio * item.qty), 0);
            const entrega = document.getElementById('chk-entrega').value;
            
            if (entrega === 'Domicilio' && totalPrice < 50000) {
                return alert("El monto mínimo de compra para envíos a domicilio es de $50.000 COP.");
            }
            
            const nombre = document.getElementById('chk-nombre').value;
            const email = document.getElementById('chk-email').value;
            const telefono = document.getElementById('chk-telefono').value;
            const direccion = document.getElementById('chk-direccion').value;
            const pago = document.getElementById('chk-pago').value;
            
            if(!nombre || !email || !telefono) {
                return alert("Por favor completa tu nombre, correo y teléfono.");
            }
            if(entrega === 'Domicilio' && !direccion) {
                return alert("Por favor ingresa tu dirección para el envío.");
            }
            
            const payload = {
                nombre: nombre,
                email: email,
                telefono: telefono,
                direccion: direccion,
                metodo_entrega: entrega,
                medio_pago: pago,
                cart: cart
            };
            
            try {
                const res = await fetch(BASE_URL + '/api/checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                let data = null;
                try {
                    data = await res.json();
                } catch(jsonErr) {
                    const rawText = await res.text();
                    alert("Error en el servidor: " + (rawText || "Respuesta vacía."));
                    return;
                }
                
                if(data && data.success) {
                    // Guardar último pedido para reordenar en el perfil
                    localStorage.setItem('lastOrder', JSON.stringify(cart));
                    
                    // Limpiar carrito
                    localStorage.removeItem('cart');
                    cart = [];
                    renderCart();
                    
                    // Abrir WhatsApp con el teléfono correcto
                    const phone = "573133768966"; 
                    const mensaje = encodeURIComponent(data.whatsapp_msg);
                    window.location.href = `https://wa.me/${phone}?text=${mensaje}`;
                } else {
                    alert("Error: " + (data ? data.error : "No se pudo procesar el pedido."));
                    if(data && data.error === "Debes iniciar sesión para comprar.") {
                        window.location.href = BASE_URL + "/login";
                    }
                }
            } catch(e) {
                alert("Error al procesar el pedido: " + (e.message || "Error de conexión"));
            }
        }
        
        document.addEventListener('DOMContentLoaded', renderCart);
        document.addEventListener('DOMContentLoaded', fetchLatestPrices);
    </script>
</body>
</html>
