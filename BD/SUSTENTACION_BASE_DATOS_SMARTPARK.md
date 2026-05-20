# Sustentacion Base de Datos - SMARTPARK

## 1. Nombre del proyecto

SMARTPARK - Sistema de gestion para el parqueadero "Zona Segura ECCI".

## 2. Problema que resuelve

El parqueadero tenia una operacion manual basada en libretas, control visual de cupos, calculo manual de tarifas y archivos de Excel sin estructura. Esto generaba perdida de informacion, errores en cobros, falta de trazabilidad y mala asignacion de espacios.

SMARTPARK soluciona esto con una base de datos relacional que permite registrar clientes, vehiculos, espacios, tarifas, reservas, ingresos, salidas, pagos, recibos, lista de espera y reportes.

## 3. Motor y caracteristicas tecnicas

- Motor: MariaDB/MySQL.
- Motor de almacenamiento: InnoDB.
- Codificacion: utf8mb4.
- Base de datos: smartpark.
- Numero de tablas del dominio: 7.
- Integridad: llaves primarias, llaves foraneas, indices, UNIQUE, CHECK, vistas y triggers.

## 4. Tablas del modelo

El modelo principal conserva 7 tablas:

1. tbl_usuarios
2. tbl_clientes
3. tbl_vehiculos
4. tbl_espacios
5. tbl_tarifas
6. tbl_parqueos
7. tbl_pagos

## 5. Descripcion de cada tabla

### tbl_usuarios

Guarda usuarios internos del sistema. Maneja los roles Administrador, Caja y Control.

Campos clave:
- id_usuario: clave primaria.
- email: unico para inicio de sesion.
- password: hash de clave.
- rol: Administrador, Caja o Control.
- estado: activo o inactivo.

Requerimiento relacionado: RF-012.

### tbl_clientes

Guarda la informacion del estudiante, docente, funcionario o visitante.

Campos clave:
- id_cliente: clave primaria.
- cedula: unica.
- nombres.
- telefono.
- correo.
- tipo_cliente.

Requerimiento relacionado: RF-001.

### tbl_vehiculos

Guarda los vehiculos asociados a clientes.

Campos clave:
- id_vehiculo: clave primaria.
- id_cliente: llave foranea hacia tbl_clientes.
- placa: unica.
- marca, modelo, color.
- tipo: auto o moto.

Relacion: un cliente puede tener varios vehiculos.

Requerimiento relacionado: RF-002.

### tbl_espacios

Representa los espacios fisicos del parqueadero.

Campos clave:
- id_espacio: clave primaria.
- codigo: codigo visible del cupo, por ejemplo P1-A01.
- piso: piso 1 o 2.
- tipo_vehiculo: auto o moto.
- estado: libre, reservado u ocupado.

Requerimiento relacionado: RF-008.

### tbl_tarifas

Guarda la tarifa por hora segun el tipo de vehiculo.

Campos clave:
- id_tarifa: clave primaria.
- tipo_vehiculo: auto o moto.
- valor_hora.
- estado.

Requerimiento relacionado: RF-011.

### tbl_parqueos

Es la tabla transaccional mas importante. Registra reservas, lista de espera, ingresos, salidas, vencimientos y cancelaciones.

Campos clave:
- id_parqueo: clave primaria.
- id_cliente: FK hacia tbl_clientes.
- id_vehiculo: FK hacia tbl_vehiculos.
- id_espacio: FK hacia tbl_espacios, puede ser NULL si esta en lista de espera.
- id_tarifa: FK hacia tbl_tarifas.
- id_usuario_ingreso: usuario que confirma ingreso.
- id_usuario_salida: usuario que registra salida.
- fecha_ingreso y hora_ingreso.
- fecha_salida y hora_salida.
- tarifa_hora_aplicada: copia historica de la tarifa.
- total_horas.
- valor_total.
- reserva_expira_en.
- codigo_reserva.
- token_publico: token seguro para consulta QR.
- cancelado_en.
- motivo_cancelacion.
- estado: espera, reservado, activo, finalizado, vencido o cancelado.

