-- =====================================================================
-- Hospital San Marcos - Esquema relacional MySQL/MariaDB (12 tablas en español)
-- Base de datos: hospital_san_marcos (utf8mb4 / InnoDB)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `hospital_san_marcos`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hospital_san_marcos`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `limites_envio`;
DROP TABLE IF EXISTS `citas`;
DROP TABLE IF EXISTS `mensajes_contacto`;
DROP TABLE IF EXISTS `usuarios_admin`;
DROP TABLE IF EXISTS `medico_especialidades`;
DROP TABLE IF EXISTS `servicio_ficha_items`;
DROP TABLE IF EXISTS `servicio_fichas`;
DROP TABLE IF EXISTS `servicio_caracteristicas`;
DROP TABLE IF EXISTS `servicios`;
DROP TABLE IF EXISTS `preguntas_frecuentes`;
DROP TABLE IF EXISTS `medicos`;
DROP TABLE IF EXISTS `especialidades`;
DROP TABLE IF EXISTS `specialty_category_map`;
DROP TABLE IF EXISTS `specialty_categories`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Especialidades médicas (17)
CREATE TABLE `especialidades` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(100) NOT NULL,
  `nombre`          VARCHAR(150) NOT NULL,
  `descripcion`     TEXT NOT NULL,
  `insignia`        VARCHAR(60) NOT NULL,
  `tipo_insignia`   VARCHAR(30) NULL DEFAULT NULL,
  `icono`           VARCHAR(60) NOT NULL,
  `texto_destacado` VARCHAR(255) NULL DEFAULT NULL,
  `orden`           INT UNSIGNED NOT NULL DEFAULT 0,
  `creado_en`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_especialidades_slug` (`slug`),
  KEY `idx_especialidades_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Médicos (28 profesionales con prefijo Dr./Dra.)
CREATE TABLE `medicos` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`                VARCHAR(120) NOT NULL,
  `nombre`              VARCHAR(150) NOT NULL,
  `titulo_especialidad` VARCHAR(150) NOT NULL,
  `categoria`           ENUM('cirugia','ginecologia','medicina-interna','traumatologia','otras') NOT NULL,
  `iniciales`           VARCHAR(6) NOT NULL,
  `ubicacion`           VARCHAR(150) NOT NULL,
  `acreditado`          TINYINT(1) NOT NULL DEFAULT 1,
  `descripcion`         TEXT NOT NULL,
  `especialidad_id`     INT UNSIGNED NULL DEFAULT NULL,
  `orden`               INT UNSIGNED NOT NULL DEFAULT 0,
  `activo`              TINYINT(1) NOT NULL DEFAULT 1,
  `creado_en`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicos_slug` (`slug`),
  KEY `idx_medicos_categoria` (`categoria`),
  KEY `idx_medicos_orden` (`orden`),
  CONSTRAINT `fk_medicos_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Relación N:M médicos <-> especialidades
CREATE TABLE `medico_especialidades` (
  `medico_id`       INT UNSIGNED NOT NULL,
  `especialidad_id` INT UNSIGNED NOT NULL,
  `es_principal`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`medico_id`, `especialidad_id`),
  KEY `idx_me_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_me_medico`       FOREIGN KEY (`medico_id`)       REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_me_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Servicios clínicos (independientes de preguntas_frecuentes y citas)
CREATE TABLE `servicios` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`           VARCHAR(100) NOT NULL,
  `nombre`         VARCHAR(150) NOT NULL,
  `etiqueta`       VARCHAR(80) NOT NULL,
  `clase_etiqueta` VARCHAR(200) NOT NULL,
  `descripcion`    TEXT NOT NULL,
  `icono`          VARCHAR(60) NOT NULL,
  `clave_ficha`    VARCHAR(40) NOT NULL,
  `orden`          INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_servicios_slug` (`slug`),
  KEY `idx_servicios_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Características de servicios
CREATE TABLE `servicio_caracteristicas` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `servicio_id`    INT UNSIGNED NOT NULL,
  `caracteristica` VARCHAR(255) NOT NULL,
  `orden`          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sc_servicio` (`servicio_id`, `orden`),
  CONSTRAINT `fk_sc_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Fichas operativas de servicios
CREATE TABLE `servicio_fichas` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave_ficha`       VARCHAR(40) NOT NULL,
  `titulo`            VARCHAR(200) NOT NULL,
  `etiqueta`          VARCHAR(120) NOT NULL,
  `descripcion`       TEXT NOT NULL,
  `icono`             VARCHAR(60) NOT NULL,
  `aviso_tiempo`      VARCHAR(255) NOT NULL,
  `aviso_preparacion` VARCHAR(255) NOT NULL,
  `orden`             INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_servicio_fichas_clave` (`clave_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ítems de alcance y preparación de fichas operativas
