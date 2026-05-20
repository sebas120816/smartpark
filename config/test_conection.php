<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$usuario = "avnadmin";
$password = "TU_PASSWORD_DE_AIVEN";
$servidor = "smartpark-smartparkecci.k.aivencloud.com";
$basededatos = "defaultdb";
$puerto = 21174;

mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_init();

mysqli_options($con, MYSQLI_OPT_CONNECT_TIMEOUT, 8);

$ok = mysqli_real_connect(
    $con,
    $servidor,
    $usuario,
    $password,
    $basededatos,
    $puerto
);

if (!$ok) {
    die("Error conectando a Aiven: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");

// echo "Conexión exitosa a Aiven";
?>