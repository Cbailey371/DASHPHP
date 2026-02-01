# Guía de Instalación - Ubuntu Server (Directa/Sin Docker)

Esta guía detalla los pasos para desplegar DASHDINA en un servidor Ubuntu limpio (22.04 LTS o 24.04 LTS).

## 1. Preparación del Servidor

Asegúrate de tener acceso root o sudo.

```bash
# Actualizar el sistema
sudo apt update && sudo apt upgrade -y

# Instalar utilidades básicas
sudo apt install -y git curl zip unzip nano
```

## 2. Instalar PHP 8.3

DASHDINA requiere una versión moderna de PHP.

```bash
# Agregar repositorio de PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP y extensiones necesarias
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-common \
php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl \
php8.3-xml php8.3-bcmath php8.3-intl php8.3-sqlite3
```

## 3. Instalar Base de Datos (SQLite / MySQL)

El proyecto está preconfigurado para usar **SQLite** en producción por su eficiencia y cero mantenimiento.

Si prefieres usar MySQL para la base de datos principal:
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

## 4. Instalar Composer y Node.js

### Composer (Gestor de Paquetes PHP)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Node.js (v20 LTS)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

## 5. Despliegue del Código

Clonaremos el proyecto en `/var/www/`.

```bash
cd /var/www
# Clona tu repositorio (usa HTTPS o configura claves SSH)
sudo git clone https://github.com/tu-usuario/dashdina.git
sudo chown -R www-data:www-data dashdina
cd dashdina
```

## 6. Configuración de la Aplicación

Ejecuta los siguientes comandos como el usuario web (`www-data`) para evitar problemas de permisos.

```bash
# Instalar dependencias de PHP (Producción)
sudo -u www-data composer install --no-dev --optimize-autoloader

# Configurar variables de entorno
sudo cp .env.example .env
sudo nano .env
```

**Variables Clave en `.env`:**
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Configuración SQLite (Recomendada)
DB_CONNECTION=sqlite
# (No requiere DB_HOST, DB_USERNAME, etc.)

# Opcional: Configuración MySQL ERP (Si conectas a un ERP externo)
ERP_DB_CONNECTION=mysql
ERP_DB_HOST=...
```

Generar la clave de encriptación:
```bash
sudo php artisan key:generate --force
```

## 7. Compilar Frontend (Assets)

Desde la carpeta del proyecto:
```bash
sudo npm install
sudo npm run build
```

## 8. Base de Datos y Optimización

1. **Preparar SQLite** (si se usa):
   ```bash
   sudo touch database/database.sqlite
   sudo chown www-data:www-data database/database.sqlite
   ```

2. **Ejecutar Migraciones**:
   ```bash
   sudo -u www-data php artisan migrate --force
   ```

3. **Optimizar Laravel**:
   ```bash
   sudo -u www-data php artisan config:cache
   sudo -u www-data php artisan route:cache
   sudo -u www-data php artisan view:cache
   sudo -u www-data php artisan filament:optimize
   ```

4. **Crear Enlace Simbólico**:
   ```bash
   sudo -u www-data php artisan storage:link
   ```

## 9. Configurar Nginx (Servidor Web)

Crear configuración del sitio:
`sudo nano /etc/nginx/sites-available/dashdina`

Pegar el siguiente contenido:
```nginx
server {
    listen 80;
    server_name tudominio.com; # CAMBIAR ESTO
    root /var/www/dashdina/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activar el sitio:
```bash
sudo ln -s /etc/nginx/sites-available/dashdina /etc/nginx/sites-enabled/
sudo unlink /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

## 10. Certificado SSL (HTTPS)

Seguridad automática con Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com
```

## 11. Configurar Permisos Finales

Asegurar que Laravel pueda escribir en las carpetas necesarias:

```bash
sudo chown -R www-data:www-data /var/www/dashdina
sudo chmod -R 775 /var/www/dashdina/storage
sudo chmod -R 775 /var/www/dashdina/bootstrap/cache
```

¡Listo! Tu aplicación debería estar accesible en `https://tudominio.com`.
