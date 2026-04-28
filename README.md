# 💈 DIONI Peluqueros - Sistema de Gestión de Reservas

Proyecto desarrollado como **Trabajo de Fin de Ciclo (TFC)** del ciclo de **Desarrollo de Aplicaciones Web (DAW)**.

Se trata de una aplicación web completa para la gestión de reservas de una peluquería, permitiendo a los clientes registrarse, consultar servicios y reservar citas online, mientras que el administrador puede gestionar todas las reservas desde un panel privado.

---

## 📌 Descripción del proyecto

El objetivo principal de este proyecto es digitalizar el proceso tradicional de reservas de una peluquería, automatizando la gestión de citas y mejorando tanto la experiencia del cliente como la organización interna del negocio.

La aplicación permite:

- Consultar información sobre la peluquería
- Ver catálogo de servicios
- Registro e inicio de sesión de usuarios
- Reserva de citas online
- Gestión de disponibilidad
- Panel de administración
- Confirmación y cancelación de reservas
- Sincronización automática con Google Calendar

---

# 🌐 Demo online

El proyecto está desplegado y funcionando en producción:

🔗 https://tfc-peluqueria.atwebpages.com/

Repositorio oficial:

🔗 https://github.com/migiva-dev/TFC

---

# 🛠 Tecnologías utilizadas

## Backend
- PHP
- Gestión de sesiones
- Validación de formularios
- Lógica de reservas
- Integración con APIs externas

## Base de datos
- MySQL
- phpMyAdmin
- Archivo SQL para importar la base de datos

## Frontend
- HTML5
- CSS3
- JavaScript Vanilla

## APIs y servicios externos
- Google Calendar API
- JWT Authentication
- Google Service Account

## Hosting / Despliegue
- AwardSpace
- Apache
- HTTPS
- `.htaccess`

## Diseño UI/UX

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
Los clientes pueden crear una cuenta para reservar citas.

### Login de usuarios
Acceso privado para clientes registrados.

### Reserva de citas
Los usuarios pueden:

- Elegir fecha
- Elegir hora
- Seleccionar servicio
- Confirmar cita
- Ver horas ocupadas en tiempo real
- Evitar fechas pasadas
- Añadir notas personalizadas

---

# 📅 Integración con Google Calendar

El sistema incorpora integración automática con **Google Calendar API**, permitiendo sincronizar las reservas con un calendario real.

### Funcionalidades implementadas

- Creación automática de eventos al reservar una cita
- Eliminación automática de eventos al cancelar una reserva
- Consulta de eventos semanales
- Autenticación mediante JWT
- Uso de Google Service Account

### Flujo de sincronización

```text
Cliente realiza reserva
↓
Reserva guardada en MySQL
↓
Evento creado automáticamente en Google Calendar
↓
Administrador visualiza la cita
```

Si una reserva se cancela:

```text
Reserva cancelada
↓
Evento eliminado automáticamente de Google Calendar
```

---

# 🔐 Panel de administración

El sistema cuenta con una zona privada exclusiva para administradores.

### Funciones disponibles:

- Ver todas las reservas
- Confirmar reservas
- Cancelar reservas
- Gestionar estados
- Consultar próximas reservas
- Ver estadísticas del sistema
- Visualizar información de clientes

---

# 🗂️ Estructura del proyecto

```bash
TFC/
│
├── admin/
│   ├── dashboard.php          # Panel principal del administrador
│   ├── gestionar.php          # Gestión de reservas
│   ├── login.php              # Login administrador
│   └── logout.php             # Cierre de sesión administrador
│
├── assets/
│   ├── css/
│   │   └── estilo.css         # Estilos principales del sistema
│   │
│   ├── img/
│   │   └── logo.png           # Recursos visuales e imágenes
│   │
│   └── js/
│       └── main.js            # Funcionalidades frontend
│
├── includes/
│   ├── config.php             # Configuración global
│   ├── db.php                 # Conexión a base de datos
│   ├── header.php             # Cabecera reutilizable
│   ├── footer.php             # Pie reutilizable
│   ├── funciones.php          # Funciones auxiliares
│   └── google-calendar.php    # Integración con Google Calendar API
│
├── public/
│   ├── index.php              # Página principal
│   ├── login.php              # Login usuarios
│   ├── logout.php             # Logout usuarios
│   ├── registro.php           # Registro de usuarios
│   ├── reservar.php           # Sistema de reservas
│   └── servicios.php          # Catálogo de servicios
│
├── sql/
│   └── peluqueria.sql         # Script de base de datos
│
├── .htaccess                  # Configuración del servidor Apache
│
└── README.md
```

---

# ⚙️ Funcionamiento del sistema

```text
Usuario entra en la web
↓
Consulta servicios
↓
Se registra / inicia sesión
↓
Selecciona servicio
↓
Selecciona fecha y hora
↓
Realiza reserva
↓
Se guarda en MySQL
↓
Se sincroniza con Google Calendar
↓
Administrador gestiona la reserva
```

---

# 🧠 Funcionalidades del frontend

El archivo `main.js` implementa:

- Bloqueo de fechas pasadas
- Confirmación antes de cancelar reservas
- Confirmación antes de confirmar reservas
- Ocultación automática de mensajes
- Menú hamburguesa responsive
- Actualización automática de horarios disponibles

---

# 🎨 Diseño responsive

El archivo `estilo.css` incluye:

- Adaptación a móviles
- Adaptación a tablets
- Menú responsive
- Panel administrativo responsive
- Grid adaptable
- Formularios adaptables

---

# 🗃️ Base de datos

La base de datos almacena información sobre:

- Usuarios
- Reservas
- Servicios
- Administradores
- Horarios
- Estados de reservas

Archivo principal:

```bash
sql/peluqueria.sql
```

---

# 🔧 Instalación local

## 1. Clonar repositorio

```bash
git clone https://github.com/migiva-dev/TFC.git
```

---

## 2. Mover proyecto al servidor local

Ejemplo con XAMPP:

```bash
htdocs/TFC
```

---

## 3. Importar base de datos

Importar:

```bash
sql/peluqueria.sql
```

desde phpMyAdmin.

---

## 4. Configurar conexión a la base de datos

Editar:

```bash
includes/db.php
```

Configurar tus credenciales de MySQL.

---

## 5. Ejecutar aplicación

```bash
http://localhost/TFC/public
```

---

# 🔒 Seguridad implementada

- Gestión de sesiones
- Restricción de acceso administrador
- Validación de formularios
- Prevención de reservas duplicadas
- Restricción de fechas pasadas
- Confirmación de acciones críticas
- Configuración segura mediante `.htaccess`
- Redirección HTTPS

---

# 🚀 Mejoras futuras

- Pasarela de pago online
- Notificaciones por email
- Panel para empleados
- API REST
- Dashboard avanzado
- Sistema automático de recordatorios
- Migración futura a Laravel

---

# 🎯 Objetivos académicos

Este proyecto fue desarrollado para aplicar conocimientos en:

- Desarrollo backend
- Bases de datos relacionales
- Frontend responsive
- APIs externas
- Seguridad web
- Despliegue real
- Arquitectura web
- Gestión de usuarios

---

# 👨‍💻 Autor

**Miguel Gavilá **

Proyecto desarrollado como Trabajo de Fin de Ciclo (DAW)

GitHub: https://github.com/migiva-dev

---

# 📄 Licencia

Proyecto desarrollado con fines educativos y académicos.