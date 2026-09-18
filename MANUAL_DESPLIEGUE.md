# Manual Técnico de Despliegue y Configuración - Ecomerkalisto

## 1. Introducción
Este documento detalla los procedimientos técnicos y estándares necesarios para la instalación, configuración y puesta en marcha del proyecto **Ecomerkalisto**. La aplicación está desarrollada bajo una arquitectura de software MVC nativa, empleando PHP, acceso seguro a la base de datos y enrutamiento centralizado.

## 2. Pila Tecnológica (Stack)
* **Lenguaje Backend:** PHP 8.x nativo.
* **Arquitectura:** Patrón MVC (Modelo-Vista-Controlador) implementando un Enrutador centralizado (Front Controller), capa de seguridad estructurada y abstracción de datos.
* **Capa de Datos:** PHP Data Objects (PDO).
* **Motor de Base de Datos:** Relacional MySQL / MariaDB (Motor InnoDB con estructura en Tercera Forma Normal - 3NF).
* **Servidor Web:** Apache HTTP Server con directivas de reescritura de URL (`mod_rewrite`).

## 3. Requisitos Previos del Sistema
Para garantizar la operatividad e integridad del aplicativo, el entorno de despliegue debe satisfacer las siguientes especificaciones:
* **Servidor Web:** Apache 2.4 o superior.
* **Intérprete:** PHP 8.0 o superior.
* **Extensiones PHP Obligatorias:**
  * `pdo_mysql` (Para conexiones seguras a la capa de datos).
  * `mbstring` (Gestión de codificación de caracteres y UTF-8).
  * `openssl` (Soporte criptográfico y conexiones seguras).
* **Base de Datos:** MySQL 5.7+ o MariaDB 10.2+.

---

## 4. Configuración del Entorno Local (XAMPP / LAMP)

### 4.1. Clonación del Repositorio
Ubíquese en el directorio público raíz de su servidor web (por ejemplo, `htdocs` en XAMPP o `/var/www/html` en entornos Linux) y clone el proyecto:

```bash
# Navegar al directorio del servidor
cd c:/xampp/htdocs/

# Clonar el código fuente
git clone https://github.com/usuario/ecomerkalisto.git
cd ecomerkalisto
```

### 4.2. Base de Datos Local
1. Inicie los servicios correspondientes de Apache y MySQL.
2. Ingrese a la consola de MySQL o utilice una interfaz de administración (ej. phpMyAdmin):
   ```sql
   CREATE DATABASE ecomerkalisto_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importe el esquema estructural relacional ejecutando el script `.sql` principal del proyecto, asegurándose de que el esquema preserve la configuración del motor InnoDB y las restricciones de llaves foráneas.

### 4.3. Parámetros de Entorno y Conexión
Ajuste los parámetros de conexión hacia la base de datos para su entorno local editando el archivo `config/Database.php` (o las variables de entorno asociadas):

```php
<?php
// config/Database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'ecomerkalisto_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Establezca la contraseña según su configuración
define('DB_CHARSET', 'utf8mb4');
```

---

## 5. Configuración de Apache y Enrutamiento

### 5.1. Habilitación de `mod_rewrite`
Es indispensable habilitar el módulo de reescritura para el funcionamiento del Router centralizado.
* **Entornos XAMPP (Windows):** Verifique en el archivo `httpd.conf` que la línea `LoadModule rewrite_module modules/mod_rewrite.so` no se encuentre comentada.
* **Entornos LAMP (Linux):** Ejecute desde la consola:
  ```bash
  sudo a2enmod rewrite
  sudo systemctl restart apache2
  ```

### 5.2. Directivas Estructurales en `.htaccess`
Dentro del directorio raíz de su proyecto se requiere el siguiente archivo `.htaccess`. Éste centraliza el tráfico y protege los activos y archivos sensibles de configuración.

```apache
# /.htaccess

<IfModule mod_rewrite.c>
    RewriteEngine On
    # Modifique el RewriteBase según la carpeta de despliegue si es necesario
    RewriteBase /ecomerkalisto/

    # Redirigir el tráfico hacia el Front Controller (Router centralizado)
    # Ignorar archivos y directorios físicos existentes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>

# Bloqueo estricto de acceso a archivos sensibles y de configuración
<FilesMatch "^\.env|config\.json|\.htaccess|\.git|Database\.php$">
    Require all denied
</FilesMatch>

# Prevención del listado de directorios
Options -Indexes
```

---

## 6. Paso a Producción (Hosting Apache / InfinityFree)

### 6.1. Subida y Despliegue de Archivos
1. A través de un cliente FTP (ej. FileZilla) o desde el Administrador de Archivos web del hosting, transfiera todo el contenido de su proyecto.
2. Asegúrese de alojar los archivos dentro de la carpeta pública de internet, comúnmente nombrada `htdocs` o `public_html`.

### 6.2. Aprovisionamiento de Base de Datos en Producción
1. Acceda al panel de control (cPanel, VistaPanel, etc.).
2. Diríjase al módulo de Bases de Datos MySQL. Proceda a crear la base de datos de producción y asigne un usuario con todos los privilegios (DML y DDL).
3. Ingrese al `phpMyAdmin` proporcionado por el proveedor e importe su script estructurado de la base de datos local.
4. Modifique el archivo `config/Database.php` reemplazando los valores `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` por las credenciales asignadas por el hosting.

### 6.3. Aseguramiento mediante Certificado SSL/TLS (HTTPS)
Para asegurar que los despachos y la navegación sean íntegros:
1. Instale y active un certificado SSL/TLS válido (Let's Encrypt o proveedor nativo) a través de la sección "SSL/TLS" del panel de control.
2. Inserte el siguiente bloque de directivas al inicio de su archivo `.htaccess` para obligar toda la comunicación por el canal cifrado:
   ```apache
   # Forzar protocolo HTTPS
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### 6.4. Validación Funcional
Tras el despliegue y puesta en marcha, se requiere ejecutar pruebas de humo (Smoke Testing):
1. **Verificación de Enrutamiento:** Navegue mediante la URL limpia a distintas secciones del aplicativo (ej. `https://sitioproduccion.com/productos/lista`) para confirmar el correcto procesamiento por `mod_rewrite`.
2. **Validación de la API de WhatsApp:** Inicie el flujo crítico simulando un pedido (selección del producto y carrito). Al confirmar (checkout), verifique que la redirección a la API de WhatsApp (`https://wa.me/numero?text=...`) se haya codificado e invocado exitosamente con los parámetros del pedido íntegros.
