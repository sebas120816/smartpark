-- SMARTPARK - Parqueadero Zona Segura ECCI
-- Version compatible con InfinityFree: sin CREATE DATABASE, USE, TRIGGERS, VIEWS ni CHECK complejos.
-- Primero selecciona la base de datos en phpMyAdmin y luego importa este archivo.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tbl_pagos;
DROP TABLE IF EXISTS tbl_parqueos;
DROP TABLE IF EXISTS tbl_tarifas;
DROP TABLE IF EXISTS tbl_espacios;
DROP TABLE IF EXISTS tbl_vehiculos;
DROP TABLE IF EXISTS tbl_clientes;
DROP TABLE IF EXISTS tbl_usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE tbl_usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('Administrador','Caja','Control') NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  ultima_sesion DATETIME DEFAULT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_usuarios_email UNIQUE (email)
) ENGINE=InnoDB COMMENT='Usuarios internos del sistema con roles de Administrador, Caja y Control.';

CREATE TABLE tbl_clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  cedula VARCHAR(30) NOT NULL,
  nombres VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  correo VARCHAR(120) DEFAULT NULL,
  tipo_cliente ENUM('estudiante','docente','funcionario','visitante') NOT NULL DEFAULT 'visitante',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_clientes_cedula UNIQUE (cedula)
) ENGINE=InnoDB COMMENT='Clientes propietarios o responsables de vehiculos.';

