-- =====================================================================
-- Migración 002: Correspondencia Médico -> Especialidad, Citas y Contacto
-- Base de datos: hospital_san_marcos (utf8mb4 / InnoDB)
-- =====================================================================

USE `hospital_san_marcos`;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Tabla puente N:M entre médicos y especialidades (soporte doble especialidad)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `doctor_specialties` (
  `doctor_id`    INT UNSIGNED NOT NULL,
  `specialty_id` INT UNSIGNED NOT NULL,
  `is_primary`   TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`doctor_id`, `specialty_id`),
  KEY `idx_ds_specialty` (`specialty_id`),
  CONSTRAINT `fk_ds_doctor`    FOREIGN KEY (`doctor_id`)    REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ds_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `doctor_specialties`;
UPDATE `doctors` SET `specialty_id` = NULL;

-- Especialidad principal (28 médicos; Juan Mateus #8 permanece en NULL)
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'cirugia-general'               SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('johny-cabrera','erick-apolo','pablo-andres-cadena-pinargote','jorge-estuardo-herrera-guerrero','sabina-tipantaxi','abdon-pacurucu');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'cirugia-pediatrica'            SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('cesar-gia');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'cirugia-estetico-maxilofacial' SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('mauricio-arichabala');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'neurocirugia'                  SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('rafael-barcia-sanchez');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'ginecologia-y-obstetricia'     SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('odeley-rodriguez','klever-mendieta','pedro-ayala');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'medicina-interna'              SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('byron-sanchez','luis-davila','luis-miguel-gaibor','ronald-morocho');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'medicina-familiar'             SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('sixto-barriga','patricio-carrion');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'cardiologia'                   SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('jorge-castro');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'gastroenterologia'             SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('enkly-heredia');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'traumatologia'                 SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('edgar-roman','luis-reyes-perez','nicolas-barzallo','jose-luis-pacheco');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'urologia'                      SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('alberto-amaya');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'otorrinolaringologia'          SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('alexander-hernandez');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'psicologia'                    SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('washington-ortega');
UPDATE `doctors` d JOIN `specialties` s ON s.`slug` = 'geriatria'                     SET d.`specialty_id` = s.`id` WHERE d.`slug` IN ('karen-cobos');

-- Poblar tabla puente con las 28 especialidades principales
INSERT INTO `doctor_specialties` (`doctor_id`, `specialty_id`, `is_primary`)
SELECT `id`, `specialty_id`, 1
  FROM `doctors`
 WHERE `specialty_id` IS NOT NULL;

-- Agregar segundas especialidades aprobadas (#13, #26 y #29)
INSERT INTO `doctor_specialties` (`doctor_id`, `specialty_id`, `is_primary`) VALUES
  ((SELECT `id` FROM `doctors` WHERE `slug` = 'sixto-barriga'),  (SELECT `id` FROM `specialties` WHERE `slug` = 'diabetologia'),      0),
  ((SELECT `id` FROM `doctors` WHERE `slug` = 'abdon-pacurucu'), (SELECT `id` FROM `specialties` WHERE `slug` = 'gastroenterologia'), 0),
  ((SELECT `id` FROM `doctors` WHERE `slug` = 'karen-cobos'),    (SELECT `id` FROM `specialties` WHERE `slug` = 'diabetologia'),      0);

-- ---------------------------------------------------------------------
-- 2. Tabla de solicitudes de citas médicas (appointments)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`                VARCHAR(20) NOT NULL,
  `attention_type`      ENUM('especialidad','laboratorio','imagen','especialista') NOT NULL DEFAULT 'especialidad',
  `specialty_id`        INT UNSIGNED NULL DEFAULT NULL,
  `attention_label`     VARCHAR(150) NOT NULL,
  `doctor_id`           INT UNSIGNED NULL DEFAULT NULL,
  `doctor_label`        VARCHAR(180) NOT NULL,
  `patient_doc_type`    ENUM('cedula','pasaporte') NOT NULL DEFAULT 'cedula',
  `patient_id_doc`      VARCHAR(20) NOT NULL,
  `patient_name`        VARCHAR(150) NOT NULL,
  `patient_phone`       VARCHAR(30) NOT NULL,
  `patient_email`       VARCHAR(150) NULL DEFAULT NULL,
  `appointment_date`    DATE NOT NULL,
  `shift`               ENUM('matutino','vespertino') NOT NULL,
  `confirmed_time`      VARCHAR(20) NULL DEFAULT NULL,
  `reason`              TEXT NULL DEFAULT NULL,
  `admin_notes`         TEXT NULL DEFAULT NULL,
  `status`              ENUM('pendiente','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  `privacy_accepted_at` DATETIME NOT NULL,
  `ip_hash`             CHAR(64) NULL DEFAULT NULL,
  `email_status`        ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `email_sent_at`       DATETIME NULL DEFAULT NULL,
  `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_appointments_code` (`code`),
  KEY `idx_appointments_date` (`appointment_date`, `status`),
  KEY `idx_appointments_doctor` (`doctor_id`),
  KEY `idx_appointments_specialty` (`specialty_id`),
  CONSTRAINT `fk_appointments_doctor`    FOREIGN KEY (`doctor_id`)    REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_appointments_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Tabla de mensajes de contacto institucional (contact_messages)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`                VARCHAR(20) NOT NULL,
  `name`                VARCHAR(150) NOT NULL,
  `email`               VARCHAR(150) NOT NULL,
  `phone`               VARCHAR(30) NULL DEFAULT NULL,
  `subject`             VARCHAR(60) NOT NULL,
  `subject_label`       VARCHAR(150) NOT NULL,
  `message`             TEXT NOT NULL,
  `status`              ENUM('nuevo','leido','respondido') NOT NULL DEFAULT 'nuevo',
  `privacy_accepted_at` DATETIME NOT NULL,
  `ip_hash`             CHAR(64) NULL DEFAULT NULL,
  `email_status`        ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `email_sent_at`       DATETIME NULL DEFAULT NULL,
  `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contact_code` (`code`),
  KEY `idx_contact_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Control de límite de envíos por IP anonimizada (form_rate_limits)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `form_rate_limits` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_key`   VARCHAR(30) NOT NULL,
  `ip_hash`    CHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`form_key`, `ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
