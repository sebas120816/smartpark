-- Procedimientos, función y ejemplos de transacción para SMARTPARK

DROP FUNCTION IF EXISTS fn_calcular_total_parqueo;
DELIMITER //
CREATE FUNCTION fn_calcular_total_parqueo(
    p_total_horas INT,
    p_valor_hora DECIMAL(10,2)
)
RETURNS DECIMAL(10,2)
DETERMINISTIC
RETURN p_total_horas * p_valor_hora;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_registrar_pago;
DELIMITER //
CREATE PROCEDURE sp_registrar_pago(
    IN p_id_parqueo INT,
    IN p_numero_recibo VARCHAR(30),
    IN p_metodo_pago VARCHAR(20),
    IN p_valor_pagado DECIMAL(10,2),
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_id_espacio INT;

    START TRANSACTION;

    INSERT INTO tbl_pagos (
        id_parqueo,
        numero_recibo,
        fecha_pago,
        metodo_pago,
        valor_pagado,
        id_usuario
    ) VALUES (
        p_id_parqueo,
        p_numero_recibo,
        NOW(),
        p_metodo_pago,
        p_valor_pagado,
        p_id_usuario
    );

    SELECT id_espacio
    INTO v_id_espacio
    FROM tbl_parqueos
    WHERE id_parqueo = p_id_parqueo
    LIMIT 1;

    UPDATE tbl_parqueos
    SET estado = 'finalizado',
        fecha_salida = CURDATE(),
        hora_salida = CURTIME(),
        valor_total = p_valor_pagado
    WHERE id_parqueo = p_id_parqueo;

    UPDATE tbl_espacios
    SET estado = 'libre'
    WHERE id_espacio = v_id_espacio;

    COMMIT;
END;
//
DELIMITER ;

-- Ejemplo de transacción manual de respaldo
-- START TRANSACTION;
-- UPDATE tbl_espacios SET estado='ocupado' WHERE id_espacio=1;
-- UPDATE tbl_parqueos SET estado='activo' WHERE id_parqueo=1;
-- ROLLBACK; -- o COMMIT;
