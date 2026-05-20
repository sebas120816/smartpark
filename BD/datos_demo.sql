USE if0_41912825_smartpark;

-- Datos demo SMARTPARK.
-- Reejecutable: elimina primero los registros demo marcados con cedulas DEMO-* y codigos DM-*.
-- Objetivo: mostrar IA, reportes, CO2, horas pico, reservas, lista de espera y pagos.

SET FOREIGN_KEY_CHECKS = 0;

DELETE pg
FROM tbl_pagos pg
INNER JOIN tbl_parqueos p ON p.id_parqueo = pg.id_parqueo
WHERE p.codigo_reserva LIKE 'DM-%';

DELETE FROM tbl_parqueos WHERE codigo_reserva LIKE 'DM-%';

UPDATE tbl_espacios
SET estado = 'libre'
WHERE codigo IN (
  'P1-A01','P1-A02','P1-A03','P1-A04','P1-A05','P1-A06','P1-A07','P1-A08',
  'P1-M01','P1-M02','P1-M03','P2-A01','P2-A02','P2-A03','P2-A04','P2-A05',
  'P2-M01','P2-M02','P2-M03','P2-M04'
);

DELETE v
FROM tbl_vehiculos v
INNER JOIN tbl_clientes c ON c.id_cliente = v.id_cliente
WHERE c.cedula LIKE 'DEMO-%';

DELETE FROM tbl_clientes WHERE cedula LIKE 'DEMO-%';

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO tbl_clientes (cedula, nombres, telefono, correo, tipo_cliente) VALUES
('DEMO-1001', 'Laura Gomez', '3001112233', 'laura.gomez@ecci.edu.co', 'estudiante'),
('DEMO-1002', 'Carlos Mendez', '3102223344', 'carlos.mendez@ecci.edu.co', 'docente'),
('DEMO-1003', 'Diana Perez', '3203334455', 'diana.perez@ecci.edu.co', 'funcionario'),
('DEMO-1004', 'Andres Rojas', '3014445566', 'andres.rojas@ecci.edu.co', 'estudiante'),
('DEMO-1005', 'Paula Torres', '3025556677', 'paula.torres@ecci.edu.co', 'docente'),
('DEMO-1006', 'Miguel Castro', '3036667788', 'miguel.castro@ecci.edu.co', 'visitante'),
('DEMO-1007', 'Natalia Ruiz', '3047778899', 'natalia.ruiz@ecci.edu.co', 'funcionario'),
('DEMO-1008', 'Johan Rojas', '3058889900', 'johan.rojas@ecci.edu.co', 'estudiante'),
('DEMO-1009', 'Harold Acosta', '3069990011', 'harold.acosta@ecci.edu.co', 'estudiante'),
('DEMO-1010', 'Joseph Sebastian', '3070001122', 'joseph.sebastian@ecci.edu.co', 'estudiante'),
('DEMO-1011', 'Marcela Nieto', '3081112233', 'marcela.nieto@ecci.edu.co', 'docente'),
('DEMO-1012', 'Oscar Salazar', '3092223344', 'oscar.salazar@ecci.edu.co', 'visitante');

INSERT INTO tbl_vehiculos (id_cliente, placa, marca, modelo, color, tipo) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1001'), 'DMO101', 'Chevrolet', 'Spark', 'Rojo', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1002'), 'DMO202', 'Yamaha', 'FZ', 'Negro', 'moto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1003'), 'DMO303', 'Renault', 'Logan', 'Gris', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1004'), 'DMO404', 'Kia', 'Picanto', 'Azul', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1005'), 'DMO505', 'Suzuki', 'Gixxer', 'Blanco', 'moto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1006'), 'DMO606', 'Mazda', '2', 'Plata', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1007'), 'DMO707', 'Honda', 'CB 160', 'Rojo', 'moto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1008'), 'DMO808', 'Toyota', 'Corolla', 'Negro', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1009'), 'DMO909', 'AKT', 'NKD', 'Verde', 'moto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1010'), 'DMO010', 'Nissan', 'Versa', 'Blanco', 'auto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1011'), 'DMO111', 'Bajaj', 'Pulsar', 'Azul', 'moto'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1012'), 'DMO212', 'Ford', 'Fiesta', 'Gris', 'auto');

SELECT id_tarifa INTO @tarifa_auto FROM tbl_tarifas WHERE tipo_vehiculo='auto';
SELECT id_tarifa INTO @tarifa_moto FROM tbl_tarifas WHERE tipo_vehiculo='moto';
SELECT id_espacio INTO @p1_a01 FROM tbl_espacios WHERE codigo='P1-A01';
SELECT id_espacio INTO @p1_a02 FROM tbl_espacios WHERE codigo='P1-A02';
SELECT id_espacio INTO @p1_m01 FROM tbl_espacios WHERE codigo='P1-M01';
SELECT id_espacio INTO @p2_a01 FROM tbl_espacios WHERE codigo='P2-A01';
SELECT id_espacio INTO @p2_a02 FROM tbl_espacios WHERE codigo='P2-A02';
SELECT id_espacio INTO @p2_a03 FROM tbl_espacios WHERE codigo='P2-A03';
SELECT id_espacio INTO @p2_a04 FROM tbl_espacios WHERE codigo='P2-A04';
SELECT id_espacio INTO @p2_m01 FROM tbl_espacios WHERE codigo='P2-M01';
SELECT id_espacio INTO @p2_m02 FROM tbl_espacios WHERE codigo='P2-M02';
SELECT id_espacio INTO @p2_m03 FROM tbl_espacios WHERE codigo='P2-M03';
SELECT id_espacio INTO @p2_m04 FROM tbl_espacios WHERE codigo='P2-M04';

