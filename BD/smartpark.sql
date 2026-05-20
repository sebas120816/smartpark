-- SMARTPARK - Parqueadero Zona Segura ECCI
-- Modelo relacional normalizado para Bases de Datos 2.
-- Numero de tablas del dominio: 7.
-- Motor recomendado: MariaDB/MySQL con InnoDB.

CREATE DATABASE IF NOT EXISTS smartpark
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE smartpark;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW IF EXISTS vw_historial_parqueos;
DROP VIEW IF EXISTS vw_ocupacion_espacios;
DROP VIEW IF EXISTS vw_ingresos_diarios;
DROP TRIGGER IF EXISTS trg_parqueo_validar_ingreso;
DROP TRIGGER IF EXISTS trg_parqueo_ingreso_ocupa_espacio;
DROP TRIGGER IF EXISTS trg_pago_libera_espacio;
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
  CONSTRAINT uk_usuarios_email UNIQUE (email),
  CONSTRAINT chk_usuarios_estado CHECK (estado IN (0, 1)),
  CONSTRAINT chk_usuarios_email CHECK (email LIKE '%_@_%._%')
) ENGINE=InnoDB COMMENT='Usuarios internos del sistema con roles de Administrador, Caja y Control.';

CREATE TABLE tbl_clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  cedula VARCHAR(30) NOT NULL,
  nombres VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  correo VARCHAR(120) DEFAULT NULL,
  tipo_cliente ENUM('estudiante','docente','funcionario','visitante') NOT NULL DEFAULT 'visitante',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_clientes_cedula UNIQUE (cedula),
  CONSTRAINT chk_clientes_correo CHECK (correo IS NULL OR correo LIKE '%_@_%._%')
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
  CONSTRAINT uk_espacios_codigo UNIQUE (codigo),
  CONSTRAINT chk_espacios_piso CHECK (piso BETWEEN 1 AND 2)
) ENGINE=InnoDB COMMENT='Espacios fisicos del parqueadero distribuidos en dos pisos.';

