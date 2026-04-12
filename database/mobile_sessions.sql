-- Mobile Sessions Table for Mobile App API
-- Created: 2026-04-11
-- Purpose: Support mobile app authentication and session management

-- Create mobile sessions table
CREATE TABLE IF NOT EXISTS mobile_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    device_token VARCHAR(255) NULL,
    device_info TEXT NULL,
    app_version VARCHAR(50) DEFAULT '1.0.0',
    platform ENUM('android','ios','web') NOT NULL,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_device_token (device_token),
    INDEX idx_last_active (last_active),
    INDEX idx_expires_at (expires_at)
);

-- Create emergency task responses table (for mobile responses)
CREATE TABLE IF NOT EXISTS emergency_task_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    personil_id VARCHAR(20) NOT NULL,
    response ENUM('acknowledged','confirmed','declined','unable') NOT NULL,
    notes TEXT NULL,
    eta VARCHAR(100) NULL,
    response_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_personil (task_id, personil_id),
    INDEX idx_response_time (response_time),
    FOREIGN KEY (task_id) REFERENCES emergency_tasks(id) ON DELETE CASCADE
);

-- Add mobile-specific columns to existing tables
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS mobile_sent BOOLEAN DEFAULT FALSE;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS mobile_read_time TIMESTAMP NULL;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS push_notification_id VARCHAR(255) NULL;

-- Create mobile notification logs table
CREATE TABLE IF NOT EXISTS mobile_notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    personil_id VARCHAR(20) NOT NULL,
    device_token VARCHAR(255) NOT NULL,
    platform ENUM('android','ios') NOT NULL,
    status ENUM('sent','delivered','read','failed','bounced') DEFAULT 'sent',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    INDEX idx_notification_personil (notification_id, personil_id),
    INDEX idx_device_token (device_token),
    INDEX idx_status (status),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE
);

-- Create mobile app analytics table
CREATE TABLE IF NOT EXISTS mobile_app_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_type ENUM('login','logout','view_schedule','update_attendance','view_tasks','respond_task','view_notifications','mark_read') NOT NULL,
    event_data JSON NULL,
    app_version VARCHAR(50) DEFAULT '1.0.0',
    platform ENUM('android','ios','web') NOT NULL,
    device_info TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_event (user_id, event_type),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
);

-- Create mobile settings table
CREATE TABLE IF NOT EXISTS mobile_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    push_notifications_enabled BOOLEAN DEFAULT TRUE,
    notification_sound BOOLEAN DEFAULT TRUE,
    notification_vibration BOOLEAN DEFAULT TRUE,
    auto_sync_enabled BOOLEAN DEFAULT TRUE,
    sync_interval_minutes INT DEFAULT 30,
    theme ENUM('light','dark','auto') DEFAULT 'auto',
    language VARCHAR(10) DEFAULT 'id',
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    biometric_enabled BOOLEAN DEFAULT FALSE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);

-- Insert default mobile settings for existing users
INSERT IGNORE INTO mobile_settings (user_id, push_notifications_enabled)
SELECT DISTINCT u.id, 1
FROM users u
WHERE u.is_active = 1
AND u.id NOT IN (SELECT user_id FROM mobile_settings);

-- Create indexes for better performance
ALTER TABLE emergency_tasks ADD INDEX IF NOT EXISTS idx_assigned_to_status (assigned_to, status);
ALTER TABLE recall_campaigns ADD INDEX IF NOT EXISTS idx_status_created (status, start_time);
ALTER TABLE fatigue_tracking ADD INDEX IF NOT EXISTS idx_personnel_date_score (personil_id, tracking_date, fatigue_score);

-- Add mobile-specific system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('mobile_api_version', '1.0.0', 'string', 'Current mobile API version', 'mobile'),
('mobile_session_timeout_hours', '24', 'integer', 'Mobile session timeout in hours', 'mobile'),
('mobile_rate_limit_per_minute', '100', 'integer', 'Mobile API rate limit per minute', 'mobile'),
('push_notification_enabled', 'true', 'boolean', 'Enable push notifications', 'mobile'),
('mobile_app_min_version', '1.0.0', 'string', 'Minimum supported mobile app version', 'mobile');

SELECT 'Mobile sessions and related tables created successfully!' as status;
