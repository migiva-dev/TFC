# 💈 DIONI Peluqueros - Sistema de Gestión de Reservas

Proyecto desarrollado como **Trabajo de Fin de Ciclo (TFC)** del ciclo de **Desarrollo de Aplicaciones Web (DAW)**.

Se trata de una aplicación web completa para la gestión de reservas de una peluquería, permitiendo a los clientes registrarse, consultar servicios y reservar citas online, mientras que el administrador puede gestionar todas las reservas desde un panel privado.

---

## 📌 Descripción del proyecto

El objetivo principal de este proyecto es digitalizar el proceso de reservas de una peluquería tradicional, permitiendo automatizar la gestión de citas y mejorar la experiencia del cliente.

La aplicación permite:

- Consultar información sobre la peluquería
- Ver catálogo de servicios
- Registro e inicio de sesión de usuarios
- Reserva de citas online
- Gestión de disponibilidad
- Panel de administración
- Confirmación y cancelación de reservas

---

## 🚀 Repositorio

Repositorio oficial del proyecto:

🔗 https://github.com/migiva-dev/TFC

---

# 🛠 Tecnologías utilizadas

## Backend
- PHP (programación principal del servidor)
- Gestión de sesiones
- Validación de formularios
- Lógica de reservas

## Base de datos
- MySQL
- phpMyAdmin
- Archivo SQL para importar la base de datos

## Frontend
- HTML5
- CSS3
- JavaScript Vanilla

## Diseño UI
Diseño inspirado en una estética premium:

- Negro
- Blanco
- Plateado
- Diseño minimalista
- Responsive Design

El diseño está basado en la identidad visual de **DIONI Peluqueros**.

---

# ✨ Funcionalidades principales

## Zona pública

### Página principal
- Presentación de la peluquería
- Información corporativa
- Acceso rápido a reservas

### Servicios
Los usuarios pueden consultar:

- Corte de pelo
- Barba
- Tratamientos
- Servicios adicionales

### Registro de usuarios
Los clientes pueden crear una cuenta.

### Login de usuarios
Acceso privado para clientes registrados.

### Reserva de citas
Los usuarios pueden:

- Elegir fecha
- Elegir hora
- Seleccionar servicio
- Confirmar cita

---

# 🔐 Panel de administración

El sistema cuenta con una zona privada para administradores:

### Funciones del administrador:
- Ver todas las reservas
- Confirmar reservas
- Cancelar reservas
- Gestionar estado de citas
- Visualizar información de clientes

---

# 🗂️ Estructura real del proyecto

```bash
TFC/
│
├── admin/
│   ├── dashboard.php        # Panel principal del administrador
│   ├── gestionar.php        # Gestión de reservas
│   ├── login.php            # Login administrador
│   └── logout.php           # Cierre de sesión administrador
│
├── assets/
│   │
│   ├── css/
│   │   └── estilo.css       # Estilos principales del sistema
│   │
│   ├── img/
│   │   └── logo.png         # Recursos visuales e imágenes
│   │
│   └── js/
│       └── main.js          # Funcionalidades frontend
│
├── includes/
│   ├── config.php           # Configuración general
│   ├── db.php               # Conexión a base de datos
│   ├── header.php           # Cabecera reutilizable
│   ├── footer.php           # Pie reutilizable
│   ├── funciones.php        # Funciones auxiliares       
│   └── google-calendar.php  # Integración con Google Calendar API
│
├── public/
│   ├── index.php            # Página principal
│   ├── login.php            # Login usuarios
│   ├── logout.php           # Logout usuarios
│   ├── registro.php         # Registro usuarios
│   ├── reservar.php         # Sistema de reservas
│   └── servicios.php        # Catálogo de servicios
│
├── sql/
│   └── peluqueria.sql       # Base de datos del proyecto
│
└── README.md