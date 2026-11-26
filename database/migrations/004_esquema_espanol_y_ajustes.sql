-- =====================================================================
-- Migración 004: Estandarización del esquema en español (12 tablas),
-- eliminación de filtros de especialidades, baja de Juan Mateus (#8),
-- prefijo Dr./Dra. directo en medicos.nombre y citas solo con especialidades.
-- =====================================================================

USE `hospital_san_marcos`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Eliminar tablas de categorías de especialidades
DROP TABLE IF EXISTS `specialty_category_map`;
DROP TABLE IF EXISTS `specialty_categories`;

-- 2. Eliminar definitivamente a Juan Mateus (#8) y ajustar prefijos Dr./Dra.
DELETE FROM `doctor_specialties` WHERE `doctor_id` IN (SELECT `id` FROM `doctors` WHERE `slug` = 'juan-mateus');
DELETE FROM `doctors` WHERE `slug` = 'juan-mateus';

UPDATE `doctors`
   SET `name` = CONCAT('Dra. ', `name`)
 WHERE `slug` IN ('sabina-tipantaxi', 'karen-cobos')
   AND `name` NOT LIKE 'Dra.%' AND `name` NOT LIKE 'Dr.%';

UPDATE `doctors`
   SET `name` = CONCAT('Dr. ', `name`)
 WHERE `slug` NOT IN ('sabina-tipantaxi', 'karen-cobos')
   AND `name` NOT LIKE 'Dr.%' AND `name` NOT LIKE 'Dra.%';

-- 3. Soltar claves foráneas previas antes de renombrar tablas y columnas
ALTER TABLE `doctors`              DROP FOREIGN KEY `fk_doctors_specialty`;
ALTER TABLE `doctor_specialties`   DROP FOREIGN KEY `fk_ds_doctor`, DROP FOREIGN KEY `fk_ds_specialty`;
ALTER TABLE `service_features`     DROP FOREIGN KEY `fk_sf_service`;
ALTER TABLE `service_detail_items` DROP FOREIGN KEY `fk_sdi_detail`;
ALTER TABLE `appointments`         DROP FOREIGN KEY `fk_appointments_doctor`, DROP FOREIGN KEY `fk_appointments_specialty`;

-- 4. Renombrar las 12 tablas a español
RENAME TABLE
  `specialties`          TO `especialidades`,
  `doctors`              TO `medicos`,
  `doctor_specialties`   TO `medico_especialidades`,
  `services`             TO `servicios`,
  `service_features`     TO `servicio_caracteristicas`,
  `service_details`      TO `servicio_fichas`,
  `service_detail_items` TO `servicio_ficha_items`,
  `faqs`                 TO `preguntas_frecuentes`,
  `appointments`         TO `citas`,
  `contact_messages`     TO `mensajes_contacto`,
  `admin_users`          TO `usuarios_admin`,
  `form_rate_limits`     TO `limites_envio`;

-- 5. Renombrar columnas en cada tabla a español

-- 5.1 especialidades
ALTER TABLE `especialidades`
  CHANGE COLUMN `name`           `nombre`          VARCHAR(150) NOT NULL,
  CHANGE COLUMN `description`    `descripcion`     TEXT NOT NULL,
  CHANGE COLUMN `badge`          `insignia`        VARCHAR(60) NOT NULL,
  CHANGE COLUMN `badge_type`     `tipo_insignia`   VARCHAR(30) NULL DEFAULT NULL,
  CHANGE COLUMN `icon`           `icono`           VARCHAR(60) NOT NULL,
  CHANGE COLUMN `highlight_text` `texto_destacado` VARCHAR(255) NULL DEFAULT NULL,
  CHANGE COLUMN `sort_order`     `orden`           INT UNSIGNED NOT NULL DEFAULT 0,
  CHANGE COLUMN `created_at`     `creado_en`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 5.2 medicos
ALTER TABLE `medicos`
  CHANGE COLUMN `name`            `nombre`              VARCHAR(150) NOT NULL,
  CHANGE COLUMN `specialty_label` `titulo_especialidad` VARCHAR(150) NOT NULL,
  CHANGE COLUMN `category`        `categoria`           ENUM('cirugia','ginecologia','medicina-interna','traumatologia','otras') NOT NULL,
  CHANGE COLUMN `initials`        `iniciales`           VARCHAR(6) NOT NULL,
  CHANGE COLUMN `location`        `ubicacion`           VARCHAR(150) NOT NULL,
  CHANGE COLUMN `accredited`      `acreditado`          TINYINT(1) NOT NULL DEFAULT 1,
  CHANGE COLUMN `description`     `descripcion`         TEXT NOT NULL,
  CHANGE COLUMN `specialty_id`    `especialidad_id`     INT UNSIGNED NULL DEFAULT NULL,
  CHANGE COLUMN `sort_order`      `orden`               INT UNSIGNED NOT NULL DEFAULT 0,
  CHANGE COLUMN `is_active`       `activo`              TINYINT(1) NOT NULL DEFAULT 1,
  CHANGE COLUMN `created_at`      `creado_en`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 5.3 medico_especialidades
ALTER TABLE `medico_especialidades`
  CHANGE COLUMN `doctor_id`    `medico_id`       INT UNSIGNED NOT NULL,
  CHANGE COLUMN `specialty_id` `especialidad_id` INT UNSIGNED NOT NULL,
  CHANGE COLUMN `is_primary`   `es_principal`    TINYINT(1) NOT NULL DEFAULT 1;

-- 5.4 servicios (sin relación con preguntas_frecuentes ni citas)
ALTER TABLE `servicios`
  CHANGE COLUMN `name`        `nombre`         VARCHAR(150) NOT NULL,
  CHANGE COLUMN `tag`         `etiqueta`       VARCHAR(80) NOT NULL,
  CHANGE COLUMN `tag_class`   `clase_etiqueta` VARCHAR(200) NOT NULL,
  CHANGE COLUMN `description` `descripcion`    TEXT NOT NULL,
  CHANGE COLUMN `icon`        `icono`          VARCHAR(60) NOT NULL,
  CHANGE COLUMN `detail_key`  `clave_ficha`    VARCHAR(40) NOT NULL,
  CHANGE COLUMN `sort_order`  `orden`          INT UNSIGNED NOT NULL DEFAULT 0;

-- 5.5 servicio_caracteristicas
ALTER TABLE `servicio_caracteristicas`
  CHANGE COLUMN `service_id` `servicio_id`    INT UNSIGNED NOT NULL,
  CHANGE COLUMN `feature`    `caracteristica` VARCHAR(255) NOT NULL,
  CHANGE COLUMN `sort_order` `orden`          TINYINT UNSIGNED NOT NULL DEFAULT 0;

-- 5.6 servicio_fichas
ALTER TABLE `servicio_fichas`
  CHANGE COLUMN `detail_key`  `clave_ficha`       VARCHAR(40) NOT NULL,
  CHANGE COLUMN `title`       `titulo`            VARCHAR(200) NOT NULL,
  CHANGE COLUMN `tag`         `etiqueta`          VARCHAR(120) NOT NULL,
  CHANGE COLUMN `description` `descripcion`       TEXT NOT NULL,
  CHANGE COLUMN `icon`        `icono`             VARCHAR(60) NOT NULL,
  CHANGE COLUMN `time_notice` `aviso_tiempo`      VARCHAR(255) NOT NULL,
  CHANGE COLUMN `prep_notice` `aviso_preparacion` VARCHAR(255) NOT NULL,
  CHANGE COLUMN `sort_order`  `orden`             INT UNSIGNED NOT NULL DEFAULT 0;

-- 5.7 servicio_ficha_items (convertir 'scope'/'prep' -> 'alcance'/'preparacion')
ALTER TABLE `servicio_ficha_items`
  MODIFY COLUMN `type` VARCHAR(20) NOT NULL;

UPDATE `servicio_ficha_items` SET `type` = 'alcance'     WHERE `type` = 'scope';
UPDATE `servicio_ficha_items` SET `type` = 'preparacion' WHERE `type` = 'prep';

ALTER TABLE `servicio_ficha_items`
  CHANGE COLUMN `detail_id`  `ficha_id`    INT UNSIGNED NOT NULL,
  CHANGE COLUMN `type`       `tipo`        ENUM('alcance','preparacion') NOT NULL,
  CHANGE COLUMN `item`       `descripcion` VARCHAR(255) NOT NULL,
  CHANGE COLUMN `sort_order` `orden`       TINYINT UNSIGNED NOT NULL DEFAULT 0;

-- 5.8 preguntas_frecuentes
ALTER TABLE `preguntas_frecuentes`
  CHANGE COLUMN `question`   `pregunta`  VARCHAR(255) NOT NULL,
  CHANGE COLUMN `answer`     `respuesta` TEXT NOT NULL,
  CHANGE COLUMN `sort_order` `orden`     INT UNSIGNED NOT NULL DEFAULT 0,
  CHANGE COLUMN `is_active`  `activo`    TINYINT(1) NOT NULL DEFAULT 1;

-- 5.9 citas (vinculada exclusivamente a especialidades y medicos)
ALTER TABLE `citas`
  DROP COLUMN `attention_type`,
  CHANGE COLUMN `code`                `codigo`                  VARCHAR(20) NOT NULL,
  CHANGE COLUMN `specialty_id`        `especialidad_id`         INT UNSIGNED NULL DEFAULT NULL,
  CHANGE COLUMN `attention_label`     `nombre_especialidad`     VARCHAR(150) NOT NULL,
  CHANGE COLUMN `doctor_id`           `medico_id`               INT UNSIGNED NULL DEFAULT NULL,
  CHANGE COLUMN `doctor_label`        `nombre_medico`           VARCHAR(180) NOT NULL,
  CHANGE COLUMN `patient_doc_type`    `tipo_documento_paciente` ENUM('cedula','pasaporte') NOT NULL DEFAULT 'cedula',
  CHANGE COLUMN `patient_id_doc`      `documento_paciente`      VARCHAR(20) NOT NULL,
  CHANGE COLUMN `patient_name`        `nombre_paciente`         VARCHAR(150) NOT NULL,
  CHANGE COLUMN `patient_phone`       `telefono_paciente`       VARCHAR(30) NOT NULL,
  CHANGE COLUMN `patient_email`       `correo_paciente`         VARCHAR(150) NULL DEFAULT NULL,
  CHANGE COLUMN `appointment_date`    `fecha_cita`              DATE NOT NULL,
  CHANGE COLUMN `shift`               `turno`                   ENUM('matutino','vespertino') NOT NULL,
  CHANGE COLUMN `confirmed_time`      `hora_confirmada`         VARCHAR(20) NULL DEFAULT NULL,
  CHANGE COLUMN `reason`              `motivo`                  TEXT NULL DEFAULT NULL,
  CHANGE COLUMN `admin_notes`         `notas_admin`             TEXT NULL DEFAULT NULL,
  CHANGE COLUMN `status`              `estado`                  ENUM('pendiente','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  CHANGE COLUMN `privacy_accepted_at` `privacidad_aceptada_en`  DATETIME NOT NULL,
  CHANGE COLUMN `ip_hash`             `hash_ip`                 CHAR(64) NULL DEFAULT NULL,
  CHANGE COLUMN `email_status`        `estado_correo`           ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  CHANGE COLUMN `email_sent_at`       `correo_enviado_en`       DATETIME NULL DEFAULT NULL,
  CHANGE COLUMN `created_at`          `creado_en`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHANGE COLUMN `updated_at`          `actualizado_en`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

UPDATE `citas` u
  JOIN `medicos` m ON m.`id` = u.`medico_id`
   SET u.`nombre_medico` = CONCAT(m.`nombre`, ' — ', m.`titulo_especialidad`)
 WHERE u.`nombre_medico` LIKE 'Dr./Dra.%';

-- 5.10 mensajes_contacto
ALTER TABLE `mensajes_contacto`
  CHANGE COLUMN `code`                `codigo`                 VARCHAR(20) NOT NULL,
  CHANGE COLUMN `name`                `nombre`                 VARCHAR(150) NOT NULL,
  CHANGE COLUMN `email`               `correo`                 VARCHAR(150) NOT NULL,
  CHANGE COLUMN `phone`               `telefono`               VARCHAR(30) NULL DEFAULT NULL,
  CHANGE COLUMN `subject`             `asunto`                 VARCHAR(60) NOT NULL,
  CHANGE COLUMN `subject_label`       `etiqueta_asunto`        VARCHAR(150) NOT NULL,
  CHANGE COLUMN `message`             `mensaje`                TEXT NOT NULL,
  CHANGE COLUMN `status`              `estado`                 ENUM('nuevo','leido','respondido') NOT NULL DEFAULT 'nuevo',
  CHANGE COLUMN `privacy_accepted_at` `privacidad_aceptada_en` DATETIME NOT NULL,
  CHANGE COLUMN `ip_hash`             `hash_ip`                CHAR(64) NULL DEFAULT NULL,
  CHANGE COLUMN `email_status`        `estado_correo`          ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  CHANGE COLUMN `email_sent_at`       `correo_enviado_en`      DATETIME NULL DEFAULT NULL,
  CHANGE COLUMN `created_at`          `creado_en`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 5.11 usuarios_admin
ALTER TABLE `usuarios_admin`
  CHANGE COLUMN `username`      `usuario`          VARCHAR(60) NOT NULL,
  CHANGE COLUMN `email`         `correo`           VARCHAR(150) NOT NULL,
  CHANGE COLUMN `password_hash` `clave_hash`       VARCHAR(255) NOT NULL,
  CHANGE COLUMN `role`          `rol`              ENUM('admin','editor','recepcion') NOT NULL DEFAULT 'editor',
  CHANGE COLUMN `is_active`     `activo`           TINYINT(1) NOT NULL DEFAULT 1,
  CHANGE COLUMN `last_login_at` `ultimo_acceso_en` DATETIME NULL DEFAULT NULL,
  CHANGE COLUMN `created_at`    `creado_en`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 5.12 limites_envio
ALTER TABLE `limites_envio`
  CHANGE COLUMN `form_key`   `formulario` VARCHAR(30) NOT NULL,
  CHANGE COLUMN `ip_hash`    `hash_ip`    CHAR(64) NOT NULL,
  CHANGE COLUMN `created_at` `creado_en`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 6. Reestablecer claves foráneas estandarizadas en español
ALTER TABLE `medicos`
  ADD CONSTRAINT `fk_medicos_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL;

ALTER TABLE `medico_especialidades`
  ADD CONSTRAINT `fk_me_medico`       FOREIGN KEY (`medico_id`)       REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_me_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE;

ALTER TABLE `servicio_caracteristicas`
  ADD CONSTRAINT `fk_sc_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE;

ALTER TABLE `servicio_ficha_items`
  ADD CONSTRAINT `fk_sfi_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `servicio_fichas` (`id`) ON DELETE CASCADE;

ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_medico`       FOREIGN KEY (`medico_id`)       REFERENCES `medicos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_citas_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;
