
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>EcomerKalisto | Supermercado Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>


    <nav class="navbar header">
        <a href="<?= BASE_URL ?>/" class="logo"><i class="fas fa-shopping-basket"></i> Ecomer<span>Kalisto</span></a>
        
        <!-- Buscador -->
        <div class="search-container">
            <input type="text" id="search-input" placeholder="¿Qué estás buscando hoy?" autocomplete="off" oninput="handleSearch()">
            <button class="search-btn"><i class="fas fa-search"></i></button>
            <div id="search-results" class="search-results"></div>
        </div>
        
        <ul class="nav-links" id="main-nav">
            <li><a href="<?= BASE_URL ?>/" class="active">Inicio</a></li>
            <li class="dropdown" id="nav-dropdown">
                <a href="#productos" class="dropbtn" id="dropbtn" onclick="toggleCategoryDropdown(event)">Productos <i class="fas fa-chevron-down" style="font-size:0.8rem;"></i></a>
                <div class="dropdown-content" id="dropdown-content">
                    <a href="#productos" onclick="filterCategory('Aseo Hogar')">Aseo Hogar</a>
                    <a href="#productos" onclick="filterCategory('Aseo Personal')">Aseo Personal</a>
                    <a href="#productos" onclick="filterCategory('Vinos y Licores')">Vinos y Licores</a>
                    <a href="#productos" onclick="filterCategory('Pasabocas')">Pasabocas</a>
                    <a href="#productos" onclick="filterCategory('Canasta Básica')">Canasta Básica</a>
                    <a href="#productos" onclick="filterCategory('Bebidas y Refrescos')">Bebidas y Refrescos</a>
                    <a href="#productos" onclick="filterCategory('Lácteos y Refrigerados')">Lácteos y Refrigerados</a>
                </div>
            </li>
            <li><a href="<?= BASE_URL ?>/horario">Horario</a></li>
            <li><a href="<?= BASE_URL ?>/contacto">Contacto</a></li>
            <li><a href="<?= BASE_URL ?>/perfil" style="color: var(--primary); font-weight: bold;"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/logout">Cerrar Sesión</a></li>
        </ul>

        <div class="nav-right">
            <a href="<?= BASE_URL ?>/carrito" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-total-price">$0.00</span>
                <span class="cart-count" id="cart-count">0</span>
            </a>
            <button class="hamburger-btn mobile-toggle" id="menu-toggle" aria-label="Menú principal" onclick="toggleMenuMobile(event)">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <main>
        <!-- Hero -->
        <!-- Hero Section -->
        <section class="hero">
            <div class="slide-bg-shape"></div>
            <div class="hero-text" data-aos="fade-right">
                <h1 id="hero-title">Cargando...</h1>
                <p id="hero-subtitle"></p>
                <a href="#productos" class="cta-btn">Comprar Ahora <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hero-image" data-aos="fade-left" data-aos-delay="200">
                <img id="hero-img" src="" alt="Supermercado">
            </div>
        </section>

        <!-- Ofertas de la Semana -->
        <section class="products-section" id="ofertas" style="background-color: var(--primary); padding-top: 40px; padding-bottom: 40px;">
            <h2 class="section-title" data-aos="fade-up">🔥 Ofertas de la Semana 🔥</h2>
            <div class="product-grid" id="ofertas-grid" style="padding: 0 5%;">
                <!-- Skeletons iniciales -->
            </div>
        </section>

        <div class="categories" id="category-container" style="margin-top: 20px;" data-aos="fade-up">
            <!-- Se generan dinamicamente -->
        </div>

        <!-- Products -->
        <section class="products-section" id="productos">
            <h2 class="section-title" data-aos="fade-up">Nuestros Productos</h2>
            <div class="product-grid" id="products-grid">
                <!-- Productos aquí -->
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contacto">
        <div class="footer-content">
            <div class="footer-col" data-aos="fade-up">
                <a href="#" class="footer-logo"><i class="fas fa-shopping-basket"></i> Ecomer<span>Kalisto</span></a>
                <p>Tu supermercado online de confianza. Ofrecemos calidad, frescura y rapidez en cada uno de tus pedidos. ¡Hacer mercado nunca fue tan fácil!</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-col" data-aos="fade-up" data-aos-delay="100">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>/">Inicio</a></li>
                    <li><a href="#productos">Productos</a></li>
                    <li><a href="<?= BASE_URL ?>/horario">Horarios de Atención</a></li>
                </ul>
            </div>
            <div class="footer-col" data-aos="fade-up" data-aos-delay="200">
                <h3>Contacto</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt" style="color:var(--primary); width: 25px;"></i> <span id="footer-address">Cl. 14 #4a-84, Ubaté, Cundinamarca</span></li>
                    <li><i class="fas fa-phone" style="color:var(--primary); width: 25px;"></i> <span id="footer-phone">+57 313 253 1923</span></li>
                    <li><i class="fas fa-envelope" style="color:var(--primary); width: 25px;"></i> <span id="footer-email">inversionesmercalisto@gmail.com</span></li>
                </ul>
                <div class="payment-methods">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 EcomerKalisto. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Mobile Cart Button -->
    <a href="<?= BASE_URL ?>/carrito" class="mobile-cart-btn">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="mobile-cart-count">0</span>
    </a>

    <!-- Toast -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle toast-icon"></i>
        <div>
            <h4 style="margin: 0;">¡Añadido!</h4>
            <p style="margin: 0; font-size: 0.9rem; color: #ccc;" id="toast-msg">Producto añadido al carrito.</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Basic functionality script -->
    <script>
        AOS.init();
        
        let allProducts = [];
        let currentFilteredProducts = [];
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let searchDebounceTimer = null;
        
        function updateCartUI() {
            let totalQty = cart.reduce((acc, item) => acc + item.qty, 0);
            let totalPrice = cart.reduce((acc, item) => acc + (item.precio * item.qty), 0);
            document.getElementById('cart-count').innerText = totalQty;
            document.getElementById('mobile-cart-count').innerText = totalQty;
            document.getElementById('cart-total-price').innerText = '$' + totalPrice.toLocaleString('es-CO');
        }

        function addToCart(id, nombre, precio, imagen) {
            let item = cart.find(i => i.id == id);
            if(item) {
                item.qty++;
            } else {
                cart.push({id, nombre, precio, imagen, qty: 1});
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartUI();
            
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').innerText = nombre + " añadido al carrito";
            toast.style.display = 'flex';
            setTimeout(() => toast.style.display = 'none', 3000);
        }

        function createProductCard(p) {
            const safeName = (p.nombre || '').replace(/'/g, "\\'");
            return `
                <div class="product-card">
                    <img src="${p.url_imagen}" alt="${p.nombre}" loading="lazy" decoding="async">
                    <div class="content">
                        <h3>${p.nombre}</h3>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                            <p class="price" style="margin: 0;">$${parseFloat(p.precio).toLocaleString('es-CO')}</p>
                            <button onclick="addToCart(${p.id}, '${safeName}', ${p.precio}, '${p.url_imagen}')" class="add-to-cart-btn" title="Agregar al carrito">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderProducts(products) {
            const grid = document.getElementById('products-grid');
            const ofertasGrid = document.getElementById('ofertas-grid');
            const ofertasSection = document.getElementById('ofertas');
            if (!grid || !ofertasGrid) return;
            
            if (!products || products.length === 0) {
                grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #888; padding: 40px 0; font-size: 1.1rem;"><i class="fas fa-search me-2"></i>No se encontraron productos.</p>';
                if (ofertasSection) ofertasSection.style.display = 'none';
                return;
            }

            let gridCards = [];
            let ofertasCards = [];

            for (let i = 0; i < products.length; i++) {
                const p = products[i];
                const card = createProductCard(p);
                gridCards.push(card);
                
                if (p.es_oferta == 1) {
                    ofertasCards.push(card);
                }
            }
            
            // Inserción única en el DOM para máximo rendimiento (evita reflows en bucle)
            grid.innerHTML = gridCards.join('');
            ofertasGrid.innerHTML = ofertasCards.join('');
            
            if (ofertasSection) {
                ofertasSection.style.display = ofertasCards.length > 0 ? 'block' : 'none';
            }
        }

        function cleanStr(s) {
            return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "").trim();
        }

        function filterCategory(cat) {
            if(!cat) {
                currentFilteredProducts = allProducts;
            } else {
                const target = cleanStr(cat);
                currentFilteredProducts = allProducts.filter(p => cleanStr(p.categoria) === target);
            }
            renderProducts(currentFilteredProducts);
        }

        function handleSearch() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                const searchInput = document.getElementById('search-input');
                const rawQuery = (searchInput ? searchInput.value : '').trim().toLowerCase();
                
                if (!rawQuery) {
                    renderProducts(currentFilteredProducts.length > 0 ? currentFilteredProducts : allProducts);
                    return;
                }
                
                // Normalizar tildes para una búsqueda rápida y flexible
                const query = rawQuery.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
                
                const filtered = allProducts.filter(p => {
                    const name = (p.nombre || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
                    const marca = (p.marca || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
                    const cat = (p.categoria || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
                    return name.includes(query) || marca.includes(query) || cat.includes(query);
                });
                
                renderProducts(filtered);
            }, 120);
        }

        async function loadData() {
            try {
                // Peticiones en paralelo para reducir el tiempo de espera a la mitad
                const [cfgRes, prodRes] = await Promise.all([
                    fetch(BASE_URL + '/api/get_config.php'),
                    fetch(BASE_URL + '/api/get_productos.php')
                ]);
                
                const [config, products] = await Promise.all([
                    cfgRes.json(),
                    prodRes.json()
                ]);
                
                allProducts = Array.isArray(products) ? products : [];
                currentFilteredProducts = allProducts;
                
                document.getElementById('hero-title').innerHTML = config.banner_title || "Todo lo que necesitas, <span>a un clic.</span>";
                const defaultSubtitle = "Descubre la forma más rápida y segura de hacer mercado. Productos frescos, precios increíbles y entrega directamente a tu puerta.";
                let sub = config.banner_subtitle || defaultSubtitle;
                if (sub.toLowerCase().includes("alert(") || sub.trim() === "") {
                    sub = defaultSubtitle;
                }
                document.getElementById('hero-subtitle').innerText = sub;
                document.getElementById('hero-img').src = config.banner_image || (BASE_URL + "/public/img/imagen_entradacora.jpeg");
                
                document.getElementById('footer-address').innerText = config.contacto_direccion || "Cl. 14 #4a-84, Ubaté, Cundinamarca";
                document.getElementById('footer-phone').innerText = config.contacto_telefono || "+57 313 253 1923";
                document.getElementById('footer-email').innerText = config.contacto_email || "inversionesmercalisto@gmail.com";
                
                renderProducts(allProducts);
                
                // Actualizar el carrito con la información más reciente del servidor (precios, nombres, imágenes)
                cart.forEach(item => {
                    const serverProduct = allProducts.find(p => p.id == item.id);
                    if (serverProduct) {
                        item.precio = serverProduct.precio;
                        item.nombre = serverProduct.nombre;
                        item.imagen = serverProduct.url_imagen;
                    }
                });
                localStorage.setItem('cart', JSON.stringify(cart));
                
                updateCartUI();
            } catch(e) {
                console.error(e);
                document.getElementById('hero-title').innerText = "Error cargando tienda";
            }
        }
        
        function toggleMenuMobile(e) {
            if (e) e.stopPropagation();
            const nav = document.getElementById('main-nav');
            const btn = document.getElementById('menu-toggle');
            if (nav) {
                nav.classList.toggle('active');
                if (btn) {
                    const icon = btn.querySelector('i');
                    if (icon) {
                        if (nav.classList.contains('active')) {
                            icon.classList.remove('fa-bars');
                            icon.classList.add('fa-times');
                        } else {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            }
        }

        function toggleCategoryDropdown(e) {
            if (window.innerWidth <= 991) {
                e.preventDefault();
                e.stopPropagation();
                const navDropdown = document.getElementById('nav-dropdown');
                if (navDropdown) {
                    navDropdown.classList.toggle('open');
                }
            }
        }

        document.addEventListener('click', function(e) {
            const nav = document.getElementById('main-nav');
            const btn = document.getElementById('menu-toggle');
            if (nav && nav.classList.contains('active')) {
                if (!nav.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    nav.classList.remove('active');
                    if (btn) {
                        const icon = btn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            loadData();
            const nav = document.getElementById('main-nav');
            const btn = document.getElementById('menu-toggle');
            if (nav) {
                nav.querySelectorAll('a:not(.dropbtn)').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 991) {
                            nav.classList.remove('active');
                            if (btn) {
                                const icon = btn.querySelector('i');
                                if (icon) {
                                    icon.classList.remove('fa-times');
                                    icon.classList.add('fa-bars');
                                }
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
