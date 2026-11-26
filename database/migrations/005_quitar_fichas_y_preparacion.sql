-- =====================================================================
-- Migración 005: Eliminación de fichas operativas y preparación al paciente
-- Esquema resultante: 10 tablas en español
-- =====================================================================

USE `hospital_san_marcos`;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `servicio_ficha_items`;
DROP TABLE IF EXISTS `servicio_fichas`;

ALTER TABLE `servicios`
  DROP COLUMN IF EXISTS `clave_ficha`;
