-- =====================================================================
-- Hospital San Marcos - Esquema relacional MySQL/MariaDB
-- Base de datos: hospital_san_marcos  (utf8mb4 / InnoDB)
-- Generado: esquema + datos iniciales migrados desde app/data/*.php
-- Es re-ejecutable: recrea las tablas desde cero.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `hospital_san_marcos`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hospital_san_marcos`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `form_rate_limits`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `doctor_specialties`;
DROP TABLE IF EXISTS `service_detail_items`;
DROP TABLE IF EXISTS `service_details`;
DROP TABLE IF EXISTS `service_features`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `specialty_category_map`;
DROP TABLE IF EXISTS `specialty_categories`;
DROP TABLE IF EXISTS `specialties`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Especialidades
-- ---------------------------------------------------------------------
CREATE TABLE `specialties` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100) NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `badge`       VARCHAR(60) NOT NULL,
  `badge_type`  VARCHAR(30) NULL DEFAULT NULL,
  `icon`        VARCHAR(60) NOT NULL,
  `highlight_text` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order`  INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_specialties_slug` (`slug`),
  KEY `idx_specialties_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `specialty_categories` (
  `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`  VARCHAR(40) NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_specialty_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `specialty_category_map` (
  `specialty_id` INT UNSIGNED NOT NULL,
  `category_id`  INT UNSIGNED NOT NULL,
  `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`specialty_id`, `category_id`),
  CONSTRAINT `fk_scm_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_scm_category`  FOREIGN KEY (`category_id`)  REFERENCES `specialty_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Doctores
-- ---------------------------------------------------------------------
CREATE TABLE `doctors` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(120) NOT NULL,
  `name`            VARCHAR(150) NOT NULL,
  `specialty_label` VARCHAR(150) NOT NULL,
  `category`        ENUM('cirugia','ginecologia','medicina-interna','traumatologia','otras') NOT NULL,
  `initials`        VARCHAR(6) NOT NULL,
  `location`        VARCHAR(150) NOT NULL,
  `accredited`      TINYINT(1) NOT NULL DEFAULT 1,
  `description`     TEXT NOT NULL,
  `specialty_id`    INT UNSIGNED NULL DEFAULT NULL,
  `sort_order`      INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doctors_slug` (`slug`),
  KEY `idx_doctors_category` (`category`),
  KEY `idx_doctors_sort` (`sort_order`),
  CONSTRAINT `fk_doctors_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Servicios y fichas operativas
-- ---------------------------------------------------------------------
CREATE TABLE `services` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100) NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `tag`         VARCHAR(80) NOT NULL,
  `tag_class`   VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `icon`        VARCHAR(60) NOT NULL,
  `detail_key`  VARCHAR(40) NOT NULL,
  `sort_order`  INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`),
  KEY `idx_services_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_features` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` INT UNSIGNED NOT NULL,
  `feature`    VARCHAR(255) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sf_service` (`service_id`, `sort_order`),
  CONSTRAINT `fk_sf_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_details` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `detail_key`  VARCHAR(40) NOT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `tag`         VARCHAR(120) NOT NULL,
  `description` TEXT NOT NULL,
  `icon`        VARCHAR(60) NOT NULL,
  `time_notice` VARCHAR(255) NOT NULL,
  `prep_notice` VARCHAR(255) NOT NULL,
  `sort_order`  INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_details_key` (`detail_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_detail_items` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `detail_id`  INT UNSIGNED NOT NULL,
  `type`       ENUM('scope','prep') NOT NULL,
  `item`       VARCHAR(255) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sdi_detail` (`detail_id`, `type`, `sort_order`),
  CONSTRAINT `fk_sdi_detail` FOREIGN KEY (`detail_id`) REFERENCES `service_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Preguntas frecuentes
-- ---------------------------------------------------------------------
CREATE TABLE `faqs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(100) NOT NULL,
  `question`   VARCHAR(255) NOT NULL,
  `answer`     TEXT NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_faqs_slug` (`slug`),
  KEY `idx_faqs_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Citas (uso en puntos posteriores de la Etapa 2)
-- ---------------------------------------------------------------------
CREATE TABLE `appointments` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`            VARCHAR(20) NOT NULL,
  `doctor_id`       INT UNSIGNED NULL DEFAULT NULL,
  `specialty_id`    INT UNSIGNED NULL DEFAULT NULL,
  `patient_name`    VARCHAR(150) NOT NULL,
  `patient_id_doc`  VARCHAR(20) NOT NULL,
  `patient_phone`   VARCHAR(30) NOT NULL,
  `patient_email`   VARCHAR(150) NULL DEFAULT NULL,
  `appointment_date` DATE NOT NULL,
  `shift`           ENUM('matutino','vespertino') NOT NULL,
  `reason`          TEXT NULL,
  `status`          ENUM('pendiente','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_appointments_code` (`code`),
  KEY `idx_appointments_date` (`appointment_date`, `status`),
  KEY `idx_appointments_doctor` (`doctor_id`),
  KEY `idx_appointments_specialty` (`specialty_id`),
  CONSTRAINT `fk_appointments_doctor`    FOREIGN KEY (`doctor_id`)    REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_appointments_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Mensajes de contacto
-- ---------------------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `phone`      VARCHAR(30) NULL DEFAULT NULL,
  `subject`    VARCHAR(150) NOT NULL,
  `message`    TEXT NOT NULL,
  `status`     ENUM('nuevo','leido','respondido') NOT NULL DEFAULT 'nuevo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Usuarios administrativos (sin credenciales por defecto)
-- ---------------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60) NOT NULL,
  `email`         VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','editor','recepcion') NOT NULL DEFAULT 'editor',
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS INICIALES (migrados de app/data/*.php)
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO `specialty_categories` (`slug`,`label`) VALUES
  ('mujer','Salud de la mujer y maternidad'),
  ('cirugia','Cirugía y procedimientos'),
  ('clinica','Medicina clínica y adultos'),
  ('mental','Salud mental y neurociencias'),
  ('pediatria','Especialidades quirúrgicas y pediátricas');

INSERT INTO `specialties` (`slug`,`name`,`description`,`badge`,`badge_type`,`icon`,`highlight_text`,`sort_order`) VALUES
  ('ginecologia-y-obstetricia','Ginecología y obstetricia','Salud femenina, control prenatal, partos y patologías reproductivas.','Maternidad',NULL,'pregnant_woman',NULL,1),
  ('neurocirugia','Neurocirugía','Diagnóstico y manejo de afecciones del sistema nervioso central y columna vertebral.','Neurociencias',NULL,'psychology',NULL,2),
  ('medicina-interna','Medicina interna','Diagnóstico y tratamiento integral de enfermedades del adulto y afecciones complejas.','Adultos',NULL,'stethoscope',NULL,3),
  ('otorrinolaringologia','Otorrinolaringología','Afecciones de oído, nariz, garganta y vías respiratorias superiores.','ORL',NULL,'hearing',NULL,4),
  ('cirugia-general','Cirugía general','Procedimientos quirúrgicos del aparato digestivo, pared abdominal y patologías quirúrgicas.','Laparoscopía','highlight','medical_services','Procedimientos de cirugía laparoscópica disponibles',5),
  ('gastroenterologia','Gastroenterología','Diagnóstico y tratamiento del aparato digestivo, endoscopia y salud hepática.','Endoscopia',NULL,'qr_code',NULL,6),
  ('cardiologia','Cardiología','Prevención, diagnóstico y tratamiento de enfermedades cardiovasculares e hipertensión.','Cardiovascular',NULL,'favorite',NULL,7),
  ('cirugia-estetico-maxilofacial','Cirugía estético-maxilofacial','Corrección y reconstrucción funcional y estética facial y maxilar.','Reconstructiva',NULL,'face',NULL,8),
  ('traumatologia','Traumatología','Manejo de lesiones osteoarticulares, fracturas, esguinces y afecciones del aparato locomotor.','Osteoarticular',NULL,'orthopedics',NULL,9),
  ('medicina-familiar','Medicina familiar','Atención médica continua e integral para todos los miembros de la familia.','Atención Continua',NULL,'family_restroom',NULL,10),
  ('diabetologia','Diabetología','Manejo especializado, control metabólico y prevención de complicaciones de la diabetes.','Metabolismo',NULL,'bloodtype',NULL,11),
  ('geriatria','Geriatría','Atención especializada para adultos mayores, promoción de autonomía y manejo geriátrico integral.','Adulto Mayor',NULL,'elderly',NULL,12),
  ('psicologia','Psicología','Acompañamiento psicoterapéutico y salud emocional para niños, jóvenes y adultos.','Bienestar Emocional',NULL,'self_improvement',NULL,13),
  ('psiquiatria','Psiquiatría','Diagnóstico y tratamiento médico de trastornos de salud mental y del estado de ánimo.','Salud Mental',NULL,'neurology',NULL,14),
  ('urologia','Urología','Tratamiento del sistema urinario masculino/femenino y aparato reproductor masculino.','Urológico',NULL,'vital_signs',NULL,15),
  ('cirugia-pediatrica','Cirugía pediátrica','Procedimientos quirúrgicos especializados y adaptados a pacientes en edad pediátrica.','Pediatría','pediatria','child_care',NULL,16),
  ('oftalmologia','Oftalmología','Salud visual integral, diagnóstico de patologías oculares y prescripción refractiva.','Salud Visual',NULL,'visibility',NULL,17);

INSERT INTO `specialty_category_map` (`specialty_id`,`category_id`,`sort_order`) VALUES
  ((SELECT id FROM specialties WHERE slug='ginecologia-y-obstetricia'),(SELECT id FROM specialty_categories WHERE slug='mujer'),0),
  ((SELECT id FROM specialties WHERE slug='neurocirugia'),(SELECT id FROM specialty_categories WHERE slug='mental'),0),
  ((SELECT id FROM specialties WHERE slug='neurocirugia'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),1),
  ((SELECT id FROM specialties WHERE slug='medicina-interna'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='otorrinolaringologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='cirugia-general'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),0),
  ((SELECT id FROM specialties WHERE slug='gastroenterologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='cardiologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='cirugia-estetico-maxilofacial'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),0),
  ((SELECT id FROM specialties WHERE slug='traumatologia'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),0),
  ((SELECT id FROM specialties WHERE slug='traumatologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),1),
  ((SELECT id FROM specialties WHERE slug='medicina-familiar'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='diabetologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='geriatria'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0),
  ((SELECT id FROM specialties WHERE slug='psicologia'),(SELECT id FROM specialty_categories WHERE slug='mental'),0),
  ((SELECT id FROM specialties WHERE slug='psiquiatria'),(SELECT id FROM specialty_categories WHERE slug='mental'),0),
  ((SELECT id FROM specialties WHERE slug='urologia'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),0),
  ((SELECT id FROM specialties WHERE slug='urologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),1),
  ((SELECT id FROM specialties WHERE slug='cirugia-pediatrica'),(SELECT id FROM specialty_categories WHERE slug='pediatria'),0),
  ((SELECT id FROM specialties WHERE slug='cirugia-pediatrica'),(SELECT id FROM specialty_categories WHERE slug='cirugia'),1),
  ((SELECT id FROM specialties WHERE slug='oftalmologia'),(SELECT id FROM specialty_categories WHERE slug='clinica'),0);

INSERT INTO `doctors` (`slug`,`name`,`specialty_label`,`category`,`initials`,`location`,`accredited`,`description`,`sort_order`) VALUES
  ('johny-cabrera','Johny Cabrera','Cirujano general y laparoscópico','cirugia','JC','Sede Pasaje · Consulta programada',1,'Especialista en procedimientos quirúrgicos del aparato digestivo, pared abdominal y cirugía laparoscópica mínimamente invasiva.',1),
  ('erick-apolo','Erick Apolo','Cirujano general y laparoscópico','cirugia','EA','Sede Pasaje · Consulta programada',1,'Experiencia en patología quirúrgica abdominal, colecistectomía laparoscópica y resolución de urgencias quirúrgicas.',2),
  ('pablo-andres-cadena-pinargote','Pablo Andrés Cadena Pinargote','Cirujano general','cirugia','PC','Sede Pasaje · Consulta programada',1,'Atención y valoración quirúrgica pre y postoperatoria, intervenciones programadas y patologías de tejidos blandos.',3),
  ('jorge-estuardo-herrera-guerrero','Jorge Estuardo Herrera Guerrero','Cirujano general','cirugia','JH','Sede Pasaje · Consulta programada',1,'Cirugía abierta y laparoscópica para resolución de hernias, apendicitis, vesícula y patologías digestivas.',4),
  ('cesar-gia','César Gia','Cirujano pediatra','cirugia','CG','Sede Pasaje · Consulta programada',1,'Especialista en intervenciones quirúrgicas neonatales, infantiles y de la adolescencia con enfoque pediátrico empático.',5),
  ('mauricio-arichabala','Mauricio Arichabala','Cirujano plástico maxilofacial','cirugia','MA','Sede Pasaje · Consulta programada',1,'Cirugía reconstructiva y estética facial, traumatismo maxilofacial y corrección anatómica funcional.',6),
  ('rafael-barcia-sanchez','Rafael Barcia Sánchez','Neurocirujano','cirugia','RB','Sede Pasaje · Consulta programada',1,'Diagnóstico y manejo quirúrgico de patologías del sistema nervioso central, columna vertebral y compresión radicular.',7),
  ('juan-mateus','Juan Mateus','Anestesiólogo','cirugia','JM','Sede Pasaje · Consulta programada',1,'Especialista en anestesiología, analgesia perioperatoria y soporte hemodinámico en quirófano.',8),
  ('odeley-rodriguez','Odeley Rodríguez','Ginecólogo obstetra','ginecologia','OR','Sede Pasaje · Consulta programada',1,'Control prenatal especializado, ecografía obstétrica, atención de partos y cesáreas, y salud ginecológica integral.',9),
  ('klever-mendieta','Klever Mendieta','Ginecólogo obstetra','ginecologia','KM','Sede Pasaje · Consulta programada',1,'Atención integral de la mujer en todas las etapas, prevención del cáncer ginecológico y seguimiento gestacional.',10),
  ('byron-sanchez','Byron Sánchez','Internista','medicina-interna','BS','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento integral de enfermedades complejas del adulto, hipertensión arterial y patologías crónicas.',11),
  ('luis-davila','Luis Dávila','Medicina interna','medicina-interna','LD','Sede Pasaje · Consulta programada',1,'Evaluación clínica completa, manejo de comorbilidades y coordinación médica de pacientes hospitalizados y ambulatorios.',12),
  ('sixto-barriga','Sixto Barriga','Medicina familiar y diabetólogo','medicina-interna','SB','Sede Pasaje · Consulta programada',1,'Control metabólico y prevención de complicaciones de la diabetes, acompañamiento médico integral para la familia.',13),
  ('jorge-castro','Jorge Castro','Cardiólogo','medicina-interna','JC','Sede Pasaje · Consulta programada',1,'Evaluación y tratamiento de afecciones cardiovasculares, hipertensión, arritmias, electrocardiogramas y riesgo quirúrgico.',14),
  ('enkly-heredia','Enkly Heredia','Gastroenterólogo','medicina-interna','EH','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento del tubo digestivo, endoscopía digestiva alta, colonoscopía y afecciones hepáticas.',15),
  ('luis-miguel-gaibor','Luis Miguel Gaibor','Neurólogo','medicina-interna','LG','Sede Pasaje · Consulta programada',1,'Manejo clínico de cefaleas, migrañas, epilepsia, trastornos neuromusculares y accidentes cerebrovasculares.',16),
  ('edgar-roman','Edgar Román','Ortopedista · Traumatólogo','traumatologia','ER','Sede Pasaje · Consulta programada',1,'Tratamiento de fracturas, luxaciones, lesiones deportivas, reemplazo articular y rehabilitación osteomuscular.',17),
  ('luis-reyes-perez','Luis Reyes Pérez','Traumatólogo','traumatologia','LR','Sede Pasaje · Consulta programada',1,'Diagnóstico y resolución de traumatismos agudos, artroscopía, patologías de rodilla, cadera y columna.',18),
  ('nicolas-barzallo','Nicolás Barzallo','Traumatólogo','traumatologia','NB','Sede Pasaje · Consulta programada',1,'Atención traumatológica de urgencias y consulta externa, corrección ortopédica y patologías del aparato locomotor.',19),
  ('alberto-amaya','Alberto Amaya','Urólogo','otras','AA','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento de patologías de la vía urinaria masculina y femenina, próstata y litiasis renal.',20),
  ('alexander-hernandez','Alexander Hernández','Otorrinolaringólogo','otras','AH','Sede Pasaje · Consulta programada',1,'Tratamiento de enfermedades de oído, nariz, garganta, rinitis alérgica, sinusitis y trastornos de la audición.',21),
  ('washington-ortega','Washington Ortega','Psicólogo','otras','WO','Sede Pasaje · Consulta programada',1,'Atención psicoterapéutica para niños, jóvenes y adultos, bienestar emocional, manejo de estrés y apoyo familiar.',22),
  ('sabina-tipantaxi','Sabina Tipantaxi F.','Cirugía vascular y endovascular','cirugia','ST','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en enfermedades vasculares, várices, pie diabético y evaluación de la circulación.',23),
  ('jose-luis-pacheco','José Luis Pacheco','Ortopedia y traumatología pediátrica','traumatologia','JP','Sede Pasaje · Disponibilidad por confirmar',0,'Atención de problemas ortopédicos infantiles, alteraciones de cadera, columna y extremidades, y traumatismos pediátricos.',24),
  ('pedro-ayala','Pedro Ayala','Ginecólogo obstetra','ginecologia','PA','Sede Pasaje · Disponibilidad por confirmar',0,'Profesional anunciado por el hospital para atención especializada en ginecología y obstetricia.',25),
  ('abdon-pacurucu','Abdón Pacurucu','Cirujano general y endoscopista digestivo','cirugia','AP','Sede Pasaje · Disponibilidad por confirmar',0,'Profesional de cirugía general y endoscopia digestiva, anunciado en publicaciones institucionales.',26),
  ('ronald-morocho','Ronald Morocho','Medicina interna','medicina-interna','RM','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en medicina interna, evaluación clínica y prevención de enfermedades.',27),
  ('patricio-carrion','Patricio Carrión','Médico general','otras','PC','Sede Pasaje · Disponibilidad por confirmar',0,'Atención médica general, valoración inicial y orientación clínica de pacientes.',28),
  ('karen-cobos','Karen Cobos S.','Geriatría y diabetología','medicina-interna','KC','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en salud de las personas mayores y evaluación de pacientes con diabetes.',29);

INSERT INTO `services` (`slug`,`name`,`tag`,`tag_class`,`description`,`icon`,`detail_key`,`sort_order`) VALUES
  ('emergencias-24h','Emergencias 24 horas y ambulancia','Atención 24/7','bg-tertiary text-on-tertiary font-bold uppercase','Atención médica inmediata para urgencias y emergencias con equipo de guardia y soporte móvil permanente.','emergency','emergencias',1),
  ('consulta-externa','Consulta externa','Agenda programada','bg-surface-container-low text-primary font-semibold','Valoración médica integral con diversas especialidades para diagnóstico, seguimiento y tratamiento programado.','stethoscope','consulta',2),
  ('cirugia-general-laparoscopica','Cirugía general y laparoscópica','Quirófano moderno','bg-surface-container-low text-primary font-semibold','Procedimientos quirúrgicos seguros y mínimamente invasivos con quirófano moderno y valoración prequirúrgica.','precision_manufacturing','cirugia',3),
  ('ginecologia-obstetricia','Ginecología y obstetricia','Materno-Fetal','bg-surface-container-low text-primary font-semibold','Cuidado integral de la salud femenina, control prenatal, atención del parto y patologías ginecológicas.','pregnant_woman','ginecologia',4),
  ('laboratorio-clinico','Laboratorio clínico','Resultados rápidos','bg-secondary-container text-on-secondary-container font-semibold','Exámenes de rutina, pruebas especiales, hematología, bioquímica y microbiología con entrega oportuna de resultados.','science','laboratorio',5),
  ('imagenologia','Imagenología','Diagnóstico visual','bg-surface-container-low text-primary font-semibold','Diagnóstico preciso con rayos X digitales, ecografías de alta resolución y tomografía axial computarizada.','radiology','imagen',6);

INSERT INTO `service_features` (`service_id`,`feature`,`sort_order`) VALUES
  ((SELECT id FROM services WHERE slug='emergencias-24h'),'Unidad móvil de soporte vital disponible',0),
  ((SELECT id FROM services WHERE slug='emergencias-24h'),'Triaje Manchester con respuesta inmediata',1),
  ((SELECT id FROM services WHERE slug='consulta-externa'),'Turnos matutinos y vespertinos',0),
  ((SELECT id FROM services WHERE slug='consulta-externa'),'Historia clínica digital centralizada',1),
  ((SELECT id FROM services WHERE slug='cirugia-general-laparoscopica'),'Recuperación ambulatoria y hospitalaria',0),
  ((SELECT id FROM services WHERE slug='cirugia-general-laparoscopica'),'Anestesiología y monitorización avanzada',1),
  ((SELECT id FROM services WHERE slug='ginecologia-obstetricia'),'Monitoreo fetal y sala de parto humanizado',0),
  ((SELECT id FROM services WHERE slug='ginecologia-obstetricia'),'Ecografía obstétrica y cribado preventivo',1),
  ((SELECT id FROM services WHERE slug='laboratorio-clinico'),'Entrega digital por correo y WhatsApp',0),
  ((SELECT id FROM services WHERE slug='laboratorio-clinico'),'Toma de muestras desde las 06:30',1),
  ((SELECT id FROM services WHERE slug='imagenologia'),'Baja radiación con sensor digital',0),
  ((SELECT id FROM services WHERE slug='imagenologia'),'Informes avalados por radiólogos',1);

INSERT INTO `service_details` (`detail_key`,`title`,`tag`,`description`,`icon`,`time_notice`,`prep_notice`,`sort_order`) VALUES
  ('laboratorio','Laboratorio Clínico Automatizado','Unidad Diagnóstica Nivel II','Área técnica de bioanálisis equipada con analizadores hematológicos y bioquímicos con calibración diaria y control de calidad internacional.','science','Rutina: 2 a 4 horas • Especiales: 24h','Disponible en zona urbana de Pasaje con previa cita',1),
  ('imagen','Imagenología y Radiodiagnóstico Digital','Tecnología de Diagnóstico Visual','Equipos de baja radiación y ecografía doppler de alta resolución con interpretación oportuna de médicos radiólogos certificados.','radiology','Informes inmediatos para urgencias • Programados: 2 a 6 horas','Protocolo de baja dosis para seguridad del paciente pediátrico y adulto',2),
  ('cirugia','Cirugía General y Laparoscópica Mínimamente Invasiva','Centro Quirúrgico Acreditado','Quirófano moderno equipado con torres laparoscópicas HD, electrocauterios bipolares y monitorización anestesiológica continua.','precision_manufacturing','Quirófanos disponibles 24/7 para emergencias quirúrgicas','Recuperación asistida con enfermería especializada y analgesia continua',3),
  ('hospitalizacion','Hospitalización y Recuperación Asistida','Cuidado Clínico y Seguridad','Habitaciones individuales y compartidas con diseño ergonómico, climatización médica, gases medicinales y enfermería permanente.','bed','Monitoreo clínico permanente las 24 horas del día','Acceso inclusivo, zona de descanso para acompañante y climatización',4);

INSERT INTO `service_detail_items` (`detail_id`,`type`,`item`,`sort_order`) VALUES
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'scope','Hematología completa y coagulación',0),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'scope','Bioquímica sanguínea (glucosa, perfil lipídico, renal, hepático)',1),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'scope','Inmunología, hormonas y marcadores tiroideos',2),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'scope','Microbiología, urocultivos y coproanálisis',3),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'prep','Ayuno requerido de 8 a 12 horas para pruebas metabólicas',0),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'prep','Evitar ingesta de alcohol y grasas la noche anterior',1),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'prep','Presentar cédula de identidad y orden médica si aplica',2),
  ((SELECT id FROM service_details WHERE detail_key='laboratorio'),'prep','Notificar toma de medicamentos continuos al personal',3),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'scope','Rayos X digitales de tórax, óseos y articulaciones',0),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'scope','Ecografías abdominales, pélvicas, renales y de tejidos blandos',1),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'scope','Ecografía obstétrica estructural y Doppler feto-placentario',2),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'scope','Tomografía axial computarizada programada',3),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'prep','Retirar joyas, hebillas u objetos metálicos previo a rayos X',0),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'prep','Ecografía pélvica/renal: vejiga llena (beber agua 45 min antes)',1),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'prep','Ecografía abdominal superior: 6 horas de ayuno',2),
  ((SELECT id FROM service_details WHERE detail_key='imagen'),'prep','Traer estudios previos para análisis comparativo',3),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'scope','Colecistectomía laparoscópica (extracción de vesícula)',0),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'scope','Apendicectomía y plastias de pared abdominal (hernias)',1),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'scope','Cirugía ginecológica y cesáreas programadas o de urgencia',2),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'scope','Procedimientos ambulatorios y biopsias quirúrgicas',3),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'prep','Valoración preanestésica obligatoria previa a la cirugía',0),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'prep','Ayuno absoluto de 8 horas antes del ingreso a quirófano',1),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'prep','Consentimiento informado firmado por el paciente o familiar',2),
  ((SELECT id FROM service_details WHERE detail_key='cirugia'),'prep','Disponer de un acompañante adulto responsable para el alta',3),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'scope','Manejo de postoperatorio inmediato y mediato',0),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'scope','Tratamientos antibióticos intravenosos y sueroterapia',1),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'scope','Control cardiorrespiratorio y hemodinámico continuo',2),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'scope','Atención integral del puerperio y recién nacido',3),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'prep','Artículos de aseo personal y ropa cómoda de descanso',0),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'prep','Documento de identidad y carpeta con historial médico previo',1),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'prep','Registro de un familiar titular para informes médicos diarios',2),
  ((SELECT id FROM service_details WHERE detail_key='hospitalizacion'),'prep','Cumplimiento de las normas de bioseguridad institucional',3);

INSERT INTO `faqs` (`slug`,`question`,`answer`,`sort_order`) VALUES
  ('emergencias-24h','¿Cómo funciona el servicio de emergencias 24 horas?','Nuestro servicio de emergencias opera de manera ininterrumpida los 365 días del año con médico de guardia residente, enfermería y laboratorio de respuesta inmediata. El ingreso se realiza mediante triaje clínico estandarizado para priorizar según la gravedad del cuadro de salud. También disponemos de ambulancia para rescate y traslados dentro y fuera de Pasaje llamando al 098 797 3511.',1),
  ('preparacion-examenes','¿Qué preparación previa necesito para exámenes de laboratorio e imagen?','Para la mayoría de exámenes sanguíneos se requiere entre 8 y 12 horas de ayuno estricto (agua simple está permitida). En ecografías pélvicas o renales se recomienda beber 1 litro de agua sin gas 45 minutos antes del estudio sin orinar. Para radiografías digitales no se requieren preparaciones digestivas, salvo retirar objetos metálicos. Puedes consultar las indicaciones exactas de tu prescripción vía WhatsApp previo a tu visita.',2),
  ('cirugia-programada','¿Cómo solicito una valoración para cirugía programada?','El primer paso es agendar una consulta médica de valoración en consulta externa con el cirujano especialista. Durante esta cita se revisarán tus exámenes clínicos previos o se solicitarán los estudios preoperatorios (laboratorio, electrocardiograma y valoración anestesiológica). Una vez aprobado el perfil quirúrgico, se programa la fecha de quirófano que mejor se ajuste a tu tiempo.',3),
  ('horarios-visita','¿Cuáles son los horarios de visita en el área de hospitalización?','Las visitas generales están habilitadas de 10:00 a 12:00 y de 16:00 a 18:00 para garantizar el reposo clínico de los pacientes. En habitaciones privadas se autoriza un acompañante permanente las 24 horas del día. Solicitamos a los visitantes mantener el orden, sanitización de manos al ingreso y evitar el acceso con niños menores de 12 años en áreas de cuidado especial.',4),
  ('agendar-consulta','¿Cómo puedo agendar una consulta médica externa?','Puedes solicitar tu turno médico a través de nuestras vías oficiales: de forma presencial en recepción en nuestra sede central en Pasaje (Av. Quito y Sucre), por mensajería instantánea en nuestra línea oficial de WhatsApp (098 797 3511), o mediante la central telefónica. Te asignaremos fecha, hora y confirmación de agenda con el especialista.',5);

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

