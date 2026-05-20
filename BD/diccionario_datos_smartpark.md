# Diccionario de Datos SMARTPARK

## tbl_usuarios

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_usuario | INT | PK, AI | Identificador del usuario interno. |
| nombre | VARCHAR(120) | NOT NULL | Nombre completo. |
| email | VARCHAR(120) | UNIQUE, NOT NULL | Correo de acceso. |
| password | VARCHAR(255) | NOT NULL | Hash de contraseña. |
| rol | ENUM | NOT NULL | Administrador, Caja o Control. |
| estado | TINYINT | CHECK | 1 activo, 0 inactivo. |
| ultima_sesion | DATETIME | NULL | Último ingreso al sistema. |
| creado_en | TIMESTAMP | DEFAULT | Fecha de creación. |

## tbl_clientes

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_cliente | INT | PK, AI | Identificador del cliente. |
| cedula | VARCHAR(30) | UNIQUE, NOT NULL | Documento, código o identificación. |
| nombres | VARCHAR(160) | NOT NULL | Nombres completos. |
| telefono | VARCHAR(30) | NOT NULL | Teléfono de contacto. |
| correo | VARCHAR(120) | CHECK | Correo opcional. |
| tipo_cliente | ENUM | NOT NULL | estudiante, docente, funcionario o visitante. |
| creado_en | TIMESTAMP | DEFAULT | Fecha de registro. |

## tbl_vehiculos

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_vehiculo | INT | PK, AI | Identificador del vehículo. |
| id_cliente | INT | FK | Cliente propietario o responsable. |
| placa | VARCHAR(20) | UNIQUE, NOT NULL | Placa del vehículo. |
| marca | VARCHAR(80) | NOT NULL | Marca. |
| modelo | VARCHAR(80) | NOT NULL | Modelo. |
| color | VARCHAR(60) | NOT NULL | Color. |
| tipo | ENUM | NOT NULL | auto o moto. |
| creado_en | TIMESTAMP | DEFAULT | Fecha de registro. |

## tbl_espacios

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_espacio | INT | PK, AI | Identificador del espacio. |
| codigo | VARCHAR(20) | UNIQUE, NOT NULL | Código visible del espacio. |
| piso | TINYINT | CHECK | Piso 1 o 2. |
| tipo_vehiculo | ENUM | NOT NULL | Tipo de vehículo permitido. |
| estado | ENUM | NOT NULL | libre, reservado u ocupado. |

## tbl_tarifas

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_tarifa | INT | PK, AI | Identificador de tarifa. |
| tipo_vehiculo | ENUM | UNIQUE, NOT NULL | auto o moto. |
| valor_hora | DECIMAL(10,2) | CHECK | Valor por hora. |
| estado | TINYINT | CHECK | Tarifa activa o inactiva. |
| actualizado_en | TIMESTAMP | DEFAULT | Última actualización. |

## tbl_parqueos

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_parqueo | INT | PK, AI | Identificador del evento. |
| id_cliente | INT | FK | Cliente asociado. |
| id_vehiculo | INT | FK | Vehículo asociado. |
| id_espacio | INT | FK, NULL | Espacio asignado. Es NULL cuando la solicitud esta en lista de espera. |
| id_tarifa | INT | FK | Tarifa usada. |
| id_usuario_ingreso | INT | FK, NULL | Usuario que confirma ingreso. |
| id_usuario_salida | INT | FK, NULL | Usuario que registra salida. |
| fecha_ingreso | DATE | NOT NULL | Fecha de reserva o ingreso. |
| hora_ingreso | TIME | NOT NULL | Hora de reserva o ingreso. |
| fecha_salida | DATE | NULL | Fecha de salida. |
| hora_salida | TIME | NULL | Hora de salida. |
| tarifa_hora_aplicada | DECIMAL(10,2) | CHECK | Snapshot de tarifa usada. |
| total_horas | INT | CHECK, NULL | Tiempo cobrado. |
| valor_total | DECIMAL(10,2) | CHECK, NULL | Valor cobrado. |
| reserva_expira_en | DATETIME | NULL | Fecha/hora límite para llegar. En espera queda NULL hasta asignar cupo. |
| codigo_reserva | VARCHAR(30) | UNIQUE, NULL | Código de reserva o lista de espera. |
| estado | ENUM | NOT NULL | espera, reservado, activo, finalizado, vencido o cancelado. |
| creado_en | TIMESTAMP | DEFAULT | Fecha de creación. |

## tbl_pagos

| Campo | Tipo | Restricción | Descripción |
|---|---|---|---|
| id_pago | INT | PK, AI | Identificador del pago. |
| id_parqueo | INT | FK, UNIQUE | Parqueo pagado. |
| numero_recibo | VARCHAR(30) | UNIQUE, NOT NULL | Recibo único. |
| fecha_pago | DATETIME | NOT NULL | Fecha y hora del pago. |
| metodo_pago | ENUM | NOT NULL | efectivo, nequi, daviplata, tarjeta o pasarela. |
| valor_pagado | DECIMAL(10,2) | CHECK | Valor pagado. |
| id_usuario | INT | FK | Usuario que registra el pago. |
