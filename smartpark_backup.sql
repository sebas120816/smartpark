-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: smartpark
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbl_clientes`
--

DROP TABLE IF EXISTS `tbl_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(30) NOT NULL,
  `nombres` varchar(160) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `tipo_cliente` enum('estudiante','docente','funcionario','visitante') NOT NULL DEFAULT 'visitante',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `uk_clientes_cedula` (`cedula`),
  CONSTRAINT `chk_clientes_correo` CHECK (`correo` is null or `correo` like '%_@_%._%')
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Clientes propietarios o responsables de vehiculos.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_clientes`
--

LOCK TABLES `tbl_clientes` WRITE;
/*!40000 ALTER TABLE `tbl_clientes` DISABLE KEYS */;
INSERT INTO `tbl_clientes` VALUES (25,'DEMO-1001','Laura Gomez','3001112233','laura.gomez@ecci.edu.co','estudiante','2026-05-13 17:36:03'),(26,'DEMO-1002','Carlos Mendez','3102223344','carlos.mendez@ecci.edu.co','docente','2026-05-13 17:36:03'),(27,'DEMO-1003','Diana Perez','3203334455','diana.perez@ecci.edu.co','funcionario','2026-05-13 17:36:03'),(28,'DEMO-1004','Andres Rojas','3014445566','andres.rojas@ecci.edu.co','estudiante','2026-05-13 17:36:03'),(29,'DEMO-1005','Paula Torres','3025556677','paula.torres@ecci.edu.co','docente','2026-05-13 17:36:03'),(30,'DEMO-1006','Miguel Castro','3036667788','miguel.castro@ecci.edu.co','visitante','2026-05-13 17:36:03'),(31,'DEMO-1007','Natalia Ruiz','3047778899','natalia.ruiz@ecci.edu.co','funcionario','2026-05-13 17:36:03'),(32,'DEMO-1008','Johan Rojas','3058889900','johan.rojas@ecci.edu.co','estudiante','2026-05-13 17:36:03'),(33,'DEMO-1009','Harold Acosta','3069990011','harold.acosta@ecci.edu.co','estudiante','2026-05-13 17:36:03'),(34,'DEMO-1010','Joseph Sebastian','3070001122','joseph.sebastian@ecci.edu.co','estudiante','2026-05-13 17:36:03'),(35,'DEMO-1011','Marcela Nieto','3081112233','marcela.nieto@ecci.edu.co','docente','2026-05-13 17:36:03'),(36,'DEMO-1012','Oscar Salazar','3092223344','oscar.salazar@ecci.edu.co','visitante','2026-05-13 17:36:03'),(37,'1','juan cardenas','3205456','jj@cun.edu.co','docente','2026-05-13 18:25:38');
/*!40000 ALTER TABLE `tbl_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_espacios`
--

DROP TABLE IF EXISTS `tbl_espacios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_espacios` (
  `id_espacio` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `piso` tinyint(4) NOT NULL,
  `tipo_vehiculo` enum('auto','moto') NOT NULL,
  `estado` enum('libre','reservado','ocupado') NOT NULL DEFAULT 'libre',
  PRIMARY KEY (`id_espacio`),
  UNIQUE KEY `uk_espacios_codigo` (`codigo`),
  KEY `idx_espacios_estado_tipo` (`estado`,`tipo_vehiculo`),
  CONSTRAINT `chk_espacios_piso` CHECK (`piso` between 1 and 2)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Espacios fisicos del parqueadero distribuidos en dos pisos.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_espacios`
--

