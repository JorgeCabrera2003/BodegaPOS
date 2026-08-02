# 🏪 BodegaPOS

BodegaPOS es un avanzado y robusto Sistema de Punto de Venta (POS) e Inventario diseñado específicamente para pequeños y medianos comercios, bodegas y tiendas en Venezuela. Desarrollado sobre Laravel 11, FilamentPHP 3, y Livewire, el sistema ofrece una experiencia de usuario rápida, reactiva y totalmente adaptada a las necesidades comerciales multimoneda.

## 🚀 Características Principales

*   **🛒 Terminal POS Reactivo (Livewire):** Interfaz de cobro ultrarrápida, búsqueda instantánea de productos con atajos de teclado y escáner de código de barras, y gestión de carrito sin recargar la página.
*   **💱 Sistema Multimoneda Avanzado (USD/VES):** Soporte total para cálculos de pagos mixtos (Dólares y Bolívares).
*   **🔄 Sincronización Automática BCV:** Integración en segundo plano con DolarApi para sincronizar la Tasa Oficial del Banco Central de Venezuela (BCV) diariamente sin afectar el rendimiento ni requerir Cron Jobs pesados. Protección anti-bot (Cloudflare/User-Agent).
*   **💸 Gestión de Múltiples Métodos de Pago:** Soporte nativo para Efectivo USD, Efectivo Bs, Pago Móvil, Punto de Venta, Zelle, y Binance Pay.
*   **📈 Dashboard Financiero Real:** Gráficos estadísticos de flujo de caja que separan inteligentemente el ingreso *real* en divisas frente al ingreso en moneda local (basado en el método de pago usado por el cliente, no solo en la conversión total).
*   **🧾 Cálculo IGTF:** Posibilidad de aplicar automáticamente el Impuesto a las Grandes Transacciones Financieras (IGTF) del 3% a los pagos recibidos en divisas extranjeras.
*   **📦 Gestión de Inventario Inteligente:** Alertas visuales de stock bajo, historial de surtido de productos, control de stock máximo y mínimo.
*   **🔐 Roles y Permisos:** Control de acceso granular para Cajeros, Administradores y Supervisores (Filament Shield).
*   **📓 Bitácora de Auditoría (ActivityLog):** Registro detallado de cualquier creación, edición o eliminación en el sistema, completamente traducido al español.
*   **🎨 Diseño Premium:** Interfaz amigable, moderna y dinámica, completamente en español.

## 🛠️ Stack Tecnológico

*   **Backend:** PHP 8.3 / Laravel 11
*   **Frontend / Panel Administrativo:** FilamentPHP v3
*   **Reactividad:** Livewire v3 / Alpine.js
*   **Estilos:** Tailwind CSS
*   **Base de Datos:** MySQL / MariaDB (vía Laragon)

## 📋 Requisitos del Sistema

*   PHP >= 8.2
*   Composer
*   Node.js & NPM
*   MySQL 8.0+ o MariaDB
*   (Recomendado) Laragon para entorno local en Windows

## ⚙️ Instalación Local

1.  **Clonar el repositorio**
    ```bash
    git clone https://github.com/JorgeCabrera2003/BodegaPOS.git
    cd BodegaPOS
    ```

2.  **Instalar dependencias de PHP y Node**
    ```bash
    composer install
    npm install
    npm run build
    ```

3.  **Configurar las variables de entorno**
    Copia el archivo `.env.example` y renómbralo a `.env`. Ajusta la conexión de la base de datos:
    ```bash
    cp .env.example .env
    ```
    *Asegúrate de configurar `DB_DATABASE=bodega`, `DB_USERNAME=root`.*

4.  **Generar la clave de la aplicación**
    ```bash
    php artisan key:generate
    ```

5.  **Ejecutar las migraciones y Semillas (Seeders)**
    ```bash
    php artisan migrate --seed
    ```

6.  **Crear el primer usuario Administrador**
    ```bash
    php artisan shield:super-admin
    ```
    Sigue las instrucciones en consola para establecer el correo y contraseña de tu cuenta principal.

7.  **Sincronizar la Tasa BCV inicial**
    ```bash
    php artisan currency:fetch-bcv --force
    ```

8.  **Iniciar el servidor local**
    ```bash
    php artisan serve
    ```
    El panel de administración estará disponible en: `http://localhost:8000/admin` o `http://bodega.test/admin` (si usas Laragon).

## 🗃️ Estructura de Módulos (Filament)

*   **Ventas (`SaleResource` / `PosTerminalPage`):** Control del flujo principal de caja.
*   **Productos (`ProductResource`):** Catálogo de artículos con código de barras, SKU y alertas de inventario.
*   **Categorías & Proveedores:** Organización del inventario y contactos comerciales.
*   **Egresos (`ExpenseResource`):** Control de gastos operativos fijos y recurrentes.
*   **Tasas BCV (`ExchangeRateResource`):** Historial inmutable de las tasas de cambio utilizadas diariamente en el sistema.

## 📄 Licencia

Este proyecto es privado. Todos los derechos reservados.
