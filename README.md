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
- Sincronización automática con Google Calendar

---

## 🌐 Demo online

El proyecto está desplegado y disponible públicamente en:

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
- Ver horas ocupadas en tiempo real
- Evitar fechas pasadas
- Añadir notas personalizadas

---

# 📅 Integración con Google Calendar

El sistema incorpora integración automática con **Google Calendar API**, permitiendo sincronizar todas las reservas con un calendario real.

### Funcionalidades implementadas:

- Creación automática de eventos al reservar una cita
- Eliminación automática de eventos al cancelar reservas
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