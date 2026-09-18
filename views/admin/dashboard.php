<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - EcoMerkaListo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
        :root {
            --primary-green: #2ecc71;
            --secondary-green: #27ae60;
            --accent-yellow: #f1c40f;
            --text-dark: #2c3e50;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background-color: var(--accent-yellow);
            color: var(--text-dark);
            font-weight: 600;
            border: none;
        }
        .btn-custom:hover {
            background-color: #f39c12;
            color: white;
        }
        .table-custom th {
            background-color: var(--text-dark);
            color: white;
        }
        .pos-cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="fas fa-cogs"></i> Panel de Administración</h1>
                <p class="mb-0 mt-2 opacity-75">Gestión integral de EcoMerkaListo</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="true"><i class="fas fa-boxes"></i> Inventario</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="pos-tab" data-bs-toggle="tab" data-bs-target="#pos" type="button" role="tab" aria-controls="pos" aria-selected="false"><i class="fas fa-cash-register"></i> Cajero (POS)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false" onclick="loadStats()"><i class="fas fa-chart-line"></i> Estadísticas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false" onclick="loadOrders()"><i class="fas fa-shipping-fast"></i> Pedidos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="false" onclick="loadConfig()"><i class="fas fa-cog"></i> Configuración</button>
            </li>
        </ul>

        <div class="tab-content pt-4" id="adminTabsContent">
            <!-- Pestaña de INVENTARIO -->
            <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                <!-- Actions Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
                            <i class="fas fa-plus-circle"></i> Nuevo Producto
                        </button>
                        <button class="btn btn-secondary ms-2" id="btnActualizarInventario" onclick="loadProducts()" title="Actualizar inventario">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Buscar producto..." onkeyup="filterTable()">
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card shadow-sm">
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-custom mb-0" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Oferta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pestaña de CAJERO (POS) -->
            <div class="tab-pane fade" id="pos" role="tabpanel" aria-labelledby="pos-tab">
                <div class="row">
                    <!-- Productos Buscador POS -->
                    <div class="col-md-7">
                        <div class="card shadow-sm">
                            <div class="card-header bg-dark text-white fw-bold">Añadir al carrito</div>
                            <div class="card-body">
                                <input type="text" id="posSearch" class="form-control mb-3" placeholder="Buscar producto para vender..." onkeyup="filterPOS()">
                                <div class="row g-2" id="posProductsList" style="max-height: 500px; overflow-y: auto;">
                                    <!-- Se llena con JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carrito POS -->
                    <div class="col-md-5">
                        <div class="card shadow-sm border-success">
                            <div class="card-header bg-success text-white fw-bold"><i class="fas fa-shopping-cart"></i> Cuenta Actual</div>
                            <div class="card-body p-0">
                                <div id="posCart" style="max-height: 350px; overflow-y: auto;">
                                    <div class="p-3 text-center text-muted">Carrito vacío</div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <h3 class="d-flex justify-content-between text-dark fw-bold">Total: <span id="posTotal">$0</span></h3>
                                <button class="btn btn-success w-100 fw-bold py-2 mt-2" id="btnProcessPOS" onclick="processPOS()"><i class="fas fa-check-circle"></i> Procesar Venta</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de ESTADÍSTICAS -->
            <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">

                <!-- Tarjetas de resumen -->
                <div class="row g-3 mb-4" id="statsResumenRow">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center p-3">
                            <div class="text-warning fs-3 fw-bold" id="statPendientes">—</div>
                            <div class="text-muted small">Pedidos Pendientes</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center p-3">
                            <div class="text-success fs-3 fw-bold" id="statCompletados">—</div>
                            <div class="text-muted small">Completados</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center p-3">
                            <div class="text-danger fs-3 fw-bold" id="statAgotados">—</div>
                            <div class="text-muted small">Productos Agotados</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center p-3">
                            <div class="text-primary fs-3 fw-bold" id="statIngresos">—</div>
                            <div class="text-muted small">Ingresos (Completados)</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-primary mb-4">
                            <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-trophy"></i> Productos Más Vendidos</div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush" id="statsTopVendidos">
                                    <li class="list-group-item text-center text-muted py-4">Cargando...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-danger mb-4">
                            <div class="card-header bg-danger text-white fw-bold"><i class="fas fa-exclamation-triangle"></i> Productos con Bajo Stock (&lt;10)</div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush" id="statsBajoStock">
                                    <li class="list-group-item text-center text-muted py-4">Cargando...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Pestaña de CONFIGURACIÓN -->
            <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-desktop"></i> Personalización del Sitio</div>
                    <div class="card-body">
                        <form id="configForm" onsubmit="saveConfig(event)">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h5 class="border-bottom pb-2">Página Principal</h5>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Texto Principal (Título)</label>
                                    <input type="text" class="form-control" id="cfg_banner_title" placeholder="Todo lo que necesitas, &lt;span&gt;a un clic.&lt;/span&gt;" required>
                                    <small class="text-muted">Usa &lt;span&gt; para resaltar en verde.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen Principal (URL o archivo)</label>
                                    <input type="text" class="form-control" id="cfg_banner_image" placeholder="public/img/imagen_entradacora.jpeg" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Texto Descriptivo</label>
                                    <textarea class="form-control" id="cfg_banner_subtitle" rows="2" required></textarea>
                                </div>
                                
                                <div class="col-md-12 mt-4">
                                    <h5 class="border-bottom pb-2">Contacto y Horarios</h5>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Horario de Atención</label>
                                    <input type="text" class="form-control" id="cfg_horario" placeholder="9:00 AM — 9:00 PM" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="cfg_contacto_telefono" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="cfg_contacto_direccion" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Correo Electrónico</label>
                                    <input type="text" class="form-control" id="cfg_contacto_email" required>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save"></i> Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pestaña de PEDIDOS -->
            <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list-alt"></i> Pedidos Recientes</span>
                        <button class="btn btn-sm btn-light text-info fw-bold" onclick="loadOrders()"><i class="fas fa-sync-alt"></i> Actualizar</button>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <tr><td colspan="7" class="text-center text-muted py-4">Cargando pedidos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Formulario Producto -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="productForm" onsubmit="saveProduct(event)">
              <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitle">Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="prod_id">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="prod_nombre" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" class="form-control" id="prod_marca" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="prod_categoria" required>
                            <option value="">Seleccione...</option>
                            <option value="Aseo Hogar">Aseo Hogar</option>
                            <option value="Aseo Personal">Aseo Personal</option>
                            <option value="Vinos y Licores">Vinos y Licores</option>
                            <option value="Pasabocas">Pasabocas</option>
                            <option value="Canasta Básica">Canasta Básica</option>
                            <option value="Bebidas y Refrescos">Bebidas y Refrescos</option>
                            <option value="Lácteos y Refrigerados">Lácteos y Refrigerados</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" class="form-control" id="prod_precio" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" id="prod_stock" required min="0">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Contenido (Ej: 500 ml)</label>
                        <input type="text" class="form-control" id="prod_contenido" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">URL de la Imagen</label>
                        <input type="text" class="form-control" id="prod_url" required placeholder="public/img/nombre.jpg">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="prod_desc" rows="2"></textarea>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success fw-bold">Guardar Producto</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Detalles de Pedido -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-receipt"></i> Detalles del Pedido #<span id="detailOrderId"></span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
             <div class="table-responsive">
                 <table class="table table-hover mb-0">
                     <thead class="table-dark">
                         <tr>
                             <th>Producto</th>
                             <th>Precio Unit.</th>
                             <th>Cant. Comprada</th>
                             <th>Ya Devuelto</th>
                             <th>Pendiente</th>
                             <th>Subtotal</th>
                             <th>Devolver</th>
                         </tr>
                     </thead>
                     <tbody id="orderDetailBody">
                         <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                     </tbody>
                 </table>
             </div>
          </div>
          <div class="modal-footer bg-light d-flex justify-content-between">
              <h4 class="mb-0 text-dark fw-bold">Total: $<span id="detailOrderTotal">0</span></h4>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allProductsData = [];
        let posCartData = [];

        // --- INVENTARIO ---

        async function loadProducts() {
            const refreshBtn = document.getElementById('btnActualizarInventario');
            const icon = refreshBtn ? refreshBtn.querySelector('i') : null;
            if (icon) icon.classList.add('fa-spin');

            try {
                const response = await fetch(BASE_URL + '/api/get_productos.php?t=' + Date.now(), {
                    cache: 'no-store'
                });
                allProductsData = await response.json();
                
                const searchInput = document.getElementById('searchInput');
                const term = (searchInput ? searchInput.value : '').trim().toLowerCase();
                if (term) {
                    const filtered = allProductsData.filter(p => 
                        (p.nombre && p.nombre.toLowerCase().includes(term)) || 
                        (p.marca && p.marca.toLowerCase().includes(term)) ||
                        (p.categoria && p.categoria.toLowerCase().includes(term))
                    );
                    renderTable(filtered);
                } else {
                    renderTable(allProductsData);
                }

                if (typeof renderPOSProducts === 'function') {
                    const posSearch = document.getElementById('posSearch');
                    const posTerm = (posSearch ? posSearch.value : '').trim().toLowerCase();
                    if (posTerm) {
                        const posFiltered = allProductsData.filter(p => 
                            (p.nombre && p.nombre.toLowerCase().includes(posTerm)) || 
                            (p.marca && p.marca.toLowerCase().includes(posTerm))
                        );
                        renderPOSProducts(posFiltered);
                    } else {
                        renderPOSProducts(allProductsData);
                    }
                }
            } catch (e) {
                console.error("Error al actualizar productos:", e);
            } finally {
                if (icon) {
                    setTimeout(() => icon.classList.remove('fa-spin'), 350);
                }
            }
        }
        
        let filterTableTimer = null;
        function filterTable() {
            clearTimeout(filterTableTimer);
            filterTableTimer = setTimeout(() => {
                const term = document.getElementById('searchInput').value.toLowerCase();
                const filtered = allProductsData.filter(p => p.nombre.toLowerCase().includes(term) || (p.marca && p.marca.toLowerCase().includes(term)));
                renderTable(filtered);
            }, 120);
        }

        function renderTable(data) {
            const tbody = document.getElementById('tableBody');
            if (!tbody) return;
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No hay productos.</td></tr>`;
                return;
            }
            
            const rows = data.map(product => {
                const isOferta = parseInt(product.es_oferta) === 1;
                return `
                    <tr>
                        <td>${product.id}</td>
                        <td><img src="${product.url_imagen}" width="40" height="40" style="object-fit:cover; border-radius:5px;" loading="lazy" decoding="async" onerror="this.src=''"></td>
                        <td><strong>${product.nombre}</strong><br><small class="text-muted">${product.marca || ''}</small></td>
                        <td><span class="badge bg-secondary">${product.categoria}</span></td>
                        <td>$${parseFloat(product.precio).toLocaleString('es-CO')}</td>
                        <td>
                            <span class="badge bg-${product.stock > 10 ? 'success' : 'danger'}">${product.stock}</span>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" role="switch" id="oferta_${product.id}" ${isOferta ? 'checked' : ''} onchange="toggleOferta(${product.id}, this.checked)">
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openEditModal(${product.id})" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = rows.join('');
        }

        async function toggleOferta(id, state) {
            try {
                const res = await fetch(BASE_URL + '/api/toggle_oferta.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id, es_oferta: state ? 1 : 0})
                });
                const data = await res.json();
                if(data.success) {
                    const p = allProductsData.find(x => x.id == id);
                    if(p) p.es_oferta = state ? 1 : 0;
                } else {
                    alert(data.error);
                    loadProducts();
                }
            } catch(e) {
                console.error(e);
                loadProducts();
            }
        }

        function openAddModal() {
            document.getElementById('productForm').reset();
            document.getElementById('prod_id').value = '';
            document.getElementById('modalTitle').innerText = 'Nuevo Producto';
        }

        function openEditModal(id) {
            const p = allProductsData.find(x => x.id == id);
            if(!p) return;
            
            document.getElementById('prod_id').value = p.id;
            document.getElementById('prod_nombre').value = p.nombre;
            document.getElementById('prod_marca').value = p.marca;
            document.getElementById('prod_categoria').value = p.categoria;
            document.getElementById('prod_precio').value = p.precio;
            document.getElementById('prod_stock').value = p.stock;
            document.getElementById('prod_contenido').value = p.contenido;
            document.getElementById('prod_url').value = p.url_imagen;
            document.getElementById('prod_desc').value = p.descripcion || '';
            
            document.getElementById('modalTitle').innerText = 'Editar Producto';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        }

        async function saveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('prod_id').value;
            
            const payload = {
                nombre: document.getElementById('prod_nombre').value,
                marca: document.getElementById('prod_marca').value,
                categoria: document.getElementById('prod_categoria').value,
                precio: document.getElementById('prod_precio').value,
                stock: document.getElementById('prod_stock').value,
                contenido: document.getElementById('prod_contenido').value,
                url_imagen: document.getElementById('prod_url').value,
                descripcion: document.getElementById('prod_desc').value
            };

            let method = 'POST';
            if (id) {
                payload.id = id;
                method = 'PUT';
            }

            try {
                const res = await fetch(BASE_URL + '/api/admin_crud.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if(data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                    loadProducts();
                } else {
                    alert("Error: " + data.error);
                }
            } catch(ex) {
                alert("Ocurrió un error.");
            }
        }

        async function deleteProduct(id) {
            if(!confirm("¿Seguro que deseas eliminar este producto?")) return;
            try {
                const res = await fetch(BASE_URL + '/api/admin_crud.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });
                const data = await res.json();
                if(data.success) loadProducts();
                else alert(data.error);
            } catch(e) {
                alert("Error eliminando producto");
            }
        }


        // --- POS CAJERO ---

        let filterPOSTimer = null;
        function filterPOS() {
            clearTimeout(filterPOSTimer);
            filterPOSTimer = setTimeout(() => {
                const term = document.getElementById('posSearch').value.toLowerCase();
                const filtered = allProductsData.filter(p => p.nombre.toLowerCase().includes(term) || (p.marca && p.marca.toLowerCase().includes(term)));
                renderPOSProducts(filtered);
            }, 120);
        }

        function renderPOSProducts(data) {
            const container = document.getElementById('posProductsList');
            if (!container) return;
            
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="col-12 text-center text-muted py-4">No se encontraron productos.</div>';
                return;
            }
            
            const cards = data.map(p => `
                <div class="col-6 col-md-4">
                    <div class="card h-100 shadow-sm border-0" style="cursor:pointer;" onclick="addToPOS(${p.id})">
                        <img src="${p.url_imagen}" class="card-img-top" style="height:100px; object-fit:contain; padding:10px;" loading="lazy" decoding="async" onerror="this.src=''">
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title mb-1 text-truncate" style="font-size:0.9rem;">${p.nombre}</h6>
                            <p class="text-success fw-bold mb-0">$${parseFloat(p.precio).toLocaleString('es-CO')}</p>
                        </div>
                    </div>
                </div>
            `);
            
            container.innerHTML = cards.join('');
        }

        function addToPOS(id) {
            const product = allProductsData.find(x => x.id == id);
            if(!product) return;
            
            const existing = posCartData.find(x => x.id == id);
            if (existing) {
                if(existing.qty >= product.stock) {
                    alert("No hay más stock disponible de este producto.");
                    return;
                }
                existing.qty++;
            } else {
                if(product.stock < 1) {
                    alert("Producto agotado.");
                    return;
                }
                posCartData.push({ ...product, qty: 1 });
            }
            renderPOSCart();
        }

        function removeFromPOS(id) {
            posCartData = posCartData.filter(x => x.id != id);
            renderPOSCart();
        }

        function changePOSQty(id, diff) {
            const item = posCartData.find(x => x.id == id);
            const product = allProductsData.find(x => x.id == id);
            if(!item || !product) return;
            
            const newQty = item.qty + diff;
            if(newQty < 1) {
                removeFromPOS(id);
                return;
            }
            if(newQty > product.stock) {
                alert("No hay más stock disponible.");
                return;
            }
            item.qty = newQty;
            renderPOSCart();
        }

        function renderPOSCart() {
            const container = document.getElementById('posCart');
            if(posCartData.length === 0) {
                container.innerHTML = `<div class="p-3 text-center text-muted">Carrito vacío</div>`;
                document.getElementById('posTotal').innerText = "$0";
                return;
            }
            
            let html = '';
            let total = 0;
            posCartData.forEach(item => {
                const sub = item.precio * item.qty;
                total += sub;
                html += `
                    <div class="pos-cart-item">
                        <div style="flex:1;">
                            <h6 class="mb-0 text-truncate" style="max-width:150px;">${item.nombre}</h6>
                            <small class="text-muted">$${parseFloat(item.precio).toLocaleString('es-CO')}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="changePOSQty(${item.id}, -1)">-</button>
                            <span class="mx-2 fw-bold">${item.qty}</span>
                            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="changePOSQty(${item.id}, 1)">+</button>
                        </div>
                        <div class="ms-3 fw-bold text-end" style="min-width:70px;">
                            $${sub.toLocaleString('es-CO')}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
            document.getElementById('posTotal').innerText = "$" + total.toLocaleString('es-CO');
        }

        let isProcessingPOS = false;

        async function processPOS() {
            if (isProcessingPOS) return;

            if (posCartData.length === 0) {
                alert("El carrito está vacío.");
                return;
            }
            
            if (!confirm("¿Procesar venta en caja? Esto descontará el inventario inmediatamente.")) return;

            const btnProcess = document.getElementById('btnProcessPOS');
            isProcessingPOS = true;
            if (btnProcess) {
                btnProcess.disabled = true;
                btnProcess.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }

            try {
                const res = await fetch(BASE_URL + '/api/pos_checkout.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ cart: posCartData })
                });
                const data = await res.json();
                
                if (data.success) {
                    alert("Venta procesada con éxito. El inventario ha sido actualizado.");
                    posCartData = [];
                    renderPOSCart();
                    await loadProducts(); // Refrescar inventario y cajero inmediatamente
                    if (typeof loadStats === 'function') {
                        loadStats();
                    }
                    if (typeof loadOrders === 'function') {
                        loadOrders();
                    }
                } else {
                    alert("Error al procesar la venta: " + (data.error || "Error desconocido"));
                }
            } catch (e) {
                console.error("Error en processPOS:", e);
                alert("Ocurrió un error al procesar la venta.");
            } finally {
                isProcessingPOS = false;
                if (btnProcess) {
                    btnProcess.disabled = false;
                    btnProcess.innerHTML = '<i class="fas fa-check-circle"></i> Procesar Venta';
                }
            }
        }


        // --- ESTADÍSTICAS ---

        async function loadStats() {
            try {
                const res = await fetch(BASE_URL + '/api/dashboard_stats.php?t=' + Date.now());
                const data = await res.json();

                if (data.error) {
                    console.error('Stats error:', data.error);
                    return;
                }

                // ── Resumen general ──
                if (data.resumen) {
                    const r = data.resumen;
                    document.getElementById('statPendientes').innerText  = r.pendientes  ?? '0';
                    document.getElementById('statCompletados').innerText = r.completados ?? '0';
                    document.getElementById('statAgotados').innerText    = r.agotados    ?? '0';
                    document.getElementById('statIngresos').innerText    = '$' + parseFloat(r.ingresos_totales || 0).toLocaleString('es-CO');
                }

                // ── Top vendidos ──
                const topCont = document.getElementById('statsTopVendidos');
                if (data.top_vendidos && data.top_vendidos.length > 0) {
                    topCont.innerHTML = '';
                    data.top_vendidos.forEach((p, idx) => {
                        const badgeColor = idx === 0 ? 'bg-warning text-dark' : idx === 1 ? 'bg-secondary' : 'bg-primary';
                        topCont.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge ${badgeColor} rounded-pill">#${idx+1}</span>
                                    <img src="${p.imagen}" width="30" height="30" style="object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                                    <span>${p.nombre} <small class="text-muted">${p.marca}</small></span>
                                </div>
                                <span class="badge bg-success rounded-pill">${p.vendidos} und.</span>
                            </li>
                        `;
                    });
                } else {
                    topCont.innerHTML = '<li class="list-group-item text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No hay ventas registradas aún.</li>';
                }

                // ── Bajo stock ──
                const stockCont = document.getElementById('statsBajoStock');
                if (data.poco_stock && data.poco_stock.length > 0) {
                    stockCont.innerHTML = '';
                    data.poco_stock.forEach(p => {
                        const color = p.stock === 0 ? 'bg-dark' : p.stock <= 3 ? 'bg-danger' : 'bg-warning text-dark';
                        const label = p.stock === 0 ? 'AGOTADO' : `Stock: ${p.stock}`;
                        stockCont.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="${p.imagen}" width="30" height="30" style="object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                                    <span>${p.nombre} <small class="text-muted">${p.marca}</small></span>
                                </div>
                                <span class="badge ${color} rounded-pill">${label}</span>
                            </li>
                        `;
                    });
                } else {
                    stockCont.innerHTML = '<li class="list-group-item text-center text-success py-3"><i class="fas fa-check-circle me-1"></i>¡Todo el inventario está sano!</li>';
                }

            } catch(e) {
                console.error("Error al cargar estadísticas", e);
            }
        }

        
        // Init
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            // loadOrders loaded via onclick
        });

        // --- CONFIGURACION ---
        async function loadConfig() {
            try {
                const res = await fetch(BASE_URL + '/api/get_config.php?t=' + Date.now());
                const data = await res.json();
                
                document.getElementById('cfg_banner_title').value = data.banner_title || '';
                document.getElementById('cfg_banner_subtitle').value = data.banner_subtitle || '';
                document.getElementById('cfg_banner_image').value = data.banner_image || '';
                document.getElementById('cfg_horario').value = data.horario || '9:00 AM — 9:00 PM';
                document.getElementById('cfg_contacto_telefono').value = data.contacto_telefono || '+57 313 253 1923';
                document.getElementById('cfg_contacto_direccion').value = data.contacto_direccion || 'Cl. 14 #4a-84, Ubaté, Cundinamarca';
                document.getElementById('cfg_contacto_email').value = data.contacto_email || 'inversionesmercalisto@gmail.com';
            } catch(e) {
                console.error("Error loading config", e);
            }
        }

        async function saveConfig(e) {
            e.preventDefault();
            const payload = {
                banner_title: document.getElementById('cfg_banner_title').value,
                banner_subtitle: document.getElementById('cfg_banner_subtitle').value,
                banner_image: document.getElementById('cfg_banner_image').value,
                horario: document.getElementById('cfg_horario').value,
                contacto_telefono: document.getElementById('cfg_contacto_telefono').value,
                contacto_direccion: document.getElementById('cfg_contacto_direccion').value,
                contacto_email: document.getElementById('cfg_contacto_email').value
            };
            try {
                const res = await fetch(BASE_URL + '/api/save_config.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if(data.success) {
                    alert(data.msg);
                } else {
                    alert("Error: " + data.error);
                }
            } catch(e) {
                alert("Ocurrió un error.");
            }
        }

        // --- PEDIDOS ---

        function estadoBadge(estado) {
            const map = {
                'Pendiente':  'bg-warning text-dark',
                'Completado': 'bg-success',
                'Cancelado':  'bg-secondary',
                'Devuelto':   'bg-danger'
            };
            return `<span class="badge ${map[estado] || 'bg-secondary'}">${estado}</span>`;
        }

        async function loadOrders() {
            try {
                const res = await fetch(BASE_URL + '/api/admin_pedidos.php');
                const data = await res.json();
                
                const tbody = document.getElementById('ordersTableBody');
                if(!data || data.error || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay pedidos registrados.</td></tr>';
                    return;
                }
                
                let html = '';
                data.forEach(o => {
                    const isPendiente = o.estado === 'Pendiente';
                    html += `
                        <tr>
                            <td class="fw-bold">#${o.id}</td>
                            <td><small>${new Date(o.fecha).toLocaleString('es-CO')}</small></td>
                            <td><strong>${o.nombre_cliente}</strong><br><small class="text-muted">${o.direccion || '—'}</small></td>
                            <td>${o.telefono}<br><small class="text-muted">${o.correo}</small></td>
                            <td class="fw-bold text-success">$${parseFloat(o.total).toLocaleString('es-CO')}</td>
                            <td>${estadoBadge(o.estado)}</td>
                            <td>
                                <button class="btn btn-sm btn-info text-white mb-1" onclick="viewOrderDetails(${o.id}, ${o.total})" title="Ver productos y devolver"><i class="fas fa-eye"></i></button>
                                ${isPendiente ? `<button class="btn btn-sm btn-success mb-1" onclick="actionOrder(${o.id}, 'completar')" title="Marcar Completado"><i class="fas fa-check"></i></button>` : ''}
                                ${isPendiente ? `<button class="btn btn-sm btn-danger mb-1" onclick="actionOrder(${o.id}, 'cancelar')" title="Cancelar y restaurar stock"><i class="fas fa-times"></i></button>` : ''}
                                <button class="btn btn-sm btn-outline-danger mb-1" onclick="deleteOrder(${o.id})" title="Eliminar pedido"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } catch(e) {
                console.error("Error cargando pedidos", e);
            }
        }

        async function actionOrder(id, actionStr) {
            const msg = actionStr === 'cancelar' 
                ? "¿Seguro que deseas CANCELAR este pedido? Los productos volverán al stock automáticamente."
                : "¿Seguro que deseas marcar este pedido como COMPLETADO?";
                
            if(!confirm(msg)) return;
            
            try {
                const res = await fetch(BASE_URL + '/api/admin_pedidos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id, accion: actionStr})
                });
                const data = await res.json();
                if(data.success) {
                    alert(data.msg);
                    loadOrders();
                    loadProducts(); // Actualizar stock si fue cancelado
                } else {
                    alert("Error: " + data.error);
                }
            } catch(e) {
                alert("Error al procesar acción");
            }
        }

        async function deleteOrder(id) {
            if(!confirm(`¿Seguro que deseas ELIMINAR permanentemente el Pedido #${id}?\n\nEsta acción quitará el pedido del sistema sin afectar el inventario de productos.`)) return;

            try {
                const res = await fetch(BASE_URL + '/api/admin_pedidos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id, accion: 'eliminar'})
                });
                const data = await res.json();
                if(data.success) {
                    alert(data.msg);
                    loadOrders();
                    loadStats();
                } else {
                    alert("Error: " + data.error);
                }
            } catch(e) {
                alert("Error al eliminar el pedido");
            }
        }

        let currentViewOrderId = null;
        
        async function viewOrderDetails(id, total) {
            currentViewOrderId = id;
            document.getElementById('detailOrderId').innerText = id;
            document.getElementById('detailOrderTotal').innerText = parseFloat(total).toLocaleString('es-CO');
            document.getElementById('orderDetailBody').innerHTML = '<tr><td colspan="5" class="text-center">Cargando...</td></tr>';
            
            new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
            
            await fetchOrderDetails(id);
        }
        
        async function fetchOrderDetails(orderId) {
            try {
                const res = await fetch(BASE_URL + '/api/get_pedido_detalles.php?id=' + orderId + '&t=' + Date.now());
                const data = await res.json();
                
                const tbody = document.getElementById('orderDetailBody');
                if(!data || data.error || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay detalles en este pedido.</td></tr>';
                    return;
                }
                
                let html = '';
                data.forEach(d => {
                    const subtotal = d.cantidad_pendiente * d.precio_unitario;
                    const todoDev  = parseInt(d.cantidad_pendiente) === 0;
                    html += `
                        <tr class="${todoDev ? 'table-secondary text-muted' : ''}">
                            <td><strong>${d.nombre}</strong><br><small class="text-muted">${d.marca}</small></td>
                            <td>$${parseFloat(d.precio_unitario).toLocaleString('es-CO')}</td>
                            <td class="fw-bold">${d.cantidad}</td>
                            <td class="text-danger fw-bold">${d.cantidad_devuelta}</td>
                            <td class="fw-bold ${todoDev ? 'text-muted' : 'text-success'}">${d.cantidad_pendiente}</td>
                            <td>$${parseFloat(subtotal).toLocaleString('es-CO')}</td>
                            <td>
                                ${!todoDev ? `
                                <div class="input-group input-group-sm" style="width:160px;">
                                    <input type="number" class="form-control form-control-sm" id="devQty_${d.id}" min="1" max="${d.cantidad_pendiente}" value="1">
                                    <button class="btn btn-sm btn-warning fw-bold" onclick="devolverDetalle(${d.id}, ${orderId})" title="Devolver cantidad">
                                        <i class="fas fa-undo"></i> Dev.
                                    </button>
                                </div>` : '<span class="badge bg-secondary">Devuelto</span>'}
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } catch(e) {
                console.error('Error cargando detalles:', e);
            }
        }

        async function devolverDetalle(detalleId, pedidoId) {
            const inputEl = document.getElementById('devQty_' + detalleId);
            const cant = parseInt(inputEl ? inputEl.value : 1);
            if (!cant || cant < 1) { alert('Ingresa una cantidad válida'); return; }
            if (!confirm(`¿Devolver ${cant} unidad(es) al inventario? Esta acción no se puede deshacer.`)) return;

            try {
                const res = await fetch(BASE_URL + '/api/admin_pedidos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: pedidoId, accion: 'devolver', detalle_id: detalleId, cantidad_devolver: cant })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.msg);
                    fetchOrderDetails(pedidoId);
                    loadOrders();
                    loadProducts();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch(e) {
                alert('Error procesando devolución');
            }
        }
    </script>
</body>
</html>
