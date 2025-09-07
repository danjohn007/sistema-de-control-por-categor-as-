# Sistema de Control de Gastos e Ingresos por Categorías

Una plataforma web completa para registrar, clasificar y analizar ingresos y gastos por categorías principales (Casa, Negocio, Oficina) y subcategorías específicas, con visualización en gráficas, calendario de actividades, y control seguro de usuarios.

## Características Principales

- ✅ **Gestión de Movimientos**: Registro de ingresos y gastos por categoría y subcategoría
- ✅ **Dashboard Interactivo**: Estadísticas en tiempo real con gráficas
- ✅ **Calendario de Actividades**: Programación de pagos y recordatorios
- ✅ **Control de Usuarios**: Sistema de autenticación con roles (Admin/Usuario)
- ✅ **Reportes y Filtros**: Filtrado avanzado y visualización de datos
- ✅ **Diseño Responsivo**: Interfaz adaptable a dispositivos móviles

## Tecnologías Utilizadas

- **Backend**: PHP 7+ (sin frameworks)
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Frameworks CSS**: Bootstrap 5
- **Gráficas**: Chart.js
- **Calendario**: FullCalendar.js
- **Arquitectura**: MVC (Model-View-Controller)

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web Apache con mod_rewrite
- Extensiones PHP: PDO, PDO_MySQL

## Instalación

### 1. Descargar o Clonar

```bash
git clone https://github.com/tu-usuario/sistema-control-gastos.git
cd sistema-control-gastos
```

### 2. Configurar Base de Datos

1. Crear una base de datos MySQL llamada `expense_control`
2. Importar el archivo `database.sql`:

```bash
mysql -u root -p expense_control < database.sql
```

### 3. Configurar Conexión

Editar el archivo `config/database.php` con las credenciales de tu base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'expense_control');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Configurar Apache

Asegúrate de que el archivo `.htaccess` esté habilitado y que `mod_rewrite` esté activo.

### 5. Acceder al Sistema

Navega a tu dominio o directorio local:

```
http://localhost/sistema-control-gastos
```

**Usuario por defecto:**
- Usuario: `admin`
- Contraseña: `password`

## Estructura del Proyecto

```
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── config/
│   ├── config.php
│   └── database.php
├── controllers/
│   ├── BaseController.php
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── CategoryController.php
│   ├── MovementController.php
│   └── CalendarController.php
├── views/
│   ├── layouts/
│   │   └── main.php
│   ├── auth/
│   │   └── login.php
│   ├── dashboard/
│   │   └── index.php
│   ├── movement/
│   │   ├── index.php
│   │   └── create.php
│   └── calendar/
│       └── index.php
├── database.sql
├── index.php
├── .htaccess
└── README.md
```

## Funcionalidades

### Dashboard Principal
- Resumen de ingresos y gastos del mes
- Gráficas de tendencias mensuales
- Gráfico circular por categorías
- Lista de movimientos recientes

### Gestión de Movimientos
- Registro de ingresos y gastos
- Categorización por tipo (Casa, Negocio, Oficina)
- Subcategorías dinámicas
- Filtros avanzados por fecha, categoría, tipo
- Edición y eliminación de registros

### Calendario de Actividades
- Programación de pagos
- Recordatorios de vencimientos
- Eventos personalizados
- Vista mensual, semanal y diaria

### Administración (Solo Admin)
- Gestión de categorías y subcategorías
- Control de usuarios
- Configuración del sistema

## Seguridad

- Autenticación con hash de contraseñas (password_hash)
- Protección CSRF en formularios
- Sanitización de entradas de usuario
- Control de sesiones seguras
- Validación en cliente y servidor

## Personalización

### Colores de Categorías
Puedes personalizar los colores de las categorías editando la base de datos o desde el panel de administración.

### Configuración de URL Base
El sistema se configura automáticamente para cualquier directorio, pero puedes ajustar manualmente en `config/config.php`:

```php
define('BASE_URL', 'http://tu-dominio.com/ruta-del-sistema');
```

## Soporte

Para reportar problemas o solicitar nuevas características, por favor crear un issue en el repositorio del proyecto.

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más detalles.

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request