Requerimientos relacionados: RF-003, RF-004, RF-005, RF-008, RF-009.

### tbl_pagos

Registra pagos y recibos.

Campos clave:
- id_pago: clave primaria.
- id_parqueo: FK y UNIQUE, porque un parqueo tiene un solo pago.
- numero_recibo: unico.
- fecha_pago.
- metodo_pago: efectivo, nequi, daviplata, tarjeta o pasarela.
- valor_pagado.
- id_usuario: usuario que registra el pago.

Requerimientos relacionados: RF-006 y RF-007.

## 6. Relaciones principales

- tbl_clientes 1:N tbl_vehiculos.
- tbl_clientes 1:N tbl_parqueos.
- tbl_vehiculos 1:N tbl_parqueos.
- tbl_espacios 1:N tbl_parqueos historicos.
- tbl_tarifas 1:N tbl_parqueos.
- tbl_parqueos 1:1 tbl_pagos.
- tbl_usuarios 1:N tbl_parqueos como usuario de ingreso o salida.
- tbl_usuarios 1:N tbl_pagos como usuario que registra pago.

## 7. Normalizacion

### Primera Forma Normal - 1FN

El modelo cumple 1FN porque:

- Todas las tablas tienen clave primaria.
- Los campos son atomicos.
- No hay columnas con listas de datos.
- No hay grupos repetitivos.

Ejemplo: en vehiculos no se guarda "Mazda rojo auto ABC123" en un solo campo. Se separa en placa, marca, modelo, color y tipo.

### Segunda Forma Normal - 2FN

El modelo cumple 2FN porque:

- Todas las tablas usan claves primarias simples.
- Los atributos dependen completamente de la clave primaria.
- No hay dependencias parciales.

Ejemplo: en tbl_vehiculos, marca, modelo, color y tipo dependen de id_vehiculo. Los datos del cliente no se repiten, solo se referencia id_cliente.

### Tercera Forma Normal - 3FN

El modelo cumple 3FN porque:

- No hay dependencias transitivas entre atributos no clave.
- Cada entidad esta separada segun su responsabilidad.
- No se repiten datos de clientes en parqueos, ni datos de vehiculos en pagos.

Ejemplo: la tarifa general vive en tbl_tarifas, pero tbl_parqueos guarda tarifa_hora_aplicada como evidencia historica. Esto evita que un cambio futuro de tarifa altere parqueos ya registrados.

## 8. Integridad referencial

La base usa llaves foraneas para impedir registros huerfanos:

- Un vehiculo no puede existir sin cliente.
- Un parqueo debe estar asociado a cliente, vehiculo y tarifa.
- Si tiene espacio asignado, debe existir en tbl_espacios.
- Un pago debe pertenecer a un parqueo existente.
- Ingresos, salidas y pagos quedan asociados a usuarios internos.

Se usa ON UPDATE CASCADE para actualizar relaciones si cambia una clave, y ON DELETE RESTRICT para evitar borrar informacion historica usada en operaciones.

## 9. Restricciones importantes

### UNIQUE

- tbl_usuarios.email.
- tbl_clientes.cedula.
- tbl_vehiculos.placa.
- tbl_espacios.codigo.
- tbl_tarifas.tipo_vehiculo.
- tbl_parqueos.codigo_reserva.
- tbl_parqueos.token_publico.
- tbl_pagos.id_parqueo.
- tbl_pagos.numero_recibo.

### CHECK

- Usuarios solo pueden estar activos o inactivos.
- Correos deben tener formato basico.
- Piso de espacio entre 1 y 2.
- Tarifas mayores a 0.
- Total de horas mayor o igual a 1.
- Valor total mayor o igual a 0.
- Coherencia del estado del parqueo.

## 10. Estados del parqueo

tbl_parqueos.estado puede tener:

