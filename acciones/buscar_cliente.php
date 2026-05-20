<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$cedula = trim($_GET['cedula'] ?? '');
if (empty($cedula)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cédula requerida']);
    exit;
}

try {
    // Primero buscar el cliente
    $stmtCliente = mysqli_prepare($con, "SELECT nombres, telefono, correo, tipo_cliente FROM tbl_clientes WHERE cedula = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtCliente, "s", $cedula);
    mysqli_stmt_execute($stmtCliente);
    $resultCliente = mysqli_stmt_get_result($stmtCliente);

    if ($cliente = mysqli_fetch_assoc($resultCliente)) {
        // Cliente encontrado, ahora buscar su vehículo más reciente
        $stmtVehiculo = mysqli_prepare($con, "SELECT placa, marca, modelo, color, tipo FROM tbl_vehiculos WHERE id_cliente = (SELECT id_cliente FROM tbl_clientes WHERE cedula = ?) ORDER BY id_vehiculo DESC LIMIT 1");
        mysqli_stmt_bind_param($stmtVehiculo, "s", $cedula);
        mysqli_stmt_execute($stmtVehiculo);
        $resultVehiculo = mysqli_stmt_get_result($stmtVehiculo);
        $vehiculo = mysqli_fetch_assoc($resultVehiculo);

        echo json_encode([
            'success' => true,
            'cliente' => [
                'nombres' => $cliente['nombres'],
                'telefono' => $cliente['telefono'],
                'correo' => $cliente['correo'] ?? '',
                'tipo_cliente' => $cliente['tipo_cliente']
            ],
            'vehiculo' => $vehiculo ? [
                'placa' => $vehiculo['placa'],
                'marca' => $vehiculo['marca'],
                'modelo' => $vehiculo['modelo'],
                'color' => $vehiculo['color'],
                'tipo' => $vehiculo['tipo']
            ] : null
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>