-- Base de datos para Sistema de Control de Gastos e Ingresos
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS expense_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expense_control;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role TINYINT DEFAULT 2 COMMENT '1=Admin, 2=User',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías principales
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de subcategorías
CREATE TABLE subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    type ENUM('income', 'expense') NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Tabla de movimientos (ingresos y gastos)
CREATE TABLE movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    subcategory_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    movement_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE CASCADE
);

-- Tabla de eventos del calendario
CREATE TABLE calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    event_type ENUM('payment', 'due_date', 'reminder', 'other') DEFAULT 'other',
    amount DECIMAL(10,2),
    category_id INT,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabla de bitácora de accesos
CREATE TABLE access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    action VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insertar usuario administrador por defecto
-- Contraseña: password
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@example.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Administrador', 1);

-- Insertar categorías por defecto
INSERT INTO categories (name, description, color) VALUES 
('Casa', 'Gastos e ingresos relacionados con el hogar', '#28a745'),
('Negocio', 'Gastos e ingresos del negocio', '#007bff'),
('Oficina', 'Gastos e ingresos de oficina', '#ffc107');

-- Insertar subcategorías por defecto
INSERT INTO subcategories (category_id, name, description, type) VALUES 
-- Casa
(1, 'Agua', 'Servicio de agua potable', 'expense'),
(1, 'Luz', 'Servicio de energía eléctrica', 'expense'),
(1, 'Gas', 'Servicio de gas natural/LP', 'expense'),
(1, 'Internet', 'Servicio de internet', 'expense'),
(1, 'Teléfono', 'Servicio telefónico', 'expense'),
(1, 'Renta', 'Ingreso por renta de propiedad', 'income'),

-- Negocio
(2, 'Ventas', 'Ingresos por ventas', 'income'),
(2, 'Nómina', 'Pago de salarios', 'expense'),
(2, 'Materiales', 'Compra de materiales', 'expense'),
(2, 'Marketing', 'Gastos de publicidad y marketing', 'expense'),
(2, 'Mantenimiento', 'Gastos de mantenimiento', 'expense'),

-- Oficina
(3, 'Papelería', 'Compra de papelería y suministros', 'expense'),
(3, 'Equipo', 'Compra de equipo de oficina', 'expense'),
(3, 'Servicios', 'Servicios profesionales', 'income'),
(3, 'Limpieza', 'Servicios de limpieza', 'expense'),
(3, 'Café', 'Suministros de cafetería', 'expense');