- espera: solicitud sin cupo asignado.
- reservado: cupo asignado por tiempo limitado.
- activo: vehiculo ya ingreso.
- finalizado: vehiculo salio y se registro el cobro.
- vencido: reserva que no fue usada a tiempo.
- cancelado: reserva cancelada por usuario o administrador.

## 11. Reservas y QR

Cuando un usuario reserva:

1. Se registra o actualiza el cliente.
2. Se registra o actualiza el vehiculo.
3. Se valida que el vehiculo no tenga otra solicitud activa.
4. Se busca un espacio libre compatible.
5. Si hay espacio, se crea un parqueo en estado reservado.
6. Se genera codigo_reserva.
7. Se genera token_publico para QR seguro.
8. Se calcula reserva_expira_en.
9. El espacio pasa a reservado.

El QR usa token_publico para evitar depender solo de un codigo visible y facil de adivinar.

## 12. Lista de espera

La lista de espera se maneja sin crear una tabla adicional. Se usa tbl_parqueos con:

- estado = espera.
- id_espacio = NULL.
- codigo_reserva con prefijo LE.
- cliente, vehiculo, tarifa y fecha/hora de solicitud.

Cuando se libera un espacio, el sistema busca la solicitud mas antigua compatible con el tipo de vehiculo y la convierte en reserva.

## 13. Cancelacion

La cancelacion se registra en tbl_parqueos:

- estado = cancelado.
- cancelado_en guarda fecha y hora.
- motivo_cancelacion guarda el motivo.
- Si tenia espacio, se libera en tbl_espacios.
- Luego se intenta asignar el cupo a la lista de espera.

## 14. Triggers

Importante para sustentacion:

El archivo academico `BD/smartpark.sql` incluye los triggers. Si el despliegue se hace en InfinityFree y el hosting no permite `CREATE TRIGGER`, se usa `BD/smartpark_infinityfree.sql` solo para publicar la demo. La logica equivalente de los triggers queda implementada en PHP para conservar la integridad operacional.

### trg_parqueo_validar_ingreso

Valida antes de insertar un parqueo:

- El espacio no puede estar reservado u ocupado.
- Un vehiculo no puede tener simultaneamente espera, reserva o parqueo activo.
- El cliente debe corresponder al vehiculo.
- Si esta en espera, no debe tener espacio asignado.
- Si es reserva, debe tener fecha de expiracion.
- El tipo de vehiculo debe coincidir con el tipo de espacio.

### trg_parqueo_ingreso_ocupa_espacio

Despues de insertar un parqueo:

- Si el estado es reservado, el espacio pasa a reservado.
- Si el estado es activo, el espacio pasa a ocupado.

### trg_pago_libera_espacio

Despues de insertar un pago:

- Libera el espacio asociado al parqueo.

## 15. Vistas

### vw_ocupacion_espacios

Muestra ocupacion agrupada por piso, tipo de vehiculo y estado.

Sirve para reportes de disponibilidad.

### vw_historial_parqueos

Une parqueos, clientes, vehiculos, espacios y pagos.

Sirve para consultar historial por placa, cliente y recibos.

### vw_ingresos_diarios

Resume pagos por fecha.

Sirve para reportes financieros diarios.

## 16. Indices

La base incluye indices para mejorar consultas frecuentes:

- idx_vehiculos_cliente.
- idx_vehiculos_tipo.
- idx_espacios_estado_tipo.
- idx_parqueos_estado.
- idx_parqueos_fechas.
- idx_parqueos_reserva_expira.
- idx_parqueos_cliente.
- idx_parqueos_vehiculo.
- idx_pagos_fecha.

Estos indices ayudan en busquedas por estado, fecha, cliente, vehiculo, tipo y pagos.

## 17. Consultas de sustentacion

Ejemplos importantes:

### Espacios disponibles por piso y tipo

```sql
SELECT piso, tipo_vehiculo, COUNT(*) AS espacios_libres
FROM tbl_espacios
WHERE estado = 'libre'
GROUP BY piso, tipo_vehiculo;
```

