<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

include_once(__DIR__ . '/../../config/config.php');

function api_fail($message)
{
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function api_query($con, $sql)
{
    $result = mysqli_query($con, $sql);
    if (!$result) {
        api_fail(mysqli_error($con));
    }
    return $result;
}

$statsResult = api_query($con, "SELECT
    COUNT(*) AS espacios,
    SUM(CASE WHEN estado='libre' THEN 1 ELSE 0 END) AS libres,
    SUM(CASE WHEN estado='ocupado' THEN 1 ELSE 0 END) AS ocupados,
    SUM(CASE WHEN estado='reservado' THEN 1 ELSE 0 END) AS reservados
FROM tbl_espacios");
$stats = mysqli_fetch_assoc($statsResult);
$total = max(1, (int)$stats['espacios']);
$ocupados = (int)$stats['ocupados'];
$reservados = (int)$stats['reservados'];
$stats['ocupacion'] = round((($ocupados + $reservados) / $total) * 100);
$stats['riesgo'] = $stats['ocupacion'] >= 85 ? 'Alto' : ($stats['ocupacion'] >= 60 ? 'Medio' : 'Bajo');

$activosResult = api_query($con, "SELECT COUNT(*) AS total FROM tbl_parqueos WHERE estado='activo'");
$activos = mysqli_fetch_assoc($activosResult);
$stats['activos'] = $activos ? (int)$activos['total'] : 0;

$ingresosResult = api_query($con, "SELECT COALESCE(SUM(valor_pagado), 0) AS total FROM tbl_pagos WHERE DATE(fecha_pago)=CURDATE()");
$ingresos = mysqli_fetch_assoc($ingresosResult);
$stats['ingresos_hoy'] = $ingresos ? (float)$ingresos['total'] : 0;

$pisos = [];
$pisosResult = api_query($con, "SELECT
    piso,
    COUNT(*) AS total,
    SUM(CASE WHEN estado='libre' THEN 1 ELSE 0 END) AS libres,
    SUM(CASE WHEN estado='ocupado' THEN 1 ELSE 0 END) AS ocupados,
    SUM(CASE WHEN estado='reservado' THEN 1 ELSE 0 END) AS reservados
FROM tbl_espacios
GROUP BY piso
ORDER BY piso ASC");
while ($row = mysqli_fetch_assoc($pisosResult)) {
    $rowTotal = max(1, (int)$row['total']);
    $row['uso'] = round((((int)$row['ocupados'] + (int)$row['reservados']) / $rowTotal) * 100);
    $pisos[] = $row;
}

$espacios = [];
$espaciosResult = api_query($con, "SELECT id_espacio, codigo, piso, tipo_vehiculo, estado
FROM tbl_espacios
ORDER BY piso ASC, codigo ASC");
while ($row = mysqli_fetch_assoc($espaciosResult)) {
    $espacios[] = $row;
}

$movimientos = [];
$movimientosResult = api_query($con, "SELECT p.fecha_ingreso, p.hora_ingreso, c.nombres, v.placa, v.tipo, e.codigo
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
ORDER BY p.creado_en DESC
LIMIT 6");
while ($row = mysqli_fetch_assoc($movimientosResult)) {
    $movimientos[] = $row;
}

$parqueosActivos = [];
$activosListadoResult = api_query($con, "SELECT p.fecha_ingreso, p.hora_ingreso, c.nombres, v.placa, v.tipo, e.codigo
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
WHERE p.estado='activo'
ORDER BY p.creado_en DESC
LIMIT 8");
while ($row = mysqli_fetch_assoc($activosListadoResult)) {
    $parqueosActivos[] = $row;
}

echo json_encode([
    'ok' => true,
    'actualizado' => date('d/m/Y h:i A'),
    'stats' => $stats,
    'pisos' => $pisos,
    'espacios' => $espacios,
    'movimientos' => $movimientos,
    'parqueos_activos' => $parqueosActivos,
], JSON_UNESCAPED_UNICODE);
