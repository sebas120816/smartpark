-- SMARTPARK - Migracion para lista de espera sin crear tabla adicional.
-- Mantiene el modelo academico de 7 tablas usando tbl_parqueos.estado='espera'.

USE smartpark;

DROP VIEW IF EXISTS vw_historial_parqueos;
DROP TRIGGER IF EXISTS trg_parqueo_validar_ingreso;
DROP TRIGGER IF EXISTS trg_parqueo_ingreso_ocupa_espacio;

ALTER TABLE tbl_parqueos DROP CONSTRAINT chk_parqueos_salida;
ALTER TABLE tbl_parqueos MODIFY id_espacio INT NULL;
ALTER TABLE tbl_parqueos MODIFY estado ENUM('espera','reservado','activo','finalizado','vencido','cancelado') NOT NULL DEFAULT 'activo';
ALTER TABLE tbl_parqueos ADD CONSTRAINT chk_parqueos_salida CHECK (
  (estado = 'espera' AND id_espacio IS NULL AND reserva_expira_en IS NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
  OR
  (estado IN ('reservado','vencido','cancelado') AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
  OR
  (estado = 'activo' AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL AND id_usuario_ingreso IS NOT NULL)
  OR
  (estado = 'finalizado' AND id_espacio IS NOT NULL AND fecha_salida IS NOT NULL AND hora_salida IS NOT NULL AND id_usuario_salida IS NOT NULL)
);

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
    SELECT COUNT(*)
    INTO v_espacio_bloqueado
    FROM tbl_espacios
    WHERE id_espacio = NEW.id_espacio AND estado IN ('reservado', 'ocupado');

    IF v_espacio_bloqueado > 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El espacio ya se encuentra reservado u ocupado.';
    END IF;

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

DELIMITER ;

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