### Historial por placa

```sql
SELECT *
FROM vw_historial_parqueos
WHERE placa = 'ABC123'
ORDER BY fecha_ingreso DESC, hora_ingreso DESC;
```

### Ingresos diarios

```sql
SELECT *
FROM vw_ingresos_diarios
ORDER BY fecha DESC;
```

### Vehiculos actualmente dentro

```sql
SELECT c.nombres, c.cedula, v.placa, e.codigo, p.fecha_ingreso, p.hora_ingreso
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
INNER JOIN tbl_espacios e ON e.id_espacio = p.id_espacio
WHERE p.estado = 'activo';
```

### Validar que no existan duplicados activos por vehiculo

```sql
SELECT id_vehiculo, COUNT(*) AS bloqueos_activos
FROM tbl_parqueos
WHERE estado IN ('espera', 'reservado', 'activo')
GROUP BY id_vehiculo
HAVING COUNT(*) > 1;
```

### Lista de espera

```sql
SELECT p.codigo_reserva, c.cedula, c.nombres, v.placa, v.tipo, p.creado_en
FROM tbl_parqueos p
INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
INNER JOIN tbl_vehiculos v ON v.id_vehiculo = p.id_vehiculo
WHERE p.estado = 'espera'
ORDER BY p.creado_en ASC;
```

## 18. Cumplimiento de requerimientos

| Requerimiento | Cumplimiento en base de datos |
|---|---|
| RF-001 Crear cliente | tbl_clientes |
| RF-002 Registrar vehiculo | tbl_vehiculos |
| RF-003 Registrar ingreso | tbl_parqueos con fecha_ingreso, hora_ingreso, id_espacio, id_usuario_ingreso |
| RF-004 Registrar salida | tbl_parqueos con fecha_salida, hora_salida, total_horas |
| RF-005 Calcular cobro | tbl_tarifas + tarifa_hora_aplicada + valor_total |
| RF-006 Generar recibo | tbl_pagos.numero_recibo |
| RF-007 Registrar pago | tbl_pagos.metodo_pago y valor_pagado |
| RF-008 Estado de espacio | tbl_espacios.estado |
| RF-009 Historial | vw_historial_parqueos |
| RF-010 Reportes de ingresos | vw_ingresos_diarios |
| RF-011 Configurar tarifas | tbl_tarifas |
| RF-012 Usuarios y roles | tbl_usuarios.rol |

## 19. Por que no se creo una tabla adicional para reservas

Las reservas, parqueos activos, finalizados, vencidos, cancelados y lista de espera son estados del mismo evento operativo: la intencion o uso real de un espacio por parte de un vehiculo.

Por eso se modelan en tbl_parqueos mediante el campo estado. Esto evita duplicar estructuras, conserva el limite de 7 tablas y mantiene coherencia del dominio.

## 20. Puntos fuertes para defender

- Modelo relacional normalizado hasta 3FN.
- 7 tablas bien separadas por responsabilidad.
- Integridad referencial con FK.
- Reglas de negocio protegidas con CHECK, UNIQUE y triggers.
- Historico financiero protegido con tarifa_hora_aplicada.
- QR seguro con token_publico.
- Lista de espera sin romper el modelo.
- Reportes mediante vistas.
- Indices para rendimiento.
- Trazabilidad por usuarios internos.
- Cancelacion formal con motivo y fecha.

## 21. Respuesta corta para sustentacion

La base de datos SMARTPARK esta disenada bajo un modelo relacional normalizado en 3FN. Cuenta con 7 tablas principales que separan usuarios, clientes, vehiculos, espacios, tarifas, parqueos y pagos. La tabla central es tbl_parqueos, porque registra el ciclo completo del servicio: espera, reserva, ingreso, salida, vencimiento o cancelacion. La integridad se garantiza con claves primarias, foraneas, restricciones UNIQUE, CHECK, indices y triggers. Ademas, las vistas permiten consultar ocupacion, historial e ingresos diarios para reportes administrativos.
