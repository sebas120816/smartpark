USE if0_41912825_smartpark;

ALTER TABLE tbl_parqueos
  ADD COLUMN IF NOT EXISTS token_publico VARCHAR(80) DEFAULT NULL AFTER codigo_reserva,
  ADD COLUMN IF NOT EXISTS cancelado_en DATETIME DEFAULT NULL AFTER token_publico,
  ADD COLUMN IF NOT EXISTS motivo_cancelacion VARCHAR(180) DEFAULT NULL AFTER cancelado_en;

UPDATE tbl_parqueos
SET token_publico = CONCAT('tk_', SHA2(CONCAT(id_parqueo, codigo_reserva, creado_en), 256))
WHERE token_publico IS NULL AND codigo_reserva IS NOT NULL;

SET @idx_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'tbl_parqueos'
    AND index_name = 'uk_parqueos_token_publico'
);

SET @sql_idx := IF(@idx_exists = 0,
  'CREATE UNIQUE INDEX uk_parqueos_token_publico ON tbl_parqueos(token_publico)',
  'SELECT "uk_parqueos_token_publico existe"'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

ALTER TABLE tbl_parqueos DROP CONSTRAINT chk_parqueos_salida;

ALTER TABLE tbl_parqueos
  ADD CONSTRAINT chk_parqueos_salida CHECK (
    (estado = 'espera' AND id_espacio IS NULL AND reserva_expira_en IS NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado IN ('reservado','vencido') AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado = 'cancelado' AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL)
    OR
    (estado = 'activo' AND id_espacio IS NOT NULL AND fecha_salida IS NULL AND hora_salida IS NULL AND id_usuario_salida IS NULL AND id_usuario_ingreso IS NOT NULL)
    OR
    (estado = 'finalizado' AND id_espacio IS NOT NULL AND fecha_salida IS NOT NULL AND hora_salida IS NOT NULL AND id_usuario_salida IS NOT NULL)
  );
