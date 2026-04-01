-- Backup system tables for SPRIN
-- Created: 2026-03-31

-- --------------------------------------------------------
-- Table structure for table `backups`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `backups` (
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

-- --------------------------------------------------------
-- Table structure for table `backup_schedule`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `backup_schedule` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Insert default daily backup schedule
-- --------------------------------------------------------

INSERT INTO `backup_schedule` (`name`, `frequency`, `hour`, `minute`, `backup_type`, `keep_count`, `next_run`) VALUES
('Daily Full Backup', 'daily', 2, 0, 'full', 7, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 2 HOUR);