CREATE TABLE tbl_vehiculos (
  id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  placa VARCHAR(20) NOT NULL,
  marca VARCHAR(80) NOT NULL,
  modelo VARCHAR(80) NOT NULL,
  color VARCHAR(60) NOT NULL,
  tipo ENUM('auto','moto') NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_vehiculos_placa UNIQUE (placa),
  CONSTRAINT fk_vehiculos_clientes FOREIGN KEY (id_cliente)
    REFERENCES tbl_clientes(id_cliente)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Vehiculos asociados a clientes. La placa identifica un vehiculo.';

CREATE TABLE tbl_espacios (
  id_espacio INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL,
  piso TINYINT NOT NULL,
  tipo_vehiculo ENUM('auto','moto') NOT NULL,
  estado ENUM('libre','reservado','ocupado') NOT NULL DEFAULT 'libre',
  CONSTRAINT uk_espacios_codigo UNIQUE (codigo)
) ENGINE=InnoDB COMMENT='Espacios fisicos del parqueadero distribuidos en dos pisos.';

CREATE TABLE tbl_tarifas (
  id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
  tipo_vehiculo ENUM('auto','moto') NOT NULL,
  valor_hora DECIMAL(10,2) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uk_tarifas_tipo UNIQUE (tipo_vehiculo)
) ENGINE=InnoDB COMMENT='Tarifas por hora segun tipo de vehiculo.';

CREATE TABLE tbl_parqueos (
  id_parqueo INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_vehiculo INT NOT NULL,
  id_espacio INT DEFAULT NULL,
  id_tarifa INT NOT NULL,
  id_usuario_ingreso INT DEFAULT NULL,
  id_usuario_salida INT DEFAULT NULL,
  fecha_ingreso DATE NOT NULL,
  hora_ingreso TIME NOT NULL,
  fecha_salida DATE DEFAULT NULL,
  hora_salida TIME DEFAULT NULL,
  tarifa_hora_aplicada DECIMAL(10,2) NOT NULL,
  total_horas INT DEFAULT NULL,
  valor_total DECIMAL(10,2) DEFAULT NULL,
  reserva_expira_en DATETIME DEFAULT NULL,
  codigo_reserva VARCHAR(30) DEFAULT NULL,
  token_publico VARCHAR(80) DEFAULT NULL,
  cancelado_en DATETIME DEFAULT NULL,
  motivo_cancelacion VARCHAR(180) DEFAULT NULL,
  estado ENUM('espera','reservado','activo','finalizado','vencido','cancelado') NOT NULL DEFAULT 'activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_parqueos_codigo_reserva UNIQUE (codigo_reserva),
  CONSTRAINT uk_parqueos_token_publico UNIQUE (token_publico),
  CONSTRAINT fk_parqueos_clientes FOREIGN KEY (id_cliente)
    REFERENCES tbl_clientes(id_cliente)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_parqueos_vehiculos FOREIGN KEY (id_vehiculo)
    REFERENCES tbl_vehiculos(id_vehiculo)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_parqueos_espacios FOREIGN KEY (id_espacio)
    REFERENCES tbl_espacios(id_espacio)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_parqueos_tarifas FOREIGN KEY (id_tarifa)
    REFERENCES tbl_tarifas(id_tarifa)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_parqueos_usuario_ingreso FOREIGN KEY (id_usuario_ingreso)
    REFERENCES tbl_usuarios(id_usuario)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_parqueos_usuario_salida FOREIGN KEY (id_usuario_salida)
    REFERENCES tbl_usuarios(id_usuario)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Eventos de ingreso y salida de vehiculos.';

CREATE TABLE tbl_pagos (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_parqueo INT NOT NULL,
  numero_recibo VARCHAR(30) NOT NULL,
  fecha_pago DATETIME NOT NULL,
  metodo_pago ENUM('efectivo','nequi','daviplata','tarjeta','pasarela') NOT NULL,
  valor_pagado DECIMAL(10,2) NOT NULL,
  id_usuario INT NOT NULL,
  CONSTRAINT uk_pagos_parqueo UNIQUE (id_parqueo),
  CONSTRAINT uk_pagos_numero_recibo UNIQUE (numero_recibo),
  CONSTRAINT fk_pagos_parqueos FOREIGN KEY (id_parqueo)
    REFERENCES tbl_parqueos(id_parqueo)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_usuarios FOREIGN KEY (id_usuario)
    REFERENCES tbl_usuarios(id_usuario)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Pagos y recibos unicos asociados a parqueos finalizados.';

CREATE INDEX idx_vehiculos_cliente ON tbl_vehiculos(id_cliente);
CREATE INDEX idx_vehiculos_tipo ON tbl_vehiculos(tipo);
CREATE INDEX idx_espacios_estado_tipo ON tbl_espacios(estado, tipo_vehiculo);
CREATE INDEX idx_parqueos_estado ON tbl_parqueos(estado);
CREATE INDEX idx_parqueos_fechas ON tbl_parqueos(fecha_ingreso, fecha_salida);
CREATE INDEX idx_parqueos_reserva_expira ON tbl_parqueos(reserva_expira_en);
CREATE INDEX idx_parqueos_cliente ON tbl_parqueos(id_cliente);
CREATE INDEX idx_parqueos_vehiculo ON tbl_parqueos(id_vehiculo);
CREATE INDEX idx_pagos_fecha ON tbl_pagos(fecha_pago);

-- Triggers y vistas omitidos para hosting gratuito. La logica equivalente esta implementada en PHP.

INSERT INTO tbl_usuarios (nombre, email, password, rol) VALUES
('Administrador SMARTPARK', 'admin@smartpark.com', '$2y$12$0AkTV5AdbQvDJv09sbDEEur6COcp36zO1emKLTE4AgmdChfh2R/S6', 'Administrador'),
('Caja SMARTPARK', 'caja@smartpark.com', '$2y$12$/j9J1IwYQw/6hkJdqzDiweQAtDB5PJcmK46Wxf/f8IubU1vhtRJSu', 'Caja'),
('Control SMARTPARK', 'control@smartpark.com', '$2y$12$aIvmM2W64nBu/XyZy2UxsewoLBDSr3jlMAcLKHI9UdbnzQMudZ1/W', 'Control');

INSERT INTO tbl_tarifas (tipo_vehiculo, valor_hora) VALUES
('auto', 3500.00),
('moto', 1800.00);

INSERT INTO tbl_espacios (codigo, piso, tipo_vehiculo) VALUES
('P1-A01', 1, 'auto'), ('P1-A02', 1, 'auto'), ('P1-A03', 1, 'auto'), ('P1-A04', 1, 'auto'), ('P1-A05', 1, 'auto'),
('P1-A06', 1, 'auto'), ('P1-A07', 1, 'auto'), ('P1-A08', 1, 'auto'), ('P1-A09', 1, 'auto'), ('P1-A10', 1, 'auto'),
('P1-M01', 1, 'moto'), ('P1-M02', 1, 'moto'), ('P1-M03', 1, 'moto'), ('P1-M04', 1, 'moto'), ('P1-M05', 1, 'moto'),
('P2-A01', 2, 'auto'), ('P2-A02', 2, 'auto'), ('P2-A03', 2, 'auto'), ('P2-A04', 2, 'auto'), ('P2-A05', 2, 'auto'),
('P2-A06', 2, 'auto'), ('P2-A07', 2, 'auto'), ('P2-A08', 2, 'auto'), ('P2-A09', 2, 'auto'), ('P2-A10', 2, 'auto'),
('P2-A11', 2, 'auto'), ('P2-A12', 2, 'auto'), ('P2-A13', 2, 'auto'), ('P2-A14', 2, 'auto'), ('P2-A15', 2, 'auto'),
('P2-A16', 2, 'auto'), ('P2-A17', 2, 'auto'), ('P2-A18', 2, 'auto'), ('P2-A19', 2, 'auto'), ('P2-A20', 2, 'auto'),
('P2-M01', 2, 'moto'), ('P2-M02', 2, 'moto'), ('P2-M03', 2, 'moto'), ('P2-M04', 2, 'moto'), ('P2-M05', 2, 'moto'),
('P2-M06', 2, 'moto'), ('P2-M07', 2, 'moto'), ('P2-M08', 2, 'moto'), ('P2-M09', 2, 'moto'), ('P2-M10', 2, 'moto'),
('P2-M11', 2, 'moto'), ('P2-M12', 2, 'moto'), ('P2-M13', 2, 'moto'), ('P2-M14', 2, 'moto'), ('P2-M15', 2, 'moto');
