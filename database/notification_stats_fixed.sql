-- Notification Statistics and Monitoring Tables (Fixed Version)
-- Created: 2026-04-11
-- Purpose: Support notification service monitoring and analytics

-- Create notification statistics table
CREATE TABLE IF NOT EXISTS notification_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_sent INT DEFAULT 0,
    total_delivered INT DEFAULT 0,
    total_read INT DEFAULT 0,
    total_failed INT DEFAULT 0,
    avg_delivery_time DECIMAL(5,2) DEFAULT 0 COMMENT 'Average delivery time in seconds',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date),
    INDEX idx_date (date)
);

-- Create notification templates table
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) UNIQUE NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title_template TEXT NOT NULL,
    message_template TEXT NOT NULL,
    default_priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    default_delivery_methods JSON DEFAULT '["in_app"]',
    action_required BOOLEAN DEFAULT FALSE,
    action_url_template TEXT NULL,
    variables JSON NULL COMMENT 'Template variables description',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_template_type (notification_type),
    INDEX idx_active (is_active)
);

-- Insert default notification templates
INSERT IGNORE INTO notification_templates (template_name, notification_type, title_template, message_template, default_priority, default_delivery_methods, action_required, variables) VALUES
('fatigue_warning', 'fatigue_warning', 'Fatigue Warning', 'Your wellness score is {wellness_score} and fatigue level is {fatigue_level}. Please consider taking rest.', 'high', '["in_app","push"]', true, '{"wellness_score": "Current wellness score", "fatigue_level": "Current fatigue level"}'),
('certification_expiry', 'certification_expiry', 'Certification Expiry Warning', 'Your certification \'{certification_name}\' expires in {days_to_expiry} days on {expiry_date}', 'medium', '["in_app","push","email"]', true, '{"certification_name": "Name of certification", "days_to_expiry": "Days until expiry", "expiry_date": "Expiry date"}'),
('emergency_task', 'emergency_task', 'Emergency Task Assignment', 'You have been assigned to emergency task: {task_name}. Priority: {priority_level}', 'high', '["in_app","push","sms"]', true, '{"task_name": "Name of emergency task", "priority_level": "Task priority"}'),
('recall_alert', 'recall_alert', 'Recall Campaign: {campaign_name}', '{message}', 'high', '["in_app","push","sms"]', true, '{"campaign_name": "Name of recall campaign", "message": "Recall message"}'),
('equipment_due', 'equipment_due', 'Equipment Maintenance Due', 'Equipment \'{equipment_name}\' is due for maintenance in {days_to_maintenance} days', 'medium', '["in_app","push"]', true, '{"equipment_name": "Equipment name", "days_to_maintenance": "Days until maintenance"}'),
('overtime_approval', 'overtime_approval', 'Overtime Approval Required', 'Overtime request for {hours} hours on {date} requires your approval', 'medium', '["in_app","push","email"]', true, '{"hours": "Overtime hours", "date": "Overtime date"}');

-- Create notification preferences table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT TRUE,
    email_enabled BOOLEAN DEFAULT FALSE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    quiet_hours_start TIME DEFAULT '22:00:00',
    quiet_hours_end TIME DEFAULT '06:00:00',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_type (user_id, notification_type),
    INDEX idx_user_id (user_id),
    INDEX idx_notification_type (notification_type)
);

-- Insert default notification preferences for all users
INSERT IGNORE INTO notification_preferences (user_id, notification_type, push_enabled, email_enabled, sms_enabled)
SELECT DISTINCT u.id, nt.notification_type, 
    CASE WHEN nt.notification_type IN ('emergency_task', 'recall_alert', 'fatigue_warning') THEN TRUE ELSE FALSE END,
    CASE WHEN nt.notification_type IN ('certification_expiry', 'overtime_approval') THEN TRUE ELSE FALSE END,
    CASE WHEN nt.notification_type IN ('emergency_task', 'recall_alert') THEN TRUE ELSE FALSE END
