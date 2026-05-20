# Cumplimiento Proyecto Final de Aula - SMARTPARK

## Objetivo del proyecto

SMARTPARK es un sistema web con base de datos para gestionar el parqueadero universitario "Zona Segura ECCI". Permite registrar clientes, vehiculos, espacios, tarifas, reservas, lista de espera, ingresos, salidas, pagos, recibos, reportes, usuarios con roles y consulta publica por QR.

## 1. Base de Datos

### Requisito: al menos 5 tablas relacionadas

Cumple.

SMARTPARK tiene 7 tablas principales:

1. tbl_usuarios
2. tbl_clientes
3. tbl_vehiculos
4. tbl_espacios
5. tbl_tarifas
6. tbl_parqueos
7. tbl_pagos

Relaciones principales:

- tbl_clientes 1:N tbl_vehiculos
- tbl_clientes 1:N tbl_parqueos
- tbl_vehiculos 1:N tbl_parqueos
- tbl_espacios 1:N tbl_parqueos historicos
- tbl_tarifas 1:N tbl_parqueos
- tbl_parqueos 1:1 tbl_pagos
- tbl_usuarios 1:N tbl_parqueos y tbl_pagos

Archivo principal:

- BD/smartpark.sql

### Requisito: procedimientos y funciones o equivalente

Cumple mediante logica equivalente en PHP y funciones reutilizables del backend.

Archivo:

- dashboard/smartpark_funciones.php

Funciones relevantes:

- sp_stats: estadisticas generales.
- sp_expirar_reservas: vence reservas y libera espacios.
- sp_asignar_lista_espera: asigna automaticamente cupos a lista de espera.
- sp_espacio_recomendado: recomienda espacio segun menor ocupacion.
- sp_ia_prediccion_ocupacion: prediccion operativa.
- sp_ia_prediccion_franjas: prediccion por franjas horarias.
- sp_alertas_operativas: genera alertas administrativas.
- sp_panel_ambiental: calcula impacto ambiental estimado.
- sp_handle_actions: centraliza procesos transaccionales del sistema.

Nota de sustentacion:

En MariaDB/MySQL se pueden crear procedimientos almacenados, pero en el entorno local de XAMPP se presento el error interno mysql.proc despues de actualizacion de MariaDB. Por eso, la logica equivalente se implemento en PHP con consultas preparadas, transacciones, commits, rollbacks y triggers en la base para las reglas criticas.

### Requisito: triggers para auditoria o validacion

Cumple.

Triggers definidos en BD/smartpark.sql:

- trg_parqueo_validar_ingreso
- trg_parqueo_ingreso_ocupa_espacio
- trg_pago_libera_espacio

Funciones:

- Validar que el espacio no este ocupado o reservado.
- Evitar que un vehiculo tenga dos procesos activos al mismo tiempo.
- Validar que el tipo de vehiculo coincida con el tipo de espacio.
- Cambiar automaticamente el estado del espacio al reservar/ocupar.
- Liberar el espacio al registrar el pago.

Auditoria equivalente:

- logs/auditoria.log
- dashboard/Auditoria.php
- Funcion sp_auditar en dashboard/smartpark_funciones.php

Nota para despliegue gratuito:

El script academico completo `BD/smartpark.sql` conserva los triggers. Para InfinityFree existe `BD/smartpark_infinityfree.sql`, porque el hosting gratuito puede bloquear permisos de `CREATE TRIGGER`. En ese caso la misma logica operacional se ejecuta desde PHP, sin eliminar los triggers del entregable academico.

### Requisito: transacciones con COMMIT y ROLLBACK

Cumple.

Ejemplos:

- ReservarParqueadero.php usa mysqli_begin_transaction, mysqli_commit y mysqli_rollback.
- Registro de cliente + vehiculo + reserva se ejecuta como una unidad atomica.
- Si falla placa, tarifa o espacio, se revierte la transaccion.

Tambien se usan operaciones transaccionales en el flujo de ingreso, salida, pago, confirmacion de reserva y asignacion de lista de espera desde dashboard/smartpark_funciones.php.

