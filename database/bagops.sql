-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: bagops
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `analytics_cache`
--

DROP TABLE IF EXISTS `analytics_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `analytics_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(100) NOT NULL,
  `cache_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cache_data`)),
  `cache_type` enum('personnel_stats','scheduling_patterns','fatigue_trends','compliance_metrics') NOT NULL,
  `valid_until` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`),
  KEY `idx_cache_key` (`cache_key`),
  KEY `idx_cache_type` (`cache_type`),
  KEY `idx_valid_until` (`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `analytics_cache`
--

LOCK TABLES `analytics_cache` WRITE;
/*!40000 ALTER TABLE `analytics_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `analytics_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apel_nominal`
--

DROP TABLE IF EXISTS `apel_nominal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apel_nominal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `jenis_apel` enum('pagi','sore') NOT NULL DEFAULT 'pagi',
  `personil_id` int(11) NOT NULL,
  `status` enum('hadir','tidak_hadir','sakit','ijin','cuti','dinas_luar','tugas_belajar') NOT NULL DEFAULT 'hadir',
  `jam_hadir` time DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `pencatat` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apel` (`tanggal`,`jenis_apel`,`personil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apel_nominal`
--

LOCK TABLES `apel_nominal` WRITE;
/*!40000 ALTER TABLE `apel_nominal` DISABLE KEYS */;
/*!40000 ALTER TABLE `apel_nominal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `personil_name` varchar(255) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation` (`operation_id`),
  KEY `idx_personil` (`personil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_schedule`
--

DROP TABLE IF EXISTS `backup_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `day_of_week` tinyint(4) DEFAULT NULL COMMENT '0=Sunday, 1=Monday, etc. For weekly backups',
  `day_of_month` tinyint(4) DEFAULT NULL COMMENT '1-31, for monthly backups',
  `hour` tinyint(4) NOT NULL DEFAULT 2 COMMENT 'Hour of day (0-23) to run backup',
  `minute` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Minute (0-59) to run backup',
  `backup_type` enum('full','partial') NOT NULL DEFAULT 'full',
  `tables_to_backup` text DEFAULT NULL COMMENT 'Comma-separated table names for partial backup',
  `keep_count` int(11) NOT NULL DEFAULT 7 COMMENT 'Number of backups to keep',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `next_run` (`next_run`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_schedule`
--

LOCK TABLES `backup_schedule` WRITE;
/*!40000 ALTER TABLE `backup_schedule` DISABLE KEYS */;
INSERT INTO `backup_schedule` VALUES (1,'Daily Full Backup','daily',NULL,NULL,2,0,'full',NULL,7,1,NULL,'2026-04-01 02:00:00','2026-03-30 19:41:20',NULL);
/*!40000 ALTER TABLE `backup_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `backup_type` enum('full','partial','scheduled') NOT NULL DEFAULT 'full',
  `tables_included` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `is_auto` tinyint(1) NOT NULL DEFAULT 0,
  `checksum` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `backup_type` (`backup_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bagian`
--

DROP TABLE IF EXISTS `bagian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bagian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_bagian` varchar(50) NOT NULL,
  `nama_bagian` varchar(100) NOT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_bagian` (`kode_bagian`),
  KEY `id_unsur` (`id_unsur`),
  CONSTRAINT `bagian_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bagian`
--

LOCK TABLES `bagian` WRITE;
/*!40000 ALTER TABLE `bagian` DISABLE KEYS */;
INSERT INTO `bagian` VALUES (1,'PIMPINAN','PIMPINAN',1,1,'Unit Pimpinan POLRES',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(2,'BAG_OPS','BAG OPS',2,1,'Bagian Operasional',1,'2026-03-28 18:16:57','2026-04-06 09:01:37'),(3,'BAG_REN','BAG REN',2,3,'Bagian Perencanaan',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(4,'BAG_SDM','BAG SDM',2,2,'Bagian Sumber Daya Manusia',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(5,'BAG_LOG','BAG LOG',2,4,'Bagian Logistik',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(6,'SAT_INTELKAM','SAT INTELKAM',3,2,'Satuan Intelijen dan Keamanan',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(7,'SAT_RESKRIM','SAT RESKRIM',3,1,'Satuan Reserse Kriminal',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(8,'SAT_RESNARKOBA','SAT RESNARKOBA',3,4,'Satuan Reserse Narkoba',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(9,'SAT_LANTAS','SAT LANTAS',3,3,'Satuan Lalu Lintas',1,'2026-03-28 18:16:57','2026-04-06 09:12:22'),(10,'SAT_SAMAPTA','SAT SAMAPTA',3,5,'Satuan Pengamanan',1,'2026-03-28 18:16:57','2026-04-06 09:01:43'),(11,'SAT_PAMOBVIT','SAT PAMOBVIT',3,6,'Satuan Pengamanan Objek Vital',1,'2026-03-28 18:16:57','2026-04-06 09:01:43'),(12,'SAT_POLAIRUD','SAT POLAIRUD',3,7,'Satuan Polisi Air dan Udara',1,'2026-03-28 18:16:57','2026-04-06 09:01:43'),(13,'SAT_TAHTI','SAT TAHTI',3,8,'Satuan Tata Usaha',1,'2026-03-28 18:16:57','2026-04-06 09:01:43'),(14,'SAT_BINMAS','SAT BINMAS',3,9,'Satuan Pembinaan Masyarakat',1,'2026-03-28 18:16:57','2026-04-06 09:01:43'),(15,'POLSEK_HARIAN_BOHO','POLSEK HARIAN BOHO',4,5,'Polsek Harian Boho',1,'2026-03-28 18:16:57','2026-04-06 08:59:20'),(16,'POLSEK_PALIPI','POLSEK PALIPI',4,1,'Polsek Palipi',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(17,'POLSEK_SIMANINDO','POLSEK SIMANINDO',4,2,'Polsek Simanindo',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(18,'POLSEK_ONAN_RUNGGU','POLSEK NAINGGOLAN',4,3,'Polsek Onan Runggu',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(19,'POLSEK_PANGURURAN','POLSEK PANGURURAN',4,4,'Polsek Pangururan',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(20,'SPKT','SPKT',5,1,'Sentra Pelayanan Kepolisian Terpadu',1,'2026-03-28 18:16:57','2026-04-06 08:58:56'),(21,'SIUM','SIUM',5,2,'Satuan Intelijen Umum',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(22,'SIKEU','SIKEU',5,3,'Satuan Keuangan',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(23,'SIDOKKES','SIDOKKES',5,4,'Satuan Dokter Kesehatan',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(24,'SIWAS','SIWAS',5,5,'Satuan Pengawasan Internal',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(25,'SITIK','SITIK',5,6,'Satuan Identifikasi dan Teknologi Forensik',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(26,'SIKUM','SIKUM',5,7,'Satuan Komunikasi',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(27,'SIPROPAM','SIPROPAM',5,8,'Satuan Profesi dan Pengamanan',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(28,'SIHUMAS','SIHUMAS',5,9,'Satuan Humas',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(29,'BKO','BKO',6,10,'Bantuan Kendali Operasional',1,'2026-03-28 18:16:57','2026-04-06 08:58:57'),(30,'PERS_MUTASI','PERS MUTASI',6,999,'Personil dalam proses mutasi',1,'2026-04-09 16:59:01','2026-04-09 16:59:01');
/*!40000 ALTER TABLE `bagian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bagian_pimpinan`
--

DROP TABLE IF EXISTS `bagian_pimpinan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bagian_pimpinan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bagian_id` int(11) NOT NULL,
  `personil_id` int(11) NOT NULL,
  `tanggal_mulai` date DEFAULT curdate(),
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_active_assignment` (`bagian_id`,`personil_id`,`tanggal_mulai`),
  KEY `personil_id` (`personil_id`),
  CONSTRAINT `bagian_pimpinan_ibfk_1` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bagian_pimpinan_ibfk_2` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bagian_pimpinan`
--

LOCK TABLES `bagian_pimpinan` WRITE;
/*!40000 ALTER TABLE `bagian_pimpinan` DISABLE KEYS */;
/*!40000 ALTER TABLE `bagian_pimpinan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_tokens`
--

DROP TABLE IF EXISTS `calendar_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `calendar_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_tokens`
--

LOCK TABLES `calendar_tokens` WRITE;
/*!40000 ALTER TABLE `calendar_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certifications`
--

DROP TABLE IF EXISTS `certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personil_id` varchar(20) NOT NULL,
  `certification_type` varchar(100) NOT NULL,
  `certification_name` varchar(200) NOT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('valid','expired','expiring','suspended') DEFAULT 'valid',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `attachment_path` varchar(500) DEFAULT NULL COMMENT 'Path to certificate file',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil_cert` (`personil_id`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_cert_status` (`status`),
  KEY `idx_cert_type` (`certification_type`),
  KEY `idx_expiry_status` (`expiry_date`,`status`),
  CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certifications`
--

LOCK TABLES `certifications` WRITE;
/*!40000 ALTER TABLE `certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `daily_attendance_summary`
--

DROP TABLE IF EXISTS `daily_attendance_summary`;
/*!50001 DROP VIEW IF EXISTS `daily_attendance_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `daily_attendance_summary` AS SELECT
 1 AS `attendance_date`,
  1 AS `total_scheduled`,
  1 AS `present`,
  1 AS `sick`,
  1 AS `permitted`,
  1 AS `absent`,
  1 AS `attendance_rate` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `emergency_task_performance`
--

DROP TABLE IF EXISTS `emergency_task_performance`;
/*!50001 DROP VIEW IF EXISTS `emergency_task_performance`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `emergency_task_performance` AS SELECT
 1 AS `task_date`,
  1 AS `total_tasks`,
  1 AS `completed`,
  1 AS `cancelled`,
  1 AS `avg_duration_minutes`,
  1 AS `critical_tasks`,
  1 AS `high_tasks` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `emergency_task_responses`
--

DROP TABLE IF EXISTS `emergency_task_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emergency_task_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `response` enum('acknowledged','confirmed','declined','unable') NOT NULL,
  `notes` text DEFAULT NULL,
  `eta` varchar(100) DEFAULT NULL,
  `response_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_task_personil` (`task_id`,`personil_id`),
  KEY `idx_response_time` (`response_time`),
  CONSTRAINT `emergency_task_responses_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `emergency_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_task_responses`
--

LOCK TABLES `emergency_task_responses` WRITE;
/*!40000 ALTER TABLE `emergency_task_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `emergency_task_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_tasks`
--

DROP TABLE IF EXISTS `emergency_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emergency_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_code` varchar(50) NOT NULL COMMENT 'Kode unik task darurat',
  `task_name` varchar(200) NOT NULL,
  `task_type` enum('urgent','critical','emergency','recall') NOT NULL,
  `description` text DEFAULT NULL,
  `priority_level` enum('low','medium','high','critical') DEFAULT 'high',
  `location` varchar(255) DEFAULT NULL,
  `required_personnel` int(11) DEFAULT 1,
  `estimated_duration` decimal(4,2) DEFAULT NULL COMMENT 'Durasi dalam jam',
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_by` varchar(50) NOT NULL,
  `assigned_to` varchar(20) DEFAULT NULL COMMENT 'NRP personil yang ditugaskan',
  `original_schedule_id` int(11) DEFAULT NULL COMMENT 'Jadwal asli yang diganti',
  `replacement_reason` text DEFAULT NULL COMMENT 'Alasan penggantian',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_code` (`task_code`),
  KEY `original_schedule_id` (`original_schedule_id`),
  KEY `idx_task_status` (`status`),
  KEY `idx_task_priority` (`priority_level`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_assigned_to_status` (`assigned_to`,`status`),
  KEY `idx_start_status` (`start_time`,`status`),
  FULLTEXT KEY `task_name` (`task_name`,`description`),
  CONSTRAINT `emergency_tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `personil` (`nrp`) ON DELETE SET NULL,
  CONSTRAINT `emergency_tasks_ibfk_2` FOREIGN KEY (`original_schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_tasks`
--

LOCK TABLES `emergency_tasks` WRITE;
/*!40000 ALTER TABLE `emergency_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `emergency_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_code` varchar(50) NOT NULL,
  `equipment_name` varchar(200) NOT NULL,
  `equipment_type` enum('weapon','vehicle','radio','protective','tools','other') NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT NULL,
  `current_status` enum('available','assigned','maintenance','retired','lost') DEFAULT 'available',
  `current_assignment` varchar(20) DEFAULT NULL COMMENT 'Assigned to personil NRP',
  `location` varchar(255) DEFAULT NULL,
  `maintenance_schedule` varchar(100) DEFAULT NULL,
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipment_code` (`equipment_code`),
  KEY `idx_equipment_type` (`equipment_type`),
  KEY `idx_equipment_status` (`current_status`),
  KEY `idx_current_assignment` (`current_assignment`),
  KEY `idx_next_maintenance` (`next_maintenance`),
  KEY `idx_maintenance_status` (`next_maintenance`,`current_status`),
  CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`current_assignment`) REFERENCES `personil` (`nrp`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_assignments`
--

DROP TABLE IF EXISTS `equipment_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `assignment_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `assignment_purpose` varchar(255) DEFAULT NULL,
  `condition_assigned` varchar(100) DEFAULT NULL,
  `condition_returned` varchar(100) DEFAULT NULL,
  `status` enum('active','returned','overdue','lost') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `assigned_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `personil_id` (`personil_id`),
  KEY `idx_equipment_personil` (`equipment_id`,`personil_id`),
  KEY `idx_assignment_status` (`status`),
  KEY `idx_assignment_date` (`assignment_date`),
  CONSTRAINT `equipment_assignments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_assignments_ibfk_2` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_assignments`
--

LOCK TABLES `equipment_assignments` WRITE;
/*!40000 ALTER TABLE `equipment_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fatigue_tracking`
--

DROP TABLE IF EXISTS `fatigue_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fatigue_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personil_id` varchar(20) NOT NULL,
  `tracking_date` date NOT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `rest_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fatigue_score` int(11) DEFAULT 100,
  `fatigue_level` enum('low','medium','high','critical') DEFAULT 'low',
  `violations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'List of fatigue violations' CHECK (json_valid(`violations`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil_fatigue` (`personil_id`,`tracking_date`),
  KEY `idx_fatigue_level` (`fatigue_level`),
  KEY `idx_tracking_date` (`tracking_date`),
  KEY `idx_personnel_date_score` (`personil_id`,`tracking_date`,`fatigue_score`),
  KEY `idx_date_level` (`tracking_date`,`fatigue_level`),
  CONSTRAINT `fatigue_tracking_ibfk_1` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fatigue_tracking`
--

LOCK TABLES `fatigue_tracking` WRITE;
/*!40000 ALTER TABLE `fatigue_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `fatigue_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generated_reports`
--

DROP TABLE IF EXISTS `generated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(50) NOT NULL,
  `report_name` varchar(200) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `format` enum('pdf','excel','csv') NOT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Auto-delete after this date',
  `download_count` int(11) DEFAULT 0,
  `last_downloaded` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_report_type` (`report_type`),
  KEY `idx_generated_at` (`generated_at`),
  KEY `idx_generated_by` (`generated_by`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generated_reports`
--

LOCK TABLES `generated_reports` WRITE;
/*!40000 ALTER TABLE `generated_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `generated_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jabatan`
--

DROP TABLE IF EXISTS `jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jabatan` varchar(50) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `id_bagian` int(11) DEFAULT NULL,
  `tingkat_jabatan` varchar(50) DEFAULT NULL,
  `eselon` varchar(20) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `is_pimpinan` tinyint(1) DEFAULT 0,
  `is_pembantu_pimpinan` tinyint(1) DEFAULT 0,
  `is_kepala_unit` tinyint(1) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `required_certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'List of required certifications' CHECK (json_valid(`required_certifications`)),
  `certification_check` tinyint(1) DEFAULT 0 COMMENT 'Check certifications before assignment',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jabatan` (`kode_jabatan`),
  KEY `id_unsur` (`id_unsur`),
  CONSTRAINT `jabatan_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jabatan`
--

LOCK TABLES `jabatan` WRITE;
/*!40000 ALTER TABLE `jabatan` DISABLE KEYS */;
INSERT INTO `jabatan` VALUES (1,'KAPOLRES_SAMOSIR','KAPOLRES SAMOSIR',1,1,NULL,'PIMPINAN',NULL,NULL,1,0,0,'Jabatan KAPOLRES SAMOSIR di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:09:51',NULL,0),(2,'WAKAPOLRES','WAKAPOLRES',1,1,NULL,'PIMPINAN',NULL,NULL,1,0,0,'Jabatan WAKAPOLRES di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:09:39',NULL,0),(3,'KABAGOPS','KABAG OPS',2,1,3,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KABAG OPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-10 04:34:57',NULL,0),(4,'PS._PAUR_SUBBAGBINOPS','PS. PAUR SUBBAGBINOPS',2,2,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. PAUR SUBBAGBINOPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(5,'BA_MIN_BAG_OPS','BA MIN BAG OPS',2,3,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BA MIN BAG OPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(6,'ASN_BAG_OPS','ASN BAG OPS',2,4,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan ASN BAG OPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(7,'KA_SPKT','KA SPKT',5,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KA SPKT di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(8,'PAMAPTA_1','PAMAPTA 1',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PAMAPTA 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(9,'PAMAPTA_2','PAMAPTA 2',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PAMAPTA 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(10,'PAMAPTA_3','PAMAPTA 3',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PAMAPTA 3 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(11,'BAMIN_PAMAPTA_2','BAMIN PAMAPTA 2',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BAMIN PAMAPTA 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(12,'BAMIN_PAMAPTA_3','BAMIN PAMAPTA 3',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BAMIN PAMAPTA 3 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(13,'BAMIN_PAMAPTA_1','BAMIN PAMAPTA 1',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BAMIN PAMAPTA 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(14,'PAURSUBBAGPROGAR','PAURSUBBAGPROGAR',2,5,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PAURSUBBAGPROGAR di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(15,'BA_MIN_BAG_REN','BA MIN BAG REN',2,6,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BA MIN BAG REN di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(16,'PS._KABAG_SDM','PS. KABAG SDM',2,7,4,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan PS. KABAG SDM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 18:00:41',NULL,0),(17,'PAURSUBBAGBINKAR','PAURSUBBAGBINKAR',2,8,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PAURSUBBAGBINKAR di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:32',NULL,0),(18,'BA_MIN_BAG_SDM','BA MIN BAG SDM',2,9,4,'STAF',NULL,NULL,0,0,0,'Jabatan BA MIN BAG SDM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 18:00:41',NULL,0),(19,'BA_POLRES_SAMOSIR','BA POLRES SAMOSIR',2,10,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BA POLRES SAMOSIR di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(20,'ADC_KAPOLRES','ADC KAPOLRES',2,11,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan ADC KAPOLRES di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(21,'BINTARA_SATLANTAS','BINTARA SATLANTAS',3,12,9,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SATLANTAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(22,'PLT._KASUBBAGBEKPAL','Plt. KASUBBAGBEKPAL',2,13,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan Plt. KASUBBAGBEKPAL di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(23,'BA_MIN_BAG_LOG','BA MIN BAG LOG',2,14,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BA MIN BAG LOG di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(24,'PS._KASIUM','PS. KASIUM',5,15,NULL,'STAF',NULL,NULL,0,0,1,'Jabatan PS. KASIUM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(25,'BINTARA_SIUM','BINTARA SIUM',5,16,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIUM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(26,'PS._KASIKEU','PS. KASIKEU',5,17,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. KASIKEU di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:40',NULL,0),(27,'BINTARA_SIKEU','BINTARA SIKEU',5,18,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIKEU di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 09:03:41',NULL,0),(28,'KASIDOKKES','KASIDOKKES',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan KASIDOKKES di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(29,'BA_SIDOKKES','BA SIDOKKES',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BA SIDOKKES di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(30,'PLT._KASIWAS','Plt. KASIWAS',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan Plt. KASIWAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(31,'BINTARA_SIWAS','BINTARA SIWAS',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIWAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(32,'BINTARA_SITIK','BINTARA SITIK',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SITIK di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(33,'KASUBSIBANKUM','KASUBSIBANKUM',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan KASUBSIBANKUM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(34,'BINTARA_SIKUM','BINTARA SIKUM',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIKUM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(35,'PS._KASIPROPAM','PS. KASIPROPAM',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. KASIPROPAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(36,'PS._KANIT_PROPOS','PS. KANIT PROPOS',5,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT PROPOS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(37,'PS._KANIT_PAMINAL','PS. KANIT PAMINAL',5,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT PAMINAL di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(38,'BINTARA_SIPROPAM','BINTARA SIPROPAM',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIPROPAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(39,'BINTARA_SIHUMAS','BINTARA SIHUMAS',5,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SIHUMAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(40,'KAURBINOPS','KAURBINOPS',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan KAURBINOPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(41,'BINTARA_SAT_BINMAS','BINTARA SAT BINMAS',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT BINMAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(42,'PS._KASAT_INTELKAM','PS. KASAT INTELKAM',3,0,6,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan PS. KASAT INTELKAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:59:48',NULL,0),(43,'PS._KAURMINTU','PS. KAURMINTU',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. KAURMINTU di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(44,'PS._KANIT_3','PS. KANIT 3',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT 3 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(45,'PS._KANIT_1','PS. KANIT 1',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(46,'PS._KANIT_2','PS. KANIT 2',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(47,'BINTARA_SAT_INTELKAM','BINTARA SAT INTELKAM',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT INTELKAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(48,'BINTARA_SATINTELKAM','BINTARA SATINTELKAM',3,0,6,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SATINTELKAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:32',NULL,0),(49,'KASAT_RESKRIM','KASAT RESKRIM',3,0,7,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASAT RESKRIM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:59:38',NULL,0),(50,'KANITIDIK_3','KANIT IDIK 3',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANITIDIK 3 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(51,'KANITIDIK_4','KANIT IDIK 4',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANITIDIK 4 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(52,'KANITIDIK_1','KANIT IDIK 1',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANITIDIK 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(53,'KANITIDIK_5','KANIT IDIK 5',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANITIDIK 5 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(54,'PS._KANITIDIK_2','PS. KANITIDIK 2',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITIDIK 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(55,'PS._KANIT_IDENTIFIKASI','PS. KANIT IDENTIFIKASI',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT IDENTIFIKASI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(56,'BINTARA_SAT_RESKRIM','BINTARA SAT RESKRIM',3,0,7,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT RESKRIM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:21',NULL,0),(57,'KASATRESNARKOBA','KASATRESNARKOBA',3,0,8,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASATRESNARKOBA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:03',NULL,0),(58,'PS.KANIT_IDIK_1','PS.KANIT IDIK 1',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS.KANIT IDIK 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(59,'BINTARA_SATRESNARKOBA','BINTARA SATRESNARKOBA',3,0,8,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SATRESNARKOBA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:49',NULL,0),(60,'KASAT_SAMAPTA','KASAT SAMAPTA',3,0,10,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASAT SAMAPTA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:59:58',NULL,0),(61,'PS._KAURBINOPS','PS. KAURBINOPS',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. KAURBINOPS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(62,'PS._KANIT_DALMAS_2','PS. KANIT DALMAS 2',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT DALMAS 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(63,'PS._KANIT_TURJAWALI','PS. KANIT TURJAWALI',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT TURJAWALI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(64,'BINTARA_SAT_SAMAPTA','BINTARA SAT SAMAPTA',3,0,10,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT SAMAPTA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:44',NULL,0),(65,'KASAT_PAMOBVIT','KASAT PAMOBVIT',3,0,11,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASAT PAMOBVIT di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:59:53',NULL,0),(66,'PS._KANITPAMWASTER','PS. KANITPAMWASTER',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITPAMWASTER di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(67,'PS._KANITPAMWISATA','PS. KANITPAMWISATA',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITPAMWISATA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(68,'PS._PANIT_PAMWASTER','PS. PANIT PAMWASTER',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan PS. PANIT PAMWASTER di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(69,'BINTARA_SAT_PAMOBVIT','BINTARA SAT PAMOBVIT',3,0,11,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT PAMOBVIT di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:38',NULL,0),(70,'KASAT_LANTAS','KASAT LANTAS',3,0,9,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASAT LANTAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:59:43',NULL,0),(71,'KANITREGIDENT_LANTAS','KANIT REGIDENT LANTAS',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANITREGIDENT LANTAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(72,'PS._KANITGAKKUM','PS. KANITGAKKUM',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITGAKKUM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(73,'PS._KANITTURJAWALI','PS. KANITTURJAWALI',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITTURJAWALI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(74,'PS._KANITKAMSEL','PS. KANITKAMSEL',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITKAMSEL di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(75,'BINTARA_SAT_LANTAS','BINTARA SAT LANTAS',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT LANTAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(76,'KASAT_POLAIRUD','KASAT POLAIRUD',3,0,12,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KASAT POLAIRUD di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:13',NULL,0),(77,'PS._KANITPATROLI','PS. KANITPATROLI',3,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITPATROLI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(78,'BINTARA_SATPOLAIRUD','BINTARA SATPOLAIRUD',3,0,12,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SATPOLAIRUD di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 08:00:55',NULL,0),(79,'PS._KASAT_TAHTI','PS. KASAT TAHTI',3,0,NULL,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan PS. KASAT TAHTI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(80,'BINTARA_SAT_TAHTI','BINTARA SAT TAHTI',3,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA SAT TAHTI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(81,'PS._KAPOLSEK_HARIAN_BOHO','PS. KAPOLSEK HARIAN BOHO',4,0,NULL,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan PS. KAPOLSEK HARIAN BOHO di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(82,'PS._KANIT_INTELKAM','PS. KANIT INTELKAM',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT INTELKAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(83,'PS._KANIT_BINMAS','PS. KANIT BINMAS',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT BINMAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(84,'PS._KANIT_RESKRIM','PS. KANIT RESKRIM',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT RESKRIM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(85,'PS.KANIT_SAMAPTA','PS.KANIT SAMAPTA',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS.KANIT SAMAPTA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(86,'BINTARA_POLSEK','BINTARA POLSEK',4,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA POLSEK di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(87,'KAPOLSEK_PALIPI','KAPOLSEK PALIPI',4,0,16,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KAPOLSEK PALIPI di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:56:26',NULL,0),(88,'PS._KA_SPKT_1','PS. KA SPKT 1',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KA SPKT 1 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(89,'PS._KANIT_SAMAPTA','PS. KANIT SAMAPTA',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANIT SAMAPTA di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(90,'PS._KA_SPKT_2','PS. KA SPKT 2',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KA SPKT 2 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(91,'BINTARA__POLSEK','BINTARA  POLSEK',4,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan BINTARA  POLSEK di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(92,'PS._KAPOLSEK_SIMANINDO','PS. KAPOLSEK SIMANINDO',4,0,17,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan PS. KAPOLSEK SIMANINDO di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:56:36',NULL,0),(93,'KANIT_RESKRIM','KANIT RESKRIM',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan KANIT RESKRIM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(94,'PS._KANITPROPAM','PS. KANIT PROPAM',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KANITPROPAM di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-09 18:01:28',NULL,0),(95,'PS._KA_SPKT_3','PS. KA SPKT 3',4,0,NULL,'KEPALA SEKSI',NULL,NULL,0,0,1,'Jabatan PS. KA SPKT 3 di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(96,'KASIHUMAS','KASIHUMAS',4,0,NULL,'STAF',NULL,NULL,0,0,0,'Jabatan KASIHUMAS di POLRES Samosir',1,'2026-03-28 18:19:11','2026-03-28 18:19:11',NULL,0),(97,'KAPOLSEK_PANGURURAN','KAPOLSEK PANGURURAN',4,0,19,'PEMBANTU PIMPINAN',NULL,NULL,0,1,1,'Jabatan KAPOLSEK PANGURURAN di POLRES Samosir',1,'2026-03-28 18:19:11','2026-04-06 07:56:31',NULL,0),(98,'BINTARA_POLSEK_PALIPI','BINTARA POLSEK PALIPI',4,0,16,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:04','2026-04-06 08:04:04',NULL,0),(99,'BINTARA_POLSEK_PANGURURAN','BINTARA POLSEK PANGURURAN',4,0,19,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:04','2026-04-06 08:04:04',NULL,0),(100,'BINTARA_POLSEK_SIMANINDO','BINTARA POLSEK SIMANINDO',4,0,17,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:04','2026-04-06 08:04:04',NULL,0),(101,'BINTARA_POLSEK_NAINGGOLAN','BINTARA POLSEK NAINGGOLAN',4,0,18,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:04','2026-04-06 08:05:17',NULL,0),(102,'BINTARA_POLSEK_HARIANBOHO','BINTARA POLSEK HARIAN BOHO',4,1,15,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:04','2026-04-06 09:21:19',NULL,0),(103,'KANIT_RESKRIM_PALIPI','KANIT RESKRIM PALIPI',4,0,16,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:10','2026-04-06 08:04:10',NULL,0),(104,'KANIT_RESKRIM_PANGURURAN','KANIT RESKRIM PANGURURAN',4,0,19,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:10','2026-04-06 08:04:10',NULL,0),(105,'KANIT_RESKRIM_SIMANINDO','KANIT RESKRIM SIMANINDO',4,0,17,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:10','2026-04-06 08:04:10',NULL,0),(106,'KANIT_RESKRIM_NAINGGOLAN','KANIT RESKRIM NAINGGOLAN',4,0,18,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:10','2026-04-06 08:05:17',NULL,0),(107,'KANIT_RESKRIM_HARIANBOHO','KANIT RESKRIM HARIAN BOHO',4,2,15,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:04:10','2026-04-06 09:21:19',NULL,0),(108,'BINTARA_POLSEK_ONANRUNGGU','BINTARA POLSEK ONAN RUNGGU',4,0,18,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:06:09','2026-04-06 08:06:09',NULL,0),(109,'KAPOLSEK_NAINGGOLAN','KAPOLSEK NAINGGOLAN',4,0,18,NULL,NULL,NULL,0,0,0,NULL,1,'2026-04-06 08:07:00','2026-04-06 08:07:00',NULL,0),(110,'PS._KANIT_PATROLI_SAT_POLAIRUD','PS. KANIT PATROLI SAT POLAIRUD',NULL,0,12,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:38:41','2026-04-09 17:38:41',NULL,0),(111,'PS._KAURMINTU_SAT_POLAIRUD','PS. KAURMINTU SAT POLAIRUD',NULL,0,12,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:38:41','2026-04-09 17:38:41',NULL,0),(112,'BINTARA_SAT_POLAIRUD','BINTARA SAT POLAIRUD',NULL,0,12,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:38:41','2026-04-09 17:38:41',NULL,0),(113,'KAURBINOPS_SAT_BINMAS','KAURBINOPS SAT BINMAS',NULL,0,14,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:43:42','2026-04-09 17:43:42',NULL,0),(114,'PS._KA_SPKT_1_POLSEK_PALIPI','PS. KA SPKT 1 POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(115,'PS._KASIUM_POLSEK_PALIPI','PS. KASIUM POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(116,'PS._KA_SPKT_2_POLSEK_PALIPI','PS. KA SPKT 2 POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(117,'PS._KANIT_SAMAPTA_POLSEK_PALIPI','PS. KANIT SAMAPTA POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(118,'PS._KANIT_BINMAS_POLSEK_PALIPI','PS. KANIT BINMAS POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(119,'PS._KANIT_INTELKAM_POLSEK_PALIPI','PS. KANIT INTELKAM POLSEK PALIPI',NULL,0,16,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:50:03','2026-04-09 17:50:03',NULL,0),(120,'PS._KA_SPKT_1_POLSEK_SIMANINDO','PS. KA SPKT 1 POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(121,'PS._KA_SPKT_3_POLSEK_SIMANINDO','PS. KA SPKT 3 POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(122,'PS._KASIUM_POLSEK_SIMANINDO','PS. KASIUM POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(123,'PS._KANITPROPAM_POLSEK_SIMANINDO','PS. KANIT PROPAM POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 18:01:28',NULL,0),(124,'PS._KANIT_BINMAS_POLSEK_SIMANINDO','PS. KANIT BINMAS POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(125,'PS._KANIT_INTELKAM_POLSEK_SIMANINDO','PS. KANIT INTELKAM POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(126,'PS._KANIT_SAMAPTA_POLSEK_SIMANINDO','PS. KANIT SAMAPTA POLSEK SIMANINDO',NULL,0,17,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(127,'PS._KANIT_BINMAS_POLSEK_NAINGGOLAN','PS. KANIT BINMAS POLSEK NAINGGOLAN',NULL,0,18,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(128,'PS._KANIT_SAMAPTA_POLSEK_NAINGGOLAN','PS. KANIT SAMAPTA POLSEK NAINGGOLAN',NULL,0,18,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(129,'PS._KANIT_INTELKAM_POLSEK_NAINGGOLAN','PS. KANIT INTELKAM POLSEK NAINGGOLAN',NULL,0,18,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(130,'PS._KANIT_INTELKAM_POLSEK_PANGURURAN','PS. KANIT INTELKAM POLSEK PANGURURAN',NULL,0,19,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(131,'PS._KANIT_BINMAS_POLSEK_PANGURURAN','PS. KANIT BINMAS POLSEK PANGURURAN',NULL,0,19,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0),(132,'PS._KANIT_SAMAPTA_POLSEK_PANGURURAN','PS. KANIT SAMAPTA POLSEK PANGURURAN',NULL,0,19,'BINTARA',NULL,NULL,0,0,0,NULL,1,'2026-04-09 17:58:10','2026-04-09 17:58:10',NULL,0);
/*!40000 ALTER TABLE `jabatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lhpt`
--

DROP TABLE IF EXISTS `lhpt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lhpt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_lhpt` varchar(100) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `isi_laporan` text NOT NULL,
  `kendala` text DEFAULT NULL,
  `hasil` text DEFAULT NULL,
  `rekomendasi` text DEFAULT NULL,
  `pelapor` varchar(255) DEFAULT NULL,
  `jabatan_pelapor` varchar(255) DEFAULT NULL,
  `status_lhpt` enum('draft','submitted','approved') DEFAULT 'draft',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `operation_id` (`operation_id`),
  CONSTRAINT `lhpt_ibfk_1` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lhpt`
--

LOCK TABLES `lhpt` WRITE;
/*!40000 ALTER TABLE `lhpt` DISABLE KEYS */;
INSERT INTO `lhpt` VALUES (1,'LHPT / 1 / IV / 2026 / OPS',1,'2026-04-10','Pelaksanaan Operasi Bina Kesuma Toba berjalan sesuai rencana','Cuaca kurang mendukung','Situasi kamtibmas kondusif','Perlu penambahan personil','KABAG OPS','Kabagops Polres Samosir','submitted','system','2026-04-10 14:41:32','2026-04-10 14:41:32');
/*!40000 ALTER TABLE `lhpt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_jenis_pegawai`
--

DROP TABLE IF EXISTS `master_jenis_pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_jenis_pegawai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('POLRI','ASN','P3K','HONORARIUM','KONTRAK','LAINNYA') DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jenis` (`kode_jenis`),
  KEY `idx_kode` (`kode_jenis`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_jenis_pegawai`
--

LOCK TABLES `master_jenis_pegawai` WRITE;
/*!40000 ALTER TABLE `master_jenis_pegawai` DISABLE KEYS */;
INSERT INTO `master_jenis_pegawai` VALUES (1,'POLRI','POLRI Aktif','Anggota Polri Republik Indonesia yang aktif','POLRI',1,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(2,'POLRI_PENSIUN','POLRI Pensiun','Anggota POLRI yang sudah pensiun','POLRI',2,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(3,'POLRI_DIK','POLRI Dalam Pendidikan','Anggota POLRI yang sedang menjalani pendidikan','POLRI',3,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(4,'ASN','Aparatur Sipil Negara','Pegawai negeri sipil','ASN',10,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(5,'ASN_HONORARIUM','ASN Honorarium','ASN dengan status honorarium','ASN',11,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(6,'ASN_KONTRAK','ASN Kontrak','ASN dengan status kontrak','ASN',12,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(7,'P3K','Pegawai Pemerintah dengan Perjanjian Kerja','P3K sesuai PP No. 49 Tahun 2018','P3K',20,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(8,'P3K_TAHUNAN','P3K Tahunan','P3K dengan kontrak tahunan','P3K',21,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(9,'P3K_BULANAN','P3K Bulanan','P3K dengan kontrak bulanan','P3K',22,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(10,'HONORARIUM','Tenaga Honorarium','Tenaga ahli dengan status honorarium','HONORARIUM',30,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(11,'KONTRAK','Tenaga Kontrak','Tenaga dengan status kontrak','KONTRAK',31,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(12,'LAINNYA','Magang','Tenaga magang/internship','LAINNYA',40,1,'2026-03-28 18:56:11','2026-03-28 18:56:11');
/*!40000 ALTER TABLE `master_jenis_pegawai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_pendidikan`
--

DROP TABLE IF EXISTS `master_pendidikan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_pendidikan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tingkat_pendidikan` enum('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3','LAINNYA') DEFAULT NULL,
  `nama_pendidikan` varchar(100) NOT NULL,
  `kode_pendidikan` varchar(20) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pendidikan` (`kode_pendidikan`),
  KEY `idx_tingkat` (`tingkat_pendidikan`),
  KEY `idx_kode` (`kode_pendidikan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_pendidikan`
--

LOCK TABLES `master_pendidikan` WRITE;
/*!40000 ALTER TABLE `master_pendidikan` DISABLE KEYS */;
INSERT INTO `master_pendidikan` VALUES (1,'SD','Sekolah Dasar','SD',NULL,1,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(2,'SMP','Sekolah Menengah Pertama','SMP',NULL,2,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(3,'SMA','Sekolah Menengah Atas','SMA',NULL,3,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(4,'D1','Diploma Satu','D1',NULL,4,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(5,'D2','Diploma Dua','D2',NULL,5,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(6,'D3','Diploma Tiga','D3',NULL,6,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(7,'D4','Diploma Empat','D4',NULL,7,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(8,'S1','Strata Satu','S1',NULL,8,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(9,'S2','Strata Dua','S2',NULL,9,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(10,'S3','Strata Tiga','S3',NULL,10,1,'2026-03-28 18:56:11','2026-03-28 18:56:11'),(11,'LAINNYA','Lain-lain','LAINNYA',NULL,11,1,'2026-03-28 18:56:11','2026-03-28 18:56:11');
/*!40000 ALTER TABLE `master_pendidikan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_app_analytics`
--

DROP TABLE IF EXISTS `mobile_app_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_app_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_type` enum('login','logout','view_schedule','update_attendance','view_tasks','respond_task','view_notifications','mark_read') NOT NULL,
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_data`)),
  `app_version` varchar(50) DEFAULT '1.0.0',
  `platform` enum('android','ios','web') NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_event` (`user_id`,`event_type`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_app_analytics`
--

LOCK TABLES `mobile_app_analytics` WRITE;
/*!40000 ALTER TABLE `mobile_app_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_app_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_notification_logs`
--

DROP TABLE IF EXISTS `mobile_notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `platform` enum('android','ios') NOT NULL,
  `status` enum('sent','delivered','read','failed','bounced') DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notification_personil` (`notification_id`,`personil_id`),
  KEY `idx_device_token` (`device_token`),
  KEY `idx_status` (`status`),
  CONSTRAINT `mobile_notification_logs_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_notification_logs`
--

LOCK TABLES `mobile_notification_logs` WRITE;
/*!40000 ALTER TABLE `mobile_notification_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_sessions`
--

DROP TABLE IF EXISTS `mobile_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_token` varchar(255) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `app_version` varchar(50) DEFAULT '1.0.0',
  `platform` enum('android','ios','web') NOT NULL,
  `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `idx_session_token` (`session_token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_device_token` (`device_token`),
  KEY `idx_last_active` (`last_active`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_user_active` (`user_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_sessions`
--

LOCK TABLES `mobile_sessions` WRITE;
/*!40000 ALTER TABLE `mobile_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_settings`
--

DROP TABLE IF EXISTS `mobile_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `push_notifications_enabled` tinyint(1) DEFAULT 1,
  `notification_sound` tinyint(1) DEFAULT 1,
  `notification_vibration` tinyint(1) DEFAULT 1,
  `auto_sync_enabled` tinyint(1) DEFAULT 1,
  `sync_interval_minutes` int(11) DEFAULT 30,
  `theme` enum('light','dark','auto') DEFAULT 'auto',
  `language` varchar(10) DEFAULT 'id',
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
  `biometric_enabled` tinyint(1) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_settings`
--

LOCK TABLES `mobile_settings` WRITE;
/*!40000 ALTER TABLE `mobile_settings` DISABLE KEYS */;
INSERT INTO `mobile_settings` VALUES (1,1,1,1,1,1,30,'auto','id','Asia/Jakarta',0,'2026-04-11 15:06:49'),(2,2,1,1,1,1,30,'auto','id','Asia/Jakarta',0,'2026-04-11 15:06:49'),(3,3,1,1,1,1,30,'auto','id','Asia/Jakarta',0,'2026-04-11 15:06:49');
/*!40000 ALTER TABLE `mobile_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `monthly_certification_summary`
--

DROP TABLE IF EXISTS `monthly_certification_summary`;
/*!50001 DROP VIEW IF EXISTS `monthly_certification_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `monthly_certification_summary` AS SELECT
 1 AS `expiry_month`,
  1 AS `total_certifications`,
  1 AS `valid`,
  1 AS `expired`,
  1 AS `expiring_soon` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `notification_dashboard`
--

DROP TABLE IF EXISTS `notification_dashboard`;
/*!50001 DROP VIEW IF EXISTS `notification_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `notification_dashboard` AS SELECT
 1 AS `notification_date`,
  1 AS `notification_type`,
  1 AS `total_notifications`,
  1 AS `sent_notifications`,
  1 AS `delivered_notifications`,
  1 AS `read_notifications`,
  1 AS `failed_notifications` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `notification_delivery_analytics`
--

DROP TABLE IF EXISTS `notification_delivery_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_delivery_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `delivery_method` enum('in_app','push','email','sms') NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `delivery_time_ms` int(11) DEFAULT NULL COMMENT 'Delivery time in milliseconds',
  `device_type` enum('mobile','desktop','tablet') DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notification_personil` (`notification_id`,`personil_id`),
  KEY `idx_delivery_method` (`delivery_method`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `notification_delivery_analytics_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_delivery_analytics`
--

LOCK TABLES `notification_delivery_analytics` WRITE;
/*!40000 ALTER TABLE `notification_delivery_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_delivery_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_delivery_log`
--

DROP TABLE IF EXISTS `notification_delivery_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_delivery_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `delivery_method` enum('in_app','email','sms','push') NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `delivery_status` enum('pending','sent','delivered','failed','bounced') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_delivery_status` (`delivery_status`),
  KEY `idx_notification_method` (`notification_id`,`delivery_method`),
  KEY `idx_notification_status` (`notification_id`,`delivery_status`),
  KEY `idx_created_status` (`created_at`,`delivery_status`),
  CONSTRAINT `notification_delivery_log_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_delivery_log`
--

LOCK TABLES `notification_delivery_log` WRITE;
/*!40000 ALTER TABLE `notification_delivery_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_delivery_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `in_app_enabled` tinyint(1) DEFAULT 1,
  `push_enabled` tinyint(1) DEFAULT 1,
  `email_enabled` tinyint(1) DEFAULT 0,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `quiet_hours_start` time DEFAULT '22:00:00',
  `quiet_hours_end` time DEFAULT '06:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_type` (`user_id`,`notification_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_notification_type` (`notification_type`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_preferences`
--

LOCK TABLES `notification_preferences` WRITE;
/*!40000 ALTER TABLE `notification_preferences` DISABLE KEYS */;
INSERT INTO `notification_preferences` VALUES (1,1,'certification_expiry',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(2,2,'certification_expiry',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(3,3,'certification_expiry',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(4,1,'emergency_task',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(5,2,'emergency_task',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(6,3,'emergency_task',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(7,1,'equipment_due',1,0,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(8,2,'equipment_due',1,0,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(9,3,'equipment_due',1,0,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(10,1,'fatigue_warning',1,1,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(11,2,'fatigue_warning',1,1,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(12,3,'fatigue_warning',1,1,0,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(13,1,'overtime_approval',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(14,2,'overtime_approval',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(15,3,'overtime_approval',1,0,1,0,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(16,1,'recall_alert',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(17,2,'recall_alert',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58'),(18,3,'recall_alert',1,1,0,1,'22:00:00','06:00:00','2026-04-11 15:07:58','2026-04-11 15:07:58');
/*!40000 ALTER TABLE `notification_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_queue`
--

DROP TABLE IF EXISTS `notification_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`notification_data`)),
  `priority` enum('low','medium','high','critical') NOT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `next_attempt_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status_priority` (`status`,`priority`),
  KEY `idx_next_attempt` (`next_attempt_at`),
  KEY `idx_scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_queue`
--

LOCK TABLES `notification_queue` WRITE;
/*!40000 ALTER TABLE `notification_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_stats`
--

DROP TABLE IF EXISTS `notification_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_sent` int(11) DEFAULT 0,
  `total_delivered` int(11) DEFAULT 0,
  `total_read` int(11) DEFAULT 0,
  `total_failed` int(11) DEFAULT 0,
  `avg_delivery_time` decimal(5,2) DEFAULT 0.00 COMMENT 'Average delivery time in seconds',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_stats`
--

LOCK TABLES `notification_stats` WRITE;
/*!40000 ALTER TABLE `notification_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_templates`
--

DROP TABLE IF EXISTS `notification_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `title_template` text NOT NULL,
  `message_template` text NOT NULL,
  `default_priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `default_delivery_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["in_app"]' CHECK (json_valid(`default_delivery_methods`)),
  `action_required` tinyint(1) DEFAULT 0,
  `action_url_template` text DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Template variables description' CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_name` (`template_name`),
  KEY `idx_template_type` (`notification_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_templates`
--

LOCK TABLES `notification_templates` WRITE;
/*!40000 ALTER TABLE `notification_templates` DISABLE KEYS */;
INSERT INTO `notification_templates` VALUES (1,'fatigue_warning','fatigue_warning','Fatigue Warning','Your wellness score is {wellness_score} and fatigue level is {fatigue_level}. Please consider taking rest.','high','[\"in_app\",\"push\"]',1,NULL,'{\"wellness_score\": \"Current wellness score\", \"fatigue_level\": \"Current fatigue level\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57'),(2,'certification_expiry','certification_expiry','Certification Expiry Warning','Your certification \'{certification_name}\' expires in {days_to_expiry} days on {expiry_date}','medium','[\"in_app\",\"push\",\"email\"]',1,NULL,'{\"certification_name\": \"Name of certification\", \"days_to_expiry\": \"Days until expiry\", \"expiry_date\": \"Expiry date\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57'),(3,'emergency_task','emergency_task','Emergency Task Assignment','You have been assigned to emergency task: {task_name}. Priority: {priority_level}','high','[\"in_app\",\"push\",\"sms\"]',1,NULL,'{\"task_name\": \"Name of emergency task\", \"priority_level\": \"Task priority\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57'),(4,'recall_alert','recall_alert','Recall Campaign: {campaign_name}','{message}','high','[\"in_app\",\"push\",\"sms\"]',1,NULL,'{\"campaign_name\": \"Name of recall campaign\", \"message\": \"Recall message\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57'),(5,'equipment_due','equipment_due','Equipment Maintenance Due','Equipment \'{equipment_name}\' is due for maintenance in {days_to_maintenance} days','medium','[\"in_app\",\"push\"]',1,NULL,'{\"equipment_name\": \"Equipment name\", \"days_to_maintenance\": \"Days until maintenance\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57'),(6,'overtime_approval','overtime_approval','Overtime Approval Required','Overtime request for {hours} hours on {date} requires your approval','medium','[\"in_app\",\"push\",\"email\"]',1,NULL,'{\"hours\": \"Overtime hours\", \"date\": \"Overtime date\"}',1,'2026-04-11 15:07:57','2026-04-11 15:07:57');
/*!40000 ALTER TABLE `notification_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type` enum('fatigue_warning','certification_expiry','emergency_task','recall_alert','equipment_due','overtime_approval') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `target_personil` varchar(20) DEFAULT NULL COMMENT 'Specific personil target',
  `target_group` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Target groups (bagian, unsur, etc)' CHECK (json_valid(`target_group`)),
  `priority_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `delivery_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Delivery methods: in_app, email, sms, push' CHECK (json_valid(`delivery_methods`)),
  `status` enum('pending','sent','delivered','read','failed') DEFAULT 'pending',
  `scheduled_time` datetime DEFAULT NULL,
  `sent_time` timestamp NULL DEFAULT NULL,
  `read_time` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `action_required` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `action_deadline` datetime DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `mobile_sent` tinyint(1) DEFAULT 0,
  `mobile_read_time` timestamp NULL DEFAULT NULL,
  `push_notification_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_target_personil` (`target_personil`),
  KEY `idx_status_priority` (`status`,`priority_level`),
  KEY `idx_scheduled_time` (`scheduled_time`),
  KEY `idx_target_personil_status` (`target_personil`,`status`),
  KEY `idx_type_priority_created` (`notification_type`,`priority_level`,`created_at`),
  KEY `idx_status_created` (`status`,`created_at`),
  KEY `idx_created_at_type` (`created_at`,`notification_type`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`target_personil`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operations`
--

DROP TABLE IF EXISTS `operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_sprint` varchar(100) DEFAULT NULL,
  `operation_name` varchar(255) NOT NULL,
  `tingkat_operasi` enum('terpusat','kewilayahan_polda','kewilayahan_polres','imbangan') DEFAULT 'kewilayahan_polres' COMMENT 'Tingkat penyelenggara operasi',
  `jenis_operasi` enum('intelijen','pengamanan_kegiatan','pemeliharaan_keamanan','penegakan_hukum','pemulihan_keamanan','kontinjensi','lainnya') DEFAULT 'pemeliharaan_keamanan' COMMENT 'Bentuk/jenis operasi sesuai Perkap',
  `operation_month` varchar(7) DEFAULT NULL COMMENT 'Format YYYY-MM, bulan pelaksanaan sebelum tanggal pasti ditentukan',
  `operation_date` date DEFAULT NULL COMMENT 'Tanggal pasti, boleh kosong jika masih rencana',
  `operation_date_end` date DEFAULT NULL COMMENT 'Tanggal akhir operasi',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `required_personnel` int(11) DEFAULT 0,
  `kuat_personil` int(11) DEFAULT 0 COMMENT 'Kekuatan / jumlah personil',
  `dukgra` decimal(15,2) DEFAULT 0.00 COMMENT 'Dukungan anggaran (Rupiah)',
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `google_event_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recurrence_type` enum('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none',
  `recurrence_interval` int(11) NOT NULL DEFAULT 1,
  `recurrence_days` varchar(20) DEFAULT NULL,
  `recurrence_end` date DEFAULT NULL,
  `recurrence_parent_id` int(11) DEFAULT NULL,
  `required_equipment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'List of required equipment' CHECK (json_valid(`required_equipment`)),
  `equipment_assigned` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`operation_date`),
  KEY `idx_status` (`status`),
  KEY `idx_status_dates` (`status`,`operation_date`,`operation_date_end`),
  FULLTEXT KEY `operation_name` (`operation_name`,`location`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operations`
--

LOCK TABLES `operations` WRITE;
/*!40000 ALTER TABLE `operations` DISABLE KEYS */;
INSERT INTO `operations` VALUES (1,'Sprin / 1 / IV / 2026 / OPS','OPS BINA KESUMA TOBA','kewilayahan_polda','pemeliharaan_keamanan','2026-03',NULL,NULL,NULL,NULL,'POLRES SAMOSIR','OPERASI KEPOLISIAN KEWILAYAHAN, DALAM RANGKA PENCEGAHAN TERJADINYA GANGGUAN KAMTIBMAS TERKAIT KENAKALAN REMAJA, PELECEHAN SEKS TERHADAP ANAK, KEKERASAN TERHADAP PEREMPUAN DAN ANAK, SERTA MASALAH TKI',0,25,23750000.00,'planned',NULL,'2026-04-10 05:50:12','2026-04-10 14:37:35','none',1,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `overtime_records`
--

DROP TABLE IF EXISTS `overtime_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `overtime_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personil_id` varchar(20) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `overtime_date` date NOT NULL,
  `regular_hours` decimal(4,2) DEFAULT 8.00,
  `overtime_hours` decimal(4,2) NOT NULL,
  `overtime_rate` enum('regular','holiday','weekend','emergency') DEFAULT 'regular',
  `rate_multiplier` decimal(3,2) DEFAULT 1.50,
  `total_compensation` decimal(10,2) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `processed_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `schedule_id` (`schedule_id`),
  KEY `idx_personil_overtime` (`personil_id`,`overtime_date`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_overtime_date` (`overtime_date`),
  KEY `idx_date_status` (`overtime_date`,`approval_status`),
  CONSTRAINT `overtime_records_ibfk_1` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE,
  CONSTRAINT `overtime_records_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `overtime_records`
--

LOCK TABLES `overtime_records` WRITE;
/*!40000 ALTER TABLE `overtime_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `overtime_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pangkat`
--

DROP TABLE IF EXISTS `pangkat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pangkat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pangkat` varchar(100) NOT NULL,
  `singkatan` varchar(20) DEFAULT NULL,
  `level_pangkat` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pangkat` (`nama_pangkat`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pangkat`
--

LOCK TABLES `pangkat` WRITE;
/*!40000 ALTER TABLE `pangkat` DISABLE KEYS */;
INSERT INTO `pangkat` VALUES (15,'Jenderal Polisi','JENDRAL',1,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(16,'Komisaris Jenderal Polisi','KOMJEN',2,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(17,'Inspektur Jenderal Polisi','IRJEN',3,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(18,'Brigadir Jenderal Polisi','BRIGJEN',4,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(19,'Komisaris Besar Polisi','KOMBES',5,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(20,'Ajun Komisaris Besar Polisi','AKBP',6,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(21,'Komisaris Polisi','KOMPOL',7,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(22,'Ajun Komisaris Polisi','AKP',8,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(23,'Inspektur Polisi Satu','IPTU',9,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(24,'Inspektur Polisi Dua','IPDA',10,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(25,'Ajun Inspektur Polisi Satu','AIPTU',11,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(26,'Ajun Inspektur Polisi Dua','AIPDA',12,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(27,'Brigadir Polisi Kepala','BRIPKA',13,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(28,'Brigadir Polisi','BRIGPOL',14,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(29,'Brigadir Polisi Satu','BRIPTU',15,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(30,'Brigadir Polisi Dua','BRIPDA',16,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(31,'Ajun Brigadir Polisi','ABRIPOL',17,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(32,'Ajun Brigadir Polisi Satu','ABRIPTU',18,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(33,'Ajun Brigadir Polisi Dua','ABRIPDA',19,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(34,'Bhayangkara Kepala','BHARAKA',20,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(35,'Bhayangkara Satu','BHARATU',21,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(36,'Bhayangkara Dua','BHARADA',22,'2026-03-28 15:44:37','2026-03-28 15:51:58'),(37,'Pembina Utama','PEBINA',23,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(38,'Pembina Utama Madya','PEBINA MADYA',24,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(39,'Pembina Utama Muda','PEBINA MUDA',25,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(40,'Pembina Tingkat I','PEBINA TK I',26,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(41,'Pembina','PEBINA',27,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(42,'Penata Tingkat I','PENDA',28,'2026-03-28 15:44:37','2026-03-28 18:23:21'),(43,'Penata','PENATA',29,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(44,'Penata Muda Tingkat I','PENATA MUDA TK I',30,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(45,'Penata Muda','PENATA MUDA',31,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(46,'Pengatur Tingkat I','PENGATUR TK I',32,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(47,'Pengatur','PENGATUR',33,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(48,'Pengatur Muda Tingkat I','PENGATUR MUDA TK I',34,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(49,'Pengatur Muda','PENGATUR MUDA',35,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(50,'Juru Tingkat I','JURU TK I',36,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(51,'Juru','JURU',37,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(52,'Juru Muda Tingkat I','JURU MUDA TK I',38,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(53,'Juru Muda','JURU MUDA',39,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(54,'Honorer','HONORER',40,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(55,'Tenaga Harian Lepas','THL',41,'2026-03-28 15:44:37','2026-03-28 15:52:06'),(56,'Kontrak','KONTRAK',42,'2026-03-28 15:44:37','2026-03-28 15:52:06');
/*!40000 ALTER TABLE `pangkat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelatihan`
--

DROP TABLE IF EXISTS `pelatihan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pelatihan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pelatihan` varchar(255) NOT NULL,
  `jenis` enum('menembak','bela_diri','sar','ketahanan','teknis','lainnya') NOT NULL DEFAULT 'lainnya',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `jam_latihan` decimal(5,1) DEFAULT 0.0,
  `lokasi` varchar(255) DEFAULT NULL,
  `instruktur` varchar(255) DEFAULT NULL,
  `peserta_target` int(11) DEFAULT 0,
  `peserta_hadir` int(11) DEFAULT 0,
  `bagian_id` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('rencana','berlangsung','selesai','batal') DEFAULT 'rencana',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelatihan`
--

LOCK TABLES `pelatihan` WRITE;
/*!40000 ALTER TABLE `pelatihan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pelatihan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil`
--

DROP TABLE IF EXISTS `personil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `JK` enum('L','P') DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `wellness_score` int(11) DEFAULT 100 COMMENT 'Skor kesehatan 0-100',
  `max_weekly_hours` decimal(5,2) DEFAULT 40.00 COMMENT 'Maksimal jam kerja per minggu',
  `fatigue_level` enum('low','medium','high','critical') DEFAULT 'low',
  `last_fatigue_check` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nrp` (`nrp`),
  KEY `idx_wellness_score` (`wellness_score`),
  FULLTEXT KEY `nama` (`nama`,`nrp`)
) ENGINE=InnoDB AUTO_INCREMENT=342 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil`
--

LOCK TABLES `personil` WRITE;
/*!40000 ALTER TABLE `personil` DISABLE KEYS */;
INSERT INTO `personil` VALUES (1,'84031648',NULL,'RINA SRY NIRWANA TARIGAN, S.I.K., M.H.',NULL,20,1,1,1,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(2,'83081648',NULL,'BRISTON AGUS MUNTECARLO, S.T., S.I.K.',NULL,21,2,1,1,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(3,'68100259',NULL,'EDUAR, S.H.',NULL,21,3,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(4,'82080038',NULL,'PATRI SIHALOHO',NULL,26,4,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(5,'02120141',NULL,'AGUNG NUGRAHA NADAP-DAP',NULL,30,5,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:27:54',100,40.00,'low',NULL),(6,'03010386',NULL,'ALDI PRANATA GINTING',NULL,30,5,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(7,'02040489',NULL,'HENDRIKSON SILALAHI',NULL,30,5,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(8,'02071119',NULL,'TOHONAN SITOHANG',NULL,30,5,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(9,'03101364',NULL,'GILANG SUTOYO',NULL,30,5,2,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(10,'198112262024211002',NULL,'FERNANDO SILALAHI, A.Md.',NULL,NULL,6,2,2,'P3K/ BKO POLDA',NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(11,'76030248',NULL,'HENDRI SIAGIAN, S.H.',NULL,24,7,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(12,'87070134',NULL,'DENI MUSTIKA SUKMANA, S.E.',NULL,24,8,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(13,'85081770',NULL,'JAMIL MUNTHE, S.H., M.H.',NULL,24,9,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(14,'87030020',NULL,'BULET MARS SWANTO LBN. BATU, S.H.',NULL,24,10,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(15,'96010872',NULL,'RAMADHAN PUTRA, S.H.',NULL,29,11,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(16,'98090415',NULL,'ABEDNEGO TARIGAN',NULL,29,12,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(17,'00010166',NULL,'EDY SUSANTO PARDEDE',NULL,29,13,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(18,'98010470',NULL,'BOBBY ANGGARA PUTRA SIREGAR',NULL,30,13,20,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(19,'01070820',NULL,'GABRIEL PAULIMA NADEAK',NULL,30,13,20,5,'OP CALL CENTRE',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(20,'02091526',NULL,'ANDRE OWEN PURBA',NULL,30,11,20,5,'OP CALL CENTRE',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(21,'04070159',NULL,'EDWARD FERDINAND SIDABUTAR',NULL,30,11,20,5,'OP CALL CENTRE',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(22,'03060873',NULL,'BIMA SANTO HUTAGAOL',NULL,30,12,20,5,'OP CALL CENTRE',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(23,'03121291',NULL,'KRISTIAN M. H. NABABAN',NULL,30,12,20,5,'OP CALL CENTRE',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(24,'72100484',NULL,'SURUNG SAGALA',NULL,24,14,3,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(25,'96090857',NULL,'ZAKHARIA S. I. SIMANJUNTAK, S.H.',NULL,29,15,3,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(26,'03080202',NULL,'GRENIEL WIARTO SIHITE',NULL,30,15,3,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(27,'73010107',NULL,'TARMIZI LUBIS, S.H.',NULL,22,16,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(28,'198111252014122004',NULL,'REYMESTA AMBARITA, S.Kom.',NULL,42,17,4,2,'',NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:30:49',100,40.00,'low',NULL),(29,'97090248',NULL,'LAMTIO SINAGA, S.H.',NULL,28,18,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(30,'97120490',NULL,'DODI KURNIADI',NULL,29,18,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:54',100,40.00,'low',NULL),(31,'05070285',NULL,'EFRANTA SAPUTRA SITEPU',NULL,30,18,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(32,'86070985',NULL,'RADOS. S. TOGATOROP,S.H.',NULL,26,19,4,2,'DIK SIP',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(33,'00080579',NULL,'REYSON YOHANNES SIMBOLON',NULL,30,20,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(34,'02090891',NULL,'ANDRE TARUNA SIMBOLON',NULL,30,21,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(35,'03081525',NULL,'YOLANDA NAULIVIA ARITONANG',NULL,30,20,4,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(36,'95080918',NULL,'SYAUQI LUTFI LUBIS, S.H., M.H.',NULL,28,19,29,6,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(37,'97050575',NULL,'DANIEL BRANDO SIDABUKKE',NULL,28,19,29,6,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(38,'98010119',NULL,'SUTRISNO BUTAR-BUTAR, S.H.',NULL,29,19,29,6,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(39,'76040221',NULL,'AWALUDDIN',NULL,24,22,5,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 18:07:55',100,40.00,'low',NULL),(40,'97050588',NULL,'EFRON SARWEDY SINAGA, S.H.',NULL,29,23,5,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(41,'00010095',NULL,'PRIADI MAROJAHAN HUTABARAT',NULL,29,23,5,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(42,'03070263',NULL,'CHRIST JERICHO SAPUTRA TAMPUBOLON',NULL,30,23,5,2,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(43,'86100287',NULL,'EFRI PANDI',NULL,26,24,21,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 18:03:38',100,40.00,'low',NULL),(44,'04010804',NULL,'YOGI ADE PRATAMA SITOHANG',NULL,30,25,21,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(45,'93100676',NULL,'PENGEJAPEN, S.H.',NULL,28,26,22,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(46,'97050876',NULL,'MUHARRAM SYAHRI, S.H.',NULL,29,27,22,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(47,'97100685',NULL,'M.FATHUR RAHMAN, S.H.',NULL,29,27,22,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(48,'03070010',NULL,'HESKIEL WANDANA MELIALA',NULL,30,27,22,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(49,'03040138',NULL,'DANIEL RICARDO SARAGIH',NULL,30,27,22,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(50,'197008291993032002',NULL,'NENENG GUSNIARTI',NULL,43,28,23,5,'',NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(51,'84040532',NULL,'EDDY SURANTA SARAGIH',NULL,27,29,23,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(52,'75060617',NULL,'BILMAR SITUMORANG',NULL,25,30,24,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(53,'94080815',NULL,'YOHANES EDI SUPRIATNO, S.H., M.H.',NULL,28,31,24,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(54,'94080892',NULL,'AGUSTIAWAN SINAGA',NULL,28,31,24,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(55,'93060444',NULL,'LISTER BROUN SITORUS',NULL,28,32,25,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(56,'00070791',NULL,'ANDREAS D. S. SITANGGANG',NULL,30,32,25,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(57,'01101139',NULL,'JACKSON SIDABUTAR',NULL,30,32,25,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(58,'73050261',NULL,'PARIMPUNAN SIREGAR',NULL,24,33,26,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(59,'95030599',NULL,'DANIEL E. LUMBANTORUAN, S.H.',NULL,28,34,26,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(60,'76120670',NULL,'DENNI BOYKE H. SIREGAR, S.H.',NULL,24,35,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(61,'81010202',NULL,'BENNI ARDINAL, S.H., M.H.',NULL,26,36,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(62,'85081088',NULL,'AGUSTINUS SINAGA',NULL,26,37,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(63,'86081359',NULL,'RAMBO CISLER NADEAK',NULL,27,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(64,'95030796',NULL,'PERY RAPEN YONES PARDOSI, S.H.',NULL,28,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(65,'97070014',NULL,'DWI HETRIANDY, S.H.',NULL,28,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(66,'97120554',NULL,'TRY WIBOWO',NULL,29,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(67,'00080343',NULL,'SIMON TIGRIS SIAGIAN',NULL,29,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(68,'01080575',NULL,'FIRIAN JOSUA SITORUS',NULL,30,38,27,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(69,'93030551',NULL,'GUNAWAN SITUMORANG',NULL,28,39,28,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(70,'98091488',NULL,'DANIEL BAHTERA SINAGA',NULL,29,39,28,5,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(71,'75120560',NULL,'HORAS LARIUS SITUMORANG',NULL,24,113,14,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:47:38',100,40.00,'low',NULL),(72,'95090650',NULL,'JEFTA OCTAVIANUS NICO SIANTURI',NULL,28,41,14,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(73,'94091146',NULL,'SAHAT MARULI TUA SINAGA, S.H.',NULL,28,41,14,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(74,'04020118',NULL,'RONAL PARTOGI SITUMORANG',NULL,30,41,14,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(75,'82070670',NULL,'DONAL P. SITANGGANG, S.H., M.H.',NULL,23,42,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(76,'85050489',NULL,'MUHAMMAD YUNUS LUBIS, S.H.',NULL,24,40,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(77,'80070348',NULL,'MARBETA S. SIANIPAR, S.H.',NULL,26,43,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(78,'87080112',NULL,'SITARDA AKABRI SIBUEA',NULL,26,44,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(79,'87051430',NULL,'CINTER ROKHY SINAGA',NULL,27,45,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(80,'90080088',NULL,'VANDU P. MARPAUNG',NULL,27,46,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(81,'93080556',NULL,'ALFONSIUS GULTOM, S.H.',NULL,28,47,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(82,'97040848',NULL,'TRIFIKO P. NAINGGOLAN, S.H.',NULL,29,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(83,'98110618',NULL,'ANDRI AFRIJAL SIMARMATA',NULL,29,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(84,'02030032',NULL,'DIEN VAROSCY I. SITUMORANG',NULL,30,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(85,'02120339',NULL,'ARDY TRIANO MALAU',NULL,30,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(86,'02040459',NULL,'JUNEDI SAGALA',NULL,30,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(87,'02101010',NULL,'GABRIEL SEBASTIAN SIREGAR',NULL,30,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(88,'04020209',NULL,'RIO F. T ERENST PANJAITAN',NULL,30,48,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(89,'04080118',NULL,'AGHEO HARMANA JOUSTRA SINURAYA',NULL,30,47,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(90,'04010932',NULL,'SAMUEL RINALDI PAKPAHAN',NULL,30,47,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(91,'04040520',NULL,'RAYMONTIUS HAROMUNTE',NULL,30,47,6,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(92,'79120994',NULL,'EDWARD SIDAURUK, S.E., M.M.',NULL,22,49,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(93,'76020196',NULL,'DARMONO SAMOSIR, S.H.',NULL,24,50,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(94,'83010825',NULL,'ROYANTO PURBA, S.H.',NULL,24,51,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(95,'83120602',NULL,'SUHADIYANTO, S.H.',NULL,24,52,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(96,'88060535',NULL,'KUICAN SIMANJUNTAK',NULL,27,53,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(97,'79030434',NULL,'MARTIN HABENSONY ARITONANG',NULL,25,54,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(98,'83060084',NULL,'HENRY SIPAKKAR',NULL,25,55,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(99,'87011165',NULL,'CHANDRA HUTAPEA',NULL,27,43,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(100,'89030401',NULL,'CHANDRA BARIMBING',NULL,27,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(101,'87041596',NULL,'DEDY SAOLOAN SIGALINGGING',NULL,27,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(102,'82050798',NULL,'ISWAN LUKITO',NULL,27,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(103,'95030238',NULL,'RONI HANSVERI BANJARNAHOR',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(104,'94020506',NULL,'RODEN SUANDI TURNIP',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(105,'94121145',NULL,'SAPUTRA, S.H.',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(106,'95100554',NULL,'DIAN LESTARI GULTOM, S.H.',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(107,'95110886',NULL,'ARGIO SIMBOLON',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(108,'97070616',NULL,'EKO DAHANA PARDEDE, S.H.',NULL,28,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(109,'97040728',NULL,'GIDEON AFRIADI LUMBAN RAJA',NULL,29,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(110,'98090397',NULL,'FACHRUL REZA SILALAHI',NULL,29,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(111,'00030346',NULL,'RIDHOTUA F. SITANGGANG',NULL,29,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(112,'00110362',NULL,'NICHO FERNANDO SARAGIH',NULL,29,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(113,'00090499',NULL,'ADI P.S. MARBUN',NULL,29,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(114,'01120358',NULL,'PRIYATAMA ABDILLAH HARAHAP',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(115,'01070839',NULL,'RIZKI AFRIZAL SIMANJUNTAK',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(116,'01060553',NULL,'MIDUK YUDIANTO SINAGA',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(117,'02110342',NULL,'FRAN\'S ALEXANDER SIANIPAR',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(118,'01110817',NULL,'RAFFLES SIJABAT',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(119,'01091201',NULL,'HERIANTA TARIGAN',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(120,'03030809',NULL,'RICKY AGATHA GINTING',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(121,'03020368',NULL,'CHRISTIAN PROSPEROUS SIMANUNGKALIT',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(122,'04020196',NULL,'PINIEL RAJAGUKGUK',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(123,'03090568',NULL,'REZA SIREGAR',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(124,'04031206',NULL,'RAYMOND VAN HEZEKIEL SIAHAAN',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(125,'05080602',NULL,'M. ALAMSYAH PRAYOGA TAMBUNAN',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(126,'04090567',NULL,'IRVAN SYAPUTRA MALAU',NULL,30,56,7,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(127,'79060034',NULL,'FERRY ARIANDY, S.H., M.H',NULL,22,57,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(128,'88100591',NULL,'ALVIUS KRISTIAN GINTING, S.H.',NULL,24,40,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(129,'89010155',NULL,'BENNY SITUMORANG, S.H.',NULL,27,58,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(130,'93050797',NULL,'EKO PUTRA DAMANIK, S.H.',NULL,28,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(131,'91050361',NULL,'MAY FRANSISCO SIAGIAN, S.H.',NULL,28,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(132,'94090839',NULL,'ROBERTO MANALU',NULL,29,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(133,'98110378',NULL,'M. RONALD FAHROZI HARAHAP, S.H.',NULL,29,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(134,'97020694',NULL,'HERIANTO EFENDI, S.H.',NULL,29,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(135,'02120224',NULL,'TEDDI PARNASIPAN TOGATOROP',NULL,30,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(136,'02090838',NULL,'ONDIHON SIMBOLON',NULL,30,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(137,'05080131',NULL,'IVAN SIGOP SIHOMBING',NULL,30,59,8,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(138,'80080676',NULL,'NANDI BUTAR-BUTAR, S.H.',NULL,22,60,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(139,'80050867',NULL,'BARTO ANTONIUS SIMALANGO',NULL,25,61,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(140,'73040390',NULL,'HASUDUNGAN SILITONGA',NULL,26,62,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(141,'85090954',NULL,'JHONNY LEONARDO SILALAHI',NULL,27,63,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(142,'83081051',NULL,'ASRIL',NULL,27,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(143,'94110350',NULL,'INDIRWAN FRIDERICK, S.H.',NULL,28,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(144,'93100793',NULL,'EGIDIUM BRAUN SILITONGA',NULL,28,64,10,3,'A',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(145,'97100701',NULL,'DINAMIKA JAYA NEGARA SITANGGANG',NULL,30,64,10,3,'B',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(146,'05051087',NULL,'WIRA HARZITA',NULL,30,64,10,3,'C',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(147,'06100189',NULL,'RAHMAT ANDRIAN TAMBUNAN',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(148,'07080045',NULL,'JONATAN DWI SAPUTRA PARAPAT',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(149,'04051595',NULL,'PERDANA NIKOLA SEMBIRING',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(150,'04081205',NULL,'PETRUS SURIA HUGALUNG',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(151,'06010414',NULL,'RAFAEL ARSANLILO SINULINGGA',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(152,'06090021',NULL,'RAJASPER SIRINGORINGO',NULL,30,64,10,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(153,'79120800',NULL,'NATANAIL SURBAKTI, S.H',NULL,22,70,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(154,'75080942',NULL,'JUSUP KETAREN',NULL,24,71,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(155,'80070492',NULL,'ARON PERANGIN-ANGIN',NULL,25,72,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(156,'79060704',NULL,'HERON GINTING',NULL,27,73,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(157,'86030733',NULL,'JEFRI KHADAFI SIREGAR, S.H.',NULL,27,74,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(158,'89070031',NULL,'HERIANTO TURNIP',NULL,27,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(159,'87030647',NULL,'DION MAR\'YANSEN SILITONGA',NULL,28,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(160,'93020749',NULL,'ROY GRIMSLAY, S.H.',NULL,28,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(161,'93090673',NULL,'BAGUS DWI PRAKOSO, S.H.',NULL,28,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(162,'97040353',NULL,'ICASANDRI MONANZA BR GINTING',NULL,28,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(163,'95021078',NULL,'DIKI FEBRIAN SITORUS',NULL,29,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(164,'96031061',NULL,'MARCHLANDA SITOHANG',NULL,29,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(165,'01080438',NULL,'JULIVER SIDABUTAR',NULL,29,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(166,'01120281',NULL,'FATHURROZI TINDAON',NULL,30,75,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(167,'02111012',NULL,'BENY BOY CHRISTIAN SIAHAAN',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(168,'02111051',NULL,'RADOT NOVALDO PANDAPOTAN PURBA',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(169,'05030251',NULL,'MUHAMMAD ZIDHAN RIFALDI',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(170,'04050615',NULL,'DANI INDRA PERMANA SINAGA',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(171,'05010048',NULL,'HEZKIEL CAPRI SITINDAON',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(172,'04030824',NULL,'BONARIS TSUYOKO DITASANI SINAGA',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(173,'05010014',NULL,'ARY ANJAS SARAGIH',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:00',100,40.00,'low',NULL),(174,'04030805',NULL,'GABRIEL VERY JUNIOR SITOHANG',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(175,'02121477',NULL,'FIRMAN BAHTERA',NULL,30,21,9,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(176,'82051018',NULL,'SAUT H. SIAHAAN',NULL,26,79,13,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(177,'98050496',NULL,'FERNANDO SIMBOLON',NULL,29,80,13,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(178,'98030531',NULL,'KURNIA PERMANA',NULL,29,80,13,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(179,'05090232',NULL,'STEVEN IMANUEL SITUMEANG',NULL,30,80,13,3,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(180,'70050412',NULL,'MAXON NAINGGOLAN',NULL,22,87,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:57',100,40.00,'low',NULL),(181,'78040213',NULL,'H. SWANDI SINAGA',NULL,25,114,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:57',100,40.00,'low',NULL),(182,'77030463',NULL,'HARATUA GULTOM',NULL,25,115,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(183,'85071450',NULL,'TEGUH SYAHPUTRA',NULL,27,116,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(184,'85041500',NULL,'RUDYANTO LUMBANRAJA',NULL,27,98,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(185,'96031075',NULL,'ZULPAN SYAHPUTRA DAMANIK',NULL,29,98,16,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(186,'83061022',NULL,'RAMADAN SIREGAR, S.H.',NULL,23,92,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(187,'75120864',NULL,'GUNTAR TAMBUNAN',NULL,25,120,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:58:25',100,40.00,'low',NULL),(188,'83080042',NULL,'YOPPHY RHODEAR MUNTHE',NULL,26,121,17,4,'A',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:58:25',100,40.00,'low',NULL),(189,'84110202',NULL,'DONI SURIANTO PURBA, S.H.',NULL,27,122,17,4,'C',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:58:25',100,40.00,'low',NULL),(190,'94090490',NULL,'KURNIAWAN, S.H.',NULL,28,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(191,'95060432',NULL,'ASHARI BUTAR-BUTAR, S.H.',NULL,28,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(192,'96061331',NULL,'DIDI HOT BAGAS SITORUS',NULL,30,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(193,'01060884',NULL,'HORAS J.M. ARITONANG',NULL,30,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(194,'04060050',NULL,'ANDRE YEHEZKIEL HUTABARAT',NULL,30,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(195,'89080105',NULL,'CLAUDIUS HARIS PARDEDE',NULL,28,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(196,'02051553',NULL,'ZULKIFLI NASUTION',NULL,30,100,17,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(197,'70010290',NULL,'RADIAMAN SIMARMATA',NULL,22,109,18,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(198,'83031377',NULL,'LUHUT SIRINGO-RINGO',NULL,28,101,18,4,'C',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(199,'03100001',NULL,'ANRIAN SIGALINGGING',NULL,30,101,18,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(200,'99110755',NULL,'BONATUA LUMBANTUNGKUP',NULL,30,101,18,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:54:58',100,40.00,'low',NULL),(201,'03050116',NULL,'ANDRE SUGIARTO MARPAUNG',NULL,30,101,18,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(202,'04030125',NULL,'ERWIN KEVIN GULTOM',NULL,30,101,18,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(203,'70020298',NULL,'BANGUN TUA DALIMUNTHE',NULL,22,97,19,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(204,'79020443',NULL,'HERBINTUPA SITANGGANG',NULL,28,99,19,4,'C',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(205,'85121751',NULL,'IBRAHIM TARIGAN',NULL,28,99,19,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(206,'98090406',NULL,'AGUNG NUGRAHA HARIANJA, S.H.',NULL,29,99,19,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(207,'98091274',NULL,'DANI PUTRA RUMAHORBO',NULL,29,99,19,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:18:55',100,40.00,'low',NULL),(208,'01060198',NULL,'KRISMAN JULU GULTOM',NULL,30,99,19,4,'',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'system','system','2026-04-06 18:24:10','2026-04-09 17:28:01',100,40.00,'low',NULL),(225,'81110363',NULL,'LEONARDO SINAGA',NULL,26,19,30,6,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 18:09:13',100,40.00,'low',NULL),(273,'72100604',NULL,'TANGIO HAOJAHAN SITANGGANG, S.H.',NULL,23,65,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(274,'80100836',NULL,'MARUBA NAINGGOLAN',NULL,25,66,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(275,'85030645',NULL,'ROY HARIS ST. SIMAREMARE',NULL,26,67,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(276,'80050898',NULL,'M. DENY WAHYU',NULL,26,68,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(277,'83050202',NULL,'HENRI F. SIANIPAR',NULL,25,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(278,'85121325',NULL,'BUYUNG ANDRYANTO',NULL,27,43,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(279,'91110130',NULL,'RIANTO SITANGGANG',NULL,28,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(280,'94090948',NULL,'ROY NANDA SEMBIRING KEMBAREN',NULL,28,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(281,'96031057',NULL,'CANDRA SILALAHI, S.H.',NULL,28,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(282,'02100599',NULL,'YUNUS SAMDIO SIDABUTAR',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(283,'03010565',NULL,'RAINHEART SITANGGANG',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(284,'02011312',NULL,'BONIFASIUS NAINGGOLAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(285,'00080816',NULL,'RAY YONDO SIAHAAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(286,'03040947',NULL,'REDY EZRA JONATHAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(287,'04100485',NULL,'CHARLY H. ARITONANG',NULL,30,69,11,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(299,'68120522',NULL,'SULAIMAN PANGARIBUAN, S.H',NULL,22,76,12,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(300,'83080822',NULL,'EFENDI M.  SIREGAR',NULL,26,110,12,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:38:47',100,40.00,'low',NULL),(301,'73120275',NULL,'ROMEL LINDUNG SIAHAAN',NULL,26,111,12,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:38:47',100,40.00,'low',NULL),(302,'90060273',NULL,'FRANS HOTMAN MANURUNG, S.H.',NULL,27,112,12,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:38:47',100,40.00,'low',NULL),(303,'77070919',NULL,'ANTONIUS SIPAYUNG',NULL,28,112,12,3,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:38:47',100,40.00,'low',NULL),(305,'69090552',NULL,'RAHMAT KURNIAWAN',NULL,23,81,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(306,'79090296',NULL,'MARUKKIL J.M. PASARIBU',NULL,25,82,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(307,'82070930',NULL,'LANTRO LANDELINUS SAGALA',NULL,26,83,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(308,'87120701',NULL,'ANDY DEDY SIHOMBING, S.H.',NULL,27,107,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(309,'86021428',NULL,'RANGGA HATTA',NULL,27,85,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(310,'80120573',NULL,'ARDIANSYAH BUTAR-BUTAR',NULL,27,102,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(311,'96120123',NULL,'ADRYANTO SINAGA, S.H.',NULL,28,102,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(312,'94040538',NULL,'BROLIN ADFRIALDI HALOHO',NULL,28,102,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(313,'95110806',NULL,'SUGIANTO ERIK SIBORO',NULL,28,102,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(314,'01020739',NULL,'RISKO SIMBOLON',NULL,30,102,15,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(315,'76120606',NULL,'ASA MELKI HUTABARAT',NULL,26,117,16,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:54:58',100,40.00,'low',NULL),(316,'78100741',NULL,'JARIAHMAN SARAGIH',NULL,26,118,16,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:54:58',100,40.00,'low',NULL),(317,'87041134',NULL,'MUHAMMAD SYAFEI RAMADHAN',NULL,26,103,16,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:54:58',100,40.00,'low',NULL),(318,'86121371',NULL,'RIJALUL FIKRI SINAGA',NULL,27,119,16,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:54:58',100,40.00,'low',NULL),(319,'86071792',NULL,'WIDODO KABAN, S.H.',NULL,24,126,17,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(320,'82040124',NULL,'JEFRI RICARDO SAMOSIR',NULL,25,123,17,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:58:25',100,40.00,'low',NULL),(321,'84020306',NULL,'JUITO SUPANOTO PERANGIN-ANGIN',NULL,26,123,17,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 18:00:01',100,40.00,'low',NULL),(322,'86010311',NULL,'TUMBUR SITOHANG',NULL,26,124,17,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(323,'89020409',NULL,'PATAR F. ANRI SIAHAAN',NULL,27,125,17,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(327,'82050839',NULL,'HERMAWADI',NULL,26,106,18,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(328,'84091124',NULL,'BISSAR LUMBANTUNGKUP',NULL,26,129,18,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(329,'70090340',NULL,'BONAR JUBEL SIBARANI',NULL,27,127,18,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(330,'77020642',NULL,'RAMLES SITANGGANG',NULL,27,128,18,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:59:12',100,40.00,'low',NULL),(334,'81050713',NULL,'LANCASTER ARIANTO CANDY PASARIBU, S.H.',NULL,25,104,19,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:18:55',100,40.00,'low',NULL),(335,'80090905',NULL,'RUDY SETYAWAN',NULL,25,130,19,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:58:25',100,40.00,'low',NULL),(336,'80080892',NULL,'MANGATUR TUA TINDAON',NULL,26,131,19,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:58:25',100,40.00,'low',NULL),(337,'87110154',NULL,'RENO HOTMARULI TUA MANIK, S.H.',NULL,27,132,19,4,'aktif',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-07 18:15:26','2026-04-09 17:58:25',100,40.00,'low',NULL),(339,'123456',NULL,'Test User',NULL,1,1,1,1,'aktif',NULL,NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'2026-04-11 14:15:34','2026-04-11 14:15:34',100,40.00,'low',NULL);
/*!40000 ALTER TABLE `personil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_backup`
--

DROP TABLE IF EXISTS `personil_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nrp` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alasan_status` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_backup`
--

LOCK TABLES `personil_backup` WRITE;
/*!40000 ALTER TABLE `personil_backup` DISABLE KEYS */;
INSERT INTO `personil_backup` VALUES (256,'84031648','RINA SRY NIRWANA TARIGAN, S.I.K., M.H.',NULL,20,1,1,1,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(257,'83081648','BRISTON AGUS MUNTECARLO, S.T., S.I.K.',NULL,21,2,1,1,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(258,'68100259','EDUAR, S.H.',NULL,21,3,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(259,'82080038','PATRI SIHALOHO',NULL,26,4,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(260,'02120141','AGUNG NUGRAHA NADAP-DAP',NULL,30,5,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(261,'03010386','ALDI PRANATA GINTING',NULL,30,5,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(262,'02040489','HENDRIKSON SILALAHI',NULL,30,5,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(263,'02071119','TOHONAN SITOHANG',NULL,30,5,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(264,'03101364','GILANG SUTOYO',NULL,30,5,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(265,'76030248','HENDRI SIAGIAN, S.H.',NULL,24,7,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(266,'87070134','DENI MUSTIKA SUKMANA, S.E.',NULL,24,8,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(267,'85081770','JAMIL MUNTHE, S.H., M.H.',NULL,24,9,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(268,'87030020','BULET MARS SWANTO LBN. BATU, S.H.',NULL,24,10,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(269,'96010872','RAMADHAN PUTRA, S.H.',NULL,29,11,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(270,'98090415','ABEDNEGO TARIGAN',NULL,29,12,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(271,'00010166','EDY SUSANTO PARDEDE',NULL,29,13,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(272,'98010470','BOBBY ANGGARA PUTRA SIREGAR',NULL,30,13,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(273,'01070820','GABRIEL PAULIMA NADEAK',NULL,30,13,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(274,'02091526','ANDRE OWEN PURBA',NULL,30,11,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(275,'04070159','EDWARD FERDINAND SIDABUTAR',NULL,30,11,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(276,'03060873','BIMA SANTO HUTAGAOL',NULL,30,12,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(277,'03121291','KRISTIAN M. H. NABABAN',NULL,30,12,20,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(278,'72100484','SURUNG SAGALA',NULL,24,14,3,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(279,'96090857','ZAKHARIA S. I. SIMANJUNTAK, S.H.',NULL,29,15,3,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(280,'03080202','GRENIEL WIARTO SIHITE',NULL,30,15,3,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(281,'73010107','TARMIZI LUBIS, S.H.',NULL,22,16,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(282,'`198111252014122004','REYMESTA AMBARITA, S.Kom.',NULL,42,17,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(283,'97090248','LAMTIO SINAGA, S.H.',NULL,28,18,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(284,'97120490','DODI KURNIADI',NULL,29,18,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(285,'05070285','EFRANTA SAPUTRA SITEPU',NULL,30,18,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(286,'86070985','RADOS. S. TOGATOROP,S.H.',NULL,26,19,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(287,'00080579','REYSON YOHANNES SIMBOLON',NULL,30,20,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(288,'02090891','ANDRE TARUNA SIMBOLON',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(289,'03081525','YOLANDA NAULIVIA ARITONANG',NULL,30,20,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(290,'95080918','SYAUQI LUTFI LUBIS, S.H., M.H.',NULL,28,19,29,6,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(291,'97050575','DANIEL BRANDO SIDABUKKE',NULL,28,19,29,6,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(292,'98010119','SUTRISNO BUTAR-BUTAR, S.H.',NULL,29,19,29,6,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(293,'81110363','LEONARDO SINAGA',NULL,26,19,4,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(294,'76040221','AWALUDDIN',NULL,24,22,5,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(295,'97050588','EFRON SARWEDY SINAGA, S.H.',NULL,29,23,5,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(296,'00010095','PRIADI MAROJAHAN HUTABARAT',NULL,29,23,5,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(297,'03070263','CHRIST JERICHO SAPUTRA TAMPUBOLON',NULL,30,23,5,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(298,'86100287','EFRI PANDI',NULL,26,24,21,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(299,'04010804','YOGI ADE PRATAMA SITOHANG',NULL,30,25,21,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(300,'93100676','PENGEJAPEN, S.H.',NULL,28,26,22,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(301,'97050876','MUHARRAM SYAHRI, S.H.',NULL,29,27,22,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(302,'97100685','M.FATHUR RAHMAN, S.H.',NULL,29,27,22,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(303,'03070010','HESKIEL WANDANA MELIALA',NULL,30,27,22,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(304,'03040138','DANIEL RICARDO SARAGIH',NULL,30,27,22,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(305,'197008291993032002','NENENG GUSNIARTI',NULL,43,28,23,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(306,'84040532','EDDY SURANTA SARAGIH',NULL,27,29,23,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(307,'75060617','BILMAR SITUMORANG',NULL,25,30,24,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(308,'94080815','YOHANES EDI SUPRIATNO, S.H., M.H.',NULL,28,31,24,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(309,'94080892','AGUSTIAWAN SINAGA',NULL,28,31,24,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(310,'93060444','LISTER BROUN SITORUS',NULL,28,32,25,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(311,'00070791','ANDREAS D. S. SITANGGANG',NULL,30,32,25,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(312,'01101139','JACKSON SIDABUTAR',NULL,30,32,25,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(313,'73050261','PARIMPUNAN SIREGAR',NULL,24,33,26,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(314,'95030599','DANIEL E. LUMBANTORUAN, S.H.',NULL,28,34,26,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(315,'76120670','DENNI BOYKE H. SIREGAR, S.H.',NULL,24,35,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(316,'81010202','BENNI ARDINAL, S.H., M.H.',NULL,26,36,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(317,'85081088','AGUSTINUS SINAGA',NULL,26,37,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(318,'86081359','RAMBO CISLER NADEAK',NULL,27,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(319,'95030796','PERY RAPEN YONES PARDOSI, S.H.',NULL,28,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(320,'97070014','DWI HETRIANDY, S.H.',NULL,28,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(321,'97120554','TRY WIBOWO',NULL,29,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(322,'00080343','SIMON TIGRIS SIAGIAN',NULL,29,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(323,'01080575','FIRIAN JOSUA SITORUS',NULL,30,38,27,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(324,'93030551','GUNAWAN SITUMORANG',NULL,28,39,28,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(325,'98091488','DANIEL BAHTERA SINAGA',NULL,29,39,28,5,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(326,'75120560','HORAS LARIUS SITUMORANG',NULL,24,40,14,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(327,'95090650','JEFTA OCTAVIANUS NICO SIANTURI',NULL,28,41,14,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(328,'94091146','SAHAT MARULI TUA SINAGA, S.H.',NULL,28,41,14,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(329,'04020118','RONAL PARTOGI SITUMORANG',NULL,30,41,14,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(330,'82070670','DONAL P. SITANGGANG, S.H., M.H.',NULL,23,42,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(331,'85050489','MUHAMMAD YUNUS LUBIS, S.H.',NULL,24,40,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(332,'80070348','MARBETA S. SIANIPAR, S.H.',NULL,26,43,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(333,'87080112','SITARDA AKABRI SIBUEA',NULL,26,44,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(334,'87051430','CINTER ROKHY SINAGA',NULL,27,45,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(335,'90080088','VANDU P. MARPAUNG',NULL,27,46,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(336,'93080556','ALFONSIUS GULTOM, S.H.',NULL,28,47,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(337,'97040848','TRIFIKO P. NAINGGOLAN, S.H.',NULL,29,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(338,'98110618','ANDRI AFRIJAL SIMARMATA',NULL,29,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(339,'02030032','DIEN VAROSCY I. SITUMORANG',NULL,30,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(340,'02120339','ARDY TRIANO MALAU',NULL,30,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(341,'02040459','JUNEDI SAGALA',NULL,30,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(342,'02101010','GABRIEL SEBASTIAN SIREGAR',NULL,30,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(343,'04020209','RIO F. T ERENST PANJAITAN',NULL,30,48,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(344,'04080118','AGHEO HARMANA JOUSTRA SINURAYA',NULL,30,47,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(345,'04010932','SAMUEL RINALDI PAKPAHAN',NULL,30,47,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(346,'04040520','RAYMONTIUS HAROMUNTE',NULL,30,47,6,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(347,'79120994','EDWARD SIDAURUK, S.E., M.M.',NULL,22,49,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(348,'76020196','DARMONO SAMOSIR, S.H.',NULL,24,50,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(349,'83010825','ROYANTO PURBA, S.H.',NULL,24,51,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(350,'83120602','SUHADIYANTO, S.H.',NULL,24,52,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(351,'88060535','KUICAN SIMANJUNTAK',NULL,27,53,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(352,'79030434','MARTIN HABENSONY ARITONANG',NULL,25,54,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(353,'83060084','HENRY SIPAKKAR',NULL,25,55,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(354,'87011165','CHANDRA HUTAPEA',NULL,27,43,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(355,'89030401','CHANDRA BARIMBING',NULL,27,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(356,'87041596','DEDY SAOLOAN SIGALINGGING',NULL,27,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(357,'82050798','ISWAN LUKITO',NULL,27,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(358,'95030238','RONI HANSVERI BANJARNAHOR',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(359,'94020506','RODEN SUANDI TURNIP',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(360,'94121145','SAPUTRA, S.H.',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(361,'95100554','DIAN LESTARI GULTOM, S.H.',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(362,'95110886','ARGIO SIMBOLON',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(363,'97070616','EKO DAHANA PARDEDE, S.H.',NULL,28,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(364,'97040728','GIDEON AFRIADI LUMBAN RAJA',NULL,29,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(365,'98090397','FACHRUL REZA SILALAHI',NULL,29,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(366,'00030346','RIDHOTUA F. SITANGGANG',NULL,29,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(367,'00110362','NICHO FERNANDO SARAGIH',NULL,29,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(368,'00090499','ADI P.S. MARBUN',NULL,29,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(369,'01120358','PRIYATAMA ABDILLAH HARAHAP',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(370,'01070839','RIZKI AFRIZAL SIMANJUNTAK',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(371,'01060553','MIDUK YUDIANTO SINAGA',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(372,'02110342','FRAN\'S ALEXANDER SIANIPAR',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(373,'01110817','RAFFLES SIJABAT',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(374,'01091201','HERIANTA TARIGAN',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(375,'03030809','RICKY AGATHA GINTING',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(376,'03020368','CHRISTIAN PROSPEROUS SIMANUNGKALIT',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(377,'04020196','PINIEL RAJAGUKGUK',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(378,'03090568','REZA SIREGAR',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(379,'04031206','RAYMOND VAN HEZEKIEL SIAHAAN',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(380,'05080602','M. ALAMSYAH PRAYOGA TAMBUNAN',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(381,'04090567','IRVAN SYAPUTRA MALAU',NULL,30,56,7,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(382,'79060034','FERRY ARIANDY, S.H., M.H',NULL,22,57,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(383,'88100591','ALVIUS KRISTIAN GINTING, S.H.',NULL,24,40,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(384,'89010155','BENNY SITUMORANG, S.H.',NULL,27,58,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(385,'93050797','EKO PUTRA DAMANIK, S.H.',NULL,28,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(386,'91050361','MAY FRANSISCO SIAGIAN, S.H.',NULL,28,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(387,'94090839','ROBERTO MANALU',NULL,29,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(388,'98110378','M. RONALD FAHROZI HARAHAP, S.H.',NULL,29,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(389,'97020694','HERIANTO EFENDI, S.H.',NULL,29,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(390,'02120224','TEDDI PARNASIPAN TOGATOROP',NULL,30,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(391,'02090838','ONDIHON SIMBOLON',NULL,30,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(392,'05080131','IVAN SIGOP SIHOMBING',NULL,30,59,8,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(393,'80080676','NANDI BUTAR-BUTAR, S.H.',NULL,22,60,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(394,'80050867','BARTO ANTONIUS SIMALANGO',NULL,25,61,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(395,'73040390','HASUDUNGAN SILITONGA',NULL,26,62,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(396,'85090954','JHONNY LEONARDO SILALAHI',NULL,27,63,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(397,'83081051','ASRIL',NULL,27,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(398,'94110350','INDIRWAN FRIDERICK, S.H.',NULL,28,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(399,'93100793','EGIDIUM BRAUN SILITONGA',NULL,28,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(400,'97100701','DINAMIKA JAYA NEGARA SITANGGANG',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(401,'05051087','WIRA HARZITA',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(402,'06100189','RAHMAT ANDRIAN TAMBUNAN',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(403,'07080045','JONATAN DWI SAPUTRA PARAPAT',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(404,'04051595','PERDANA NIKOLA SEMBIRING',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(405,'04081205','PETRUS SURIA HUGALUNG',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(406,'06010414','RAFAEL ARSANLILO SINULINGGA',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(407,'06090021','RAJASPER SIRINGORINGO',NULL,30,64,10,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(408,'72100604','TANGIO HAOJAHAN SITANGGANG, S.H.',NULL,23,65,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(409,'80100836','MARUBA NAINGGOLAN',NULL,25,66,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(410,'85030645','ROY HARIS ST. SIMAREMARE',NULL,26,67,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(411,'80050898','M. DENY WAHYU',NULL,26,68,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(412,'83050202','HENRI F. SIANIPAR',NULL,25,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(413,'85121325','BUYUNG ANDRYANTO',NULL,27,43,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(414,'91110130','RIANTO SITANGGANG',NULL,28,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(415,'94090948','ROY NANDA SEMBIRING KEMBAREN',NULL,28,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(416,'96031057','CANDRA SILALAHI, S.H.',NULL,28,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(417,'02100599','YUNUS SAMDIO SIDABUTAR',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(418,'03010565','RAINHEART SITANGGANG',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(419,'02011312','BONIFASIUS NAINGGOLAN',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(420,'00080816','RAY YONDO SIAHAAN',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(421,'03040947','REDY EZRA JONATHAN',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(422,'04100485','CHARLY H. ARITONANG',NULL,30,69,11,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(423,'79120800','NATANAIL SURBAKTI, S.H',NULL,22,70,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(424,'75080942','JUSUP KETAREN',NULL,24,71,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(425,'80070492','ARON PERANGIN-ANGIN',NULL,25,72,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(426,'79060704','HERON GINTING',NULL,27,73,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(427,'86030733','JEFRI KHADAFI SIREGAR, S.H.',NULL,27,74,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(428,'89070031','HERIANTO TURNIP',NULL,27,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(429,'87030647','DION MAR\'YANSEN SILITONGA',NULL,28,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(430,'93020749','ROY GRIMSLAY, S.H.',NULL,28,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(431,'93090673','BAGUS DWI PRAKOSO, S.H.',NULL,28,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(432,'97040353','ICASANDRI MONANZA BR GINTING',NULL,28,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(433,'95021078','DIKI FEBRIAN SITORUS',NULL,29,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(434,'96031061','MARCHLANDA SITOHANG',NULL,29,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(435,'01080438','JULIVER SIDABUTAR',NULL,29,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(436,'01120281','FATHURROZI TINDAON',NULL,30,75,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(437,'02111012','BENY BOY CHRISTIAN SIAHAAN',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(438,'02111051','RADOT NOVALDO PANDAPOTAN PURBA',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(439,'05030251','MUHAMMAD ZIDHAN RIFALDI',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(440,'04050615','DANI INDRA PERMANA SINAGA',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(441,'05010048','HEZKIEL CAPRI SITINDAON',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(442,'04030824','BONARIS TSUYOKO DITASANI SINAGA',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(443,'05010014','ARY ANJAS SARAGIH',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(444,'04030805','GABRIEL VERY JUNIOR SITOHANG',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(445,'02121477','FIRMAN BAHTERA',NULL,30,21,9,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(446,'68120522','SULAIMAN PANGARIBUAN, S.H',NULL,22,76,12,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(447,'83080822','EFENDI M.  SIREGAR',NULL,26,77,12,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(448,'73120275','ROMEL LINDUNG SIAHAAN',NULL,26,43,12,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(449,'90060273','FRANS HOTMAN MANURUNG, S.H.',NULL,27,78,12,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(450,'77070919','ANTONIUS SIPAYUNG',NULL,28,78,12,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(451,'82051018','SAUT H. SIAHAAN',NULL,26,79,13,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(452,'98050496','FERNANDO SIMBOLON',NULL,29,80,13,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(453,'98030531','KURNIA PERMANA',NULL,29,80,13,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(454,'05090232','STEVEN IMANUEL SITUMEANG',NULL,30,80,13,3,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(455,'69090552','RAHMAT KURNIAWAN',NULL,23,81,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(456,'79090296','MARUKKIL J.M. PASARIBU',NULL,25,82,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(457,'82070930','LANTRO LANDELINUS SAGALA',NULL,26,83,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(458,'87120701','ANDY DEDY SIHOMBING, S.H.',NULL,27,84,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(459,'86021428','RANGGA HATTA',NULL,27,85,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(460,'80120573','ARDIANSYAH BUTAR-BUTAR',NULL,27,86,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(461,'96120123','ADRYANTO SINAGA, S.H.',NULL,28,86,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(462,'94040538','BROLIN ADFRIALDI HALOHO',NULL,28,86,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(463,'95110806','SUGIANTO ERIK SIBORO',NULL,28,86,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(464,'01020739','RISKO SIMBOLON',NULL,30,86,15,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(465,'70050412','MAXON NAINGGOLAN',NULL,22,87,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(466,'78040213','H. SWANDI SINAGA',NULL,25,88,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(467,'77030463','HARATUA GULTOM',NULL,25,24,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(468,'76120606','ASA MELKI HUTABARAT',NULL,26,89,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(469,'78100741','JARIAHMAN SARAGIH',NULL,26,83,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(470,'87041134','MUHAMMAD SYAFEI RAMADHAN',NULL,26,84,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(471,'86121371','RIJALUL FIKRI SINAGA',NULL,27,82,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(472,'85071450','TEGUH SYAHPUTRA',NULL,27,90,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(473,'85041500','RUDYANTO LUMBANRAJA',NULL,27,91,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(474,'96031075','ZULPAN SYAHPUTRA DAMANIK',NULL,29,91,16,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(475,'83061022','RAMADAN SIREGAR, S.H.',NULL,23,92,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(476,'86071792','WIDODO KABAN, S.H.',NULL,24,93,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(477,'75120864','GUNTAR TAMBUNAN',NULL,25,88,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(478,'82040124','JEFRI RICARDO SAMOSIR',NULL,25,94,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(479,'84020306','JUITO SUPANOTO PERANGIN-ANGIN',NULL,26,83,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(480,'83080042','YOPPHY RHODEAR MUNTHE',NULL,26,95,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(481,'86010311','TUMBUR SITOHANG',NULL,26,82,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(482,'84110202','DONI SURIANTO PURBA, S.H.',NULL,27,24,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(483,'89020409','PATAR F. ANRI SIAHAAN',NULL,27,89,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(484,'94090490','KURNIAWAN, S.H.',NULL,28,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(485,'95060432','ASHARI BUTAR-BUTAR, S.H.',NULL,28,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(486,'96061331','DIDI HOT BAGAS SITORUS',NULL,30,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(487,'01060884','HORAS J.M. ARITONANG',NULL,30,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(488,'04060050','ANDRE YEHEZKIEL HUTABARAT',NULL,30,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(489,'89080105','CLAUDIUS HARIS PARDEDE',NULL,28,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(490,'02051553','ZULKIFLI NASUTION',NULL,30,86,17,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(491,'70010290','RADIAMAN SIMARMATA',NULL,22,96,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(492,'82050839','HERMAWADI',NULL,26,84,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(493,'84091124','BISSAR LUMBANTUNGKUP',NULL,26,83,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(494,'70090340','BONAR JUBEL SIBARANI',NULL,27,89,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(495,'77020642','RAMLES SITANGGANG',NULL,27,82,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(496,'83031377','LUHUT SIRINGO-RINGO',NULL,28,86,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(497,'03100001','ANRIAN SIGALINGGING',NULL,30,86,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(498,'99110755','BONATUA LUMBANTUNGKUP',NULL,30,86,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(499,'03050116','ANDRE SUGIARTO MARPAUNG',NULL,30,86,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(500,'04030125','ERWIN KEVIN GULTOM',NULL,30,86,18,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(501,'70020298','BANGUN TUA DALIMUNTHE',NULL,22,97,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(502,'81050713','LANCASTER ARIANTO CANDY PASARIBU, S.H.',NULL,25,84,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(503,'80090905','RUDY SETYAWAN',NULL,25,82,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(504,'80080892','MANGATUR TUA TINDAON',NULL,26,83,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(505,'87110154','RENO HOTMARULI TUA MANIK, S.H.',NULL,27,89,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(506,'79020443','HERBINTUPA SITANGGANG',NULL,28,86,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(507,'85121751','IBRAHIM TARIGAN',NULL,28,86,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(508,'98090406','AGUNG NUGRAHA HARIANJA, S.H.',NULL,29,86,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(509,'98091274','DANI PUTRA RUMAHORBO',NULL,29,86,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(510,'01060198','KRISMAN JULU GULTOM',NULL,30,86,19,4,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 18:25:53',NULL),(511,'198112262024211002','FERNANDO SILALAHI, A.Md.',NULL,NULL,6,2,2,'aktif',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:26:34','2026-03-28 18:26:34',NULL);
/*!40000 ALTER TABLE `personil_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_backup_20260407_014326`
--

DROP TABLE IF EXISTS `personil_backup_20260407_014326`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_backup_20260407_014326` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nrp` (`nrp`),
  KEY `idx_nrp` (`nrp`),
  KEY `idx_nama` (`nama`),
  KEY `idx_pangkat` (`id_pangkat`),
  KEY `idx_jabatan` (`id_jabatan`),
  KEY `idx_bagian` (`id_bagian`),
  KEY `idx_unsur` (`id_unsur`),
  KEY `idx_status` (`status_ket`),
  KEY `idx_active` (`is_active`),
  KEY `idx_deleted` (`is_deleted`),
  KEY `fk_personil_jenis_pegawai` (`id_jenis_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_backup_20260407_014326`
--

LOCK TABLES `personil_backup_20260407_014326` WRITE;
/*!40000 ALTER TABLE `personil_backup_20260407_014326` DISABLE KEYS */;
INSERT INTO `personil_backup_20260407_014326` VALUES (256,'84031648',NULL,'RINA SRY NIRWANA TARIGAN, S.I.K., M.H.','S.I.K., M.H.',20,1,1,1,'aktif',NULL,1,NULL,'1984-03-01','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(257,'83081648',NULL,'BRISTON AGUS MUNTECARLO, S.T., S.I.K.','S.T., S.I.K.',21,2,1,1,'aktif',NULL,1,NULL,'1983-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(258,'68100259',NULL,'EDUAR, S.H.','S.H.',21,3,2,2,'aktif',NULL,1,NULL,'1968-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(259,'82080038',NULL,'PATRI SIHALOHO, S.H.','S.H.',26,4,2,2,'aktif',NULL,1,NULL,'1982-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:41'),(260,'02120141',NULL,'AGUNG NUGRAHA NADAP-DAP',NULL,30,5,2,2,'aktif',NULL,1,NULL,'2002-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:18'),(261,'03010386',NULL,'ALDI PRANATA GINTING',NULL,30,5,2,2,'aktif',NULL,1,NULL,'2003-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(262,'02040489',NULL,'HENDRIKSON SILALAHI',NULL,30,5,2,2,'aktif',NULL,1,NULL,'2002-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(263,'02071119',NULL,'TOHONAN SITOHANG',NULL,30,5,2,2,'aktif',NULL,1,NULL,'2002-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(264,'03101364',NULL,'GILANG SUTOYO',NULL,30,5,2,2,'aktif',NULL,1,NULL,'2003-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(265,'76030248',NULL,'HENDRI SIAGIAN, S.H.','S.H.',24,7,20,5,'aktif',NULL,1,NULL,'1976-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(266,'87070134',NULL,'DENI MUSTIKA SUKMANA, S.E.','S.E.',24,8,20,5,'aktif',NULL,1,NULL,'1987-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:41'),(267,'85081770',NULL,'JAMIL MUNTHE, S.H., M.H.','S.H.',24,9,20,5,'aktif',NULL,1,NULL,'1985-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(268,'87030020',NULL,'BULET MARS SWANTO LBN. BATU, S.H.','S.H.',24,10,20,5,'aktif',NULL,1,NULL,'1987-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:41'),(269,'96010872',NULL,'RAMADHAN PUTRA, S.H.','S.H.',29,11,20,5,'aktif',NULL,1,NULL,'1996-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(270,'98090415',NULL,'ABEDNEGO TARIGAN',NULL,29,12,20,5,'aktif',NULL,1,NULL,'1998-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(271,'00010166',NULL,'EDY SUSANTO PARDEDE',NULL,29,13,20,5,'aktif',NULL,1,NULL,'2000-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:41'),(272,'98010470',NULL,'BOBBY ANGGARA PUTRA SIREGAR',NULL,30,13,20,5,'aktif',NULL,1,NULL,'1998-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(273,'01070820',NULL,'GABRIEL PAULIMA NADEAK',NULL,30,13,20,5,'aktif',NULL,1,NULL,'2001-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(274,'02091526',NULL,'ANDRE OWEN PURBA',NULL,30,11,20,5,'aktif',NULL,1,NULL,'2002-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(275,'04070159',NULL,'EDWARD FERDINAND SIDABUTAR',NULL,30,11,20,5,'aktif',NULL,1,NULL,'2004-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(276,'03060873',NULL,'BIMA SANTO HUTAGAOL',NULL,30,12,20,5,'aktif',NULL,1,NULL,'2003-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(277,'03121291',NULL,'KRISTIAN M. H. NABABAN',NULL,30,12,20,5,'aktif',NULL,1,NULL,'2003-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(278,'72100484',NULL,'SURUNG SAGALA',NULL,24,14,3,2,'aktif',NULL,1,NULL,'1972-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(279,'96090857',NULL,'ZAKHARIA S. I. SIMANJUNTAK, S.H.','S.H.',29,15,3,2,'aktif',NULL,1,NULL,'1996-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(280,'03080202',NULL,'GRENIEL WIARTO SIHITE',NULL,30,15,3,2,'aktif',NULL,1,NULL,'2003-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(281,'73010107',NULL,'TARMIZI LUBIS, S.H.','S.H.',22,16,4,2,'aktif',NULL,1,NULL,'1973-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(282,'198111252014122004',NULL,'REYMESTA AMBARITA, S.Kom.','S.Kom.',42,17,4,2,'aktif',NULL,4,NULL,'1981-11-25','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 22:01:30'),(283,'97090248',NULL,'LAMTIO SINAGA, S.H.','S.H.',28,18,4,2,'aktif',NULL,1,NULL,'1997-09-01','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:46:22'),(284,'97120490',NULL,'DODI KURNIADI',NULL,29,18,4,2,'aktif',NULL,1,NULL,'1997-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(285,'05070285',NULL,'EFRANTA SAPUTRA SITEPU',NULL,30,18,4,2,'aktif',NULL,1,NULL,'2005-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(286,'86070985',NULL,'RADOS. S. TOGATOROP,S.H.',NULL,26,19,4,2,'nonaktif','Pendidikan SIP',3,NULL,'1986-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-30 04:11:27'),(287,'00080579',NULL,'REYSON YOHANNES SIMBOLON',NULL,30,20,4,2,'aktif',NULL,1,NULL,'2000-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(288,'02090891',NULL,'ANDRE TARUNA SIMBOLON',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2002-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(289,'03081525',NULL,'YOLANDA NAULIVIA ARITONANG',NULL,30,20,4,2,'aktif',NULL,1,NULL,'2003-08-01','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(290,'95080918',NULL,'SYAUQI LUTFI LUBIS, S.H., M.H.','S.H.',28,19,29,6,'aktif',NULL,1,NULL,'1995-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(291,'97050575',NULL,'DANIEL BRANDO SIDABUKKE',NULL,28,19,29,6,'aktif',NULL,1,NULL,'1997-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(292,'98010119',NULL,'SUTRISNO BUTAR-BUTAR, S.H.','S.H.',29,19,29,6,'aktif',NULL,1,NULL,'1998-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(293,'81110363',NULL,'LEONARDO SINAGA',NULL,26,19,4,2,'nonaktif','Belum menghadap',1,NULL,'1981-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-30 04:10:59'),(294,'76040221',NULL,'AWALUDDIN',NULL,24,22,5,2,'aktif',NULL,1,NULL,'1976-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(295,'97050588',NULL,'EFRON SARWEDY SINAGA, S.H.','S.H.',29,23,5,2,'aktif',NULL,1,NULL,'1997-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(296,'00010095',NULL,'PRIADI MAROJAHAN HUTABARAT',NULL,29,23,5,2,'aktif',NULL,1,NULL,'2000-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(297,'03070263',NULL,'CHRIST JERICHO SAPUTRA TAMPUBOLON',NULL,30,23,5,2,'aktif',NULL,1,NULL,'2003-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(298,'86100287',NULL,'EFRI PANDI',NULL,26,24,21,5,'aktif',NULL,1,NULL,'1986-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(299,'04010804',NULL,'YOGI ADE PRATAMA SITOHANG',NULL,30,25,21,5,'aktif',NULL,1,NULL,'2004-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(300,'93100676',NULL,'PENGEJAPEN, S.H.','S.H.',28,26,22,5,'aktif',NULL,1,NULL,'1993-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(301,'97050876',NULL,'MUHARRAM SYAHRI, S.H.','S.H.',29,27,22,5,'aktif',NULL,1,NULL,'1997-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(302,'97100685',NULL,'M.FATHUR RAHMAN, S.H.','S.H.',29,27,22,5,'aktif',NULL,1,NULL,'1997-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(303,'03070010',NULL,'HESKIEL WANDANA MELIALA',NULL,30,27,22,5,'aktif',NULL,1,NULL,'2003-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(304,'03040138',NULL,'DANIEL RICARDO SARAGIH',NULL,30,27,22,5,'aktif',NULL,1,NULL,'2003-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(305,'197008291993032002',NULL,'NENENG GUSNIARTI',NULL,43,28,23,5,'aktif',NULL,4,NULL,'1970-08-29','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:20:08'),(306,'84040532',NULL,'EDDY SURANTA SARAGIH',NULL,27,29,23,5,'nonaktif','Sakit Menahun',1,NULL,'1984-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-30 04:14:22'),(307,'75060617',NULL,'BILMAR SITUMORANG',NULL,25,30,24,5,'aktif',NULL,1,NULL,'1975-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(308,'94080815',NULL,'YOHANES EDI SUPRIATNO, S.H., M.H.','S.H.',28,31,24,5,'aktif',NULL,1,NULL,'1994-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:10'),(309,'94080892',NULL,'AGUSTIAWAN SINAGA',NULL,28,31,24,5,'aktif',NULL,1,NULL,'1994-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(310,'93060444',NULL,'LISTER BROUN SITORUS',NULL,28,32,25,5,'aktif',NULL,1,NULL,'1993-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(311,'00070791',NULL,'ANDREAS D. S. SITANGGANG',NULL,30,32,25,5,'aktif',NULL,1,NULL,'2000-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(312,'01101139',NULL,'JACKSON SIDABUTAR',NULL,30,32,25,5,'aktif',NULL,1,NULL,'2001-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(313,'73050261',NULL,'PARIMPUNAN SIREGAR',NULL,24,33,26,5,'aktif',NULL,1,NULL,'1973-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(314,'95030599',NULL,'DANIEL E. LUMBANTORUAN, S.H.','S.H.',28,34,26,5,'aktif',NULL,1,NULL,'1995-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(315,'76120670',NULL,'DENNI BOYKE H. SIREGAR, S.H.','S.H.',24,35,27,5,'aktif',NULL,1,NULL,'1976-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(316,'81010202',NULL,'BENNI ARDINAL, S.H., M.H.','S.H.',26,36,27,5,'aktif',NULL,1,NULL,'1981-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(317,'85081088',NULL,'AGUSTINUS SINAGA',NULL,26,37,27,5,'aktif',NULL,1,NULL,'1985-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(318,'86081359',NULL,'RAMBO CISLER NADEAK',NULL,27,38,27,5,'aktif',NULL,1,NULL,'1986-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(319,'95030796',NULL,'PERY RAPEN YONES PARDOSI, S.H.','S.H.',28,38,27,5,'aktif',NULL,1,NULL,'1995-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(320,'97070014',NULL,'DWI HETRIANDY, S.H.','S.H.',28,38,27,5,'aktif',NULL,1,NULL,'1997-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(321,'97120554',NULL,'TRY WIBOWO',NULL,29,38,27,5,'aktif',NULL,1,NULL,'1997-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(322,'00080343',NULL,'SIMON TIGRIS SIAGIAN',NULL,29,38,27,5,'aktif',NULL,1,NULL,'2000-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(323,'01080575',NULL,'FIRIAN JOSUA SITORUS',NULL,30,38,27,5,'aktif',NULL,1,NULL,'2001-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(324,'93030551',NULL,'GUNAWAN SITUMORANG',NULL,28,39,28,5,'aktif',NULL,1,NULL,'1993-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(325,'98091488',NULL,'DANIEL BAHTERA SINAGA',NULL,29,39,28,5,'aktif',NULL,1,NULL,'1998-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(326,'75120560',NULL,'HORAS LARIUS SITUMORANG',NULL,24,40,14,3,'aktif',NULL,1,NULL,'1975-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(327,'95090650',NULL,'JEFTA OCTAVIANUS NICO SIANTURI',NULL,28,41,14,3,'aktif',NULL,1,NULL,'1995-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(328,'94091146',NULL,'SAHAT MARULI TUA SINAGA, S.H.','S.H.',28,41,14,3,'aktif',NULL,1,NULL,'1994-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(329,'04020118',NULL,'RONAL PARTOGI SITUMORANG',NULL,30,41,14,3,'aktif',NULL,1,NULL,'2004-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(330,'82070670',NULL,'DONAL P. SITANGGANG, S.H., M.H.','S.H.',23,42,6,3,'aktif',NULL,1,NULL,'1982-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(331,'85050489',NULL,'MUHAMMAD YUNUS LUBIS, S.H.','S.H.',24,40,6,3,'aktif',NULL,1,NULL,'1985-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(332,'80070348',NULL,'MARBETA S. SIANIPAR, S.H.','S.H.',26,43,6,3,'aktif',NULL,1,NULL,'1980-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(333,'87080112',NULL,'SITARDA AKABRI SIBUEA',NULL,26,44,6,3,'aktif',NULL,1,NULL,'1987-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(334,'87051430',NULL,'CINTER ROKHY SINAGA',NULL,27,45,6,3,'aktif',NULL,1,NULL,'1987-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(335,'90080088',NULL,'VANDU P. MARPAUNG',NULL,27,46,6,3,'aktif',NULL,1,NULL,'1990-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(336,'93080556',NULL,'ALFONSIUS GULTOM, S.H.','S.H.',28,47,6,3,'aktif',NULL,1,NULL,'1993-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(337,'97040848',NULL,'TRIFIKO P. NAINGGOLAN, S.H.','S.H.',29,48,6,3,'aktif',NULL,1,NULL,'1997-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(338,'98110618',NULL,'ANDRI AFRIJAL SIMARMATA',NULL,29,48,6,3,'aktif',NULL,1,NULL,'1998-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(339,'02030032',NULL,'DIEN VAROSCY I. SITUMORANG',NULL,30,48,6,3,'aktif',NULL,1,NULL,'2002-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(340,'02120339',NULL,'ARDY TRIANO MALAU',NULL,30,48,6,3,'aktif',NULL,1,NULL,'2002-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(341,'02040459',NULL,'JUNEDI SAGALA',NULL,30,48,6,3,'aktif',NULL,1,NULL,'2002-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(342,'02101010',NULL,'GABRIEL SEBASTIAN SIREGAR',NULL,30,48,6,3,'aktif',NULL,1,NULL,'2002-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(343,'04020209',NULL,'RIO F. T ERENST PANJAITAN',NULL,30,48,6,3,'aktif',NULL,1,NULL,'2004-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(344,'04080118',NULL,'AGHEO HARMANA JOUSTRA SINURAYA',NULL,30,47,6,3,'aktif',NULL,1,NULL,'2004-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(345,'04010932',NULL,'SAMUEL RINALDI PAKPAHAN',NULL,30,47,6,3,'aktif',NULL,1,NULL,'2004-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(346,'04040520',NULL,'RAYMONTIUS HAROMUNTE',NULL,30,47,6,3,'aktif',NULL,1,NULL,'2004-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(347,'79120994',NULL,'EDWARD SIDAURUK, S.E., M.M.','S.E.',22,49,7,3,'aktif',NULL,1,NULL,'1979-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(348,'76020196',NULL,'DARMONO SAMOSIR, S.H.','S.H.',24,50,7,3,'aktif',NULL,1,NULL,'1976-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(349,'83010825',NULL,'ROYANTO PURBA, S.H.','S.H.',24,51,7,3,'aktif',NULL,1,NULL,'1983-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(350,'83120602',NULL,'SUHADIYANTO, S.H.','S.H.',24,52,7,3,'aktif',NULL,1,NULL,'1983-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(351,'88060535',NULL,'KUICAN SIMANJUNTAK',NULL,27,53,7,3,'aktif',NULL,1,NULL,'1988-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(352,'79030434',NULL,'MARTIN HABENSONY ARITONANG',NULL,25,54,7,3,'aktif',NULL,1,NULL,'1979-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(353,'83060084',NULL,'HENRY SIPAKKAR',NULL,25,55,7,3,'aktif',NULL,1,NULL,'1983-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(354,'87011165',NULL,'CHANDRA HUTAPEA',NULL,27,43,7,3,'aktif',NULL,1,NULL,'1987-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(355,'89030401',NULL,'CHANDRA BARIMBING',NULL,27,56,7,3,'aktif',NULL,1,NULL,'1989-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(356,'87041596',NULL,'DEDY SAOLOAN SIGALINGGING',NULL,27,56,7,3,'aktif',NULL,1,NULL,'1987-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(357,'82050798',NULL,'ISWAN LUKITO',NULL,27,56,7,3,'aktif',NULL,1,NULL,'1982-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(358,'95030238',NULL,'RONI HANSVERI BANJARNAHOR',NULL,28,56,7,3,'aktif',NULL,1,NULL,'1995-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(359,'94020506',NULL,'RODEN SUANDI TURNIP',NULL,28,56,7,3,'aktif',NULL,1,NULL,'1994-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(360,'94121145',NULL,'SAPUTRA, S.H.','S.H.',28,56,7,3,'aktif',NULL,1,NULL,'1994-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(361,'95100554',NULL,'DIAN LESTARI GULTOM, S.H.','S.H.',28,56,7,3,'aktif',NULL,1,NULL,'1995-10-01','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:46:22'),(362,'95110886',NULL,'ARGIO SIMBOLON',NULL,28,56,7,3,'aktif',NULL,1,NULL,'1995-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(363,'97070616',NULL,'EKO DAHANA PARDEDE, S.H.','S.H.',28,56,7,3,'aktif',NULL,1,NULL,'1997-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(364,'97040728',NULL,'GIDEON AFRIADI LUMBAN RAJA',NULL,29,56,7,3,'aktif',NULL,1,NULL,'1997-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(365,'98090397',NULL,'FACHRUL REZA SILALAHI',NULL,29,56,7,3,'aktif',NULL,1,NULL,'1998-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(366,'00030346',NULL,'RIDHOTUA F. SITANGGANG',NULL,29,56,7,3,'aktif',NULL,1,NULL,'2000-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(367,'00110362',NULL,'NICHO FERNANDO SARAGIH',NULL,29,56,7,3,'aktif',NULL,1,NULL,'2000-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(368,'00090499',NULL,'ADI P.S. MARBUN',NULL,29,56,7,3,'aktif',NULL,1,NULL,'2000-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(369,'01120358',NULL,'PRIYATAMA ABDILLAH HARAHAP',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2001-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(370,'01070839',NULL,'RIZKI AFRIZAL SIMANJUNTAK',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2001-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(371,'01060553',NULL,'MIDUK YUDIANTO SINAGA',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2001-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(372,'02110342',NULL,'FRAN\'S ALEXANDER SIANIPAR',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2002-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(373,'01110817',NULL,'RAFFLES SIJABAT',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2001-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(374,'01091201',NULL,'HERIANTA TARIGAN',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2001-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(375,'03030809',NULL,'RICKY AGATHA GINTING',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2003-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(376,'03020368',NULL,'CHRISTIAN PROSPEROUS SIMANUNGKALIT',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2003-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(377,'04020196',NULL,'PINIEL RAJAGUKGUK',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2004-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(378,'03090568',NULL,'REZA SIREGAR',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2003-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(379,'04031206',NULL,'RAYMOND VAN HEZEKIEL SIAHAAN',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2004-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(380,'05080602',NULL,'M. ALAMSYAH PRAYOGA TAMBUNAN',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2005-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(381,'04090567',NULL,'IRVAN SYAPUTRA MALAU',NULL,30,56,7,3,'aktif',NULL,1,NULL,'2004-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(382,'79060034',NULL,'FERRY ARIANDY, S.H., M.H','S.H.',22,57,8,3,'aktif',NULL,1,NULL,'1979-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(383,'88100591',NULL,'ALVIUS KRISTIAN GINTING, S.H.','S.H.',24,40,8,3,'aktif',NULL,1,NULL,'1988-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(384,'89010155',NULL,'BENNY SITUMORANG, S.H.','S.H.',27,58,8,3,'aktif',NULL,1,NULL,'1989-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(385,'93050797',NULL,'EKO PUTRA DAMANIK, S.H.','S.H.',28,59,8,3,'aktif',NULL,1,NULL,'1993-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(386,'91050361',NULL,'MAY FRANSISCO SIAGIAN, S.H.','S.H.',28,59,8,3,'aktif',NULL,1,NULL,'1991-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(387,'94090839',NULL,'ROBERTO MANALU',NULL,29,59,8,3,'aktif',NULL,1,NULL,'1994-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(388,'98110378',NULL,'M. RONALD FAHROZI HARAHAP, S.H.','S.H.',29,59,8,3,'aktif',NULL,1,NULL,'1998-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(389,'97020694',NULL,'HERIANTO EFENDI, S.H.','S.H.',29,59,8,3,'aktif',NULL,1,NULL,'1997-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(390,'02120224',NULL,'TEDDI PARNASIPAN TOGATOROP',NULL,30,59,8,3,'aktif',NULL,1,NULL,'2002-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(391,'02090838',NULL,'ONDIHON SIMBOLON',NULL,30,59,8,3,'aktif',NULL,1,NULL,'2002-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(392,'05080131',NULL,'IVAN SIGOP SIHOMBING',NULL,30,59,8,3,'aktif',NULL,1,NULL,'2005-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(393,'80080676',NULL,'NANDI BUTAR-BUTAR, S.H.','S.H.',22,60,10,3,'aktif',NULL,1,NULL,'1980-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(394,'80050867',NULL,'BARTO ANTONIUS SIMALANGO',NULL,25,61,10,3,'aktif',NULL,1,NULL,'1980-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(395,'73040390',NULL,'HASUDUNGAN SILITONGA',NULL,26,62,10,3,'aktif',NULL,1,NULL,'1973-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(396,'85090954',NULL,'JHONNY LEONARDO SILALAHI',NULL,27,63,10,3,'aktif',NULL,1,NULL,'1985-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(397,'83081051',NULL,'ASRIL',NULL,27,64,10,3,'nonaktif','DPO',1,NULL,'1983-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-30 04:12:11'),(398,'94110350',NULL,'INDIRWAN FRIDERICK, S.H.','S.H.',28,64,10,3,'aktif',NULL,1,NULL,'1994-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(399,'93100793',NULL,'EGIDIUM BRAUN SILITONGA',NULL,28,64,10,3,'aktif',NULL,1,NULL,'1993-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(400,'97100701',NULL,'DINAMIKA JAYA NEGARA SITANGGANG',NULL,30,64,10,3,'aktif',NULL,1,NULL,'1997-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(401,'05051087',NULL,'WIRA HARZITA',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2005-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(402,'06100189',NULL,'RAHMAT ANDRIAN TAMBUNAN',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2006-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(403,'07080045',NULL,'JONATAN DWI SAPUTRA PARAPAT',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2007-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(404,'04051595',NULL,'PERDANA NIKOLA SEMBIRING',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2004-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(405,'04081205',NULL,'PETRUS SURIA HUGALUNG',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2004-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(406,'06010414',NULL,'RAFAEL ARSANLILO SINULINGGA',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2006-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(407,'06090021',NULL,'RAJASPER SIRINGORINGO',NULL,30,64,10,3,'aktif',NULL,1,NULL,'2006-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(408,'72100604',NULL,'TANGIO HAOJAHAN SITANGGANG, S.H.','S.H.',23,65,11,3,'aktif',NULL,1,NULL,'1972-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(409,'80100836',NULL,'MARUBA NAINGGOLAN',NULL,25,66,11,3,'aktif',NULL,1,NULL,'1980-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(410,'85030645',NULL,'ROY HARIS ST. SIMAREMARE',NULL,26,67,11,3,'aktif',NULL,1,NULL,'1985-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(411,'80050898',NULL,'M. DENY WAHYU',NULL,26,68,11,3,'aktif',NULL,1,NULL,'1980-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(412,'83050202',NULL,'HENRI F. SIANIPAR',NULL,25,69,11,3,'aktif',NULL,1,NULL,'1983-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(413,'85121325',NULL,'BUYUNG ANDRYANTO',NULL,27,43,11,3,'aktif',NULL,1,NULL,'1985-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(414,'91110130',NULL,'RIANTO SITANGGANG',NULL,28,69,11,3,'aktif',NULL,1,NULL,'1991-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(415,'94090948',NULL,'ROY NANDA SEMBIRING KEMBAREN',NULL,28,69,11,3,'aktif',NULL,1,NULL,'1994-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(416,'96031057',NULL,'CANDRA SILALAHI, S.H.','S.H.',28,69,11,3,'aktif',NULL,1,NULL,'1996-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(417,'02100599',NULL,'YUNUS SAMDIO SIDABUTAR',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2002-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(418,'03010565',NULL,'RAINHEART SITANGGANG',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2003-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(419,'02011312',NULL,'BONIFASIUS NAINGGOLAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2002-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(420,'00080816',NULL,'RAY YONDO SIAHAAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2000-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(421,'03040947',NULL,'REDY EZRA JONATHAN',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2003-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(422,'04100485',NULL,'CHARLY H. ARITONANG',NULL,30,69,11,3,'aktif',NULL,1,NULL,'2004-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(423,'79120800',NULL,'NATANAIL SURBAKTI, S.H',NULL,22,70,9,3,'aktif',NULL,1,NULL,'1979-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(424,'75080942',NULL,'JUSUP KETAREN',NULL,24,71,9,3,'aktif',NULL,1,NULL,'1975-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(425,'80070492',NULL,'ARON PERANGIN-ANGIN',NULL,25,72,9,3,'aktif',NULL,1,NULL,'1980-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(426,'79060704',NULL,'HERON GINTING',NULL,27,73,9,3,'aktif',NULL,1,NULL,'1979-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(427,'86030733',NULL,'JEFRI KHADAFI SIREGAR, S.H.','S.H.',27,74,9,3,'aktif',NULL,1,NULL,'1986-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(428,'89070031',NULL,'HERIANTO TURNIP',NULL,27,75,9,3,'aktif',NULL,1,NULL,'1989-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(429,'87030647',NULL,'DION MAR\'YANSEN SILITONGA',NULL,28,75,9,3,'aktif',NULL,1,NULL,'1987-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(430,'93020749',NULL,'ROY GRIMSLAY, S.H.','S.H.',28,75,9,3,'aktif',NULL,1,NULL,'1993-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(431,'93090673',NULL,'BAGUS DWI PRAKOSO, S.H.','S.H.',28,75,9,3,'aktif',NULL,1,NULL,'1993-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(432,'97040353',NULL,'ICASANDRI MONANZA BR GINTING',NULL,28,75,9,3,'aktif',NULL,1,NULL,'1997-04-01','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:47:17'),(433,'95021078',NULL,'DIKI FEBRIAN SITORUS',NULL,29,75,9,3,'aktif',NULL,1,NULL,'1995-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(434,'96031061',NULL,'MARCHLANDA SITOHANG',NULL,29,75,9,3,'aktif',NULL,1,NULL,'1996-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(435,'01080438',NULL,'JULIVER SIDABUTAR',NULL,29,75,9,3,'aktif',NULL,1,NULL,'2001-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(436,'01120281',NULL,'FATHURROZI TINDAON',NULL,30,75,9,3,'aktif',NULL,1,NULL,'2001-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(437,'02111012',NULL,'BENY BOY CHRISTIAN SIAHAAN',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2002-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(438,'02111051',NULL,'RADOT NOVALDO PANDAPOTAN PURBA',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2002-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(439,'05030251',NULL,'MUHAMMAD ZIDHAN RIFALDI',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2005-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(440,'04050615',NULL,'DANI INDRA PERMANA SINAGA',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2004-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(441,'05010048',NULL,'HEZKIEL CAPRI SITINDAON',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2005-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(442,'04030824',NULL,'BONARIS TSUYOKO DITASANI SINAGA',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2004-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(443,'05010014',NULL,'ARY ANJAS SARAGIH',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2005-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(444,'04030805',NULL,'GABRIEL VERY JUNIOR SITOHANG',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2004-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(445,'02121477',NULL,'FIRMAN BAHTERA',NULL,30,21,9,3,'aktif',NULL,1,NULL,'2002-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(446,'68120522',NULL,'SULAIMAN PANGARIBUAN, S.H',NULL,22,76,12,3,'aktif',NULL,1,NULL,'1968-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(447,'83080822',NULL,'EFENDI M.  SIREGAR',NULL,26,77,12,3,'aktif',NULL,1,NULL,'1983-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(448,'73120275',NULL,'ROMEL LINDUNG SIAHAAN',NULL,26,43,12,3,'aktif',NULL,1,NULL,'1973-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(449,'90060273',NULL,'FRANS HOTMAN MANURUNG, S.H.','S.H.',27,78,12,3,'aktif',NULL,1,NULL,'1990-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(450,'77070919',NULL,'ANTONIUS SIPAYUNG',NULL,28,78,12,3,'aktif',NULL,1,NULL,'1977-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(451,'82051018',NULL,'SAUT H. SIAHAAN',NULL,26,79,13,3,'aktif',NULL,1,NULL,'1982-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(452,'98050496',NULL,'FERNANDO SIMBOLON',NULL,29,80,13,3,'aktif',NULL,1,NULL,'1998-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(453,'98030531',NULL,'KURNIA PERMANA',NULL,29,80,13,3,'aktif',NULL,1,NULL,'1998-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(454,'05090232',NULL,'STEVEN IMANUEL SITUMEANG',NULL,30,80,13,3,'aktif',NULL,1,NULL,'2005-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(455,'69090552',NULL,'RAHMAT KURNIAWAN',NULL,23,81,15,4,'aktif',NULL,1,NULL,'1969-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(456,'79090296',NULL,'MARUKKIL J.M. PASARIBU',NULL,25,82,15,4,'aktif',NULL,1,NULL,'1979-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(457,'82070930',NULL,'LANTRO LANDELINUS SAGALA',NULL,26,83,15,4,'aktif',NULL,1,NULL,'1982-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(458,'87120701',NULL,'ANDY DEDY SIHOMBING, S.H.','S.H.',27,84,15,4,'aktif',NULL,1,NULL,'1987-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(459,'86021428',NULL,'RANGGA HATTA',NULL,27,85,15,4,'aktif',NULL,1,NULL,'1986-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(460,'80120573',NULL,'ARDIANSYAH BUTAR-BUTAR',NULL,27,86,18,4,'aktif',NULL,1,NULL,'1980-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(461,'96120123',NULL,'ADRYANTO SINAGA, S.H.','S.H.',28,86,18,4,'aktif',NULL,1,NULL,'1996-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(462,'94040538',NULL,'BROLIN ADFRIALDI HALOHO',NULL,28,86,18,4,'aktif',NULL,1,NULL,'1994-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(463,'95110806',NULL,'SUGIANTO ERIK SIBORO',NULL,28,86,18,4,'aktif',NULL,1,NULL,'1995-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(464,'01020739',NULL,'RISKO SIMBOLON',NULL,30,86,18,4,'aktif',NULL,1,NULL,'2001-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(465,'70050412',NULL,'MAXON NAINGGOLAN',NULL,22,87,16,4,'aktif',NULL,1,NULL,'1970-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(466,'78040213',NULL,'H. SWANDI SINAGA',NULL,25,88,16,4,'aktif',NULL,1,NULL,'1978-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(467,'77030463',NULL,'HARATUA GULTOM',NULL,25,24,16,4,'aktif',NULL,1,NULL,'1977-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(468,'76120606',NULL,'ASA MELKI HUTABARAT',NULL,26,89,16,4,'aktif',NULL,1,NULL,'1976-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(469,'78100741',NULL,'JARIAHMAN SARAGIH',NULL,26,83,16,4,'aktif',NULL,1,NULL,'1978-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(470,'87041134',NULL,'MUHAMMAD SYAFEI RAMADHAN',NULL,26,84,16,4,'aktif',NULL,1,NULL,'1987-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(471,'86121371',NULL,'RIJALUL FIKRI SINAGA',NULL,27,82,16,4,'aktif',NULL,1,NULL,'1986-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:53'),(472,'85071450',NULL,'TEGUH SYAHPUTRA',NULL,27,90,16,4,'aktif',NULL,1,NULL,'1985-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(473,'85041500',NULL,'RUDYANTO LUMBANRAJA',NULL,27,91,16,4,'aktif',NULL,1,NULL,'1985-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(474,'96031075',NULL,'ZULPAN SYAHPUTRA DAMANIK',NULL,29,91,16,4,'aktif',NULL,1,NULL,'1996-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(475,'83061022',NULL,'RAMADAN SIREGAR, S.H.','S.H.',23,92,17,4,'aktif',NULL,1,NULL,'1983-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(476,'86071792',NULL,'WIDODO KABAN, S.H.','S.H.',24,93,17,4,'aktif',NULL,1,NULL,'1986-07-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(477,'75120864',NULL,'GUNTAR TAMBUNAN',NULL,25,88,17,4,'aktif',NULL,1,NULL,'1975-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(478,'82040124',NULL,'JEFRI RICARDO SAMOSIR',NULL,25,94,17,4,'aktif',NULL,1,NULL,'1982-04-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(479,'84020306',NULL,'JUITO SUPANOTO PERANGIN-ANGIN',NULL,26,83,17,4,'aktif',NULL,1,NULL,'1984-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(480,'83080042',NULL,'YOPPHY RHODEAR MUNTHE',NULL,26,95,17,4,'aktif',NULL,1,NULL,'1983-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(481,'86010311',NULL,'TUMBUR SITOHANG',NULL,26,82,17,4,'aktif',NULL,1,NULL,'1986-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(482,'84110202',NULL,'DONI SURIANTO PURBA, S.H.','S.H.',27,24,17,4,'aktif',NULL,1,NULL,'1984-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(483,'89020409',NULL,'PATAR F. ANRI SIAHAAN',NULL,27,89,17,4,'aktif',NULL,1,NULL,'1989-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(484,'94090490',NULL,'KURNIAWAN, S.H.','S.H.',28,86,18,4,'aktif',NULL,1,NULL,'1994-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(485,'95060432',NULL,'ASHARI BUTAR-BUTAR, S.H.','S.H.',28,86,18,4,'aktif',NULL,1,NULL,'1995-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(486,'96061331',NULL,'DIDI HOT BAGAS SITORUS',NULL,30,86,18,4,'aktif',NULL,1,NULL,'1996-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(487,'01060884',NULL,'HORAS J.M. ARITONANG',NULL,30,86,18,4,'aktif',NULL,1,NULL,'2001-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(488,'04060050',NULL,'ANDRE YEHEZKIEL HUTABARAT',NULL,30,86,18,4,'aktif',NULL,1,NULL,'2004-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(489,'89080105',NULL,'CLAUDIUS HARIS PARDEDE',NULL,28,86,18,4,'aktif',NULL,1,NULL,'1989-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(490,'02051553',NULL,'ZULKIFLI NASUTION',NULL,30,86,18,4,'aktif',NULL,1,NULL,'2002-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(491,'70010290',NULL,'RADIAMAN SIMARMATA',NULL,22,109,18,4,'aktif',NULL,1,NULL,'1970-01-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:07:18'),(492,'82050839',NULL,'HERMAWADI',NULL,26,84,18,4,'aktif',NULL,1,NULL,'1982-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(493,'84091124',NULL,'BISSAR LUMBANTUNGKUP',NULL,26,83,18,4,'aktif',NULL,1,NULL,'1984-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(494,'70090340',NULL,'BONAR JUBEL SIBARANI',NULL,27,89,18,4,'aktif',NULL,1,NULL,'1970-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(495,'77020642',NULL,'RAMLES SITANGGANG',NULL,27,82,18,4,'aktif',NULL,1,NULL,'1977-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(496,'83031377',NULL,'LUHUT SIRINGO-RINGO',NULL,28,101,18,4,'aktif',NULL,1,NULL,'1983-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:05:46'),(497,'03100001',NULL,'ANRIAN SIGALINGGING',NULL,30,101,18,4,'aktif',NULL,1,NULL,'2003-10-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:05:46'),(498,'99110755',NULL,'BONATUA LUMBANTUNGKUP',NULL,30,101,18,4,'aktif',NULL,1,NULL,'1999-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:05:46'),(499,'03050116',NULL,'ANDRE SUGIARTO MARPAUNG',NULL,30,101,18,4,'aktif',NULL,1,NULL,'2003-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:05:46'),(500,'04030125',NULL,'ERWIN KEVIN GULTOM',NULL,30,101,18,4,'aktif',NULL,1,NULL,'2004-03-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 08:05:46'),(501,'70020298',NULL,'BANGUN TUA DALIMUNTHE',NULL,22,97,19,4,'aktif',NULL,1,NULL,'1970-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(502,'81050713',NULL,'LANCASTER ARIANTO CANDY PASARIBU, S.H.','S.H.',25,84,19,4,'aktif',NULL,1,NULL,'1981-05-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:35:37'),(503,'80090905',NULL,'RUDY SETYAWAN',NULL,25,82,19,4,'aktif',NULL,1,NULL,'1980-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(504,'80080892',NULL,'MANGATUR TUA TINDAON',NULL,26,83,19,4,'aktif',NULL,1,NULL,'1980-08-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:12:11'),(505,'87110154',NULL,'RENO HOTMARULI TUA MANIK, S.H.','S.H.',27,89,19,4,'aktif',NULL,1,NULL,'1987-11-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-03-28 19:09:54'),(506,'79020443',NULL,'HERBINTUPA SITANGGANG',NULL,28,86,18,4,'aktif',NULL,1,NULL,'1979-02-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(507,'85121751',NULL,'IBRAHIM TARIGAN',NULL,28,86,18,4,'aktif',NULL,1,NULL,'1985-12-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(508,'98090406',NULL,'AGUNG NUGRAHA HARIANJA, S.H.','S.H.',29,86,18,4,'aktif',NULL,1,NULL,'1998-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(509,'98091274',NULL,'DANI PUTRA RUMAHORBO',NULL,29,86,18,4,'aktif',NULL,1,NULL,'1998-09-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(510,'01060198',NULL,'KRISMAN JULU GULTOM',NULL,30,86,18,4,'aktif',NULL,1,NULL,'2001-06-01','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:25:53','2026-04-06 18:39:15'),(511,'198112262024211002',NULL,'FERNANDO SILALAHI, A.Md.',NULL,NULL,6,2,2,'aktif',NULL,7,NULL,'1981-12-26','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,'SYSTEM_IMPORT',NULL,'2026-03-28 18:26:34','2026-03-28 19:21:42');
/*!40000 ALTER TABLE `personil_backup_20260407_014326` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_backup_20260407_014421`
--

DROP TABLE IF EXISTS `personil_backup_20260407_014421`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_backup_20260407_014421` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nrp` (`nrp`),
  KEY `idx_nrp` (`nrp`),
  KEY `idx_nama` (`nama`),
  KEY `idx_pangkat` (`id_pangkat`),
  KEY `idx_jabatan` (`id_jabatan`),
  KEY `idx_bagian` (`id_bagian`),
  KEY `idx_unsur` (`id_unsur`),
  KEY `idx_status` (`status_ket`),
  KEY `idx_active` (`is_active`),
  KEY `idx_deleted` (`is_deleted`),
  KEY `fk_personil_jenis_pegawai` (`id_jenis_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_backup_20260407_014421`
--

LOCK TABLES `personil_backup_20260407_014421` WRITE;
/*!40000 ALTER TABLE `personil_backup_20260407_014421` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_backup_20260407_014421` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_backup_20260407_014434`
--

DROP TABLE IF EXISTS `personil_backup_20260407_014434`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_backup_20260407_014434` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nrp` (`nrp`),
  KEY `idx_nrp` (`nrp`),
  KEY `idx_nama` (`nama`),
  KEY `idx_pangkat` (`id_pangkat`),
  KEY `idx_jabatan` (`id_jabatan`),
  KEY `idx_bagian` (`id_bagian`),
  KEY `idx_unsur` (`id_unsur`),
  KEY `idx_status` (`status_ket`),
  KEY `idx_active` (`is_active`),
  KEY `idx_deleted` (`is_deleted`),
  KEY `fk_personil_jenis_pegawai` (`id_jenis_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_backup_20260407_014434`
--

LOCK TABLES `personil_backup_20260407_014434` WRITE;
/*!40000 ALTER TABLE `personil_backup_20260407_014434` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_backup_20260407_014434` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_backup_20260407_014445`
--

DROP TABLE IF EXISTS `personil_backup_20260407_014445`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_backup_20260407_014445` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_backup_20260407_014445`
--

LOCK TABLES `personil_backup_20260407_014445` WRITE;
/*!40000 ALTER TABLE `personil_backup_20260407_014445` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_backup_20260407_014445` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_kontak`
--

DROP TABLE IF EXISTS `personil_kontak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_kontak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_personil` int(11) NOT NULL,
  `jenis_kontak` enum('TELEPON','EMAIL','WHATSAPP','FAX','LAINNYA') DEFAULT NULL,
  `nilai_kontak` varchar(255) NOT NULL,
  `is_utama` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil` (`id_personil`),
  KEY `idx_jenis` (`jenis_kontak`),
  CONSTRAINT `personil_kontak_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_kontak`
--

LOCK TABLES `personil_kontak` WRITE;
/*!40000 ALTER TABLE `personil_kontak` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_kontak` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_medsos`
--

DROP TABLE IF EXISTS `personil_medsos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_medsos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_personil` int(11) NOT NULL,
  `platform_medsos` enum('INSTAGRAM','FACEBOOK','TWITTER','LINKEDIN','TIKTOK','YOUTUBE','LAINNYA') DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `url_profile` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil` (`id_personil`),
  KEY `idx_platform` (`platform_medsos`),
  CONSTRAINT `personil_medsos_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_medsos`
--

LOCK TABLES `personil_medsos` WRITE;
/*!40000 ALTER TABLE `personil_medsos` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_medsos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_new`
--

DROP TABLE IF EXISTS `personil_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `JK` enum('L','P') DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_new`
--

LOCK TABLES `personil_new` WRITE;
/*!40000 ALTER TABLE `personil_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_new` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personil_pendidikan`
--

DROP TABLE IF EXISTS `personil_pendidikan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personil_pendidikan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_personil` int(11) NOT NULL,
  `id_pendidikan` int(11) NOT NULL,
  `nama_institusi` varchar(200) DEFAULT NULL,
  `jurusan` varchar(150) DEFAULT NULL,
  `tahun_lulus` varchar(10) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `is_pendidikan_terakhir` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil` (`id_personil`),
  KEY `idx_pendidikan` (`id_pendidikan`),
  CONSTRAINT `personil_pendidikan_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personil_pendidikan_ibfk_2` FOREIGN KEY (`id_pendidikan`) REFERENCES `master_pendidikan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personil_pendidikan`
--

LOCK TABLES `personil_pendidikan` WRITE;
/*!40000 ALTER TABLE `personil_pendidikan` DISABLE KEYS */;
/*!40000 ALTER TABLE `personil_pendidikan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piket_absensi`
--

DROP TABLE IF EXISTS `piket_absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piket_absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL COMMENT 'FK schedules.id',
  `personil_id` varchar(20) NOT NULL COMMENT 'NRP personil',
  `tim_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('hadir','tidak_hadir','sakit','ijin','terlambat') NOT NULL DEFAULT 'hadir',
  `jam_hadir` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `input_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_schedule_personil` (`schedule_id`,`personil_id`),
  KEY `idx_personil` (`personil_id`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_tim` (`tim_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Absensi/konfirmasi kehadiran piket';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piket_absensi`
--

LOCK TABLES `piket_absensi` WRITE;
/*!40000 ALTER TABLE `piket_absensi` DISABLE KEYS */;
/*!40000 ALTER TABLE `piket_absensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `predictive_models`
--

DROP TABLE IF EXISTS `predictive_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predictive_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_type` enum('staffing_forecast','fatigue_prediction','absence_prediction') NOT NULL,
  `model_version` varchar(20) DEFAULT '1.0',
  `model_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`model_parameters`)),
  `accuracy_score` decimal(5,4) DEFAULT NULL,
  `training_data_period_start` date DEFAULT NULL,
  `training_data_period_end` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_model_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predictive_models`
--

LOCK TABLES `predictive_models` WRITE;
/*!40000 ALTER TABLE `predictive_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `predictive_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recall_campaigns`
--

DROP TABLE IF EXISTS `recall_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recall_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_code` varchar(50) NOT NULL,
  `campaign_name` varchar(200) NOT NULL,
  `campaign_type` enum('emergency','recall','standby','alert') NOT NULL,
  `description` text DEFAULT NULL,
  `priority_level` enum('low','medium','high','critical') DEFAULT 'high',
  `target_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Target personil groups' CHECK (json_valid(`target_groups`)),
  `message_template` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
  `created_by` varchar(50) NOT NULL,
  `total_sent` int(11) DEFAULT 0,
  `total_responded` int(11) DEFAULT 0,
  `total_confirmed` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_code` (`campaign_code`),
  KEY `idx_campaign_status` (`status`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_campaign_type` (`campaign_type`),
  KEY `idx_status_created` (`status`,`start_time`),
  KEY `idx_start_status` (`start_time`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recall_campaigns`
--

LOCK TABLES `recall_campaigns` WRITE;
/*!40000 ALTER TABLE `recall_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `recall_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recall_responses`
--

DROP TABLE IF EXISTS `recall_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recall_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `response_status` enum('pending','acknowledged','confirmed','declined','unable') DEFAULT 'pending',
  `response_time` timestamp NULL DEFAULT NULL,
  `response_note` text DEFAULT NULL,
  `eta_time` datetime DEFAULT NULL COMMENT 'Estimated time of arrival',
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `personil_id` (`personil_id`),
  KEY `idx_campaign_personil` (`campaign_id`,`personil_id`),
  KEY `idx_response_status` (`response_status`),
  KEY `idx_response_time` (`response_time`),
  CONSTRAINT `recall_responses_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `recall_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recall_responses_ibfk_2` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recall_responses`
--

LOCK TABLES `recall_responses` WRITE;
/*!40000 ALTER TABLE `recall_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `recall_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_access_log`
--

DROP TABLE IF EXISTS `report_access_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('generated','downloaded','viewed','deleted') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_report_id` (`report_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `report_access_log_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `generated_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_access_log`
--

LOCK TABLES `report_access_log` WRITE;
/*!40000 ALTER TABLE `report_access_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_access_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_email_log`
--

DROP TABLE IF EXISTS `report_email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','bounced') DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_report_id` (`report_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_email_log`
--

LOCK TABLES `report_email_log` WRITE;
/*!40000 ALTER TABLE `report_email_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_email_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_stats`
--

DROP TABLE IF EXISTS `report_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `reports_generated` int(11) DEFAULT 0,
  `total_size_mb` decimal(10,2) DEFAULT 0.00,
  `unique_users` int(11) DEFAULT 0,
  `downloads_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_stats`
--

LOCK TABLES `report_stats` WRITE;
/*!40000 ALTER TABLE `report_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_subscriptions`
--

DROP TABLE IF EXISTS `report_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `format` enum('pdf','excel','csv') NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `delivery_method` enum('email','download','both') DEFAULT 'email',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_report` (`user_id`,`report_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_report_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_subscriptions`
--

LOCK TABLES `report_subscriptions` WRITE;
/*!40000 ALTER TABLE `report_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduled_reports`
--

DROP TABLE IF EXISTS `scheduled_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduled_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(50) NOT NULL,
  `report_name` varchar(200) NOT NULL,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `format` enum('pdf','excel','csv') NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'List of user IDs to receive report' CHECK (json_valid(`recipients`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_frequency` (`frequency`),
  KEY `idx_next_run` (`next_run`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_reports`
--

LOCK TABLES `scheduled_reports` WRITE;
/*!40000 ALTER TABLE `scheduled_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduled_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personil_id` varchar(20) NOT NULL,
  `personil_name` varchar(255) NOT NULL,
  `bagian` varchar(100) NOT NULL,
  `shift_type` varchar(20) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tim_id` int(11) DEFAULT NULL COMMENT 'FK tim_piket',
  `recurrence_type` enum('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none',
  `recurrence_interval` int(11) NOT NULL DEFAULT 1 COMMENT 'Setiap N hari/minggu/bulan',
  `recurrence_days` varchar(20) DEFAULT NULL COMMENT 'weekly: 1,3,5 = Sen,Rab,Jum',
  `recurrence_end` date DEFAULT NULL,
  `recurrence_parent_id` int(11) DEFAULT NULL COMMENT 'NULL = induk series',
  `fatigue_risk` enum('low','medium','high') DEFAULT 'low',
  `consecutive_days` int(11) DEFAULT 1 COMMENT 'Hari kerja berturut-turut',
  `weekly_hours` decimal(5,2) DEFAULT 0.00 COMMENT 'Total jam kerja minggu ini',
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `overtime_rate` enum('regular','holiday','weekend','emergency') DEFAULT 'regular',
  `overtime_approved` tinyint(1) DEFAULT 0,
  `overtime_approved_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_personil` (`personil_id`),
  KEY `idx_date` (`shift_date`),
  KEY `idx_bagian` (`bagian`),
  KEY `idx_fatigue_risk` (`fatigue_risk`,`shift_date`),
  KEY `idx_personil_date` (`personil_id`,`shift_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduling_patterns`
--

DROP TABLE IF EXISTS `scheduling_patterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduling_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_type` enum('seasonal','weekly','daily','emergency') NOT NULL,
  `bagian_id` int(11) DEFAULT NULL,
  `unsur_id` int(11) DEFAULT NULL,
  `day_of_week` tinyint(4) DEFAULT NULL COMMENT '0-6 (Sunday-Saturday)',
  `week_of_year` tinyint(4) DEFAULT NULL COMMENT '1-52',
  `month` tinyint(4) DEFAULT NULL COMMENT '1-12',
  `hour_of_day` tinyint(4) DEFAULT NULL COMMENT '0-23',
  `personnel_demand` int(11) DEFAULT 0,
  `historical_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Historical demand data' CHECK (json_valid(`historical_data`)),
  `confidence_score` decimal(5,4) DEFAULT 0.5000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unsur_id` (`unsur_id`),
  KEY `idx_pattern_type` (`pattern_type`),
  KEY `idx_demand_period` (`day_of_week`,`week_of_year`,`month`),
  KEY `idx_bagian_unsur` (`bagian_id`,`unsur_id`),
  CONSTRAINT `scheduling_patterns_ibfk_1` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scheduling_patterns_ibfk_2` FOREIGN KEY (`unsur_id`) REFERENCES `unsur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduling_patterns`
--

LOCK TABLES `scheduling_patterns` WRITE;
/*!40000 ALTER TABLE `scheduling_patterns` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduling_patterns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siklus_piket_fase`
--

DROP TABLE IF EXISTS `siklus_piket_fase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siklus_piket_fase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_bagian` int(11) NOT NULL,
  `nama_fase` varchar(100) NOT NULL COMMENT 'Piket Fungsi, Lepas Piket, Piket Cadangan',
  `urutan` int(11) NOT NULL DEFAULT 1,
  `durasi_jam` decimal(4,1) NOT NULL DEFAULT 8.0,
  `jam_mulai_default` time NOT NULL DEFAULT '07:00:00',
  `jam_mulai_mode` enum('auto','manual') NOT NULL DEFAULT 'auto' COMMENT 'auto=hitung dari fase sebelumnya',
  `is_wajib` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=opsional seperti Piket Cadangan',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bagian_urutan` (`id_bagian`,`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Definisi fase siklus piket per bagian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siklus_piket_fase`
--

LOCK TABLES `siklus_piket_fase` WRITE;
/*!40000 ALTER TABLE `siklus_piket_fase` DISABLE KEYS */;
/*!40000 ALTER TABLE `siklus_piket_fase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_ekspedisi`
--

DROP TABLE IF EXISTS `surat_ekspedisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat_ekspedisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_agenda` varchar(100) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `nomor_surat` varchar(150) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `tanggal_terima` date DEFAULT NULL,
  `perihal` varchar(255) NOT NULL,
  `pengirim` varchar(255) DEFAULT NULL,
  `tujuan` varchar(255) DEFAULT NULL,
  `kategori` enum('biasa','penting','rahasia','segera') DEFAULT 'biasa',
  `status` enum('diterima','diproses','selesai','diarsipkan') DEFAULT 'diterima',
  `disposisi` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file_lampiran` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_ekspedisi`
--

LOCK TABLES `surat_ekspedisi` WRITE;
/*!40000 ALTER TABLE `surat_ekspedisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `surat_ekspedisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`setting_value`)),
  `setting_type` enum('boolean','integer','decimal','string','json') NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'fatigue_max_weekly_hours','40','integer','Maximum weekly working hours','fatigue',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(2,'fatigue_min_rest_hours','12','integer','Minimum rest hours between shifts','fatigue',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(3,'fatigue_consecutive_days_limit','7','integer','Maximum consecutive working days','fatigue',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(4,'certification_expiry_warning_days','30','integer','Days before expiry to send warning','certification',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(5,'overtime_auto_approval_limit','2','decimal','Auto-approve overtime under this limit','overtime',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(6,'emergency_task_auto_assign','false','boolean','Auto-assign emergency tasks','emergency',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(7,'recall_response_timeout_minutes','30','integer','Timeout for recall response in minutes','recall',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(8,'equipment_maintenance_warning_days','7','integer','Days before maintenance to send warning','equipment',1,'2026-04-11 14:59:35','2026-04-11 14:59:35'),(25,'mobile_session_timeout_hours','24','integer','Mobile session timeout in hours','mobile',1,'2026-04-11 15:06:49','2026-04-11 15:06:49'),(26,'mobile_rate_limit_per_minute','100','integer','Mobile API rate limit per minute','mobile',1,'2026-04-11 15:06:49','2026-04-11 15:06:49'),(27,'push_notification_enabled','true','boolean','Enable push notifications','mobile',1,'2026-04-11 15:06:49','2026-04-11 15:06:49'),(30,'notification_service_enabled','true','boolean','Enable notification service','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(31,'notification_queue_enabled','true','boolean','Enable notification queue processing','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(32,'notification_cleanup_days','90','integer','Days to keep notification history','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(33,'notification_max_attempts','3','integer','Maximum delivery attempts per notification','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(34,'notification_batch_size','100','integer','Batch size for notification processing','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(35,'email_notifications_enabled','true','boolean','Enable email notifications','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(36,'sms_notifications_enabled','false','boolean','Enable SMS notifications','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(37,'notification_quiet_hours_enabled','true','boolean','Enable quiet hours for notifications','notifications',1,'2026-04-11 15:08:15','2026-04-11 15:08:15'),(39,'reporting_enabled','true','boolean','Enable automated reporting system','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(40,'report_retention_days','90','integer','Days to keep generated reports','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(41,'max_report_size_mb','50','integer','Maximum report file size in MB','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(42,'auto_cleanup_reports','true','boolean','Automatically cleanup old reports','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(43,'email_reports_enabled','true','boolean','Enable email delivery of reports','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(44,'report_generation_timeout','300','integer','Report generation timeout in seconds','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28'),(45,'concurrent_reports_limit','5','integer','Maximum concurrent report generation','reporting',1,'2026-04-11 15:09:28','2026-04-11 15:09:28');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_conflicts`
--

DROP TABLE IF EXISTS `task_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_conflicts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `emergency_task_id` int(11) NOT NULL,
  `conflict_type` enum('overlap','resource','fatigue') NOT NULL,
  `resolution_status` enum('pending','resolved','escalated') DEFAULT 'pending',
  `resolution_action` text DEFAULT NULL,
  `resolved_by` varchar(50) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `emergency_task_id` (`emergency_task_id`),
  KEY `idx_conflict_status` (`resolution_status`),
  KEY `idx_schedule_id` (`schedule_id`),
  CONSTRAINT `task_conflicts_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_conflicts_ibfk_2` FOREIGN KEY (`emergency_task_id`) REFERENCES `emergency_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_conflicts`
--

LOCK TABLES `task_conflicts` WRITE;
/*!40000 ALTER TABLE `task_conflicts` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_conflicts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tim_piket`
--

DROP TABLE IF EXISTS `tim_piket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tim_piket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_tim` varchar(100) NOT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `jenis` enum('piket','satuan_tugas','kegiatan') NOT NULL DEFAULT 'piket',
  `shift_default` varchar(20) DEFAULT NULL COMMENT 'PAGI/SIANG/MALAM/FULL_DAY/ROTASI',
  `pola_rotasi` varchar(100) DEFAULT NULL COMMENT 'Urutan shift rotasi, misal: PAGI,SIANG,MALAM',
  `keterangan` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fase_siklus_id` int(11) DEFAULT NULL COMMENT 'FK siklus_piket_fase — tim ini sedang di fase mana',
  `jam_mulai_aktif` time DEFAULT NULL COMMENT 'Override jam mulai aktual tim',
  `durasi_jam` decimal(4,1) DEFAULT NULL COMMENT 'Durasi tugas dalam jam',
  PRIMARY KEY (`id`),
  KEY `idx_bagian` (`id_bagian`),
  KEY `idx_unsur` (`id_unsur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Manajemen tim/regu piket per bagian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tim_piket`
--

LOCK TABLES `tim_piket` WRITE;
/*!40000 ALTER TABLE `tim_piket` DISABLE KEYS */;
/*!40000 ALTER TABLE `tim_piket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tim_piket_anggota`
--

DROP TABLE IF EXISTS `tim_piket_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tim_piket_anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tim_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `peran` enum('ketua','wakil','anggota') NOT NULL DEFAULT 'anggota',
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tim_personil` (`tim_id`,`personil_id`),
  KEY `idx_tim` (`tim_id`),
  KEY `idx_personil` (`personil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Anggota tim piket';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tim_piket_anggota`
--

LOCK TABLES `tim_piket_anggota` WRITE;
/*!40000 ALTER TABLE `tim_piket_anggota` DISABLE KEYS */;
/*!40000 ALTER TABLE `tim_piket_anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_compliance`
--

DROP TABLE IF EXISTS `training_compliance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_compliance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personil_id` varchar(20) NOT NULL,
  `training_type` varchar(100) NOT NULL,
  `training_name` varchar(200) NOT NULL,
  `provider` varchar(200) DEFAULT NULL,
  `training_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` enum('required','in_progress','completed','expired','failed') DEFAULT 'required',
  `hours_completed` decimal(4,2) DEFAULT 0.00,
  `required_hours` decimal(4,2) DEFAULT 0.00,
  `next_due` date DEFAULT NULL COMMENT 'Tanggal harus training ulang',
  `certificate_required` tinyint(1) DEFAULT 1,
  `completion_certificate_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personil_training` (`personil_id`),
  KEY `idx_training_status` (`status`),
  KEY `idx_next_due` (`next_due`),
  CONSTRAINT `training_compliance_ibfk_1` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_compliance`
--

LOCK TABLES `training_compliance` WRITE;
/*!40000 ALTER TABLE `training_compliance` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_compliance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unsur`
--

DROP TABLE IF EXISTS `unsur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unsur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_unsur` varchar(50) NOT NULL,
  `nama_unsur` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `dasar_hukum` varchar(255) DEFAULT NULL,
  `tingkat` varchar(50) DEFAULT 'POLRES',
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_unsur` (`kode_unsur`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unsur`
--

LOCK TABLES `unsur` WRITE;
/*!40000 ALTER TABLE `unsur` DISABLE KEYS */;
INSERT INTO `unsur` VALUES (1,'UNSUR_PIMPINAN','UNSUR PIMPINAN','Kapolres dan Wakapolres','PERKAP No. 23 Tahun 2010 Pasal 4','POLRES',1,1,'2026-03-28 18:16:57','2026-04-10 04:02:05'),(2,'UNSUR_PEMBANTU_PIMPINAN','UNSUR PEMBANTU PIMPINAN','Kepala Bagian (KABAG), Kepala Satuan (KASAT), Kepala Polsek (KAPOLSEK)','PERKAP No. 23 Tahun 2010 Pasal 5','POLRES',2,1,'2026-03-28 18:16:57','2026-04-10 04:02:05'),(3,'UNSUR_PELAKSANA_TUGAS_POKOK','UNSUR PELAKSANA TUGAS POKOK','Satuan Tugas Pokok di tingkat POLRES','PERKAP No. 23 Tahun 2010 Pasal 6','POLRES',3,1,'2026-03-28 18:16:57','2026-04-10 04:02:05'),(4,'UNSUR_PELAKSANA_KEWILAYAHAN','UNSUR PELAKSANA KEWILAYAHAN','Kepolisian Sektor (POLSEK) jajaran POLRES','PERKAP No. 23 Tahun 2010 Pasal 7','POLRES',4,1,'2026-03-28 18:16:57','2026-04-10 04:02:05'),(5,'UNSUR_PENDUKUNG','UNSUR PENDUKUNG','Unit pendukung operasional dan administrasi','PERKAP No. 23 Tahun 2010 Pasal 8','POLRES',5,1,'2026-03-28 18:16:57','2026-04-10 04:02:05'),(6,'UNSUR_LAINNYA','UNSUR LAINNYA','Unit khusus dan penugasan khusus','PERKAP No. 23 Tahun 2010','POLRES',6,1,'2026-03-28 18:16:57','2026-04-10 04:02:05');
/*!40000 ALTER TABLE `unsur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity_log`
--

DROP TABLE IF EXISTS `user_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity_log`
--

LOCK TABLES `user_activity_log` WRITE;
/*!40000 ALTER TABLE `user_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','operator','viewer') NOT NULL DEFAULT 'viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'bagops','$2y$10$evPCY3JbFpDMBIgpv8sB/eO/tKnlYYCZrWaozH2Nl3dod/AQmJTWW','admin@polressamosir.id','Administrator BAGOPS','admin',1,'2026-04-11 21:43:07',0,NULL,'2026-03-30 19:40:54','2026-04-11 14:43:07',NULL),(2,'operator','$2y$10$FZiaJAqG8l.1RqpH3PRnjuIsL/tOOzMqnHfk3nEbk9Azp1kTxRvT.',NULL,'Staf Operator Bagops','operator',1,NULL,0,NULL,'2026-04-10 14:43:57','2026-04-10 14:43:57',NULL),(3,'viewer','$2y$10$Rjy2pebO3qzDyPGJHl7C8.O2Mo29KB4llHnYRTMmUrDj8IybJ.2s.',NULL,'Kapolres Samosir','viewer',1,NULL,0,NULL,'2026-04-10 14:43:57','2026-04-10 14:43:57',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `weekly_fatigue_summary`
--

DROP TABLE IF EXISTS `weekly_fatigue_summary`;
/*!50001 DROP VIEW IF EXISTS `weekly_fatigue_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `weekly_fatigue_summary` AS SELECT
 1 AS `week_year`,
  1 AS `week_start`,
  1 AS `week_end`,
  1 AS `total_records`,
  1 AS `avg_fatigue_score`,
  1 AS `critical_cases`,
  1 AS `high_cases`,
  1 AS `avg_hours_worked`,
  1 AS `avg_rest_hours` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `daily_attendance_summary`
--

/*!50001 DROP VIEW IF EXISTS `daily_attendance_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `daily_attendance_summary` AS select cast(`s`.`shift_date` as date) AS `attendance_date`,count(0) AS `total_scheduled`,count(case when `pa`.`status` = 'hadir' then 1 end) AS `present`,count(case when `pa`.`status` = 'sakit' then 1 end) AS `sick`,count(case when `pa`.`status` = 'ijin' then 1 end) AS `permitted`,count(case when `pa`.`status` = 'tidak_hadir' then 1 end) AS `absent`,round(count(case when `pa`.`status` = 'hadir' then 1 end) * 100.0 / count(0),2) AS `attendance_rate` from (`schedules` `s` left join `piket_absensi` `pa` on(`pa`.`schedule_id` = `s`.`id`)) where `s`.`shift_date` >= curdate() - interval 90 day group by cast(`s`.`shift_date` as date) order by cast(`s`.`shift_date` as date) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `emergency_task_performance`
--

/*!50001 DROP VIEW IF EXISTS `emergency_task_performance`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `emergency_task_performance` AS select cast(`et`.`start_time` as date) AS `task_date`,count(0) AS `total_tasks`,count(case when `et`.`status` = 'completed' then 1 end) AS `completed`,count(case when `et`.`status` = 'cancelled' then 1 end) AS `cancelled`,avg(timestampdiff(MINUTE,`et`.`start_time`,coalesce(`et`.`end_time`,current_timestamp()))) AS `avg_duration_minutes`,count(case when `et`.`priority_level` = 'critical' then 1 end) AS `critical_tasks`,count(case when `et`.`priority_level` = 'high' then 1 end) AS `high_tasks` from `emergency_tasks` `et` where `et`.`start_time` >= curdate() - interval 90 day group by cast(`et`.`start_time` as date) order by cast(`et`.`start_time` as date) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `monthly_certification_summary`
--

/*!50001 DROP VIEW IF EXISTS `monthly_certification_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `monthly_certification_summary` AS select date_format(`c`.`expiry_date`,'%Y-%m') AS `expiry_month`,count(0) AS `total_certifications`,count(case when `c`.`status` = 'valid' then 1 end) AS `valid`,count(case when `c`.`status` = 'expired' then 1 end) AS `expired`,count(case when `c`.`expiry_date` between curdate() and curdate() + interval 30 day and `c`.`status` = 'valid' then 1 end) AS `expiring_soon` from `certifications` `c` where `c`.`expiry_date` >= curdate() - interval 24 month group by date_format(`c`.`expiry_date`,'%Y-%m') order by date_format(`c`.`expiry_date`,'%Y-%m') desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `notification_dashboard`
--

/*!50001 DROP VIEW IF EXISTS `notification_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `notification_dashboard` AS select cast(`n`.`created_at` as date) AS `notification_date`,`n`.`notification_type` AS `notification_type`,count(0) AS `total_notifications`,count(case when `n`.`status` = 'sent' then 1 end) AS `sent_notifications`,count(case when `n`.`status` = 'delivered' then 1 end) AS `delivered_notifications`,count(case when `n`.`status` = 'read' then 1 end) AS `read_notifications`,count(case when `n`.`status` = 'failed' then 1 end) AS `failed_notifications` from (`notifications` `n` left join `notification_delivery_analytics` `ndl` on(`ndl`.`notification_id` = `n`.`id`)) where `n`.`created_at` >= curdate() - interval 30 day group by cast(`n`.`created_at` as date),`n`.`notification_type` order by cast(`n`.`created_at` as date) desc,`n`.`notification_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `weekly_fatigue_summary`
--

/*!50001 DROP VIEW IF EXISTS `weekly_fatigue_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `weekly_fatigue_summary` AS select yearweek(`ft`.`tracking_date`,0) AS `week_year`,min(`ft`.`tracking_date`) AS `week_start`,max(`ft`.`tracking_date`) AS `week_end`,count(0) AS `total_records`,avg(`ft`.`fatigue_score`) AS `avg_fatigue_score`,count(case when `ft`.`fatigue_level` = 'critical' then 1 end) AS `critical_cases`,count(case when `ft`.`fatigue_level` = 'high' then 1 end) AS `high_cases`,avg(`ft`.`hours_worked`) AS `avg_hours_worked`,avg(`ft`.`rest_hours`) AS `avg_rest_hours` from `fatigue_tracking` `ft` where `ft`.`tracking_date` >= curdate() - interval 52 week group by yearweek(`ft`.`tracking_date`,0) order by yearweek(`ft`.`tracking_date`,0) desc */;
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

-- Dump completed on 2026-04-11 22:19:44
