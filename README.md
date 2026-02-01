# DASHDINA - Sistema de Gestión y Cotizaciones

Sistema administrativo moderno desarrollado con **Laravel + Filament**, diseñado para gestionar cotizaciones, órdenes de trabajo y clientes con una interfaz intuitiva y roles de usuario.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-v3-e6a00f?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 🚀 Características Principales

- **Gestión de Cotizaciones**: Creación, edición y seguimiento de cotizaciones con estados (Aprobada, Facturada, Pendiente, etc.).
- **Dashboard Interactivo**: Gráficos de tendencias y métricas de cumplimiento en tiempo real.
- **Control de Roles (RBAC)**: Sistema de permisos granular (Simulador de usuario, Admin, etc.) para proteger recursos sensibles.
- **Integración ERP Mock**: Conexión a base de datos externa simulada localmente con SQLite para desarrollo seguro.
- **Reportes**: Exportación de datos masiva a Excel.

## 🛠️ Stack Tecnológico

- **Backend**: Laravel Framework 12.x
- **Admin Panel**: FilamentPHP v3
- **Frontend**: Blade, Livewire, Tailwind CSS v4
- **Base de Datos**: 
  - Producción: SQLite (Configurado por defecto) o MySQL.
  - ERP: Mock Local (SQLite) o Conexión Remota (MySQL).
- **Assets**: Vite + Node.js v20

## 📦 Instalación

Para desplegar este proyecto en un servidor **Ubuntu**, hemos preparado una guía paso a paso sin Docker:

👉 **[GUÍA DE INSTALACIÓN PASO A PASO (Ubuntu Server)](INSTALLATION.md)**

### Instalación Local (Desarrollo)

1. **Clonar repositorio**:
   ```bash
   git clone <repo-url>
   cd DASHDINA
   ```

2. **Instalar dependencias**:
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Asegúrate de configurar `DB_CONNECTION=sqlite` para desarrollo rápido.*

4. **Base de datos**:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Iniciar**:
   ```bash
   npm run build
   php artisan serve
   ```

## 🔒 Seguridad

El sistema implementa políticas de acceso estrictas (`Policies`) y sanitización de datos.

---
© 2026 DASHDINA. Todos los derechos reservados.
