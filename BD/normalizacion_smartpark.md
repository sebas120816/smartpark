# SMARTPARK - Normalizacion y Diseno de Base de Datos

## Objetivo

La base de datos SMARTPARK modela la operacion del parqueadero "Zona Segura ECCI" con capacidad aproximada de 50 espacios distribuidos en dos pisos. El diseno busca resolver registros manuales, errores de cobro, perdida de informacion y falta de control de espacios.

## Tablas del Modelo

El modelo mantiene 7 tablas principales:

1. `tbl_usuarios`: usuarios internos del sistema con roles Administrador, Caja y Control.
2. `tbl_clientes`: datos de clientes.
3. `tbl_vehiculos`: vehiculos asociados a clientes.
4. `tbl_espacios`: espacios fisicos del parqueadero.
5. `tbl_tarifas`: tarifas por hora segun tipo de vehiculo.
6. `tbl_parqueos`: eventos de lista de espera, reserva, ingreso y salida.
7. `tbl_pagos`: pagos y recibos unicos.

## Relaciones

- Un cliente puede tener muchos vehiculos: `tbl_clientes 1:N tbl_vehiculos`.
- Un cliente puede tener muchos parqueos: `tbl_clientes 1:N tbl_parqueos`.
- Un vehiculo puede tener muchos parqueos historicos, pero solo una solicitud en espera, reserva o parqueo activo por operacion del sistema.
- Un espacio puede tener muchos parqueos historicos, pero su estado actual es libre, reservado u ocupado. Las solicitudes en espera viven en `tbl_parqueos` con `id_espacio` nulo hasta recibir asignacion.
- Una tarifa puede asociarse a muchos parqueos.
- Un parqueo puede tener un solo pago: `tbl_parqueos 1:1 tbl_pagos`.
- Un usuario registra ingresos, salidas y pagos.

## Primera Forma Normal (1FN)

Todas las tablas cumplen 1FN porque:

- Cada campo almacena valores atomicos.
- No existen listas repetidas dentro de una columna.
- Cada registro se identifica con una clave primaria.

Ejemplo: los datos del vehiculo se separan en `placa`, `marca`, `modelo`, `color` y `tipo`; no se guardan como un texto compuesto.

## Segunda Forma Normal (2FN)

Todas las tablas cumplen 2FN porque:

- Cada tabla tiene una clave primaria simple.
- Los atributos no clave dependen completamente de la clave primaria.
- No hay dependencias parciales.

Ejemplo: en `tbl_vehiculos`, `marca`, `modelo`, `color` y `tipo` dependen de `id_vehiculo`; los datos del cliente no se repiten ahi, solo se referencia `id_cliente`.

## Tercera Forma Normal (3FN)

El modelo cumple 3FN porque:

- No hay dependencias transitivas entre atributos no clave.
- Los datos de clientes, vehiculos, tarifas, espacios, usuarios, parqueos y pagos estan separados por entidad.
- Los valores derivados del cobro se guardan en el evento transaccional para conservar evidencia historica del recibo.

Ejemplo: el valor de la tarifa por hora vive en `tbl_tarifas`; al registrar un parqueo se guarda `tarifa_hora_aplicada` en `tbl_parqueos` como snapshot historico para que un cambio futuro de tarifa no altere recibos antiguos.

## Reglas de Integridad

El SQL define:

- Llaves primarias en todas las tablas.
- Llaves foraneas para conservar integridad referencial.
- Restricciones `UNIQUE` para cedula, placa, email, codigo de espacio y numero de recibo.
- Restricciones `CHECK` para valores positivos, estado de usuarios, pisos validos y coherencia entre estado activo/finalizado.
- Indices para busquedas frecuentes por cliente, vehiculo, estado, fechas y pagos.

## Vistas

Se agregan vistas para consultas academicas y reportes:

- `vw_ocupacion_espacios`: muestra ocupacion por piso, tipo de vehiculo y estado.
- `vw_historial_parqueos`: consolida cliente, vehiculo, espacio, parqueo y pago.
- `vw_ingresos_diarios`: resume ingresos diarios del parqueadero.

