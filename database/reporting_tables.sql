-- Reporting System Tables
-- Created: 2026-04-11
-- Purpose: Support automated reporting system

-- Create generated reports table
CREATE TABLE IF NOT EXISTS generated_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    report_name VARCHAR(200) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    format ENUM('pdf','excel','csv') NOT NULL,
    file_size BIGINT DEFAULT 0,
    parameters JSON NULL,
    generated_by INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL COMMENT 'Auto-delete after this date',
    download_count INT DEFAULT 0,
    last_downloaded TIMESTAMP NULL,
    INDEX idx_report_type (report_type),
    INDEX idx_generated_at (generated_at),
    INDEX idx_generated_by (generated_by),
    INDEX idx_expires_at (expires_at)
);

-- Create scheduled reports table
CREATE TABLE IF NOT EXISTS scheduled_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    report_name VARCHAR(200) NOT NULL,
    frequency ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL,
    format ENUM('pdf','excel','csv') NOT NULL,
    parameters JSON NULL,
    recipients JSON NOT NULL COMMENT 'List of user IDs to receive report',
    is_active BOOLEAN DEFAULT TRUE,
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_frequency (frequency),
    INDEX idx_next_run (next_run),
    INDEX idx_active (is_active)
);

-- Create report subscriptions table
CREATE TABLE IF NOT EXISTS report_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    frequency ENUM('daily','weekly','monthly') NOT NULL,
    format ENUM('pdf','excel','csv') NOT NULL,
    parameters JSON NULL,
    delivery_method ENUM('email','download','both') DEFAULT 'email',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_report (user_id, report_type),
    INDEX idx_user_id (user_id),
    INDEX idx_report_type (report_type)
);

-- Create report access log table
CREATE TABLE IF NOT EXISTS report_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('generated','downloaded','viewed','deleted') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (report_id) REFERENCES generated_reports(id) ON DELETE CASCADE
);

-- Add indexes for better performance on existing tables
ALTER TABLE notifications ADD INDEX IF NOT EXISTS idx_created_at_type (created_at, notification_type);
ALTER TABLE fatigue_tracking ADD INDEX IF NOT EXISTS idx_date_level (tracking_date, fatigue_level);
ALTER TABLE certifications ADD INDEX IF NOT EXISTS idx_expiry_status (expiry_date, status);
ALTER TABLE emergency_tasks ADD INDEX IF NOT EXISTS idx_start_status (start_time, status);
ALTER TABLE equipment ADD INDEX IF NOT EXISTS idx_maintenance_status (next_maintenance, current_status);
ALTER TABLE overtime_records ADD INDEX IF NOT EXISTS idx_date_status (overtime_date, approval_status);
ALTER TABLE recall_campaigns ADD INDEX IF NOT EXISTS idx_start_status (start_time, status);

-- Create views for common reporting queries
CREATE OR REPLACE VIEW daily_attendance_summary AS
SELECT 
    DATE(s.shift_date) as attendance_date,
    COUNT(*) as total_scheduled,
    COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as present,
    COUNT(CASE WHEN pa.status = 'sakit' THEN 1 END) as sick,
    COUNT(CASE WHEN pa.status = 'ijin' THEN 1 END) as permitted,
    COUNT(CASE WHEN pa.status = 'tidak_hadir' THEN 1 END) as absent,
    ROUND(COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) * 100.0 / COUNT(*), 2) as attendance_rate
FROM schedules s
LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY DATE(s.shift_date)
ORDER BY attendance_date DESC;

CREATE OR REPLACE VIEW weekly_fatigue_summary AS
SELECT 
    YEARWEEK(ft.tracking_date) as week_year,
    MIN(ft.tracking_date) as week_start,
    MAX(ft.tracking_date) as week_end,
    COUNT(*) as total_records,
    AVG(ft.fatigue_score) as avg_fatigue_score,
    COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
    COUNT(CASE WHEN ft.fatigue_level = 'high' THEN 1 END) as high_cases,
    AVG(ft.hours_worked) as avg_hours_worked,
    AVG(ft.rest_hours) as avg_rest_hours
FROM fatigue_tracking ft
WHERE ft.tracking_date >= DATE_SUB(CURDATE(), INTERVAL 52 WEEK)
GROUP BY YEARWEEK(ft.tracking_date)
ORDER BY week_year DESC;

CREATE OR REPLACE VIEW monthly_certification_summary AS
SELECT 
    DATE_FORMAT(c.expiry_date, '%Y-%m') as expiry_month,
    COUNT(*) as total_certifications,
    COUNT(CASE WHEN c.status = 'valid' THEN 1 END) as valid,
    COUNT(CASE WHEN c.status = 'expired' THEN 1 END) as expired,
    COUNT(CASE WHEN c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.status = 'valid' THEN 1 END) as expiring_soon
FROM certifications c
WHERE c.expiry_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
GROUP BY DATE_FORMAT(c.expiry_date, '%Y-%m')
ORDER BY expiry_month DESC;

CREATE OR REPLACE VIEW emergency_task_performance AS
SELECT 
    DATE(et.start_time) as task_date,
    COUNT(*) as total_tasks,
    COUNT(CASE WHEN et.status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN et.status = 'cancelled' THEN 1 END) as cancelled,
    AVG(TIMESTAMPDIFF(MINUTE, et.start_time, COALESCE(et.end_time, NOW()))) as avg_duration_minutes,
    COUNT(CASE WHEN et.priority_level = 'critical' THEN 1 END) as critical_tasks,
    COUNT(CASE WHEN et.priority_level = 'high' THEN 1 END) as high_tasks
FROM emergency_tasks et
WHERE et.start_time >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY DATE(et.start_time)
ORDER BY task_date DESC;

-- Add system settings for reporting
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('reporting_enabled', 'true', 'boolean', 'Enable automated reporting system', 'reporting'),
('report_retention_days', '90', 'integer', 'Days to keep generated reports', 'reporting'),
('max_report_size_mb', '50', 'integer', 'Maximum report file size in MB', 'reporting'),
('auto_cleanup_reports', 'true', 'boolean', 'Automatically cleanup old reports', 'reporting'),
('default_report_format', 'pdf', 'string', 'Default report format', 'reporting'),
('email_reports_enabled', 'true', 'boolean', 'Enable email delivery of reports', 'reporting'),
('report_generation_timeout', '300', 'integer', 'Report generation timeout in seconds', 'reporting'),
('concurrent_reports_limit', '5', 'integer', 'Maximum concurrent report generation', 'reporting');

-- Create default scheduled reports
INSERT IGNORE INTO scheduled_reports (report_type, report_name, frequency, format, parameters, recipients, is_active, created_by) VALUES
('attendance_report', 'Daily Attendance Report', 'daily', 'pdf', '{"start_date": "' . date('Y-m-d', strtotime('-7 days')) . '", "end_date": "' . date('Y-m-d') . '"}', '[1]', true, 1),
('fatigue_analysis', 'Weekly Fatigue Analysis', 'weekly', 'pdf', '{"start_date": "' . date('Y-m-d', strtotime('-30 days')) . '", "end_date": "' . date('Y-m-d') . '"}', '[1]', true, 1),
('certification_compliance', 'Monthly Certification Compliance', 'monthly', 'excel', '{"expiring_days": 90}', '[1]', true, 1),
('emergency_tasks', 'Daily Emergency Tasks Report', 'daily', 'pdf', '{"start_date": "' . date('Y-m-d', strtotime('-7 days')) . '", "end_date": "' . date('Y-m-d') . '"}', '[1]', true, 1);

SELECT 'Reporting system tables created successfully!' as status;
