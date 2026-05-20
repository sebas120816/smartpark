# Checklist de Entrega - SMARTPARK

## 1. Base de datos
- [x] Contiene al menos 5 tablas relacionadas.
- [x] Incluye `tbl_usuarios`, `tbl_clientes`, `tbl_vehiculos`, `tbl_espacios`, `tbl_tarifas`, `tbl_parqueos`, `tbl_pagos`.
- [x] Implementa triggers en `BD/smartpark.sql`:
  - `trg_parqueo_validar_ingreso`
  - `trg_parqueo_ingreso_ocupa_espacio`
  - `trg_pago_libera_espacio`
- [x] Agrega procedimientos/funciones en `BD/procedimientos_y_transacciones.sql`:
  - `fn_calcular_total_parqueo`
  - `sp_registrar_pago`
- [x] Ejemplos de transacciones con `START TRANSACTION`, `COMMIT` y `ROLLBACK` en `BD/procedimientos_y_transacciones.sql`.
- [x] Explicación de control de concurrencia en el informe:
  - Uso de `SELECT ... FOR UPDATE` en reservas.

## 2. Interfaz
- [x] Aplicación web con PHP/HTML/CSS/JS.
- [x] Permite registro, consulta, actualización y eliminación de datos.
- [x] Incluye formularios con validaciones y controles básicos.

## 3. Despliegue
- [ ] URL pública del sistema.
- [x] Instrucciones de despliegue incluidas en el informe.
- [x] Configuración de correo SMTP centralizada en `emails/mailer_config.php`.

## 4. Seguridad
- [x] Roles y permisos definidos en `tbl_usuarios`.
- [x] Uso de consultas preparadas (`mysqli_prepare`) para evitar inyección SQL.
- [x] Manejo seguro de contraseñas con `password_hash` y `password_verify`.

## 5. Documentación
- [x] Informe base creado en `BD/Informe_Entrega.md`.
- [x] Diagrama ER incluido y diccionario de datos disponible.
- [x] Scripts SQL listados: `BD/smartpark.sql`, `BD/smartpark_infinityfree.sql`, `BD/procedimientos_y_transacciones.sql`.
- [ ] Agregar capturas de pantalla de la interfaz.
- [ ] Generar versión PDF del informe.

## 6. Entrega
- [ ] Comprimir carpeta con:
  - Código fuente.
  - Scripts SQL.
  - Informe PDF.
- [ ] Incluir URL pública para prueba.
