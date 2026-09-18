<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - EcoMerkaListo</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .profile-header { background: #D5FF6A; color: #2c3e50; padding: 40px 0; margin-bottom: 30px; }
        .nav-back { padding: 15px; display: inline-block; text-decoration: none; color: #2c3e50; font-weight: 600; opacity: 0.8; }
        .nav-back:hover { opacity: 1; color: #1a252f; }
    </style>
</head>
<body>

    <div class="profile-header">
        <div class="container">
            <a href="<?= BASE_URL ?>/" class="nav-back mb-3"><i class="fas fa-arrow-left"></i> Volver a la Tienda</a>
            <div class="d-flex align-items-center">
                <div class="bg-light rounded-circle text-dark d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2.5rem;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="ms-4">
                    <h2>Bienvenido, <?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></h2>
                    <p class="mb-0 opacity-75"><?php echo $_SESSION['usuario_email'] ?? 'correo@ejemplo.com'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title border-bottom pb-2 mb-3"><i class="fas fa-cog text-muted"></i> Mi Cuenta</h5>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-dark fw-bold bg-light rounded" href="#"><i class="fas fa-box"></i> Mis Pedidos</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="mb-4">Historial de Pedidos</h4>
                        
                        <div id="historial-container">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>Cargando tus pedidos...</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let userOrders = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadUserOrders();
        });

        function getEstadoBadge(estado) {
            const badges = {
                'Pendiente':  '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pendiente</span>',
                'Completado': '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Completado</span>',
                'Cancelado':  '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Cancelado</span>',
                'Devuelto':   '<span class="badge bg-danger"><i class="fas fa-undo me-1"></i>Devuelto</span>'
            };
            return badges[estado] || `<span class="badge bg-info">${estado}</span>`;
        }

        async function loadUserOrders() {
            const container = document.getElementById('historial-container');
            try {
                const res = await fetch(BASE_URL + '/api/mis_pedidos.php?t=' + Date.now());
                if (!res.ok) {
                    throw new Error("No se pudo cargar el historial.");
                }
                const orders = await res.json();
                userOrders = orders;

                if (!orders || orders.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                            <h5>Aún no tienes pedidos</h5>
                            <p>Tus compras recientes aparecerán aquí.</p>
                            <a href="${BASE_URL}/" class="btn btn-success mt-2 fw-bold" style="background-color: #2ecc71; border: none;">Ir a comprar</a>
                        </div>
                    `;
                    return;
                }

                let html = '<div class="d-flex flex-column gap-4">';
                orders.forEach(order => {
                    const fecha = new Date(order.fecha).toLocaleString('es-CO', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    });

                    let itemsHtml = '';
                    if (order.detalles && order.detalles.length > 0) {
                        order.detalles.forEach(d => {
                            const subtotal = d.cantidad * d.precio_unitario;
                            itemsHtml += `
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="${d.url_imagen || ''}" width="48" height="48" style="object-fit:cover; border-radius:8px;" onerror="this.src='${BASE_URL}/assets/placeholder.png'; this.onerror=null;">
                                        <div>
                                            <div class="fw-bold">${d.nombre}</div>
                                            <small class="text-muted">${d.marca || ''} &bull; ${d.cantidad} ud. &times; $${parseFloat(d.precio_unitario).toLocaleString('es-CO')}</small>
                                            ${parseInt(d.cantidad_devuelta) > 0 ? `<br><span class="badge bg-danger-subtle text-danger border border-danger">Devuelto(s): ${d.cantidad_devuelta}</span>` : ''}
                                        </div>
                                    </div>
                                    <span class="fw-semibold">$${parseFloat(subtotal).toLocaleString('es-CO')}</span>
                                </div>
                            `;
                        });
                    } else {
                        itemsHtml = '<div class="text-muted py-2">Detalles no disponibles.</div>';
                    }

                    html += `
                        <div class="card border shadow-sm">
                            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                                <div>
                                    <span class="fw-bold text-dark fs-6 me-2">Pedido #${order.id}</span>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${fecha}</small>
                                </div>
                                <div>
                                    ${getEstadoBadge(order.estado)}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 small text-muted">
                                    <span><i class="fas fa-truck me-1"></i>${order.metodo_entrega || 'Domicilio'}</span>
                                    ${order.medio_pago ? `<span class="ms-3"><i class="fas fa-credit-card me-1"></i>${order.medio_pago}</span>` : ''}
                                    ${order.direccion ? `<span class="ms-3"><i class="fas fa-map-marker-alt me-1"></i>${order.direccion}</span>` : ''}
                                </div>
                                <div class="mb-3">
                                    ${itemsHtml}
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2">
                                    <span class="fs-6 fw-bold">Total:</span>
                                    <span class="fs-5 fw-bold text-success">$${parseFloat(order.total).toLocaleString('es-CO')}</span>
                                </div>
                            </div>
                            <div class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                                <button class="btn btn-sm btn-outline-success fw-bold" onclick="volverAPedirOrden(${order.id})">
                                    <i class="fas fa-redo me-1"></i> Volver a pedir estos productos
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarPedidoUsuario(${order.id})" title="Eliminar de mi historial">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar del historial
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';

                container.innerHTML = html;
            } catch(e) {
                console.error("Error al cargar pedidos:", e);
                container.innerHTML = `
                    <div class="alert alert-danger py-3 text-center">
                        <i class="fas fa-exclamation-triangle me-1"></i> Ocurrió un error al cargar tus pedidos.
                        <br><button class="btn btn-sm btn-outline-danger mt-2" onclick="loadUserOrders()">Reintentar</button>
                    </div>
                `;
            }
        }

        async function eliminarPedidoUsuario(orderId) {
            if (!confirm(`¿Estás seguro de que deseas eliminar el pedido #${orderId} de tu historial?\n\nEsta acción quitará el pedido de tu cuenta.`)) {
                return;
            }

            try {
                const res = await fetch(BASE_URL + '/api/mis_pedidos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId, accion: 'eliminar' })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.msg);
                    loadUserOrders();
                } else {
                    alert("Error: " + (data.error || "No se pudo eliminar el pedido"));
                }
            } catch(e) {
                alert("Error de conexión al eliminar el pedido.");
            }
        }

        async function volverAPedirOrden(orderId) {
            const order = userOrders.find(o => o.id == orderId);
            if (!order || !order.detalles || order.detalles.length === 0) {
                alert("No hay productos disponibles para este pedido.");
                return;
            }

            try {
                const res = await fetch(BASE_URL + '/api/get_productos.php');
                const productos = await res.json();

                let newCart = [];
                let missing = [];

                for (let item of order.detalles) {
                    let p = productos.find(x => x.id == item.producto_id);
                    const qty = parseInt(item.cantidad);

                    if (!p) {
                        missing.push(`- ${item.nombre} (Ya no está en el catálogo)`);
                    } else if (p.stock < qty) {
                        if (p.stock > 0) {
                            missing.push(`- ${item.nombre} (Solo hay ${p.stock} unidades disponibles)`);
                            newCart.push({
                                id: p.id,
                                nombre: p.nombre,
                                marca: p.marca,
                                precio: parseFloat(p.precio),
                                imagen: p.url_imagen,
                                qty: p.stock
                            });
                        } else {
                            missing.push(`- ${item.nombre} (Agotado)`);
                        }
                    } else {
                        newCart.push({
                            id: p.id,
                            nombre: p.nombre,
                            marca: p.marca,
                            precio: parseFloat(p.precio),
                            imagen: p.url_imagen,
                            qty: qty
                        });
                    }
                }

                if (newCart.length > 0) {
                    localStorage.setItem('cart', JSON.stringify(newCart));
                    if (missing.length > 0) {
                        alert("Información sobre tu pedido:\n" + missing.join("\n"));
                    }
                    window.location.href = BASE_URL + '/carrito';
                } else {
                    alert("Lamentablemente, ninguno de los productos de este pedido está disponible en este momento.");
                }
            } catch(e) {
                alert("Error al verificar la disponibilidad de productos.");
            }
        }
    </script>
</body>
</html>