-- Historial finalizado con pagos para reportes financieros, CO2 y horas pico.
INSERT INTO tbl_parqueos (
  id_cliente, id_vehiculo, id_espacio, id_tarifa, id_usuario_ingreso, id_usuario_salida,
  fecha_ingreso, hora_ingreso, fecha_salida, hora_salida, tarifa_hora_aplicada,
  total_horas, valor_total, codigo_reserva, estado
) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1001'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO101'), @p1_a01, @tarifa_auto, 1, 2, CURDATE() - INTERVAL 6 DAY, '07:15:00', CURDATE() - INTERVAL 6 DAY, '10:05:00', 3500, 3, 10500, 'DM-FIN-001', 'finalizado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1002'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO202'), @p1_m01, @tarifa_moto, 1, 2, CURDATE() - INTERVAL 5 DAY, '08:10:00', CURDATE() - INTERVAL 5 DAY, '11:20:00', 1800, 4, 7200, 'DM-FIN-002', 'finalizado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1003'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO303'), @p1_a02, @tarifa_auto, 1, 2, CURDATE() - INTERVAL 4 DAY, '17:35:00', CURDATE() - INTERVAL 4 DAY, '20:15:00', 3500, 3, 10500, 'DM-FIN-003', 'finalizado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1004'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO404'), @p2_a01, @tarifa_auto, 1, 2, CURDATE() - INTERVAL 3 DAY, '18:05:00', CURDATE() - INTERVAL 3 DAY, '21:10:00', 3500, 4, 14000, 'DM-FIN-004', 'finalizado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1005'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO505'), @p2_m01, @tarifa_moto, 1, 2, CURDATE() - INTERVAL 2 DAY, '07:45:00', CURDATE() - INTERVAL 2 DAY, '09:00:00', 1800, 2, 3600, 'DM-FIN-005', 'finalizado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1006'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO606'), @p2_a02, @tarifa_auto, 1, 2, CURDATE() - INTERVAL 1 DAY, '08:30:00', CURDATE() - INTERVAL 1 DAY, '12:05:00', 3500, 4, 14000, 'DM-FIN-006', 'finalizado');

INSERT INTO tbl_pagos (id_parqueo, numero_recibo, fecha_pago, metodo_pago, valor_pagado, id_usuario) VALUES
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-001'), 'DM-RC-001', CONCAT(CURDATE() - INTERVAL 6 DAY, ' 10:05:00'), 'efectivo', 10500, 2),
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-002'), 'DM-RC-002', CONCAT(CURDATE() - INTERVAL 5 DAY, ' 11:20:00'), 'efectivo', 7200, 2),
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-003'), 'DM-RC-003', CONCAT(CURDATE() - INTERVAL 4 DAY, ' 20:15:00'), 'daviplata', 10500, 2),
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-004'), 'DM-RC-004', CONCAT(CURDATE() - INTERVAL 3 DAY, ' 21:10:00'), 'efectivo', 14000, 2),
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-005'), 'DM-RC-005', CONCAT(CURDATE() - INTERVAL 2 DAY, ' 09:00:00'), 'efectivo', 3600, 2),
((SELECT id_parqueo FROM tbl_parqueos WHERE codigo_reserva='DM-FIN-006'), 'DM-RC-006', CONCAT(CURDATE() - INTERVAL 1 DAY, ' 12:05:00'), 'daviplata', 14000, 2);

-- Reservas activas, parqueos activos, vencidos y lista de espera.
INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, reserva_expira_en, codigo_reserva, estado) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1007'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO707'), @p2_m02, @tarifa_moto, CURDATE(), CURTIME(), 1800, DATE_ADD(NOW(), INTERVAL 12 MINUTE), 'DM-RS-001', 'reservado'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1008'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO808'), @p2_a03, @tarifa_auto, CURDATE(), CURTIME(), 3500, DATE_ADD(NOW(), INTERVAL 7 MINUTE), 'DM-RS-002', 'reservado');

INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, id_usuario_ingreso, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, codigo_reserva, estado) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1009'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO909'), @p2_m03, @tarifa_moto, 3, CURDATE(), '07:55:00', 1800, 'DM-ACT-001', 'activo'),
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1010'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO010'), @p2_a04, @tarifa_auto, 3, CURDATE(), '08:20:00', 3500, 'DM-ACT-002', 'activo');

INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, reserva_expira_en, codigo_reserva, estado) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1011'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO111'), @p2_m04, @tarifa_moto, CURDATE(), '06:40:00', 1800, DATE_SUB(NOW(), INTERVAL 20 MINUTE), 'DM-VEN-001', 'vencido');

INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, codigo_reserva, estado) VALUES
((SELECT id_cliente FROM tbl_clientes WHERE cedula='DEMO-1012'), (SELECT id_vehiculo FROM tbl_vehiculos WHERE placa='DMO212'), NULL, @tarifa_auto, CURDATE(), CURTIME(), 3500, 'DM-LE-001', 'espera');
