# 🚀 Instrucciones de Despliegue - EcomerKalisto

Felicidades por finalizar tu proyecto de grado. He reestructurado completamente el sistema para que sea 100% seguro y cumpla con tus lineamientos. Sigue estas instrucciones para desplegarlo en producción o ponerlo a correr localmente de forma correcta.

## 1. Configuración de Google OAuth 2.0 (OBLIGATORIO)

Dado que eliminamos el registro inseguro con usuario/contraseña, **debes** configurar tu propio Client ID de Google para que el inicio de sesión funcione.

1. Ve a la [Consola de Google Cloud](https://console.cloud.google.com/).
2. Crea un nuevo proyecto llamado `EcomerKalisto`.
3. Ve a **APIs & Services** > **OAuth consent screen** y configura la pantalla de consentimiento (puedes ponerla en modo Externo/Testing).
4. Ve a **Credentials** > **Create Credentials** > **OAuth client ID**.
5. Selecciona "Web application".
6. En **Authorized JavaScript origins**, agrega:
   - `http://localhost` (para pruebas locales).
   - `https://tudominio.com` (para producción).
7. En **Authorized redirect URIs**, agrega:
   - `http://localhost/login_mvc/google_login.php`
   - `https://tudominio.com/google_login.php`
8. Copia tu **Client ID** (termina en `.apps.googleusercontent.com`).
9. Abre el archivo `index.php` de tu proyecto y reemplaza la línea que dice:
   `data-client_id="TU_CLIENT_ID_DE_GOOGLE_AQUI.apps.googleusercontent.com"`
   con el Client ID que copiaste.

## 2. Base de Datos

El sistema sigue utilizando MySQL para gestionar los usuarios que inician sesión con Google, pero previene inyecciones SQL mediante *Prepared Statements*.

1. Asegúrate de que en XAMPP o en tu servidor de producción tienes creada la base de datos `login_mvc`.
2. Ejecuta el siguiente comando SQL en tu gestor (ej. phpMyAdmin) si no tenías la tabla de usuarios configurada correctamente:

```sql
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
> **Nota:** La columna `password` sigue existiendo, pero se guarda en blanco automáticamente, ya que la autenticación ahora depende criptográficamente de Google.

## 3. Catálogo de Productos

Tus productos ahora viven de forma estructurada en un archivo JSON seguro.
- **Ruta:** `api/productos.json`
- **Modificación:** Si necesitas alterar precios o agregar un producto (recordando no agregar frutas ni verduras), simplemente edita ese archivo JSON. La interfaz (`ecomerkalisto.php`) leerá y pintará el catálogo de inmediato sin necesidad de tocar código PHP o JavaScript.

## 4. Despliegue en Servidor 24/7 (Producción)

Si vas a subir este proyecto a un hosting real (Hostinger, AWS, Heroku, etc.):
1. Sube todos los archivos (excepto el `.git` o archivos temporales) por FTP o cPanel a tu directorio público (usualmente `public_html`).
2. Actualiza los orígenes autorizados en Google Cloud (paso 1).
3. Modifica las credenciales de la base de datos en `google_login.php` con las que te proporcione tu proveedor de hosting:
   ```php
   $servername = "localhost"; // O el que te den
   $username = "usuario_cpanel";
   $password = "tu_contraseña_segura";
   $dbname = "tu_basededatos";
   ```
4. **Seguridad Adicional (HTTPS):** Para que las cookies protegidas (`Secure`) que configuramos en `auth_middleware.php` funcionen correctamente, tu sitio **debe** tener un certificado SSL (HTTPS). Si lo corres localmente en HTTP sin certificado, algunos navegadores podrían rechazar la cookie. Para entorno local, puedes comentar temporalmente `ini_set('session.cookie_secure', 1);` en `auth_middleware.php` y en `google_login.php` si presentas problemas de inicio de sesión en localhost.

¡Éxitos con la presentación de tu proyecto de grado! 🎉
