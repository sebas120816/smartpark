<?php
$configPath = __DIR__ . '/config/smartpark_institucional.json';
$config = file_exists($configPath) ? json_decode((string)file_get_contents($configPath), true) : [];
$numeroWhatsapp = preg_replace('/\D+/', '', $config['telefono_whatsapp'] ?? '573001112233');

$origen = isset($_GET['origen']) ? trim($_GET['origen']) : 'web';
$mensaje = "Hola SMARTPARK, quiero reservar parqueadero.\n\n"
    . "Tipo de usuario: \n"
    . "Cedula/codigo: \n"
    . "Nombre completo: \n"
    . "Telefono: \n"
    . "Placa: \n"
    . "Tipo de vehiculo (auto/moto): \n"
    . "Marca/modelo/color: \n\n"
    . "Origen: {$origen}";

header('Location: https://wa.me/' . $numeroWhatsapp . '?text=' . urlencode($mensaje));
exit;
