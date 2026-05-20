# Instalación Local SMARTPARK

## Requisitos

- XAMPP con Apache y MariaDB.
- PHP 7.4 o superior.
- Navegador web.

## Ruta del Proyecto

La carpeta debe estar en:

```text
/Applications/XAMPP/xamppfiles/htdocs/sistema-parking-estacionamiento
```

## Base de Datos

1. Abrir phpMyAdmin:

```text
http://localhost/phpmyadmin
```

2. Importar:

```text
BD/smartpark.sql
```

El script crea la base `smartpark`, las 7 tablas, índices, vistas, triggers, usuarios iniciales, tarifas y espacios.

## Configuración

Archivo:

```text
config/config.php
```

Valores por defecto para XAMPP:

```php
$usuario = "root";
$password = "";
$servidor = "localhost";
$basededatos = "smartpark";
```

## Acceso

URL:

```text
http://localhost/sistema-parking-estacionamiento/
```

Usuarios iniciales:

```text
admin@smartpark.com / Admin123*
caja@smartpark.com / Caja123*
control@smartpark.com / Control123*
```

Reserva pública:

```text
http://localhost/sistema-parking-estacionamiento/ReservarParqueadero.php
```

Consulta pública por QR o código:

```text
http://localhost/sistema-parking-estacionamiento/ConsultarReserva.php
```

## Flujo de Prueba

1. Crear o importar un cliente.
2. Crear o importar un vehículo.
3. Crear reserva pública.
4. En admin, abrir Reservas y confirmar llegada.
5. En Operación, cerrar parqueo y registrar pago.
6. Revisar recibo, historial y reportes.