CREATE TABLE tbl_tarifas (
  id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
  tipo_vehiculo ENUM('auto','moto') NOT NULL,
  valor_hora DECIMAL(10,2) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uk_tarifas_tipo UNIQUE (tipo_vehiculo),
  CONSTRAINT chk_tarifas_valor CHECK (valor_hora > 0),
  CONSTRAINT chk_tarifas_estado CHECK (estado IN (0, 1))
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
    ON DELETE RESTRICT,
  CONSTRAINT chk_parqueos_horas CHECK (total_horas IS NULL OR total_horas >= 1),
  CONSTRAINT chk_parqueos_valor CHECK (valor_total IS NULL OR valor_total >= 0),
  CONSTRAINT chk_parqueos_tarifa CHECK (tarifa_hora_aplicada > 0),
  CONSTRAINT chk_parqueos_salida CHECK (
    (estado = 'espera' AND id_espacio IS NULL AND reserva_expira_en IS NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado IN ('reservado','vencido') AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado = 'cancelado' AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado = 'activo' AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL AND id_usuario_ingreso IS NOT NULL)
    OR
    (estado = 'finalizado' AND id_espacio IS NOT NULL AND fecha_salida IS NOT NULL AND hora_salida IS NOT NULL AND id_usuario_salida IS NOT NULL)
  )
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
    ON DELETE RESTRICT,
  CONSTRAINT chk_pagos_valor CHECK (valor_pagado > 0)
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

DELIMITER //

CREATE TRIGGER trg_parqueo_validar_ingreso
BEFORE INSERT ON tbl_parqueos
FOR EACH ROW
BEGIN
  DECLARE v_espacio_bloqueado INT DEFAULT 0;
  DECLARE v_vehiculo_activo INT DEFAULT 0;
  DECLARE v_tipo_vehiculo VARCHAR(10);
  DECLARE v_tipo_espacio VARCHAR(10);
  DECLARE v_cliente_vehiculo INT;

  SELECT COUNT(*)
  INTO v_espacio_bloqueado
  FROM tbl_espacios
  WHERE id_espacio = NEW.id_espacio AND estado IN ('reservado', 'ocupado');

  IF v_espacio_bloqueado > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El espacio ya se encuentra reservado u ocupado.';
  END IF;

  SELECT COUNT(*)
  INTO v_vehiculo_activo
  FROM tbl_parqueos
  WHERE id_vehiculo = NEW.id_vehiculo AND estado IN ('espera', 'reservado', 'activo');

  IF v_vehiculo_activo > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehiculo ya tiene una solicitud, reserva o parqueo activo.';
  END IF;

  SELECT id_cliente, tipo
  INTO v_cliente_vehiculo, v_tipo_vehiculo
  FROM tbl_vehiculos
  WHERE id_vehiculo = NEW.id_vehiculo;

  IF NEW.id_cliente <> v_cliente_vehiculo THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El cliente no corresponde al vehiculo.';
  END IF;

  IF NEW.estado = 'espera' THEN
    IF NEW.id_espacio IS NOT NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una solicitud en espera no debe tener espacio asignado.';
    END IF;
  ELSE
    SELECT tipo_vehiculo
    INTO v_tipo_espacio
    FROM tbl_espacios
    WHERE id_espacio = NEW.id_espacio;

    IF v_tipo_vehiculo <> v_tipo_espacio THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El tipo de vehiculo no corresponde al tipo de espacio.';
    END IF;

    IF NEW.estado = 'reservado' AND NEW.reserva_expira_en IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La reserva debe tener fecha y hora de expiracion.';
    END IF;
  END IF;
END//

CREATE TRIGGER trg_parqueo_ingreso_ocupa_espacio
AFTER INSERT ON tbl_parqueos
FOR EACH ROW
BEGIN
  IF NEW.id_espacio IS NOT NULL THEN
    UPDATE tbl_espacios
    SET estado = CASE
      WHEN NEW.estado = 'reservado' THEN 'reservado'
      ELSE 'ocupado'
    END
    WHERE id_espacio = NEW.id_espacio;
  END IF;
END//

CREATE TRIGGER trg_pago_libera_espacio
AFTER INSERT ON tbl_pagos
FOR EACH ROW
BEGIN
  UPDATE tbl_espacios e
  INNER JOIN tbl_parqueos p ON p.id_espacio = e.id_espacio
  SET e.estado = 'libre'
  WHERE p.id_parqueo = NEW.id_parqueo;
END//

DELIMITER ;

CREATE VIEW vw_ocupacion_espacios AS
SELECT
  piso,
  tipo_vehiculo,
  estado,
  COUNT(*) AS total_espacios
FROM tbl_espacios
GROUP BY piso, tipo_vehiculo, estado;

CREATE VIEW vw_historial_parqueos AS
SELECT
  p.id_parqueo,
  pg.numero_recibo,
  p.codigo_reserva,
  c.cedula,
  c.nombres AS cliente,
  c.tipo_cliente,
  v.placa,
  v.tipo AS tipo_vehiculo,
  e.codigo AS espacio,
  p.fecha_ingreso,
  p.hora_ingreso,
  p.fecha_salida,
  p.hora_salida,
  p.total_horas,
  p.tarifa_hora_aplicada,
  p.valor_total,
  p.reserva_expira_en,
  pg.metodo_pago,
  p.estado
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
LEFT JOIN tbl_espacios e ON e.id_espacio = p.id_espacio
LEFT JOIN tbl_pagos pg ON pg.id_parqueo = p.id_parqueo;

CREATE VIEW vw_ingresos_diarios AS
SELECT
  DATE(fecha_pago) AS fecha,
  COUNT(*) AS cantidad_pagos,
  SUM(valor_pagado) AS total_recaudado
FROM tbl_pagos
GROUP BY DATE(fecha_pago);

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