### Requisito: control basico de concurrencia

Cumple.

SMARTPARK maneja concurrencia con:

- Motor InnoDB.
- Llaves foraneas.
- Restricciones UNIQUE.
- Triggers de validacion.
- Bloqueos con SELECT ... FOR UPDATE en la reserva publica.
- Transacciones con COMMIT y ROLLBACK.

Ejemplo:

Cuando dos usuarios intentan reservar al mismo tiempo, la consulta selecciona el espacio libre con FOR UPDATE dentro de una transaccion. Esto bloquea temporalmente el registro hasta terminar la operacion. Si otro usuario intenta tomar el mismo espacio, la base y los triggers impiden la duplicidad.

## 2. Interfaz

### Requisito: interfaz web

Cumple.

Tecnologias:

- HTML
- CSS
- JavaScript
- PHP
- Bootstrap
- MariaDB/MySQL

Paginas publicas:

- Presentacion.php
- ReservarParqueadero.php
- Disponibilidad.php
- ConsultarReserva.php
- PortalUsuario.php
- WhatsAppReserva.php

Panel administrativo:

- dashboard/index.php
- dashboard/Clientes.php
- dashboard/Vehiculos.php
- dashboard/Espacios.php
- dashboard/Tarifas.php
- dashboard/Usuarios.php
- dashboard/ReservasSmartpark.php
- dashboard/ListaEspera.php
- dashboard/Porteria.php
- dashboard/Operacion.php
- dashboard/Reportes.php
- dashboard/IAInsights.php
- dashboard/Auditoria.php
- dashboard/Configuracion.php

### Requisito: registro, consulta, actualizacion y eliminacion de datos

Cumple.

Registro:

- Clientes.
- Vehiculos.
- Usuarios.
- Reservas.
- Ingresos.
- Salidas.
- Pagos.
- Tarifas.

Consulta:

- Historial por cliente o vehiculo.
- Disponibilidad publica.
- Consulta QR.
- Portal de usuario.
- Reportes.
- Auditoria.

Actualizacion:

- Clientes frecuentes.
- Vehiculos.
- Tarifas.
- Configuracion institucional.
- Estado de reserva: espera, reservado, activo, finalizado, vencido o cancelado.

Eliminacion:

Se maneja como eliminacion logica o cancelacion para no borrar historicos financieros ni operativos.

Ejemplos:

- Cancelar reserva: estado = cancelado.
- Desactivar usuario: estado = 0.
- Finalizar parqueo: estado = finalizado.

Esta decision protege la trazabilidad.

### Requisito: validaciones y seguridad

Cumple.

Validaciones:

- Campos obligatorios.
- Validacion de correo.
- Validacion de placa.
- Tipo de cliente controlado.
- Tipo de vehiculo controlado.
- Tarifas positivas.
- Espacios por piso valido.
- Estado del parqueo coherente.

Seguridad:

- Consultas preparadas con mysqli_prepare.
- password_hash para contrasenas.
- Roles de usuario.
- Escape de salida HTML con htmlspecialchars.
- Token publico para QR.
- Restricciones en base de datos.

## 3. Despliegue

### Requisito: sistema en linea o ejecutable

Estado actual:

- Cumple en entorno local con XAMPP.
- Para InfinityFree se debe importar `BD/smartpark_infinityfree.sql`.
- Pendiente para entrega final: pegar la URL publica final.

Opciones recomendadas:

1. Hosting PHP + MySQL tradicional.
2. InfinityFree, AwardSpace, 000webhost alternativo si esta disponible.
3. Servidor propio o VPS.
4. Render/Railway solo si se adapta PHP + MySQL externo.

Pendiente de entrega:

- URL publica.
- Importar BD/smartpark.sql en el hosting.
- Ajustar config/config.php.
- Probar login, reserva, consulta QR y dashboard.

## 4. Seguridad

### Requisito: roles y permisos

Cumple.

Roles:

- Administrador.
- Caja.
- Control.

Tabla:

- tbl_usuarios.rol

