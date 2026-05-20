<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$usuario = "root";
$password = "";
$servidor = "localhost";
$basededatos = "smartpark";
$puerto = 3306;
$socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";

mysqli_report(MYSQLI_REPORT_OFF);
$con = mysqli_connect($servidor, $usuario, $password, $basededatos, $puerto, $socket);

function manejarErrorDeConexion($error_message)
{
    http_response_code(500);
    die("<div style='font-family: Arial, sans-serif; max-width: 760px; margin: 40px auto; padding: 24px; border: 1px solid #f1b0b7; background: #fff5f5; color: #721c24; border-radius: 8px;'>
        <h2 style='margin-top:0;'>SMARTPARK no pudo conectar a MySQL</h2>
        <p><strong>Error:</strong> " . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "</p>
        <p>Verifica que MySQL esté encendido en XAMPP y que la base <code>smartpark</code> exista en phpMyAdmin.</p>
    </div>");
}

if (mysqli_connect_errno()) {
    manejarErrorDeConexion(mysqli_connect_error());
}

if (!$con) {
    manejarErrorDeConexion(mysqli_connect_error());
}
//echo "Conexión exitosa a la Base de Datos";
