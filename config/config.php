<?php
if (defined("SMARTPARK_CONFIG_LOADED")) {
    return;
}
define("SMARTPARK_CONFIG_LOADED", true);

$host = $_SERVER["HTTP_HOST"] ?? "";
$isLocal = PHP_SAPI === "cli" || $host === "localhost" || $host === "127.0.0.1" || strpos($host, "localhost:") === 0 || strpos($host, "127.0.0.1:") === 0;

if ($isLocal && file_exists(__DIR__ . "/config_local.php")) {
    include __DIR__ . "/config_local.php";
    return;
}

if (file_exists(__DIR__ . "/config_hosting.php")) {
    include __DIR__ . "/config_hosting.php";
    return;
}

http_response_code(500);
die("No se encontro una configuracion de base de datos valida para SMARTPARK.");
