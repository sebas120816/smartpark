USE smartpark;

-- 1. Consultar espacios disponibles por piso y tipo de vehiculo.
SELECT piso, tipo_vehiculo, COUNT(*) AS espacios_libres
FROM tbl_espacios
WHERE estado = 'libre'
GROUP BY piso, tipo_vehiculo;

-- 2. Historial de parqueos por placa.
SELECT *
FROM vw_historial_parqueos
WHERE placa = 'ABC123'
ORDER BY fecha_ingreso DESC, hora_ingreso DESC;

-- 3. Historial de parqueos por cliente.
SELECT *
FROM vw_historial_parqueos
WHERE cedula = '1000000001'
ORDER BY fecha_ingreso DESC, hora_ingreso DESC;

-- 4. Ingresos diarios.
SELECT *
FROM vw_ingresos_diarios
ORDER BY fecha DESC;

-- 5. Vehiculos actualmente dentro del parqueadero.
SELECT
  c.nombres AS cliente,
  c.cedula,
  v.placa,
  v.tipo,
  e.codigo AS espacio,
  p.fecha_ingreso,
  p.hora_ingreso
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
INNER JOIN tbl_espacios e ON e.id_espacio = p.id_espacio
WHERE p.estado = 'activo';

-- 5B. Reservas vigentes con tiempo restante.
SELECT
  codigo_reserva,
  cliente,
  tipo_cliente,
  placa,
  tipo_vehiculo,
  espacio,
  reserva_expira_en,
  TIMESTAMPDIFF(MINUTE, NOW(), reserva_expira_en) AS minutos_restantes
FROM vw_historial_parqueos
WHERE estado = 'reservado'
ORDER BY reserva_expira_en ASC;

-- 6. Total recaudado por metodo de pago.
SELECT metodo_pago, COUNT(*) AS pagos, SUM(valor_pagado) AS total
FROM tbl_pagos
GROUP BY metodo_pago;

-- 7. Ocupacion general del parqueadero.
SELECT *
FROM vw_ocupacion_espacios
ORDER BY piso, tipo_vehiculo, estado;

-- 8. Top clientes por cantidad de parqueos.
SELECT c.cedula, c.nombres, COUNT(*) AS total_parqueos
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
GROUP BY c.id_cliente, c.cedula, c.nombres
ORDER BY total_parqueos DESC;

-- 9. Reservas vencidas por tipo de usuario.
SELECT c.tipo_cliente, COUNT(*) AS reservas_vencidas
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
WHERE p.estado = 'vencido'
GROUP BY c.tipo_cliente;

-- 10. Promedio de permanencia por tipo de vehiculo.
SELECT v.tipo, AVG(p.total_horas) AS promedio_horas
FROM tbl_parqueos p
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
WHERE p.estado = 'finalizado'
GROUP BY v.tipo;

-- 11. Espacios con mayor uso historico.
SELECT e.codigo, e.piso, e.tipo_vehiculo, COUNT(*) AS usos
FROM tbl_parqueos p
INNER JOIN tbl_espacios e ON e.id_espacio = p.id_espacio
GROUP BY e.id_espacio, e.codigo, e.piso, e.tipo_vehiculo
ORDER BY usos DESC;

-- 12. Validar que no existan vehiculos con mas de una solicitud/reserva/parqueo activo.
SELECT id_vehiculo, COUNT(*) AS bloqueos_activos
FROM tbl_parqueos
WHERE estado IN ('espera', 'reservado', 'activo')
GROUP BY id_vehiculo
HAVING COUNT(*) > 1;

-- 13. Lista de espera inteligente por orden de llegada.
SELECT
  p.codigo_reserva,
  c.cedula,
  c.nombres,
  c.tipo_cliente,
  v.placa,
  v.tipo,
  TIMESTAMPDIFF(MINUTE, p.creado_en, NOW()) AS minutos_espera
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
WHERE p.estado = 'espera'
ORDER BY p.creado_en ASC;

-- 14. Ranking de horas pico de los ultimos 30 dias.
SELECT
  HOUR(hora_ingreso) AS hora,
  COUNT(*) AS movimientos,
  SUM(estado = 'reservado') AS reservas,
  SUM(estado = 'finalizado') AS finalizados
FROM tbl_parqueos
WHERE fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY HOUR(hora_ingreso)
ORDER BY movimientos DESC, hora ASC;

-- 15. Estimacion ambiental por reduccion de vueltas buscando cupo.
SELECT
  COUNT(*) AS eventos_digitales,
  COUNT(*) * 8 AS minutos_evitados,
  ROUND(COUNT(*) * 0.8, 1) AS km_evitados,
  ROUND((COUNT(*) * 0.8) * 0.21, 2) AS kg_co2_evitado
FROM tbl_parqueos
WHERE codigo_reserva IS NOT NULL OR estado IN ('activo', 'finalizado');
