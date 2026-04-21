-- =====================================================
-- BASE DE DATOS: Peluquería TGC
-- IMPORTANTE: La base de datos '4752575_peluqueria'
--             ya está creada en AwardSpace.
--             Este script SOLO crea las tablas.
-- =====================================================

-- -----------------------------------------------------
-- TABLA: usuarios
-- Almacena los clientes registrados en la web
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL,
    apellidos   VARCHAR(100) NOT NULL,
    telefono    VARCHAR(15)  NOT NULL,
    email       VARCHAR(100) UNIQUE,           -- Opcional pero único si se indica
    password    VARCHAR(255) NOT NULL,         -- Contraseña cifrada con bcrypt
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- TABLA: servicios
-- Servicios que ofrece la peluquería
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS servicios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio      DECIMAL(5,2) NOT NULL,         -- Precio en euros
    duracion    INT NOT NULL                   -- Duración en minutos
);

-- -----------------------------------------------------
-- TABLA: reservas
-- Cada reserva pertenece a un usuario y un servicio
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    servicio_id INT NOT NULL,
    fecha       DATE NOT NULL,                 -- Día de la cita
    hora        TIME NOT NULL,                 -- Hora de la cita
    estado      ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
    notas       TEXT,                          -- Notas opcionales del cliente
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- TABLA: administradores
-- Usuarios con acceso al panel privado
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS administradores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario     VARCHAR(50)  NOT NULL UNIQUE,  -- Nombre de usuario del admin
    password    VARCHAR(255) NOT NULL,         -- Contraseña cifrada con bcrypt
    nombre      VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Servicios de la peluquería
INSERT INTO servicios (nombre, descripcion, precio, duracion) VALUES
('Corte de pelo',    'Corte clásico con tijera o máquina',      12.00, 30),
('Corte + Barba',    'Corte de pelo y arreglo de barba',        18.00, 45),
('Arreglo de barba', 'Perfilado y arreglo de barba',             8.00, 20),
('Tinte',            'Tinte completo con producto incluido',    25.00, 60),
('Corte infantil',   'Corte para niños menores de 12 años',      8.00, 20);

-- Administrador por defecto
-- Usuario: admin | Contraseña: Admin1234
INSERT INTO administradores (usuario, password, nombre) VALUES
(
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Administrador Principal'
);