Uso:

- Administrador gestiona usuarios, tarifas, reportes, configuracion, auditoria.
- Caja registra pagos/cobros.
- Control gestiona ingresos, salidas y porteria.

### Requisito: proteccion contra inyecciones SQL

Cumple.

Se usan consultas preparadas en los flujos principales:

- Reservas.
- Portal de usuario.
- Consulta QR.
- Porteria.
- Operacion.
- Usuarios.
- Tarifas.

### Requisito: hashing de contrasenas

Cumple.

Se usa password_hash en PHP para crear usuarios y password_verify para iniciar sesion.

Tabla:

- tbl_usuarios.password VARCHAR(255)

## 5. Documentacion

### Informe PDF

Pendiente de generar en PDF, pero el contenido base ya existe en Markdown.

Archivos de apoyo:

- BD/SUSTENTACION_BASE_DATOS_SMARTPARK.md
- BD/CUMPLIMIENTO_PROYECTO_FINAL.md
- BD/normalizacion_smartpark.md
- BD/diccionario_datos_smartpark.md
- BD/consultas_smartpark.sql
- BD/smartpark.sql

El informe debe incluir:

1. Descripcion del proyecto.
2. Objetivo.
3. Problema.
4. Diagrama entidad-relacion.
5. Modelo relacional.
6. Normalizacion.
7. Scripts SQL.
8. Triggers.
9. Transacciones.
10. Concurrencia.
11. Seguridad.
12. Capturas de interfaz.
13. URL publica.
14. Conclusiones.

## 6. Criterios de evaluacion

### Funcionalidad 30%

Cumple.

SMARTPARK permite:

- Crear clientes.
- Registrar vehiculos.
- Reservar espacios.
- Manejar lista de espera.
- Confirmar ingreso.
- Registrar salida.
- Calcular cobro.
- Registrar pago.
- Generar recibos.
- Consultar historial.
- Generar reportes.
- Usar QR.
- Usar roles.

### Aplicacion de conceptos 30%

Cumple.

Conceptos aplicados:

- Consultas SQL.
- Normalizacion.
- Relaciones 1:N y 1:1.
- Llaves primarias.
- Llaves foraneas.
- UNIQUE.
- CHECK.
- Triggers.
- Vistas.
- Transacciones.
- Control de concurrencia.
- Seguridad con hashing.
- Consultas preparadas.

### Interfaz y usabilidad 20%

Cumple.

El sistema tiene:

- Interfaz publica.
- Portal de usuario.
- Dashboard administrativo.
- Pantalla de porteria.
- Reportes.
- IA operativa.
- Diseño responsive.
- Transiciones.
- Marca del equipo.

### Despliegue y documentacion 20%

Parcial.

Documentacion: cumple en Markdown, falta convertir a PDF.

Despliegue: pendiente publicar en una URL publica.

## 7. Pendientes antes de entregar

1. Publicar en hosting.
2. Importar `BD/smartpark_infinityfree.sql` en InfinityFree.
3. Configurar config.php.
4. Activar config.php para ambiente hosting.
5. Tomar capturas de:
   - Login.
   - Propuesta.
   - Reserva.
   - Consulta QR.
   - Portal usuario.
   - Dashboard.
   - Porteria.
   - Reportes.
   - Auditoria.
6. Generar PDF final.
7. Incluir URL publica en el PDF.
8. Comprimir carpeta con codigo y scripts SQL.

## 8. Resumen para exponer

SMARTPARK es un sistema web con base de datos relacional desarrollado en PHP y MariaDB. La base de datos tiene 7 tablas principales relacionadas, cumple normalizacion hasta tercera forma normal, usa llaves primarias, foraneas, restricciones UNIQUE y CHECK, indices, vistas y triggers. El sistema implementa transacciones con COMMIT y ROLLBACK, control de concurrencia con InnoDB y SELECT FOR UPDATE, seguridad mediante roles, contrasenas hasheadas y consultas preparadas. La interfaz permite registrar, consultar, actualizar y cancelar datos sin perder trazabilidad historica.
