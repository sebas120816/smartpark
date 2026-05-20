# Despliegue en InfinityFree - SMARTPARK

## 1. Por que hay dos scripts SQL

El proyecto academico completo esta en:

- `BD/smartpark.sql`

Ese archivo contiene:

- Tablas.
- Relaciones.
- Vistas.
- Indices.
- Restricciones.
- Triggers.
- Datos iniciales.

Ese es el archivo que se debe mostrar en la sustentacion porque cumple el requisito de triggers.

Para InfinityFree se usa:

- `BD/smartpark_infinityfree.sql`

Este archivo no contiene `CREATE TRIGGER`, porque muchos hostings gratuitos no dan permiso para crear triggers. La misma logica critica se implemento en PHP:

- reservar espacio;
- ocupar espacio al registrar ingreso;
- liberar espacio al registrar salida/pago;
- cancelar reserva y liberar cupo;
- asignar lista de espera.

## 2. Como importar la base en InfinityFree

1. Entra al panel de InfinityFree.
2. Crea una base de datos MySQL.
3. Abre phpMyAdmin.
4. Selecciona la base de datos creada.
5. Ve a Importar.
6. Importa `BD/smartpark_infinityfree.sql`.

Importante: no importes `smartpark.sql` en InfinityFree si el hosting te rechaza triggers.

## 3. Configurar conexion

Edita este archivo:

- `config/config.php`

Pega los datos reales del panel MySQL:

```php
$usuario = "USUARIO_MYSQL";
$password = "PASSWORD_MYSQL";
$servidor = "HOST_MYSQL";
$basededatos = "NOMBRE_BASE_DATOS";
$puerto = 3306;
$socket = null;
```

En InfinityFree no se usa socket de XAMPP. El valor de `$socket` debe quedar en `null`.

## 4. Que explicar a la profesora

Respuesta sugerida:

> El script academico `smartpark.sql` incluye los triggers requeridos por el curso y funciona en MariaDB local. Para el despliegue gratuito en InfinityFree se uso `smartpark_infinityfree.sql` porque el hosting no otorga permisos para `CREATE TRIGGER`. La logica equivalente de esos triggers se implemento en PHP para mantener la integridad operacional en produccion.

## 5. Triggers academicos del proyecto

En `BD/smartpark.sql` existen:

- `trg_parqueo_validar_ingreso`
- `trg_parqueo_ingreso_ocupa_espacio`
- `trg_pago_libera_espacio`

## 6. Logica equivalente en PHP para hosting

Archivos principales:

- `ReservarParqueadero.php`
- `dashboard/smartpark_funciones.php`
- `ConsultarReserva.php`
- `PortalUsuario.php`

Ejemplos:

- Al reservar, PHP actualiza `tbl_espacios.estado='reservado'`.
- Al registrar ingreso, PHP actualiza `tbl_espacios.estado='ocupado'`.
- Al registrar pago, PHP actualiza `tbl_espacios.estado='libre'`.
- Al cancelar, PHP actualiza `tbl_parqueos.estado='cancelado'` y libera el espacio.

## 7. URLs para probar despues de subir

- `/`
- `/Presentacion.php`
- `/ReservarParqueadero.php`
- `/Disponibilidad.php`
- `/ConsultarReserva.php`
- `/PortalUsuario.php`
- `/dashboard/`