FROM users u
CROSS JOIN (SELECT DISTINCT notification_type FROM notification_templates WHERE is_active = TRUE) nt
WHERE u.is_active = 1;

-- Create notification queue table for high-volume processing
CREATE TABLE IF NOT EXISTS notification_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_data JSON NOT NULL,
    priority ENUM('low','medium','high','critical') NOT NULL,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    next_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_priority (status, priority),
    INDEX idx_next_attempt (next_attempt_at),
    INDEX idx_scheduled_at (scheduled_at)
);

-- Create notification delivery analytics table
CREATE TABLE IF NOT EXISTS notification_delivery_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    personil_id VARCHAR(20) NOT NULL,
    delivery_method ENUM('in_app','push','email','sms') NOT NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    delivery_time_ms INT NULL COMMENT 'Delivery time in milliseconds',
    device_type ENUM('mobile','desktop','tablet') NULL,
    platform VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notification_personil (notification_id, personil_id),
    INDEX idx_delivery_method (delivery_method),
    INDEX idx_sent_at (sent_at)
);

-- Add indexes for better performance on existing tables
ALTER TABLE notifications ADD INDEX IF NOT EXISTS idx_target_personil_status (target_personil, status);
ALTER TABLE notifications ADD INDEX IF NOT EXISTS idx_type_priority_created (notification_type, priority_level, created_at);
ALTER TABLE notifications ADD INDEX IF NOT EXISTS idx_status_created (status, created_at);

ALTER TABLE notification_delivery_log ADD INDEX IF NOT EXISTS idx_notification_status (notification_id, delivery_status);
ALTER TABLE notification_delivery_log ADD INDEX IF NOT EXISTS idx_created_status (created_at, delivery_status);

ALTER TABLE mobile_sessions ADD INDEX IF NOT EXISTS idx_user_active (user_id, is_active);
ALTER TABLE mobile_sessions ADD INDEX IF NOT EXISTS idx_last_active (last_active);

-- Create view for notification dashboard
CREATE OR REPLACE VIEW notification_dashboard AS
SELECT 
    DATE(n.created_at) as notification_date,
    n.notification_type,
    COUNT(*) as total_notifications,
    COUNT(CASE WHEN n.status = 'sent' THEN 1 END) as sent_notifications,
    COUNT(CASE WHEN n.status = 'delivered' THEN 1 END) as delivered_notifications,
    COUNT(CASE WHEN n.status = 'read' THEN 1 END) as read_notifications,
    COUNT(CASE WHEN n.status = 'failed' THEN 1 END) as failed_notifications
FROM notifications n
LEFT JOIN notification_delivery_analytics ndl ON ndl.notification_id = n.id
WHERE n.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(n.created_at), n.notification_type
ORDER BY notification_date DESC, n.notification_type;

-- Add system settings for notification service
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('notification_service_enabled', 'true', 'boolean', 'Enable notification service', 'notifications'),
('notification_queue_enabled', 'true', 'boolean', 'Enable notification queue processing', 'notifications'),
('notification_cleanup_days', '90', 'integer', 'Days to keep notification history', 'notifications'),
('notification_max_attempts', '3', 'integer', 'Maximum delivery attempts per notification', 'notifications'),
('notification_batch_size', '100', 'integer', 'Batch size for notification processing', 'notifications'),
('push_notification_enabled', 'true', 'boolean', 'Enable push notifications', 'notifications'),
('email_notifications_enabled', 'true', 'boolean', 'Enable email notifications', 'notifications'),
('sms_notifications_enabled', 'false', 'boolean', 'Enable SMS notifications', 'notifications'),
('notification_quiet_hours_enabled', 'true', 'boolean', 'Enable quiet hours for notifications', 'notifications');

SELECT 'Notification statistics and monitoring tables created successfully!' as status;