LOCK TABLES `tbl_espacios` WRITE;
/*!40000 ALTER TABLE `tbl_espacios` DISABLE KEYS */;
INSERT INTO `tbl_espacios` VALUES (1,'P1-A01',1,'auto','libre'),(2,'P1-A02',1,'auto','libre'),(3,'P1-A03',1,'auto','libre'),(4,'P1-A04',1,'auto','libre'),(5,'P1-A05',1,'auto','libre'),(6,'P1-A06',1,'auto','libre'),(7,'P1-A07',1,'auto','libre'),(8,'P1-A08',1,'auto','libre'),(9,'P1-A09',1,'auto','libre'),(10,'P1-A10',1,'auto','libre'),(11,'P1-M01',1,'moto','libre'),(12,'P1-M02',1,'moto','libre'),(13,'P1-M03',1,'moto','libre'),(14,'P1-M04',1,'moto','libre'),(15,'P1-M05',1,'moto','libre'),(16,'P2-A01',2,'auto','libre'),(17,'P2-A02',2,'auto','libre'),(18,'P2-A03',2,'auto','libre'),(19,'P2-A04',2,'auto','ocupado'),(20,'P2-A05',2,'auto','libre'),(21,'P2-A06',2,'auto','libre'),(22,'P2-A07',2,'auto','libre'),(23,'P2-A08',2,'auto','libre'),(24,'P2-A09',2,'auto','libre'),(25,'P2-A10',2,'auto','libre'),(26,'P2-A11',2,'auto','libre'),(27,'P2-A12',2,'auto','libre'),(28,'P2-A13',2,'auto','libre'),(29,'P2-A14',2,'auto','libre'),(30,'P2-A15',2,'auto','libre'),(31,'P2-A16',2,'auto','libre'),(32,'P2-A17',2,'auto','libre'),(33,'P2-A18',2,'auto','libre'),(34,'P2-A19',2,'auto','libre'),(35,'P2-A20',2,'auto','libre'),(36,'P2-M01',2,'moto','libre'),(37,'P2-M02',2,'moto','libre'),(38,'P2-M03',2,'moto','ocupado'),(39,'P2-M04',2,'moto','ocupado'),(40,'P2-M05',2,'moto','libre'),(41,'P2-M06',2,'moto','libre'),(42,'P2-M07',2,'moto','libre'),(43,'P2-M08',2,'moto','libre'),(44,'P2-M09',2,'moto','libre'),(45,'P2-M10',2,'moto','libre'),(46,'P2-M11',2,'moto','libre'),(47,'P2-M12',2,'moto','libre'),(48,'P2-M13',2,'moto','libre'),(49,'P2-M14',2,'moto','libre'),(50,'P2-M15',2,'moto','libre');
/*!40000 ALTER TABLE `tbl_espacios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_pagos`
--

DROP TABLE IF EXISTS `tbl_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_parqueo` int(11) NOT NULL,
  `numero_recibo` varchar(30) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  `metodo_pago` enum('efectivo','nequi','daviplata','tarjeta','pasarela') NOT NULL,
  `valor_pagado` decimal(10,2) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_pago`),
  UNIQUE KEY `uk_pagos_parqueo` (`id_parqueo`),
  UNIQUE KEY `uk_pagos_numero_recibo` (`numero_recibo`),
  KEY `fk_pagos_usuarios` (`id_usuario`),
  KEY `idx_pagos_fecha` (`fecha_pago`),
  CONSTRAINT `fk_pagos_parqueos` FOREIGN KEY (`id_parqueo`) REFERENCES `tbl_parqueos` (`id_parqueo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pagos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `chk_pagos_valor` CHECK (`valor_pagado` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pagos y recibos unicos asociados a parqueos finalizados.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_pagos`
--

LOCK TABLES `tbl_pagos` WRITE;
/*!40000 ALTER TABLE `tbl_pagos` DISABLE KEYS */;
INSERT INTO `tbl_pagos` VALUES (7,19,'DM-RC-001','2026-05-07 10:05:00','efectivo',10500.00,2),(8,20,'DM-RC-002','2026-05-08 11:20:00','efectivo',7200.00,2),(9,21,'DM-RC-003','2026-05-09 20:15:00','daviplata',10500.00,2),(10,22,'DM-RC-004','2026-05-10 21:10:00','efectivo',14000.00,2),(11,23,'DM-RC-005','2026-05-11 09:00:00','efectivo',3600.00,2),(12,24,'DM-RC-006','2026-05-12 12:05:00','daviplata',14000.00,2);
/*!40000 ALTER TABLE `tbl_pagos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pago_libera_espacio
AFTER INSERT ON tbl_pagos
FOR EACH ROW
BEGIN
  UPDATE tbl_espacios e
  INNER JOIN tbl_parqueos p ON p.id_espacio = e.id_espacio
  SET e.estado = 'libre'
  WHERE p.id_parqueo = NEW.id_parqueo;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tbl_parqueos`
--

DROP TABLE IF EXISTS `tbl_parqueos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_parqueos` (
  `id_parqueo` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `id_espacio` int(11) DEFAULT NULL,
  `id_tarifa` int(11) NOT NULL,
  `id_usuario_ingreso` int(11) DEFAULT NULL,
  `id_usuario_salida` int(11) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `hora_ingreso` time NOT NULL,
  `fecha_salida` date DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `tarifa_hora_aplicada` decimal(10,2) NOT NULL,
  `total_horas` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `reserva_expira_en` datetime DEFAULT NULL,
  `codigo_reserva` varchar(30) DEFAULT NULL,
  `token_publico` varchar(80) DEFAULT NULL,
  `cancelado_en` datetime DEFAULT NULL,
  `motivo_cancelacion` varchar(180) DEFAULT NULL,
  `estado` enum('espera','reservado','activo','finalizado','vencido','cancelado') NOT NULL DEFAULT 'activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_parqueo`),
  UNIQUE KEY `uk_parqueos_codigo_reserva` (`codigo_reserva`),
  UNIQUE KEY `uk_parqueos_token_publico` (`token_publico`),
  KEY `fk_parqueos_espacios` (`id_espacio`),
  KEY `fk_parqueos_tarifas` (`id_tarifa`),
  KEY `fk_parqueos_usuario_ingreso` (`id_usuario_ingreso`),
  KEY `fk_parqueos_usuario_salida` (`id_usuario_salida`),
  KEY `idx_parqueos_estado` (`estado`),
  KEY `idx_parqueos_fechas` (`fecha_ingreso`,`fecha_salida`),
  KEY `idx_parqueos_reserva_expira` (`reserva_expira_en`),
  KEY `idx_parqueos_cliente` (`id_cliente`),
  KEY `idx_parqueos_vehiculo` (`id_vehiculo`),
  CONSTRAINT `fk_parqueos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_parqueos_espacios` FOREIGN KEY (`id_espacio`) REFERENCES `tbl_espacios` (`id_espacio`) ON UPDATE CASCADE,
  CONSTRAINT `fk_parqueos_tarifas` FOREIGN KEY (`id_tarifa`) REFERENCES `tbl_tarifas` (`id_tarifa`) ON UPDATE CASCADE,
  CONSTRAINT `fk_parqueos_usuario_ingreso` FOREIGN KEY (`id_usuario_ingreso`) REFERENCES `tbl_usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_parqueos_usuario_salida` FOREIGN KEY (`id_usuario_salida`) REFERENCES `tbl_usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_parqueos_vehiculos` FOREIGN KEY (`id_vehiculo`) REFERENCES `tbl_vehiculos` (`id_vehiculo`) ON UPDATE CASCADE,
  CONSTRAINT `chk_parqueos_horas` CHECK (`total_horas` is null or `total_horas` >= 1),
  CONSTRAINT `chk_parqueos_valor` CHECK (`valor_total` is null or `valor_total` >= 0),
  CONSTRAINT `chk_parqueos_tarifa` CHECK (`tarifa_hora_aplicada` > 0),
  CONSTRAINT `chk_parqueos_salida` CHECK (`estado` = 'espera' and `id_espacio` is null and `reserva_expira_en` is null and `fecha_salida` is null and `hora_salida` is null and `id_usuario_salida` is null or `estado` in ('reservado','vencido') and `id_espacio` is not null and `fecha_salida` is null and `hora_salida` is null and `id_usuario_salida` is null or `estado` = 'cancelado' and `fecha_salida` is null and `hora_salida` is null and `id_usuario_salida` is null or `estado` = 'activo' and `id_espacio` is not null and `fecha_salida` is null and `hora_salida` is null and `id_usuario_salida` is null and `id_usuario_ingreso` is not null or `estado` = 'finalizado' and `id_espacio` is not null and `fecha_salida` is not null and `hora_salida` is not null and `id_usuario_salida` is not null)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Eventos de ingreso y salida de vehiculos.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_parqueos`
--

LOCK TABLES `tbl_parqueos` WRITE;
/*!40000 ALTER TABLE `tbl_parqueos` DISABLE KEYS */;
INSERT INTO `tbl_parqueos` VALUES (19,25,25,1,1,1,2,'2026-05-07','07:15:00','2026-05-07','10:05:00',3500.00,3,10500.00,NULL,'DM-FIN-001','tk_bf8ba2b0a236ba9a316b7daa00feeb0a68d41c11fa76af91075715040c9ac584',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(20,26,26,11,2,1,2,'2026-05-08','08:10:00','2026-05-08','11:20:00',1800.00,4,7200.00,NULL,'DM-FIN-002','tk_422f70f7219d5e7d1407ee59d2b062c4d5fe49165958bdd0a905adf0607a34c9',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(21,27,27,2,1,1,2,'2026-05-09','17:35:00','2026-05-09','20:15:00',3500.00,3,10500.00,NULL,'DM-FIN-003','tk_2c59e278a4f3351c945da533f9a5e0f297974057fe9f51d750abc42200ebd51f',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(22,28,28,16,1,1,2,'2026-05-10','18:05:00','2026-05-10','21:10:00',3500.00,4,14000.00,NULL,'DM-FIN-004','tk_4e86309a9351cf72049431eb6299bd753c910288dd72b03c4076648d1a2e0e38',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(23,29,29,36,2,1,2,'2026-05-11','07:45:00','2026-05-11','09:00:00',1800.00,2,3600.00,NULL,'DM-FIN-005','tk_0e948cc7d97cacad388a4570039c580d127dbd547643ab1d765b4de620d0ef3d',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(24,30,30,17,1,1,2,'2026-05-12','08:30:00','2026-05-12','12:05:00',3500.00,4,14000.00,NULL,'DM-FIN-006','tk_e41d56028f11816667407132e59f10f7b0ba47b70e1e73fce518c6318554f3dd',NULL,NULL,'finalizado','2026-05-13 17:36:03'),(25,31,31,37,2,NULL,NULL,'2026-05-13','12:36:03',NULL,NULL,1800.00,NULL,NULL,'2026-05-13 12:48:03','DM-RS-001','tk_9da50c7747253bb0c86b9fe473d7ba7884d927f8ad661e2e2f1ab75bdd5cc2ae',NULL,NULL,'vencido','2026-05-13 17:36:03'),(26,32,32,18,1,NULL,NULL,'2026-05-13','12:36:03',NULL,NULL,3500.00,NULL,NULL,'2026-05-13 12:43:03','DM-RS-002','tk_1a4c6b10f14294e38af439167abe46df40736ca823f399183fc8579379cd02ef',NULL,NULL,'vencido','2026-05-13 17:36:03'),(27,33,33,38,2,3,NULL,'2026-05-13','07:55:00',NULL,NULL,1800.00,NULL,NULL,NULL,'DM-ACT-001','tk_9c68348c5ef542900f0c475a3fbdc07eb3aa16913457933f6e634fb49458d905',NULL,NULL,'activo','2026-05-13 17:36:03'),(28,34,34,19,1,3,NULL,'2026-05-13','08:20:00',NULL,NULL,3500.00,NULL,NULL,NULL,'DM-ACT-002','tk_a63a6d68582d9ecab761add5928ac5aa5cf3f40e8423e77f7ab5889efb45db59',NULL,NULL,'activo','2026-05-13 17:36:03'),(29,35,35,39,2,NULL,NULL,'2026-05-13','06:40:00',NULL,NULL,1800.00,NULL,NULL,'2026-05-13 12:16:03','DM-VEN-001','tk_b8cd749c8b4c9c6e3c1cf7fd56ebcc4dd7e99876ac7f1feb60af4927717ab637',NULL,NULL,'vencido','2026-05-13 17:36:03'),(30,36,36,1,1,NULL,NULL,'2026-05-13','12:36:03',NULL,NULL,3500.00,NULL,NULL,'2026-05-13 12:51:12','DM-LE-001','tk_f815f0ab3917256285acaa23aa52ff41adc1a4a64c17c287ac91451d291fcf27',NULL,NULL,'vencido','2026-05-13 17:36:03'),(31,37,37,11,2,NULL,NULL,'2026-05-13','13:25:38',NULL,NULL,1800.00,NULL,NULL,'2026-05-13 13:40:38','RS-20260513132538-820','tk_34fafd48f601bd201fb6daf3ca93d2792e882a409c7c52237c96ba4fe190a7e9',NULL,NULL,'vencido','2026-05-13 18:25:38');
/*!40000 ALTER TABLE `tbl_parqueos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_parqueo_validar_ingreso
BEFORE INSERT ON tbl_parqueos
FOR EACH ROW
BEGIN
  DECLARE v_espacio_bloqueado INT DEFAULT 0;
  DECLARE v_vehiculo_activo INT DEFAULT 0;
  DECLARE v_tipo_vehiculo VARCHAR(10);
  DECLARE v_tipo_espacio VARCHAR(10);
  DECLARE v_cliente_vehiculo INT;

  SELECT COUNT(*)
  INTO v_espacio_bloqueado
  FROM tbl_espacios
  WHERE id_espacio = NEW.id_espacio AND estado IN ('reservado', 'ocupado');

  IF v_espacio_bloqueado > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El espacio ya se encuentra reservado u ocupado.';
  END IF;

  SELECT COUNT(*)
  INTO v_vehiculo_activo
  FROM tbl_parqueos
  WHERE id_vehiculo = NEW.id_vehiculo AND estado IN ('espera', 'reservado', 'activo');

  IF v_vehiculo_activo > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehiculo ya tiene una solicitud, reserva o parqueo activo.';
  END IF;

  SELECT id_cliente, tipo
  INTO v_cliente_vehiculo, v_tipo_vehiculo
  FROM tbl_vehiculos
  WHERE id_vehiculo = NEW.id_vehiculo;

  IF NEW.id_cliente <> v_cliente_vehiculo THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El cliente no corresponde al vehiculo.';
  END IF;

  IF NEW.estado = 'espera' THEN
    IF NEW.id_espacio IS NOT NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una solicitud en espera no debe tener espacio asignado.';
    END IF;
  ELSE
    SELECT tipo_vehiculo
    INTO v_tipo_espacio
    FROM tbl_espacios
    WHERE id_espacio = NEW.id_espacio;

    IF v_tipo_vehiculo <> v_tipo_espacio THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El tipo de vehiculo no corresponde al tipo de espacio.';
    END IF;

    IF NEW.estado = 'reservado' AND NEW.reserva_expira_en IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La reserva debe tener fecha y hora de expiracion.';
    END IF;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_parqueo_ingreso_ocupa_espacio
AFTER INSERT ON tbl_parqueos
FOR EACH ROW
BEGIN
  IF NEW.id_espacio IS NOT NULL THEN
    UPDATE tbl_espacios
    SET estado = CASE
      WHEN NEW.estado = 'reservado' THEN 'reservado'
      ELSE 'ocupado'
    END
    WHERE id_espacio = NEW.id_espacio;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tbl_tarifas`
--

DROP TABLE IF EXISTS `tbl_tarifas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_tarifas` (
  `id_tarifa` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_vehiculo` enum('auto','moto') NOT NULL,
  `valor_hora` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tarifa`),
  UNIQUE KEY `uk_tarifas_tipo` (`tipo_vehiculo`),
  CONSTRAINT `chk_tarifas_valor` CHECK (`valor_hora` > 0),
  CONSTRAINT `chk_tarifas_estado` CHECK (`estado` in (0,1))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tarifas por hora segun tipo de vehiculo.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tarifas`
--

LOCK TABLES `tbl_tarifas` WRITE;
/*!40000 ALTER TABLE `tbl_tarifas` DISABLE KEYS */;
INSERT INTO `tbl_tarifas` VALUES (1,'auto',3500.00,1,'2026-05-13 17:24:36'),(2,'moto',1800.00,1,'2026-05-13 17:24:36');
/*!40000 ALTER TABLE `tbl_tarifas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_usuarios`
--

DROP TABLE IF EXISTS `tbl_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','Caja','Control') NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `ultima_sesion` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  CONSTRAINT `chk_usuarios_estado` CHECK (`estado` in (0,1)),
  CONSTRAINT `chk_usuarios_email` CHECK (`email` like '%_@_%._%')
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuarios internos del sistema con roles de Administrador, Caja y Control.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_usuarios`
--

LOCK TABLES `tbl_usuarios` WRITE;
/*!40000 ALTER TABLE `tbl_usuarios` DISABLE KEYS */;
INSERT INTO `tbl_usuarios` VALUES (1,'Administrador SMARTPARK','admin@smartpark.com','$2y$12$0AkTV5AdbQvDJv09sbDEEur6COcp36zO1emKLTE4AgmdChfh2R/S6','Administrador',1,'2026-05-13 20:37:43','2026-05-13 17:24:35'),(2,'Caja SMARTPARK','caja@smartpark.com','$2y$12$/j9J1IwYQw/6hkJdqzDiweQAtDB5PJcmK46Wxf/f8IubU1vhtRJSu','Caja',1,NULL,'2026-05-13 17:24:35'),(3,'Control SMARTPARK','control@smartpark.com','$2y$12$aIvmM2W64nBu/XyZy2UxsewoLBDSr3jlMAcLKHI9UdbnzQMudZ1/W','Control',1,NULL,'2026-05-13 17:24:35');
/*!40000 ALTER TABLE `tbl_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vehiculos`
--

DROP TABLE IF EXISTS `tbl_vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vehiculos` (
  `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `marca` varchar(80) NOT NULL,
  `modelo` varchar(80) NOT NULL,
  `color` varchar(60) NOT NULL,
  `tipo` enum('auto','moto') NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_vehiculo`),
  UNIQUE KEY `uk_vehiculos_placa` (`placa`),
  KEY `idx_vehiculos_cliente` (`id_cliente`),
  KEY `idx_vehiculos_tipo` (`tipo`),
  CONSTRAINT `fk_vehiculos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vehiculos asociados a clientes. La placa identifica un vehiculo.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vehiculos`
--

LOCK TABLES `tbl_vehiculos` WRITE;
/*!40000 ALTER TABLE `tbl_vehiculos` DISABLE KEYS */;
INSERT INTO `tbl_vehiculos` VALUES (25,25,'DMO101','Chevrolet','Spark','Rojo','auto','2026-05-13 17:36:03'),(26,26,'DMO202','Yamaha','FZ','Negro','moto','2026-05-13 17:36:03'),(27,27,'DMO303','Renault','Logan','Gris','auto','2026-05-13 17:36:03'),(28,28,'DMO404','Kia','Picanto','Azul','auto','2026-05-13 17:36:03'),(29,29,'DMO505','Suzuki','Gixxer','Blanco','moto','2026-05-13 17:36:03'),(30,30,'DMO606','Mazda','2','Plata','auto','2026-05-13 17:36:03'),(31,31,'DMO707','Honda','CB 160','Rojo','moto','2026-05-13 17:36:03'),(32,32,'DMO808','Toyota','Corolla','Negro','auto','2026-05-13 17:36:03'),(33,33,'DMO909','AKT','NKD','Verde','moto','2026-05-13 17:36:03'),(34,34,'DMO010','Nissan','Versa','Blanco','auto','2026-05-13 17:36:03'),(35,35,'DMO111','Bajaj','Pulsar','Azul','moto','2026-05-13 17:36:03'),(36,36,'DMO212','Ford','Fiesta','Gris','auto','2026-05-13 17:36:03'),(37,37,'HCK678','ford','2013','gris','moto','2026-05-13 18:25:38');
/*!40000 ALTER TABLE `tbl_vehiculos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_historial_parqueos`
--

DROP TABLE IF EXISTS `vw_historial_parqueos`;
/*!50001 DROP VIEW IF EXISTS `vw_historial_parqueos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_historial_parqueos` AS SELECT
 1 AS `id_parqueo`,
  1 AS `numero_recibo`,
  1 AS `codigo_reserva`,
  1 AS `cedula`,
  1 AS `cliente`,
  1 AS `tipo_cliente`,
  1 AS `placa`,
  1 AS `tipo_vehiculo`,
  1 AS `espacio`,
  1 AS `fecha_ingreso`,
  1 AS `hora_ingreso`,
  1 AS `fecha_salida`,
  1 AS `hora_salida`,
  1 AS `total_horas`,
  1 AS `tarifa_hora_aplicada`,
  1 AS `valor_total`,
  1 AS `reserva_expira_en`,
  1 AS `metodo_pago`,
  1 AS `estado` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_ingresos_diarios`
--

DROP TABLE IF EXISTS `vw_ingresos_diarios`;
/*!50001 DROP VIEW IF EXISTS `vw_ingresos_diarios`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_ingresos_diarios` AS SELECT
 1 AS `fecha`,
  1 AS `cantidad_pagos`,
  1 AS `total_recaudado` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_ocupacion_espacios`
--

DROP TABLE IF EXISTS `vw_ocupacion_espacios`;
/*!50001 DROP VIEW IF EXISTS `vw_ocupacion_espacios`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_ocupacion_espacios` AS SELECT
 1 AS `piso`,
  1 AS `tipo_vehiculo`,
  1 AS `estado`,
  1 AS `total_espacios` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_historial_parqueos`
--

/*!50001 DROP VIEW IF EXISTS `vw_historial_parqueos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_historial_parqueos` AS select `p`.`id_parqueo` AS `id_parqueo`,`pg`.`numero_recibo` AS `numero_recibo`,`p`.`codigo_reserva` AS `codigo_reserva`,`c`.`cedula` AS `cedula`,`c`.`nombres` AS `cliente`,`c`.`tipo_cliente` AS `tipo_cliente`,`v`.`placa` AS `placa`,`v`.`tipo` AS `tipo_vehiculo`,`e`.`codigo` AS `espacio`,`p`.`fecha_ingreso` AS `fecha_ingreso`,`p`.`hora_ingreso` AS `hora_ingreso`,`p`.`fecha_salida` AS `fecha_salida`,`p`.`hora_salida` AS `hora_salida`,`p`.`total_horas` AS `total_horas`,`p`.`tarifa_hora_aplicada` AS `tarifa_hora_aplicada`,`p`.`valor_total` AS `valor_total`,`p`.`reserva_expira_en` AS `reserva_expira_en`,`pg`.`metodo_pago` AS `metodo_pago`,`p`.`estado` AS `estado` from ((((`tbl_parqueos` `p` join `tbl_clientes` `c` on(`c`.`id_cliente` = `p`.`id_cliente`)) join `tbl_vehiculos` `v` on(`v`.`id_vehiculo` = `p`.`id_vehiculo`)) left join `tbl_espacios` `e` on(`e`.`id_espacio` = `p`.`id_espacio`)) left join `tbl_pagos` `pg` on(`pg`.`id_parqueo` = `p`.`id_parqueo`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_ingresos_diarios`
--

/*!50001 DROP VIEW IF EXISTS `vw_ingresos_diarios`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ingresos_diarios` AS select cast(`tbl_pagos`.`fecha_pago` as date) AS `fecha`,count(0) AS `cantidad_pagos`,sum(`tbl_pagos`.`valor_pagado`) AS `total_recaudado` from `tbl_pagos` group by cast(`tbl_pagos`.`fecha_pago` as date) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_ocupacion_espacios`
--

/*!50001 DROP VIEW IF EXISTS `vw_ocupacion_espacios`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ocupacion_espacios` AS select `tbl_espacios`.`piso` AS `piso`,`tbl_espacios`.`tipo_vehiculo` AS `tipo_vehiculo`,`tbl_espacios`.`estado` AS `estado`,count(0) AS `total_espacios` from `tbl_espacios` group by `tbl_espacios`.`piso`,`tbl_espacios`.`tipo_vehiculo`,`tbl_espacios`.`estado` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-13 14:56:55
