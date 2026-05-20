<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once('../config/config.php');
date_default_timezone_set("America/Bogota");

function sp_db_error($con, $context)
{
    die("<div style='font-family: Arial, sans-serif; max-width: 760px; margin: 40px auto; padding: 24px; border: 1px solid #f1b0b7; background: #fff5f5; color: #721c24; border-radius: 8px;'>
        <h2 style='margin-top:0;'>SMARTPARK no pudo cargar</h2>
        <p><strong>Detalle:</strong> {$context}</p>
        <p><strong>MySQL:</strong> " . htmlspecialchars(mysqli_error($con), ENT_QUOTES, 'UTF-8') . "</p>
        <p>Revisa que hayas importado <code>BD/smartpark.sql</code> en phpMyAdmin y que la base activa se llame <code>smartpark</code>.</p>
    </div>");
}

function sp_query($con, $sql, $context)
{
    $result = mysqli_query($con, $sql);
    if (!$result) {
        sp_db_error($con, $context);
    }
    return $result;
}

function sp_prepare($con, $sql, $context)
{
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        sp_db_error($con, $context);
    }
    return $stmt;
}

function sp_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sp_config_path()
{
    return dirname(__DIR__) . '/config/smartpark_institucional.json';
}

function sp_config_institucional()
{
    $defaults = [
        'nombre_parqueadero' => 'Zona Segura ECCI',
        'universidad' => 'Universidad ECCI',
        'tiempo_reserva_minutos' => 15,
        'horario_atencion' => 'Lunes a sábado, 6:00 a.m. - 10:00 p.m.',
        'telefono_whatsapp' => '573001112233',
        'mensaje_portal' => 'Reserva, consulta y administra tu cupo desde SMARTPARK.',
    ];
    $path = sp_config_path();
    if (!file_exists($path)) {
        return $defaults;
    }
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

function sp_guardar_config_institucional($data)
{
    $config = [
        'nombre_parqueadero' => trim($data['nombre_parqueadero'] ?? 'Zona Segura ECCI'),
        'universidad' => trim($data['universidad'] ?? 'Universidad ECCI'),
        'tiempo_reserva_minutos' => max(5, min(60, (int)($data['tiempo_reserva_minutos'] ?? 15))),
        'horario_atencion' => trim($data['horario_atencion'] ?? ''),
        'telefono_whatsapp' => preg_replace('/\D+/', '', $data['telefono_whatsapp'] ?? ''),
        'mensaje_portal' => trim($data['mensaje_portal'] ?? ''),
    ];
    file_put_contents(sp_config_path(), json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, LOCK_EX);
}

function sp_generar_token_publico()
{
    return bin2hex(random_bytes(24));
}

function sp_role_allowed($roles)
{
    return isset($_SESSION['rol']) && in_array($_SESSION['rol'], $roles, true);
}

function sp_redirect($url)
{
    header("Location: {$url}");
    exit;
}

function sp_format_money($value)
{
    return '$' . number_format((float)$value, 0, ',', '.');
}

function sp_badge_estado($estado)
{
    $clases = [
        'libre' => 'badge-success',
        'reservado' => 'badge-warning',
        'ocupado' => 'badge-danger',
        'activo' => 'badge-danger',
        'finalizado' => 'badge-success',
        'vencido' => 'badge-secondary',
        'cancelado' => 'badge-dark',
        'espera' => 'badge-info',
    ];
    $class = $clases[$estado] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . sp_escape($estado) . '</span>';
}

function sp_badge_riesgo_no_show($riesgo)
{
    $riesgo = (float)$riesgo;
    if ($riesgo >= 50) {
        return '<span class="badge badge-danger">Alto ' . sp_escape($riesgo) . '%</span>';
    }
    if ($riesgo >= 25) {
        return '<span class="badge badge-warning">Medio ' . sp_escape($riesgo) . '%</span>';
    }
    return '<span class="badge badge-success">Bajo ' . sp_escape($riesgo) . '%</span>';
}

function sp_auditar($accion, $detalle = '')
{
    $usuario = $_SESSION['emailUser'] ?? 'publico';
    $linea = date('Y-m-d H:i:s') . " | {$usuario} | {$accion} | " . str_replace(["\r", "\n"], ' ', $detalle) . PHP_EOL;
    $archivo = dirname(__DIR__) . '/logs/auditoria.log';
    if (!is_dir(dirname($archivo))) {
        mkdir(dirname($archivo), 0775, true);
    }
    file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
}

function sp_auditoria_eventos($limite = 120)
{
    $archivo = dirname(__DIR__) . '/logs/auditoria.log';
    if (!file_exists($archivo)) {
        return [];
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lineas) {
        return [];
    }

    $lineas = array_slice(array_reverse($lineas), 0, max(1, (int)$limite));
    $eventos = [];

    foreach ($lineas as $linea) {
        $partes = array_map('trim', explode('|', $linea, 4));
        $eventos[] = [
            'fecha' => $partes[0] ?? '',
            'usuario' => $partes[1] ?? '',
            'accion' => $partes[2] ?? '',
            'detalle' => $partes[3] ?? '',
        ];
    }

    return $eventos;
}

function sp_expirar_reservas($con)
{
    $sql = "UPDATE tbl_parqueos p
        LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        SET p.estado='vencido', e.estado='libre'
        WHERE p.estado='reservado' AND p.reserva_expira_en < NOW()";
    sp_query($con, $sql, "No se pudieron vencer las reservas expiradas.");
    sp_asignar_lista_espera($con);
}

function sp_stats($con)
{
    $stats = [
        'espacios' => 0,
        'libres' => 0,
        'ocupados' => 0,
        'reservados' => 0,
        'espera' => 0,
        'activos' => 0,
        'ingresos_hoy' => 0,
    ];

    $queries = [
        'espacios' => "SELECT COUNT(*) total FROM tbl_espacios",
        'libres' => "SELECT COUNT(*) total FROM tbl_espacios WHERE estado='libre'",
        'ocupados' => "SELECT COUNT(*) total FROM tbl_espacios WHERE estado='ocupado'",
        'reservados' => "SELECT COUNT(*) total FROM tbl_espacios WHERE estado='reservado'",
        'espera' => "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='espera'",
        'activos' => "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='activo'",
        'ingresos_hoy' => "SELECT COALESCE(SUM(valor_pagado), 0) total FROM tbl_pagos WHERE DATE(fecha_pago)=CURDATE()",
    ];

    foreach ($queries as $key => $sql) {
        $result = sp_query($con, $sql, "No se pudieron consultar las estadísticas del panel.");
        $row = mysqli_fetch_assoc($result);
        $stats[$key] = $row ? $row['total'] : 0;
    }
    return $stats;
}

function sp_clientes($con)
{
    return sp_query($con, "SELECT * FROM tbl_clientes ORDER BY nombres ASC", "No se pudieron consultar los clientes.");
}

function sp_vehiculos($con)
{
    return sp_query($con, "SELECT v.*, c.nombres, c.cedula FROM tbl_vehiculos v INNER JOIN tbl_clientes c ON c.id_cliente=v.id_cliente ORDER BY v.placa ASC", "No se pudieron consultar los vehículos.");
}

function sp_vehiculos_disponibles($con)
{
    return sp_query($con, "SELECT v.*, c.nombres FROM tbl_vehiculos v INNER JOIN tbl_clientes c ON c.id_cliente=v.id_cliente WHERE NOT EXISTS (SELECT 1 FROM tbl_parqueos p WHERE p.id_vehiculo=v.id_vehiculo AND p.estado IN ('espera','reservado','activo')) ORDER BY v.placa ASC", "No se pudieron consultar los vehículos disponibles.");
}

function sp_espacios($con)
{
    return sp_query($con, "SELECT * FROM tbl_espacios ORDER BY piso ASC, codigo ASC", "No se pudieron consultar los espacios.");
}

function sp_espacios_detalle($con)
{
    return sp_query($con, "SELECT e.*,
            p.id_parqueo,
            p.estado AS estado_parqueo,
            p.codigo_reserva,
            p.reserva_expira_en,
            c.nombres,
            c.cedula,
            v.placa,
            v.tipo
        FROM tbl_espacios e
        LEFT JOIN tbl_parqueos p ON p.id_espacio=e.id_espacio AND p.estado IN ('reservado','activo')
        LEFT JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        LEFT JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        ORDER BY e.piso ASC, e.codigo ASC", "No se pudo consultar el mapa de espacios.");
}

function sp_espacio_recomendado($con, $tipo)
{
    $sql = "SELECT e.id_espacio, e.codigo, e.piso, e.tipo_vehiculo,
            piso_stats.total_piso,
            piso_stats.ocupados_piso,
            ROUND((piso_stats.ocupados_piso / piso_stats.total_piso) * 100, 1) ocupacion_piso
        FROM tbl_espacios e
        INNER JOIN (
            SELECT piso,
                COUNT(*) total_piso,
                SUM(estado IN ('reservado','ocupado')) ocupados_piso
            FROM tbl_espacios
            GROUP BY piso
        ) piso_stats ON piso_stats.piso=e.piso
        WHERE e.estado='libre' AND e.tipo_vehiculo=?
        ORDER BY ocupacion_piso ASC, e.piso ASC, e.codigo ASC
        LIMIT 1";
    $stmt = sp_prepare($con, $sql, "No se pudo preparar la recomendación de espacio.");
    mysqli_stmt_bind_param($stmt, "s", $tipo);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function sp_tarifas($con)
{
    return sp_query($con, "SELECT * FROM tbl_tarifas ORDER BY tipo_vehiculo ASC", "No se pudieron consultar las tarifas.");
}

function sp_parqueos_activos($con)
{
    return sp_query($con, "SELECT p.*, c.nombres, c.cedula, v.placa, v.tipo, e.codigo, e.piso
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.estado='activo'
        ORDER BY p.creado_en DESC", "No se pudieron consultar los parqueos activos.");
}

function sp_reservas_por_vencer($con, $limit = 5)
{
    $limit = (int)$limit;
    return sp_query($con, "SELECT p.*, c.nombres, c.cedula, c.tipo_cliente, v.placa, e.codigo
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.estado='reservado'
        ORDER BY p.reserva_expira_en ASC
        LIMIT {$limit}", "No se pudieron consultar las reservas por vencer.");
}

function sp_reservas_pendientes($con)
{
    return sp_query($con, "SELECT p.*, c.nombres, c.cedula, c.telefono, c.correo, c.tipo_cliente, v.placa, v.tipo, v.marca, v.modelo, e.codigo, e.piso,
            (SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.codigo_reserva IS NOT NULL) reservas_cliente,
            (SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.estado='vencido') reservas_vencidas_cliente,
            COALESCE(ROUND(((SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.estado='vencido') /
                NULLIF((SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.codigo_reserva IS NOT NULL), 0)) * 100, 1), 0) riesgo_no_show
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.estado='reservado'
        ORDER BY p.reserva_expira_en ASC", "No se pudieron consultar las reservas pendientes.");
}

function sp_reservas_recientes($con)
{
    return sp_query($con, "SELECT p.*, c.nombres, c.cedula, c.tipo_cliente, v.placa, v.tipo, e.codigo
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.estado IN ('reservado','espera','vencido','cancelado')
        ORDER BY p.creado_en DESC
        LIMIT 80", "No se pudieron consultar las reservas recientes.");
}

function sp_portal_usuario($con, $busqueda)
{
    $busqueda = trim($busqueda);
    $sqlCliente = "SELECT DISTINCT c.*
        FROM tbl_clientes c
        LEFT JOIN tbl_parqueos p ON p.id_cliente=c.id_cliente
        WHERE c.cedula=? OR p.codigo_reserva=? OR p.token_publico=?
        LIMIT 1";
    $stmt = sp_prepare($con, $sqlCliente, "No se pudo preparar el portal de usuario.");
    mysqli_stmt_bind_param($stmt, "sss", $busqueda, $busqueda, $busqueda);
    mysqli_stmt_execute($stmt);
    $cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$cliente) {
        return null;
    }

    $idCliente = (int)$cliente['id_cliente'];
    $stmtVehiculos = sp_prepare($con, "SELECT * FROM tbl_vehiculos WHERE id_cliente=? ORDER BY creado_en DESC", "No se pudieron preparar los vehículos del usuario.");
    mysqli_stmt_bind_param($stmtVehiculos, "i", $idCliente);
    mysqli_stmt_execute($stmtVehiculos);

    $stmtReservas = sp_prepare($con, "SELECT p.*, v.placa, v.tipo, v.marca, v.modelo, e.codigo AS espacio, e.piso
        FROM tbl_parqueos p
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.id_cliente=?
        ORDER BY FIELD(p.estado,'reservado','espera','activo','vencido','cancelado','finalizado'), p.creado_en DESC
        LIMIT 30", "No se pudieron preparar las reservas del usuario.");
    mysqli_stmt_bind_param($stmtReservas, "i", $idCliente);
    mysqli_stmt_execute($stmtReservas);

    return [
        'cliente' => $cliente,
        'vehiculos' => mysqli_stmt_get_result($stmtVehiculos),
        'reservas' => mysqli_stmt_get_result($stmtReservas),
    ];
}

function sp_lista_espera($con)
{
    return sp_query($con, "SELECT p.*, c.nombres, c.cedula, c.telefono, c.correo, c.tipo_cliente, v.placa, v.tipo, v.marca, v.modelo,
            TIMESTAMPDIFF(MINUTE, p.creado_en, NOW()) minutos_espera
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        WHERE p.estado='espera'
        ORDER BY p.creado_en ASC", "No se pudo consultar la lista de espera.");
}

function sp_asignar_lista_espera($con)
{
    $espacios = sp_query($con, "SELECT id_espacio, codigo, tipo_vehiculo
        FROM tbl_espacios
        WHERE estado='libre'
        ORDER BY piso ASC, codigo ASC", "No se pudieron consultar cupos libres para lista de espera.");

    while ($espacio = mysqli_fetch_assoc($espacios)) {
        $sql = "SELECT p.id_parqueo
            FROM tbl_parqueos p
            INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
            WHERE p.estado='espera' AND v.tipo=?
            ORDER BY p.creado_en ASC
            LIMIT 1";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar asignación de lista de espera.");
        mysqli_stmt_bind_param($stmt, "s", $espacio['tipo_vehiculo']);
        mysqli_stmt_execute($stmt);
        $espera = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$espera) {
            continue;
        }

        $expira = date('Y-m-d H:i:s', time() + 900);
        $stmtUpdate = sp_prepare($con, "UPDATE tbl_parqueos
            SET id_espacio=?, estado='reservado', reserva_expira_en=?
            WHERE id_parqueo=? AND estado='espera'", "No se pudo asignar cupo a lista de espera.");
        mysqli_stmt_bind_param($stmtUpdate, "isi", $espacio['id_espacio'], $expira, $espera['id_parqueo']);
        mysqli_stmt_execute($stmtUpdate);

        if (mysqli_stmt_affected_rows($stmtUpdate) > 0) {
            $stmtEspacio = sp_prepare($con, "UPDATE tbl_espacios SET estado='reservado' WHERE id_espacio=? AND estado='libre'", "No se pudo reservar el espacio asignado.");
            mysqli_stmt_bind_param($stmtEspacio, "i", $espacio['id_espacio']);
            mysqli_stmt_execute($stmtEspacio);
            sp_auditar('asignar_lista_espera', 'parqueo_id=' . $espera['id_parqueo'] . ', espacio=' . $espacio['codigo']);
        }
    }
}

function sp_historial($con, $buscar = '')
{
    $like = '%' . $buscar . '%';
    $sql = "SELECT p.*, pg.numero_recibo, pg.metodo_pago, pg.fecha_pago, c.nombres, c.cedula, v.placa, v.tipo, e.codigo
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        LEFT JOIN tbl_pagos pg ON pg.id_parqueo=p.id_parqueo
        WHERE (? = '' OR c.nombres LIKE ? OR c.cedula LIKE ? OR v.placa LIKE ?)
        ORDER BY p.creado_en DESC";
    $stmt = sp_prepare($con, $sql, "No se pudo preparar la consulta del historial.");
    mysqli_stmt_bind_param($stmt, "ssss", $buscar, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function sp_reporte_ingresos($con)
{
    return sp_query($con, "SELECT DATE(fecha_pago) fecha, COUNT(*) pagos, SUM(valor_pagado) total
        FROM tbl_pagos
        GROUP BY DATE(fecha_pago)
        ORDER BY fecha DESC", "No se pudo consultar el reporte de ingresos.");
}

function sp_reporte_metodos_pago($con)
{
    return sp_query($con, "SELECT metodo_pago, COUNT(*) pagos, COALESCE(SUM(valor_pagado), 0) total
        FROM tbl_pagos
        GROUP BY metodo_pago
        ORDER BY total DESC", "No se pudo consultar el reporte por método de pago.");
}

function sp_reporte_tipo_vehiculo($con)
{
    return sp_query($con, "SELECT v.tipo, COUNT(*) parqueos, COALESCE(SUM(p.valor_total), 0) total
        FROM tbl_parqueos p
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        WHERE p.estado='finalizado'
        GROUP BY v.tipo
        ORDER BY total DESC", "No se pudo consultar el reporte por tipo de vehículo.");
}

function sp_ranking_horas_pico($con)
{
    return sp_query($con, "SELECT HOUR(hora_ingreso) hora,
            COUNT(*) movimientos,
            SUM(estado='reservado') reservas,
            SUM(estado='activo') activos,
            SUM(estado='finalizado') finalizados
        FROM tbl_parqueos
        WHERE fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY HOUR(hora_ingreso)
        ORDER BY movimientos DESC, hora ASC
        LIMIT 8", "No se pudo consultar el ranking de horas pico.");
}

function sp_panel_ambiental($con)
{
    $result = sp_query($con, "SELECT COUNT(*) eventos
        FROM tbl_parqueos
        WHERE codigo_reserva IS NOT NULL OR estado IN ('activo','finalizado')", "No se pudo calcular el panel ambiental.");
    $row = mysqli_fetch_assoc($result);
    $eventos = $row ? (int)$row['eventos'] : 0;

    $minutosEvitados = $eventos * 8;
    $kmEvitados = round($eventos * 0.8, 1);
    $co2Evitado = round($kmEvitados * 0.21, 2);
    $arbolesEquivalentes = round($co2Evitado / 21.77, 2);

    return [
        'eventos' => $eventos,
        'minutos_evitados' => $minutosEvitados,
        'km_evitados' => $kmEvitados,
        'co2_evitado' => $co2Evitado,
        'arboles_equivalentes' => $arbolesEquivalentes,
        'nota' => 'Estimación basada en menos vueltas buscando cupo: 0,8 km evitados por evento y 0,21 kg CO2/km.',
    ];
}

function sp_alertas_operativas($con)
{
    $stats = sp_stats($con);
    $alertas = [];
    $ocupacion = $stats['espacios'] > 0 ? (($stats['ocupados'] + $stats['reservados']) / $stats['espacios']) * 100 : 0;

    if ($ocupacion >= 85) {
        $alertas[] = ['nivel' => 'danger', 'titulo' => 'Ocupación crítica', 'mensaje' => 'La ocupación supera el 85%. Conviene limitar nuevas reservas y monitorear salidas.'];
    } elseif ($ocupacion >= 65) {
        $alertas[] = ['nivel' => 'warning', 'titulo' => 'Ocupación media-alta', 'mensaje' => 'La demanda está subiendo. Portería debe vigilar reservas próximas a vencer.'];
    }

    if ((int)$stats['espera'] > 0) {
        $alertas[] = ['nivel' => 'info', 'titulo' => 'Lista de espera activa', 'mensaje' => 'Hay ' . $stats['espera'] . ' solicitud(es) esperando asignación automática.'];
    }

    $porVencer = sp_query($con, "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='reservado' AND reserva_expira_en BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 5 MINUTE)", "No se pudieron consultar reservas por vencer.");
    $rowPorVencer = mysqli_fetch_assoc($porVencer);
    if ($rowPorVencer && (int)$rowPorVencer['total'] > 0) {
        $alertas[] = ['nivel' => 'warning', 'titulo' => 'Reservas por vencer', 'mensaje' => $rowPorVencer['total'] . ' reserva(s) vencen en menos de 5 minutos.'];
    }

    $sinTarifa = sp_query($con, "SELECT COUNT(*) total FROM tbl_tarifas WHERE estado=1 AND valor_hora <= 0", "No se pudieron validar tarifas.");
    $rowSinTarifa = mysqli_fetch_assoc($sinTarifa);
    if ($rowSinTarifa && (int)$rowSinTarifa['total'] > 0) {
        $alertas[] = ['nivel' => 'danger', 'titulo' => 'Tarifa inválida', 'mensaje' => 'Existe una tarifa activa con valor no válido. Revisar módulo Tarifas.'];
    }

    if (count($alertas) === 0) {
        $alertas[] = ['nivel' => 'success', 'titulo' => 'Operación estable', 'mensaje' => 'No hay alertas críticas en este momento.'];
    }

    return $alertas;
}

function sp_ocupacion_por_piso($con)
{
    return sp_query($con, "SELECT piso,
        SUM(estado='libre') libres,
        SUM(estado='reservado') reservados,
        SUM(estado='ocupado') ocupados,
        COUNT(*) total
        FROM tbl_espacios
        GROUP BY piso
        ORDER BY piso ASC", "No se pudo consultar la ocupación por piso.");
}

function sp_usuarios($con)
{
    return sp_query($con, "SELECT id_usuario, nombre, email, rol, estado, ultima_sesion, creado_en FROM tbl_usuarios ORDER BY id_usuario DESC", "No se pudieron consultar los usuarios.");
}

function sp_buscar_porteria($con, $busqueda)
{
    $like = '%' . $busqueda . '%';
    $sql = "SELECT p.*, c.nombres, c.cedula, c.tipo_cliente, v.placa, v.marca, v.modelo, v.color, v.tipo, e.codigo, e.piso,
            (SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.codigo_reserva IS NOT NULL) reservas_cliente,
            (SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.estado='vencido') reservas_vencidas_cliente,
            COALESCE(ROUND(((SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.estado='vencido') /
                NULLIF((SELECT COUNT(*) FROM tbl_parqueos ph WHERE ph.id_cliente=c.id_cliente AND ph.codigo_reserva IS NOT NULL), 0)) * 100, 1), 0) riesgo_no_show
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.codigo_reserva = ?
           OR p.token_publico = ?
           OR v.placa = ?
           OR c.cedula = ?
           OR c.nombres LIKE ?
        ORDER BY FIELD(p.estado, 'reservado', 'activo', 'vencido', 'finalizado', 'cancelado'), p.creado_en DESC
        LIMIT 20";
    $stmt = sp_prepare($con, $sql, "No se pudo preparar la búsqueda de portería.");
    mysqli_stmt_bind_param($stmt, "sssss", $busqueda, $busqueda, $busqueda, $busqueda, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function sp_ia_insights($con)
{
    $stats = sp_stats($con);
    $insights = [];
    $ocupacion = $stats['espacios'] > 0 ? (($stats['ocupados'] + $stats['reservados']) / $stats['espacios']) * 100 : 0;

    if ($ocupacion >= 85) {
        $insights[] = ['Crítico', 'La ocupación supera el 85%. Conviene restringir reservas nuevas o priorizar comunidad ECCI.'];
    } elseif ($ocupacion >= 65) {
        $insights[] = ['Alerta', 'La ocupación está subiendo. Portería debería monitorear reservas por vencer.'];
    } else {
        $insights[] = ['Normal', 'La ocupación está controlada. Hay margen para aceptar reservas.'];
    }

    $vencidas = sp_query($con, "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='vencido' AND DATE(creado_en)=CURDATE()", "No se pudieron analizar reservas vencidas.");
    $rowVencidas = mysqli_fetch_assoc($vencidas);
    if ((int)$rowVencidas['total'] >= 3) {
        $insights[] = ['No show', 'Hay varias reservas vencidas hoy. Se recomienda evaluar penalización o limitar nuevas reservas por usuario.'];
    }

    $porVencer = sp_query($con, "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='reservado' AND reserva_expira_en BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 5 MINUTE)", "No se pudieron analizar reservas por vencer.");
    $rowPorVencer = mysqli_fetch_assoc($porVencer);
    if ((int)$rowPorVencer['total'] > 0) {
        $insights[] = ['Atención', 'Hay reservas que vencen en menos de 5 minutos. Portería debe validar llegada o liberar cupos.'];
    }

    return $insights;
}

function sp_ia_prediccion_ocupacion($con)
{
    $hora = (int)date('G');
    $diaSemana = (int)date('w');
    $stats = sp_stats($con);
    $ocupacionActual = $stats['espacios'] > 0 ? (($stats['ocupados'] + $stats['reservados']) / $stats['espacios']) * 100 : 0;

    $sql = "SELECT AVG(conteo) promedio
        FROM (
            SELECT DATE(fecha_ingreso) fecha, COUNT(*) conteo
            FROM tbl_parqueos
            WHERE HOUR(hora_ingreso) = ? AND DAYOFWEEK(fecha_ingreso) = ?
            GROUP BY DATE(fecha_ingreso)
        ) base";
    $stmt = sp_prepare($con, $sql, "No se pudo preparar la predicción de ocupación.");
    $mysqlDia = $diaSemana + 1;
    mysqli_stmt_bind_param($stmt, "ii", $hora, $mysqlDia);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $promedioHistorico = $row && $row['promedio'] !== null ? (float)$row['promedio'] : 0;

    $esperados = (int)round($promedioHistorico);
    $ocupacionProyectada = $stats['espacios'] > 0 ? min(100, (($stats['ocupados'] + $stats['reservados'] + $esperados) / $stats['espacios']) * 100) : 0;

    if ($promedioHistorico == 0) {
        $mensaje = 'Aún no hay suficiente histórico para esta franja. Se usa la ocupación actual como referencia.';
    } elseif ($ocupacionProyectada >= 85) {
        $mensaje = 'Alta probabilidad de congestión en la próxima hora. Recomendar control de reservas.';
    } elseif ($ocupacionProyectada >= 65) {
        $mensaje = 'Demanda moderada esperada. Mantener monitoreo en portería.';
    } else {
        $mensaje = 'Demanda baja o controlada para la próxima hora.';
    }

    return [
        'hora' => $hora,
        'ocupacion_actual' => round($ocupacionActual, 1),
        'promedio_historico' => round($promedioHistorico, 1),
        'ocupacion_proyectada' => round($ocupacionProyectada, 1),
        'mensaje' => $mensaje,
    ];
}

function sp_ia_prediccion_franjas($con, $horas = 8)
{
    $stats = sp_stats($con);
    $capacidad = max(1, (int)$stats['espacios']);
    $baseUso = (int)$stats['ocupados'] + (int)$stats['reservados'];
    $diaSemana = (int)date('w') + 1;
    $actual = new DateTime('now', new DateTimeZone('America/Bogota'));
    $franjas = [];

    for ($i = 0; $i < max(1, (int)$horas); $i++) {
        $horaObjetivo = (clone $actual)->modify("+{$i} hour");
        $hora = (int)$horaObjetivo->format('G');

        $sql = "SELECT AVG(conteo) promedio
            FROM (
                SELECT DATE(fecha_ingreso) fecha, COUNT(*) conteo
                FROM tbl_parqueos
                WHERE HOUR(hora_ingreso)=? AND DAYOFWEEK(fecha_ingreso)=?
                GROUP BY DATE(fecha_ingreso)
            ) historico";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar la predicción por franjas.");
        mysqli_stmt_bind_param($stmt, "ii", $hora, $diaSemana);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $promedio = $row && $row['promedio'] !== null ? (float)$row['promedio'] : 0;

        $factorPico = (($hora >= 6 && $hora <= 9) || ($hora >= 17 && $hora <= 20)) ? 1.25 : 1;
        $demandaEsperada = (int)ceil($promedio * $factorPico);
        $ocupacion = min(100, round((($baseUso + $demandaEsperada) / $capacidad) * 100, 1));

        if ($ocupacion >= 85) {
            $riesgo = 'Alto';
            $recomendacion = 'Limitar reservas y priorizar usuarios institucionales.';
            $class = 'danger';
        } elseif ($ocupacion >= 65) {
            $riesgo = 'Medio';
            $recomendacion = 'Mantener cupos controlados y vigilar vencimientos.';
            $class = 'warning';
        } else {
            $riesgo = 'Bajo';
            $recomendacion = 'Aceptar reservas con normalidad.';
            $class = 'success';
        }

        $franjas[] = [
            'hora' => $horaObjetivo->format('H:00'),
            'promedio' => round($promedio, 1),
            'demanda_esperada' => $demandaEsperada,
            'ocupacion' => $ocupacion,
            'riesgo' => $riesgo,
            'recomendacion' => $recomendacion,
            'class' => $class,
        ];
    }

    return $franjas;
}

function sp_ia_riesgo_no_show($con)
{
    return sp_query($con, "SELECT c.tipo_cliente,
            COUNT(*) reservas,
            SUM(p.estado='vencido') vencidas,
            ROUND((SUM(p.estado='vencido') / COUNT(*)) * 100, 1) riesgo
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        WHERE p.codigo_reserva IS NOT NULL
        GROUP BY c.tipo_cliente
        ORDER BY riesgo DESC", "No se pudo calcular riesgo de no-show.");
}

function sp_ia_recomendacion_cupos($con)
{
    $stats = sp_stats($con);
    $libres = (int)$stats['libres'];
    $reservados = (int)$stats['reservados'];
    $ocupacion = $stats['espacios'] > 0 ? (($stats['ocupados'] + $stats['reservados']) / $stats['espacios']) * 100 : 0;

    if ($libres <= 3 || $ocupacion >= 90) {
        return [
            'politica' => 'Restrictiva',
            'mensaje' => 'Aceptar solo reservas prioritarias o usuarios institucionales mientras baja la ocupación.',
            'cupos_reserva' => max(0, min(2, $libres)),
        ];
    }

    if ($reservados >= 8 || $ocupacion >= 70) {
        return [
            'politica' => 'Moderada',
            'mensaje' => 'Mantener cupos de reserva limitados y vigilar vencimientos próximos.',
            'cupos_reserva' => max(0, min(5, (int)floor($libres * 0.4))),
        ];
    }

    return [
        'politica' => 'Flexible',
        'mensaje' => 'Se pueden aceptar reservas con normalidad.',
        'cupos_reserva' => max(0, min(12, (int)floor($libres * 0.6))),
    ];
}

function sp_ia_asistente($con, $pregunta)
{
    $texto = mb_strtolower(trim($pregunta), 'UTF-8');

    if ($texto === '') {
        return '';
    }

    if (strpos($texto, 'recaud') !== false || strpos($texto, 'ingreso') !== false) {
        $r = sp_query($con, "SELECT COALESCE(SUM(valor_pagado),0) total FROM tbl_pagos WHERE DATE(fecha_pago)=CURDATE()", "No se pudo consultar recaudo.");
        $row = mysqli_fetch_assoc($r);
        return 'Hoy se ha recaudado ' . sp_format_money($row['total']) . '.';
    }

    if (strpos($texto, 'ocup') !== false || strpos($texto, 'libre') !== false) {
        $stats = sp_stats($con);
        return 'Estado actual: ' . $stats['libres'] . ' libres, ' . $stats['reservados'] . ' reservados y ' . $stats['ocupados'] . ' ocupados de ' . $stats['espacios'] . ' espacios.';
    }

    if (strpos($texto, 'venc') !== false || strpos($texto, 'no show') !== false) {
        $r = sp_query($con, "SELECT COUNT(*) total FROM tbl_parqueos WHERE estado='vencido'", "No se pudieron consultar vencidas.");
        $row = mysqli_fetch_assoc($r);
        return 'Hay ' . $row['total'] . ' reservas vencidas registradas en el sistema.';
    }

    if (strpos($texto, 'moto') !== false || strpos($texto, 'auto') !== false) {
        $r = sp_query($con, "SELECT v.tipo, COUNT(*) total FROM tbl_parqueos p INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo GROUP BY v.tipo", "No se pudo consultar uso por tipo.");
        $partes = [];
        while ($row = mysqli_fetch_assoc($r)) {
            $partes[] = $row['tipo'] . ': ' . $row['total'];
        }
        return 'Uso histórico por tipo de vehículo: ' . implode(', ', $partes) . '.';
    }

    return 'Puedo responder sobre recaudo, ocupación, espacios libres, reservas vencidas y uso por auto/moto.';
}

function sp_recibo($con, $idParqueo)
{
    $sql = "SELECT p.*, pg.numero_recibo, pg.metodo_pago, pg.fecha_pago, pg.valor_pagado, c.nombres, c.cedula, c.telefono, c.correo,
            v.placa, v.marca, v.modelo, v.color, v.tipo, e.codigo, e.piso
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        LEFT JOIN tbl_pagos pg ON pg.id_parqueo=p.id_parqueo
        WHERE p.id_parqueo=? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idParqueo);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function sp_buscar_espacio_libre($con, $tipo)
{
    $sql = "SELECT id_espacio FROM tbl_espacios WHERE tipo_vehiculo=? AND estado='libre' ORDER BY piso ASC, codigo ASC LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $tipo);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? (int)$row['id_espacio'] : null;
}

function sp_tarifa_tipo($con, $tipo)
{
    $sql = "SELECT valor_hora FROM tbl_tarifas WHERE tipo_vehiculo=? AND estado=1 LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $tipo);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? (float)$row['valor_hora'] : 0;
}

function sp_handle_actions($con)
{
    if (($_SERVER["REQUEST_METHOD"] ?? '') !== "POST" || !isset($_POST['accion'])) {
        return;
    }

    $accion = $_POST['accion'];

    if ($accion === 'crearCliente') {
        $tipoCliente = $_POST['tipo_cliente'] ?? 'visitante';
        $sql = "INSERT INTO tbl_clientes (cedula, nombres, telefono, correo, tipo_cliente) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $_POST['cedula'], $_POST['nombres'], $_POST['telefono'], $_POST['correo'], $tipoCliente);
        mysqli_stmt_execute($stmt);
        sp_redirect("Clientes.php?success=1");
    }

    if ($accion === 'crearVehiculo') {
        if (empty($_POST['id_cliente']) || empty($_POST['tipo'])) {
            sp_redirect("Vehiculos.php?error=datos");
        }
        $sql = "INSERT INTO tbl_vehiculos (id_cliente, placa, marca, modelo, color, tipo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar el registro del vehículo.");
        mysqli_stmt_bind_param($stmt, "isssss", $_POST['id_cliente'], $_POST['placa'], $_POST['marca'], $_POST['modelo'], $_POST['color'], $_POST['tipo']);
        if (!mysqli_stmt_execute($stmt)) {
            sp_db_error($con, "No se pudo guardar el vehículo. Revisa que la placa no esté repetida.");
        }
        sp_auditar('crear_vehiculo', 'placa=' . $_POST['placa']);
        sp_redirect("Vehiculos.php?success=1");
    }

    if ($accion === 'registrarIngreso') {
        $idVehiculo = (int)$_POST['id_vehiculo'];
        $idUsuario = (int)$_SESSION['IdUser'];

        $sqlVehiculo = "SELECT id_cliente, tipo FROM tbl_vehiculos WHERE id_vehiculo=? LIMIT 1";
        $stmtVehiculo = sp_prepare($con, $sqlVehiculo, "No se pudo preparar la consulta del vehículo.");
        mysqli_stmt_bind_param($stmtVehiculo, "i", $idVehiculo);
        mysqli_stmt_execute($stmtVehiculo);
        $vehiculo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVehiculo));
        if (!$vehiculo) {
            sp_redirect("Operacion.php?error=vehiculo");
        }

        $idEspacio = sp_buscar_espacio_libre($con, $vehiculo['tipo']);
        if (!$idEspacio) {
            sp_redirect("Operacion.php?error=sin_espacio");
        }

        $sqlTarifa = "SELECT id_tarifa, valor_hora FROM tbl_tarifas WHERE tipo_vehiculo=? AND estado=1 LIMIT 1";
        $stmtTarifa = sp_prepare($con, $sqlTarifa, "No se pudo preparar la consulta de tarifa.");
        mysqli_stmt_bind_param($stmtTarifa, "s", $vehiculo['tipo']);
        mysqli_stmt_execute($stmtTarifa);
        $tarifa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTarifa));
        if (!$tarifa) {
            sp_redirect("Operacion.php?error=tarifa");
        }

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $sql = "INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, id_usuario_ingreso, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo')";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar el registro de ingreso.");
        mysqli_stmt_bind_param($stmt, "iiiiissd", $vehiculo['id_cliente'], $idVehiculo, $idEspacio, $tarifa['id_tarifa'], $idUsuario, $fecha, $hora, $tarifa['valor_hora']);
        if (!mysqli_stmt_execute($stmt)) {
            sp_db_error($con, "No se pudo registrar el ingreso del vehículo.");
        }
        $stmtEspacio = sp_prepare($con, "UPDATE tbl_espacios SET estado='ocupado' WHERE id_espacio=? AND estado='libre'", "No se pudo ocupar el espacio.");
        mysqli_stmt_bind_param($stmtEspacio, "i", $idEspacio);
        mysqli_stmt_execute($stmtEspacio);
        sp_auditar('registrar_ingreso', 'vehiculo_id=' . $idVehiculo);
        sp_redirect("Operacion.php?ingreso=1");
    }

    if ($accion === 'confirmarLlegadaReserva') {
        $idParqueo = (int)$_POST['id_parqueo'];
        $idUsuario = (int)$_SESSION['IdUser'];
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $sql = "UPDATE tbl_parqueos p
            INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
            SET p.estado='activo',
                p.id_usuario_ingreso=?,
                p.fecha_ingreso=?,
                p.hora_ingreso=?,
                e.estado='ocupado'
            WHERE p.id_parqueo=?
              AND p.estado='reservado'
              AND p.reserva_expira_en >= NOW()";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar la confirmación de llegada.");
        mysqli_stmt_bind_param($stmt, "issi", $idUsuario, $fecha, $hora, $idParqueo);
        if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) === 0) {
            sp_redirect("ReservasSmartpark.php?error=vencida");
        }
        sp_auditar('confirmar_reserva', 'parqueo_id=' . $idParqueo);
        sp_redirect("Operacion.php?reserva_activa=1");
    }

    if ($accion === 'cancelarReserva') {
        $idParqueo = (int)$_POST['id_parqueo'];
        $motivo = trim($_POST['motivo_cancelacion'] ?? 'Cancelación administrativa');
        $sql = "UPDATE tbl_parqueos p
            LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
            SET p.estado='cancelado',
                p.cancelado_en=NOW(),
                p.motivo_cancelacion=?,
                e.estado=IF(e.id_espacio IS NULL, e.estado, 'libre')
            WHERE p.id_parqueo=? AND p.estado IN ('reservado','espera')";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar la cancelación de reserva.");
        mysqli_stmt_bind_param($stmt, "si", $motivo, $idParqueo);
        mysqli_stmt_execute($stmt);
        sp_auditar('cancelar_reserva', 'parqueo_id=' . $idParqueo . ', motivo=' . $motivo);
        sp_asignar_lista_espera($con);
        sp_redirect("ReservasSmartpark.php?cancelada=1");
    }

    if ($accion === 'guardarConfiguracion' && sp_role_allowed(['Administrador'])) {
        sp_guardar_config_institucional($_POST);
        sp_auditar('guardar_configuracion', 'configuracion institucional actualizada');
        sp_redirect("Configuracion.php?success=1");
    }

    if ($accion === 'registrarSalidaPago') {
        $idParqueo = (int)$_POST['id_parqueo'];
        $metodoPago = $_POST['metodo_pago'];
        $idUsuario = (int)$_SESSION['IdUser'];

        $sql = "SELECT * FROM tbl_parqueos WHERE id_parqueo=? AND estado='activo' LIMIT 1";
        $stmt = sp_prepare($con, $sql, "No se pudo preparar la consulta del parqueo activo.");
        mysqli_stmt_bind_param($stmt, "i", $idParqueo);
        mysqli_stmt_execute($stmt);
        $parqueo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$parqueo) {
            sp_redirect("Operacion.php?error=parqueo");
        }

        $entrada = strtotime($parqueo['fecha_ingreso'] . ' ' . $parqueo['hora_ingreso']);
        $salida = time();
        $totalHoras = max(1, (int)ceil(($salida - $entrada) / 3600));
        $valorTotal = $totalHoras * (float)$parqueo['tarifa_hora_aplicada'];
        $fechaSalida = date('Y-m-d');
        $horaSalida = date('H:i:s');

        $sqlUpdate = "UPDATE tbl_parqueos SET id_usuario_salida=?, fecha_salida=?, hora_salida=?, total_horas=?, valor_total=?, estado='finalizado' WHERE id_parqueo=?";
        $stmtUpdate = sp_prepare($con, $sqlUpdate, "No se pudo preparar el cierre del parqueo.");
        mysqli_stmt_bind_param($stmtUpdate, "issidi", $idUsuario, $fechaSalida, $horaSalida, $totalHoras, $valorTotal, $idParqueo);
        if (!mysqli_stmt_execute($stmtUpdate)) {
            sp_db_error($con, "No se pudo cerrar el parqueo.");
        }

        $numeroRecibo = 'SP-' . date('Ymd') . '-' . str_pad($idParqueo, 5, '0', STR_PAD_LEFT);
        $fechaPago = date('Y-m-d H:i:s');
        $sqlPago = "INSERT INTO tbl_pagos (id_parqueo, numero_recibo, fecha_pago, metodo_pago, valor_pagado, id_usuario) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtPago = sp_prepare($con, $sqlPago, "No se pudo preparar el registro de pago.");
        mysqli_stmt_bind_param($stmtPago, "isssdi", $idParqueo, $numeroRecibo, $fechaPago, $metodoPago, $valorTotal, $idUsuario);
        if (!mysqli_stmt_execute($stmtPago)) {
            sp_db_error($con, "No se pudo registrar el pago.");
        }
        $stmtEspacio = sp_prepare($con, "UPDATE tbl_espacios SET estado='libre' WHERE id_espacio=?", "No se pudo liberar el espacio.");
        mysqli_stmt_bind_param($stmtEspacio, "i", $parqueo['id_espacio']);
        mysqli_stmt_execute($stmtEspacio);
        sp_auditar('cerrar_parqueo_pago', 'parqueo_id=' . $idParqueo . ', total=' . $valorTotal);
        sp_asignar_lista_espera($con);
        sp_redirect("Recibo.php?idParqueo={$idParqueo}");
    }

    if ($accion === 'actualizarTarifa') {
        $sql = "UPDATE tbl_tarifas SET valor_hora=? WHERE tipo_vehiculo=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ds", $_POST['valor_hora'], $_POST['tipo_vehiculo']);
        mysqli_stmt_execute($stmt);
        sp_redirect("Tarifas.php?success=1");
    }

    if ($accion === 'crearUsuario' && sp_role_allowed(['Administrador'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO tbl_usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $_POST['nombre'], $_POST['email'], $password, $_POST['rol']);
        mysqli_stmt_execute($stmt);
        sp_redirect("Usuarios.php?success=1");
    }
}

sp_expirar_reservas($con);
sp_handle_actions($con);