CREATE TABLE `servicio_ficha_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ficha_id`    INT UNSIGNED NOT NULL,
  `tipo`        ENUM('alcance','preparacion') NOT NULL,
  `descripcion` VARCHAR(255) NOT NULL,
  `orden`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sfi_ficha` (`ficha_id`, `tipo`, `orden`),
  CONSTRAINT `fk_sfi_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `servicio_fichas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Preguntas frecuentes (independientes de servicios)
CREATE TABLE `preguntas_frecuentes` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`      VARCHAR(100) NOT NULL,
  `pregunta`  VARCHAR(255) NOT NULL,
  `respuesta` TEXT NOT NULL,
  `orden`     INT UNSIGNED NOT NULL DEFAULT 0,
  `activo`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_preguntas_slug` (`slug`),
  KEY `idx_preguntas_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Solicitudes de citas médicas (solo especialidades y médicos)
CREATE TABLE `citas` (
  `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo`                  VARCHAR(20) NOT NULL,
  `especialidad_id`         INT UNSIGNED NULL DEFAULT NULL,
  `nombre_especialidad`     VARCHAR(150) NOT NULL,
  `medico_id`               INT UNSIGNED NULL DEFAULT NULL,
  `nombre_medico`           VARCHAR(180) NOT NULL,
  `tipo_documento_paciente` ENUM('cedula','pasaporte') NOT NULL DEFAULT 'cedula',
  `documento_paciente`      VARCHAR(20) NOT NULL,
  `nombre_paciente`         VARCHAR(150) NOT NULL,
  `telefono_paciente`       VARCHAR(30) NOT NULL,
  `correo_paciente`         VARCHAR(150) NULL DEFAULT NULL,
  `fecha_cita`              DATE NOT NULL,
  `turno`                   ENUM('matutino','vespertino') NOT NULL,
  `hora_confirmada`         VARCHAR(20) NULL DEFAULT NULL,
  `motivo`                  TEXT NULL DEFAULT NULL,
  `notas_admin`             TEXT NULL DEFAULT NULL,
  `estado`                  ENUM('pendiente','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  `privacidad_aceptada_en`  DATETIME NOT NULL,
  `hash_ip`                 CHAR(64) NULL DEFAULT NULL,
  `estado_correo`           ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `correo_enviado_en`       DATETIME NULL DEFAULT NULL,
  `creado_en`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_citas_codigo` (`codigo`),
  KEY `idx_citas_fecha` (`fecha_cita`, `estado`),
  KEY `idx_citas_medico` (`medico_id`),
  KEY `idx_citas_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_citas_medico`       FOREIGN KEY (`medico_id`)       REFERENCES `medicos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_citas_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Mensajes de contacto
CREATE TABLE `mensajes_contacto` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo`                 VARCHAR(20) NOT NULL,
  `nombre`                 VARCHAR(150) NOT NULL,
  `correo`                 VARCHAR(150) NOT NULL,
  `telefono`               VARCHAR(30) NULL DEFAULT NULL,
  `asunto`                 VARCHAR(60) NOT NULL,
  `etiqueta_asunto`        VARCHAR(150) NOT NULL,
  `mensaje`                TEXT NOT NULL,
  `estado`                 ENUM('nuevo','leido','respondido') NOT NULL DEFAULT 'nuevo',
  `privacidad_aceptada_en` DATETIME NOT NULL,
  `hash_ip`                CHAR(64) NULL DEFAULT NULL,
  `estado_correo`          ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `correo_enviado_en`      DATETIME NULL DEFAULT NULL,
  `creado_en`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contacto_codigo` (`codigo`),
  KEY `idx_contacto_estado` (`estado`, `creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Usuarios administrativos (sin credenciales por defecto)
CREATE TABLE `usuarios_admin` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario`          VARCHAR(60) NOT NULL,
  `correo`           VARCHAR(150) NOT NULL,
  `clave_hash`       VARCHAR(255) NOT NULL,
  `rol`              ENUM('admin','editor','recepcion') NOT NULL DEFAULT 'editor',
  `activo`           TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_acceso_en` DATETIME NULL DEFAULT NULL,
  `creado_en`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_admin_usuario` (`usuario`),
  UNIQUE KEY `uq_usuarios_admin_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Control de límites de envío por IP anonimizada
CREATE TABLE `limites_envio` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `formulario` VARCHAR(30) NOT NULL,
  `hash_ip`    CHAR(64) NOT NULL,
  `creado_en`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_limites_busqueda` (`formulario`, `hash_ip`, `creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `especialidades` (`slug`,`nombre`,`descripcion`,`insignia`,`tipo_insignia`,`icono`,`texto_destacado`,`orden`) VALUES
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

INSERT INTO `medicos` (`slug`,`nombre`,`titulo_especialidad`,`categoria`,`iniciales`,`ubicacion`,`acreditado`,`descripcion`,`especialidad_id`,`orden`) VALUES
  ('johny-cabrera','Dr. Johny Cabrera','Cirujano general y laparoscópico','cirugia','JC','Sede Pasaje · Consulta programada',1,'Especialista en procedimientos quirúrgicos del aparato digestivo, pared abdominal y cirugía laparoscópica mínimamente invasiva.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),1),
  ('erick-apolo','Dr. Erick Apolo','Cirujano general y laparoscópico','cirugia','EA','Sede Pasaje · Consulta programada',1,'Experiencia en patología quirúrgica abdominal, colecistectomía laparoscópica y resolución de urgencias quirúrgicas.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),2),
  ('pablo-andres-cadena-pinargote','Dr. Pablo Andrés Cadena Pinargote','Cirujano general','cirugia','PC','Sede Pasaje · Consulta programada',1,'Atención y valoración quirúrgica pre y postoperatoria, intervenciones programadas y patologías de tejidos blandos.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),3),
  ('jorge-estuardo-herrera-guerrero','Dr. Jorge Estuardo Herrera Guerrero','Cirujano general','cirugia','JH','Sede Pasaje · Consulta programada',1,'Cirugía abierta y laparoscópica para resolución de hernias, apendicitis, vesícula y patologías digestivas.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),4),
  ('cesar-gia','Dr. César Gia','Cirujano pediatra','cirugia','CG','Sede Pasaje · Consulta programada',1,'Especialista en intervenciones quirúrgicas neonatales, infantiles y de la adolescencia con enfoque pediátrico empático.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-pediatrica'),5),
  ('mauricio-arichabala','Dr. Mauricio Arichabala','Cirujano plástico maxilofacial','cirugia','MA','Sede Pasaje · Consulta programada',1,'Cirugía reconstructiva y estética facial, traumatismo maxilofacial y corrección anatómica funcional.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-estetico-maxilofacial'),6),
  ('rafael-barcia-sanchez','Dr. Rafael Barcia Sánchez','Neurocirujano','cirugia','RB','Sede Pasaje · Consulta programada',1,'Diagnóstico y manejo quirúrgico de patologías del sistema nervioso central, columna vertebral y compresión radicular.',(SELECT `id` FROM `especialidades` WHERE `slug`='neurocirugia'),7),
  ('odeley-rodriguez','Dr. Odeley Rodríguez','Ginecólogo obstetra','ginecologia','OR','Sede Pasaje · Consulta programada',1,'Control prenatal especializado, ecografía obstétrica, atención de partos y cesáreas, y salud ginecológica integral.',(SELECT `id` FROM `especialidades` WHERE `slug`='ginecologia-y-obstetricia'),8),
  ('klever-mendieta','Dr. Klever Mendieta','Ginecólogo obstetra','ginecologia','KM','Sede Pasaje · Consulta programada',1,'Atención integral de la mujer en todas las etapas, prevención del cáncer ginecológico y seguimiento gestacional.',(SELECT `id` FROM `especialidades` WHERE `slug`='ginecologia-y-obstetricia'),9),
  ('byron-sanchez','Dr. Byron Sánchez','Internista','medicina-interna','BS','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento integral de enfermedades complejas del adulto, hipertensión arterial y patologías crónicas.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-interna'),10),
  ('luis-davila','Dr. Luis Dávila','Medicina interna','medicina-interna','LD','Sede Pasaje · Consulta programada',1,'Evaluación clínica completa, manejo de comorbilidades y coordinación médica de pacientes hospitalizados y ambulatorios.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-interna'),11),
  ('sixto-barriga','Dr. Sixto Barriga','Medicina familiar y diabetólogo','medicina-interna','SB','Sede Pasaje · Consulta programada',1,'Control metabólico y prevención de complicaciones de la diabetes, acompañamiento médico integral para la familia.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-familiar'),12),
  ('jorge-castro','Dr. Jorge Castro','Cardiólogo','medicina-interna','JC','Sede Pasaje · Consulta programada',1,'Evaluación y tratamiento de afecciones cardiovasculares, hipertensión, arritmias, electrocardiogramas y riesgo quirúrgico.',(SELECT `id` FROM `especialidades` WHERE `slug`='cardiologia'),13),
  ('enkly-heredia','Dr. Enkly Heredia','Gastroenterólogo','medicina-interna','EH','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento del tubo digestivo, endoscopía digestiva alta, colonoscopía y afecciones hepáticas.',(SELECT `id` FROM `especialidades` WHERE `slug`='gastroenterologia'),14),
  ('luis-miguel-gaibor','Dr. Luis Miguel Gaibor','Neurólogo','medicina-interna','LG','Sede Pasaje · Consulta programada',1,'Manejo clínico de cefaleas, migrañas, epilepsia, trastornos neuromusculares y accidentes cerebrovasculares.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-interna'),15),
  ('edgar-roman','Dr. Edgar Román','Ortopedista · Traumatólogo','traumatologia','ER','Sede Pasaje · Consulta programada',1,'Tratamiento de fracturas, luxaciones, lesiones deportivas, reemplazo articular y rehabilitación osteomuscular.',(SELECT `id` FROM `especialidades` WHERE `slug`='traumatologia'),16),
  ('luis-reyes-perez','Dr. Luis Reyes Pérez','Traumatólogo','traumatologia','LR','Sede Pasaje · Consulta programada',1,'Diagnóstico y resolución de traumatismos agudos, artroscopía, patologías de rodilla, cadera y columna.',(SELECT `id` FROM `especialidades` WHERE `slug`='traumatologia'),17),
  ('nicolas-barzallo','Dr. Nicolás Barzallo','Traumatólogo','traumatologia','NB','Sede Pasaje · Consulta programada',1,'Atención traumatológica de urgencias y consulta externa, corrección ortopédica y patologías del aparato locomotor.',(SELECT `id` FROM `especialidades` WHERE `slug`='traumatologia'),18),
  ('alberto-amaya','Dr. Alberto Amaya','Urólogo','otras','AA','Sede Pasaje · Consulta programada',1,'Diagnóstico y tratamiento de patologías de la vía urinaria masculina y femenina, próstata y litiasis renal.',(SELECT `id` FROM `especialidades` WHERE `slug`='urologia'),19),
  ('alexander-hernandez','Dr. Alexander Hernández','Otorrinolaringólogo','otras','AH','Sede Pasaje · Consulta programada',1,'Tratamiento de enfermedades de oído, nariz, garganta, rinitis alérgica, sinusitis y trastornos de la audición.',(SELECT `id` FROM `especialidades` WHERE `slug`='otorrinolaringologia'),20),
  ('washington-ortega','Dr. Washington Ortega','Psicólogo','otras','WO','Sede Pasaje · Consulta programada',1,'Atención psicoterapéutica para niños, jóvenes y adultos, bienestar emocional, manejo de estrés y apoyo familiar.',(SELECT `id` FROM `especialidades` WHERE `slug`='psicologia'),21),
  ('sabina-tipantaxi','Dra. Sabina Tipantaxi F.','Cirugía vascular y endovascular','cirugia','ST','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en enfermedades vasculares, várices, pie diabético y evaluación de la circulación.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),22),
  ('jose-luis-pacheco','Dr. José Luis Pacheco','Ortopedia y traumatología pediátrica','traumatologia','JP','Sede Pasaje · Disponibilidad por confirmar',0,'Atención de problemas ortopédicos infantiles, alteraciones de cadera, columna y extremidades, y traumatismos pediátricos.',(SELECT `id` FROM `especialidades` WHERE `slug`='traumatologia'),23),
  ('pedro-ayala','Dr. Pedro Ayala','Ginecólogo obstetra','ginecologia','PA','Sede Pasaje · Disponibilidad por confirmar',0,'Profesional anunciado por el hospital para atención especializada en ginecología y obstetricia.',(SELECT `id` FROM `especialidades` WHERE `slug`='ginecologia-y-obstetricia'),24),
  ('abdon-pacurucu','Dr. Abdón Pacurucu','Cirujano general y endoscopista digestivo','cirugia','AP','Sede Pasaje · Disponibilidad por confirmar',0,'Profesional de cirugía general y endoscopia digestiva, anunciado en publicaciones institucionales.',(SELECT `id` FROM `especialidades` WHERE `slug`='cirugia-general'),25),
  ('ronald-morocho','Dr. Ronald Morocho','Medicina interna','medicina-interna','RM','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en medicina interna, evaluación clínica y prevención de enfermedades.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-interna'),26),
  ('patricio-carrion','Dr. Patricio Carrión','Médico general','otras','PC','Sede Pasaje · Disponibilidad por confirmar',0,'Atención médica general, valoración inicial y orientación clínica de pacientes.',(SELECT `id` FROM `especialidades` WHERE `slug`='medicina-familiar'),27),
  ('karen-cobos','Dra. Karen Cobos S.','Geriatría y diabetología','medicina-interna','KC','Sede Pasaje · Disponibilidad por confirmar',0,'Atención especializada en salud de las personas mayores y evaluación de pacientes con diabetes.',(SELECT `id` FROM `especialidades` WHERE `slug`='geriatria'),28);

INSERT INTO `medico_especialidades` (`medico_id`,`especialidad_id`,`es_principal`)
SELECT `id`, `especialidad_id`, 1 FROM `medicos` WHERE `especialidad_id` IS NOT NULL;

INSERT INTO `medico_especialidades` (`medico_id`,`especialidad_id`,`es_principal`) VALUES
  ((SELECT `id` FROM `medicos` WHERE `slug`='sixto-barriga'),(SELECT `id` FROM `especialidades` WHERE `slug`='diabetologia'),0),
  ((SELECT `id` FROM `medicos` WHERE `slug`='abdon-pacurucu'),(SELECT `id` FROM `especialidades` WHERE `slug`='gastroenterologia'),0),
  ((SELECT `id` FROM `medicos` WHERE `slug`='karen-cobos'),(SELECT `id` FROM `especialidades` WHERE `slug`='diabetologia'),0);

INSERT INTO `servicios` (`slug`,`nombre`,`etiqueta`,`clase_etiqueta`,`descripcion`,`icono`,`clave_ficha`,`orden`) VALUES
  ('emergencias-24h','Emergencias 24 horas y ambulancia','Atención 24/7','bg-tertiary text-on-tertiary font-bold uppercase','Atención médica inmediata para urgencias y emergencias con equipo de guardia y soporte móvil permanente.','emergency','emergencias',1),
  ('consulta-externa','Consulta externa','Agenda programada','bg-surface-container-low text-primary font-semibold','Valoración médica integral con diversas especialidades para diagnóstico, seguimiento y tratamiento programado.','stethoscope','consulta',2),
  ('cirugia-general-laparoscopica','Cirugía general y laparoscópica','Quirófano moderno','bg-surface-container-low text-primary font-semibold','Procedimientos quirúrgicos seguros y mínimamente invasivos con quirófano moderno y valoración prequirúrgica.','precision_manufacturing','cirugia',3),
  ('ginecologia-obstetricia','Ginecología y obstetricia','Materno-Fetal','bg-surface-container-low text-primary font-semibold','Cuidado integral de la salud femenina, control prenatal, atención del parto y patologías ginecológicas.','pregnant_woman','ginecologia',4),
  ('laboratorio-clinico','Laboratorio clínico','Resultados rápidos','bg-secondary-container text-on-secondary-container font-semibold','Exámenes de rutina, pruebas especiales, hematología, bioquímica y microbiología con entrega oportuna de resultados.','science','laboratorio',5),
  ('imagenologia','Imagenología','Diagnóstico visual','bg-surface-container-low text-primary font-semibold','Diagnóstico preciso con rayos X digitales, ecografías de alta resolución y tomografía axial computarizada.','radiology','imagen',6);

INSERT INTO `servicio_caracteristicas` (`servicio_id`,`caracteristica`,`orden`) VALUES
  ((SELECT `id` FROM `servicios` WHERE `slug`='emergencias-24h'),'Unidad móvil de soporte vital disponible',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='emergencias-24h'),'Triaje Manchester con respuesta inmediata',1),
  ((SELECT `id` FROM `servicios` WHERE `slug`='consulta-externa'),'Turnos matutinos y vespertinos',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='consulta-externa'),'Historia clínica digital centralizada',1),
  ((SELECT `id` FROM `servicios` WHERE `slug`='cirugia-general-laparoscopica'),'Recuperación ambulatoria y hospitalaria',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='cirugia-general-laparoscopica'),'Anestesiología y monitorización avanzada',1),
  ((SELECT `id` FROM `servicios` WHERE `slug`='ginecologia-obstetricia'),'Monitoreo fetal y sala de parto humanizado',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='ginecologia-obstetricia'),'Ecografía obstétrica y cribado preventivo',1),
  ((SELECT `id` FROM `servicios` WHERE `slug`='laboratorio-clinico'),'Entrega digital por correo y WhatsApp',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='laboratorio-clinico'),'Toma de muestras desde las 06:30',1),
  ((SELECT `id` FROM `servicios` WHERE `slug`='imagenologia'),'Baja radiación con sensor digital',0),
  ((SELECT `id` FROM `servicios` WHERE `slug`='imagenologia'),'Informes avalados por radiólogos',1);

INSERT INTO `servicio_fichas` (`clave_ficha`,`titulo`,`etiqueta`,`descripcion`,`icono`,`aviso_tiempo`,`aviso_preparacion`,`orden`) VALUES
  ('laboratorio','Laboratorio Clínico Automatizado','Unidad Diagnóstica Nivel II','Área técnica de bioanálisis equipada con analizadores hematológicos y bioquímicos con calibración diaria y control de calidad internacional.','science','Rutina: 2 a 4 horas • Especiales: 24h','Disponible en zona urbana de Pasaje con previa cita',1),
  ('imagen','Imagenología y Radiodiagnóstico Digital','Tecnología de Diagnóstico Visual','Equipos de baja radiación y ecografía doppler de alta resolución con interpretación oportuna de médicos radiólogos certificados.','radiology','Informes inmediatos para urgencias • Programados: 2 a 6 horas','Protocolo de baja dosis para seguridad del paciente pediátrico y adulto',2),
  ('cirugia','Cirugía General y Laparoscópica Mínimamente Invasiva','Centro Quirúrgico Acreditado','Quirófano moderno equipado con torres laparoscópicas HD, electrocauterios bipolares y monitorización anestesiológica continua.','precision_manufacturing','Quirófanos disponibles 24/7 para emergencias quirúrgicas','Recuperación asistida con enfermería especializada y analgesia continua',3),
  ('hospitalizacion','Hospitalización y Recuperación Asistida','Cuidado Clínico y Seguridad','Habitaciones individuales y compartidas con diseño ergonómico, climatización médica, gases medicinales y enfermería permanente.','bed','Monitoreo clínico permanente las 24 horas del día','Acceso inclusivo, zona de descanso para acompañante y climatización',4);

INSERT INTO `servicio_ficha_items` (`ficha_id`,`tipo`,`descripcion`,`orden`) VALUES
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'alcance','Hematología completa y coagulación',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'alcance','Bioquímica sanguínea (glucosa, perfil lipídico, renal, hepático)',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'alcance','Inmunología, hormonas y marcadores tiroideos',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'alcance','Microbiología, urocultivos y coproanálisis',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'preparacion','Ayuno requerido de 8 a 12 horas para pruebas metabólicas',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'preparacion','Evitar ingesta de alcohol y grasas la noche anterior',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'preparacion','Presentar cédula de identidad y orden médica si aplica',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='laboratorio'),'preparacion','Notificar toma de medicamentos continuos al personal',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'alcance','Rayos X digitales de tórax, óseos y articulaciones',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'alcance','Ecografías abdominales, pélvicas, renales y de tejidos blandos',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'alcance','Ecografía obstétrica estructural y Doppler feto-placentario',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'alcance','Tomografía axial computarizada programada',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'preparacion','Retirar joyas, hebillas u objetos metálicos previo a rayos X',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'preparacion','Ecografía pélvica/renal: vejiga llena (beber agua 45 min antes)',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'preparacion','Ecografía abdominal superior: 6 horas de ayuno',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='imagen'),'preparacion','Traer estudios previos para análisis comparativo',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'alcance','Colecistectomía laparoscópica (extracción de vesícula)',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'alcance','Apendicectomía y plastias de pared abdominal (hernias)',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'alcance','Cirugía ginecológica y cesáreas programadas o de urgencia',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'alcance','Procedimientos ambulatorios y biopsias quirúrgicas',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'preparacion','Valoración preanestésica obligatoria previa a la cirugía',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'preparacion','Ayuno absoluto de 8 horas antes del ingreso a quirófano',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'preparacion','Consentimiento informado firmado por el paciente o familiar',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='cirugia'),'preparacion','Disponer de un acompañante adulto responsable para el alta',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'alcance','Manejo de postoperatorio inmediato y mediato',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'alcance','Tratamientos antibióticos intravenosos y sueroterapia',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'alcance','Control cardiorrespiratorio y hemodinámico continuo',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'alcance','Atención integral del puerperio y recién nacido',3),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'preparacion','Artículos de aseo personal y ropa cómoda de descanso',0),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'preparacion','Documento de identidad y carpeta con historial médico previo',1),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'preparacion','Registro de un familiar titular para informes médicos diarios',2),
  ((SELECT `id` FROM `servicio_fichas` WHERE `clave_ficha`='hospitalizacion'),'preparacion','Cumplimiento de las normas de bioseguridad institucional',3);

INSERT INTO `preguntas_frecuentes` (`slug`,`pregunta`,`respuesta`,`orden`) VALUES
  ('emergencias-24h','¿Cómo funciona el servicio de emergencias 24 horas?','Nuestro servicio de emergencias opera de manera ininterrumpida los 365 días del año con médico de guardia residente, enfermería y laboratorio de respuesta inmediata. El ingreso se realiza mediante triaje clínico estandarizado para priorizar según la gravedad del cuadro de salud. También disponemos de ambulancia para rescate y traslados dentro y fuera de Pasaje llamando al 098 797 3511.',1),
  ('preparacion-examenes','¿Qué preparación previa necesito para exámenes de laboratorio e imagen?','Para la mayoría de exámenes sanguíneos se requiere entre 8 y 12 horas de ayuno estricto (agua simple está permitida). En ecografías pélvicas o renales se recomienda beber 1 litro de agua sin gas 45 minutos antes del estudio sin orinar. Para radiografías digitales no se requieren preparaciones digestivas, salvo retirar objetos metálicos. Puedes consultar las indicaciones exactas de tu prescripción vía WhatsApp previo a tu visita.',2),
  ('cirugia-programada','¿Cómo solicito una valoración para cirugía programada?','El primer paso es agendar una consulta médica de valoración en consulta externa con el cirujano especialista. Durante esta cita se revisarán tus exámenes clínicos previos o se solicitarán los estudios preoperatorios (laboratorio, electrocardiograma y valoración anestesiológica). Una vez aprobado el perfil quirúrgico, se programa la fecha de quirófano que mejor se ajuste a tu tiempo.',3),
  ('horarios-visita','¿Cuáles son los horarios de visita en el área de hospitalización?','Las visitas generales están habilitadas de 10:00 a 12:00 y de 16:00 a 18:00 para garantizar el reposo clínico de los pacientes. En habitaciones privadas se autoriza un acompañante permanente las 24 horas del día. Solicitamos a los visitantes mantener el orden, sanitización de manos al ingreso y evitar el acceso con niños menores de 12 años en áreas de cuidado especial.',4),
  ('agendar-consulta','¿Cómo puedo agendar una consulta médica externa?','Puedes solicitar tu turno médico a través de nuestras vías oficiales: de forma presencial en recepción en nuestra sede central en Pasaje (Av. Quito y Sucre), por mensajería instantánea en nuestra línea oficial de WhatsApp (098 797 3511), o mediante la central telefónica. Te asignaremos fecha, hora y confirmación de agenda con el especialista.',5);
