-- =====================================================================
-- Migración 003: Campos de gestión administrativa en citas (appointments)
-- Base de datos: hospital_san_marcos (utf8mb4 / InnoDB)
-- =====================================================================

USE `hospital_san_marcos`;
SET NAMES utf8mb4;

ALTER TABLE `appointments`
  ADD COLUMN IF NOT EXISTS `confirmed_time` VARCHAR(20) NULL DEFAULT NULL AFTER `shift`,
  ADD COLUMN IF NOT EXISTS `admin_notes`    TEXT NULL DEFAULT NULL AFTER `reason`;