## Reservas de 15 Minutos

La reserva se implementa dentro de `tbl_parqueos` para conservar el limite academico de 7 tablas. Un parqueo puede estar en estado `espera`, `reservado`, `activo`, `finalizado`, `vencido` o `cancelado`.

Cuando un estudiante, docente o funcionario reserva:

- se registra o actualiza el cliente en `tbl_clientes`;
- se registra o actualiza el vehiculo en `tbl_vehiculos`;
- se bloquea un espacio libre compatible con el tipo de vehiculo;
- se crea un registro en `tbl_parqueos` con estado `reservado`;
- se calcula `reserva_expira_en` con 15 minutos de vigencia;
- el espacio pasa a estado `reservado`.

Si el usuario llega antes de vencer, el administrador o control confirma la llegada y el estado cambia a `activo`. Si no llega, el sistema marca la reserva como `vencido` y libera el espacio.

Cada reserva tiene un `codigo_reserva` único. Ese código se usa para generar un QR de consulta pública sin crear tablas adicionales, conservando el modelo de 7 tablas.

## Lista de Espera Inteligente

La lista de espera no crea una octava tabla. Se modela como un evento en `tbl_parqueos` con:

- `estado='espera'`;
- `id_espacio=NULL`;
- `codigo_reserva` con prefijo `LE`;
- datos de cliente, vehiculo, tarifa y hora de solicitud.

Cuando se libera un espacio por salida, vencimiento o cancelacion, la logica del sistema busca la solicitud mas antigua compatible con el tipo de vehiculo y la convierte en `reservado`, asignando `id_espacio` y una nueva vigencia de 15 minutos.

Esta decision mantiene 3FN porque la lista de espera representa el mismo hecho transaccional del dominio: una solicitud de uso del parqueadero asociada a cliente, vehiculo y tarifa.

## Logica Transaccional

El modelo queda preparado para procedimientos almacenados, pero en esta version se evita crearlos porque algunas instalaciones de XAMPP/MariaDB presentan errores en la tabla interna `mysql.proc` despues de actualizaciones. La logica de registro se mantiene en consultas parametrizadas desde PHP y la integridad critica se conserva en la base mediante llaves foraneas, restricciones y triggers.

La base sigue controlando:

- integridad referencial con FK;
- unicidad con restricciones `UNIQUE`;
- valores validos con `CHECK`;
- coherencia de ingreso con `trg_parqueo_validar_ingreso`;
- estado de espacios con triggers.

## Triggers

Se agregan triggers para mantener consistencia automatica:

- `trg_parqueo_ingreso_ocupa_espacio`: al crear un parqueo, marca el espacio como ocupado.
- `trg_pago_libera_espacio`: al registrar el pago, libera el espacio.
- `trg_parqueo_validar_ingreso`: impide asignar espacios ocupados, evita parqueos activos duplicados para un mismo vehiculo y valida que el tipo de espacio corresponda al tipo de vehiculo.

## Archivos de Apoyo

- `smartpark.sql`: script completo de creacion de base de datos.
- `diagrama_er_smartpark.md`: diagrama entidad-relacion en formato Mermaid.
- `consultas_smartpark.sql`: consultas SQL de prueba y reportes para sustentar el proyecto.

## Cumplimiento de Requerimientos

- RF-001: `tbl_clientes`
- RF-002: `tbl_vehiculos`
- RF-003: `tbl_parqueos`, ingreso, espacio y usuario responsable
- RF-004: `tbl_parqueos`, salida y tiempo total
- RF-005: `tbl_tarifas` + `sp_registrar_salida_pago`
- RF-006: `tbl_pagos.numero_recibo`
- RF-007: `tbl_pagos.metodo_pago`
- RF-008: `tbl_espacios.estado`
- RF-009: `vw_historial_parqueos`
- RF-010: `vw_ingresos_diarios`
- RF-011: `tbl_tarifas`
- RF-012: `tbl_usuarios.rol`
