-- Production Features Migration for SPRIN Application (Safe Version)
-- Created: 2026-04-11
-- Purpose: Add production-ready features for emergency task management, fatigue tracking, certifications, etc.

-- ============================================
-- 1. FATIGUE MANAGEMENT SYSTEM
-- ============================================

-- Create fatigue tracking table
CREATE TABLE IF NOT EXISTS fatigue_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personil_id VARCHAR(20) NOT NULL,
    tracking_date DATE NOT NULL,
    hours_worked DECIMAL(5,2) NOT NULL DEFAULT 0,
    rest_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
    fatigue_score INT DEFAULT 100,
    fatigue_level ENUM('low','medium','high','critical') DEFAULT 'low',
    violations JSON NULL COMMENT 'List of fatigue violations',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personil_fatigue (personil_id, tracking_date),
    INDEX idx_fatigue_level (fatigue_level),
    INDEX idx_tracking_date (tracking_date)
);

-- ============================================
-- 2. EMERGENCY TASK ASSIGNMENT SYSTEM
-- ============================================

-- Create emergency tasks table
CREATE TABLE IF NOT EXISTS emergency_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Kode unik task darurat',
    task_name VARCHAR(200) NOT NULL,
    task_type ENUM('urgent','critical','emergency','recall') NOT NULL,
    description TEXT,
    priority_level ENUM('low','medium','high','critical') DEFAULT 'high',
    location VARCHAR(255),
    required_personnel INT DEFAULT 1,
    estimated_duration DECIMAL(4,2) COMMENT 'Durasi dalam jam',
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    status ENUM('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
    created_by VARCHAR(50) NOT NULL,
    assigned_to VARCHAR(20) NULL COMMENT 'NRP personil yang ditugaskan',
    original_schedule_id INT NULL COMMENT 'Jadwal asli yang diganti',
    replacement_reason TEXT NULL COMMENT 'Alasan penggantian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_task_status (status),
    INDEX idx_task_priority (priority_level),
    INDEX idx_start_time (start_time),
    INDEX idx_assigned_to (assigned_to)
);

-- Create task assignment conflicts tracking
CREATE TABLE IF NOT EXISTS task_conflicts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    emergency_task_id INT NOT NULL,
    conflict_type ENUM('overlap','resource','fatigue') NOT NULL,
    resolution_status ENUM('pending','resolved','escalated') DEFAULT 'pending',
    resolution_action TEXT,
    resolved_by VARCHAR(50),
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conflict_status (resolution_status),
    INDEX idx_schedule_id (schedule_id)
);

-- ============================================
-- 3. CERTIFICATION & TRAINING COMPLIANCE
-- ============================================

-- Create certifications table
CREATE TABLE IF NOT EXISTS certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personil_id VARCHAR(20) NOT NULL,
    certification_type VARCHAR(100) NOT NULL,
    certification_name VARCHAR(200) NOT NULL,
    issuing_authority VARCHAR(200),
    certificate_number VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    status ENUM('valid','expired','expiring','suspended') DEFAULT 'valid',
    reminder_sent BOOLEAN DEFAULT FALSE,
    attachment_path VARCHAR(500) COMMENT 'Path to certificate file',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personil_cert (personil_id),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_cert_status (status),
    INDEX idx_cert_type (certification_type)
);

-- Create training compliance table
CREATE TABLE IF NOT EXISTS training_compliance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personil_id VARCHAR(20) NOT NULL,
    training_type VARCHAR(100) NOT NULL,
    training_name VARCHAR(200) NOT NULL,
    provider VARCHAR(200),
    training_date DATE,
    completion_date DATE,
    status ENUM('required','in_progress','completed','expired','failed') DEFAULT 'required',
    hours_completed DECIMAL(4,2) DEFAULT 0,
    required_hours DECIMAL(4,2) DEFAULT 0,
    next_due DATE COMMENT 'Tanggal harus training ulang',
    certificate_required BOOLEAN DEFAULT TRUE,
    completion_certificate_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personil_training (personil_id),
    INDEX idx_training_status (status),
    INDEX idx_next_due (next_due)
);

-- ============================================
-- 4. OVERTIME & COMPENSATION MANAGEMENT
-- ============================================

-- Create overtime records table
CREATE TABLE IF NOT EXISTS overtime_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personil_id VARCHAR(20) NOT NULL,
    schedule_id INT NOT NULL,
    overtime_date DATE NOT NULL,
    regular_hours DECIMAL(4,2) DEFAULT 8.00,
    overtime_hours DECIMAL(4,2) NOT NULL,
    overtime_rate ENUM('regular','holiday','weekend','emergency') DEFAULT 'regular',
    rate_multiplier DECIMAL(3,2) DEFAULT 1.5,
    total_compensation DECIMAL(10,2),
    approval_status ENUM('pending','approved','rejected','processed') DEFAULT 'pending',
    approved_by VARCHAR(50),
    approved_at TIMESTAMP NULL,
    processed_date DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personil_overtime (personil_id, overtime_date),
    INDEX idx_approval_status (approval_status),
    INDEX idx_overtime_date (overtime_date)
);

-- ============================================
-- 5. ADVANCED ANALYTICS & PREDICTIVE SCHEDULING
-- ============================================

-- Create analytics cache table
CREATE TABLE IF NOT EXISTS analytics_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(100) UNIQUE NOT NULL,
    cache_data JSON NOT NULL,
    cache_type ENUM('personnel_stats','scheduling_patterns','fatigue_trends','compliance_metrics') NOT NULL,
    valid_until DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cache_key (cache_key),
    INDEX idx_cache_type (cache_type),
    INDEX idx_valid_until (valid_until)
);

-- Create predictive models table
CREATE TABLE IF NOT EXISTS predictive_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    model_type ENUM('staffing_forecast','fatigue_prediction','absence_prediction') NOT NULL,
    model_version VARCHAR(20) DEFAULT '1.0',
    model_parameters JSON NOT NULL,
    accuracy_score DECIMAL(5,4),
    training_data_period_start DATE,
    training_data_period_end DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_model_type (model_type),
    INDEX idx_model_active (is_active)
);

-- Create scheduling patterns table
CREATE TABLE IF NOT EXISTS scheduling_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_type ENUM('seasonal','weekly','daily','emergency') NOT NULL,
    bagian_id INT NULL,
    unsur_id INT NULL,
    day_of_week TINYINT COMMENT '0-6 (Sunday-Saturday)',
    week_of_year TINYINT COMMENT '1-52',
    month TINYINT COMMENT '1-12',
    hour_of_day TINYINT COMMENT '0-23',
    personnel_demand INT DEFAULT 0,
    historical_data JSON COMMENT 'Historical demand data',
    confidence_score DECIMAL(5,4) DEFAULT 0.5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pattern_type (pattern_type),
    INDEX idx_demand_period (day_of_week, week_of_year, month),
    INDEX idx_bagian_unsur (bagian_id, unsur_id)
);

-- ============================================
-- 6. EMERGENCY RESPONSE & RECALL SYSTEM
-- ============================================

-- Create recall campaigns table
CREATE TABLE IF NOT EXISTS recall_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_code VARCHAR(50) UNIQUE NOT NULL,
    campaign_name VARCHAR(200) NOT NULL,
    campaign_type ENUM('emergency','recall','standby','alert') NOT NULL,
    description TEXT,
    priority_level ENUM('low','medium','high','critical') DEFAULT 'high',
    target_groups JSON COMMENT 'Target personil groups',
    message_template TEXT,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    status ENUM('draft','active','completed','cancelled') DEFAULT 'draft',
    created_by VARCHAR(50) NOT NULL,
    total_sent INT DEFAULT 0,
    total_responded INT DEFAULT 0,
    total_confirmed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaign_status (status),
    INDEX idx_start_time (start_time),
    INDEX idx_campaign_type (campaign_type)
);

-- Create recall responses table
CREATE TABLE IF NOT EXISTS recall_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    personil_id VARCHAR(20) NOT NULL,
    response_status ENUM('pending','acknowledged','confirmed','declined','unable') DEFAULT 'pending',
    response_time TIMESTAMP NULL,
    response_note TEXT,
    eta_time DATETIME NULL COMMENT 'Estimated time of arrival',
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaign_personil (campaign_id, personil_id),
    INDEX idx_response_status (response_status),
    INDEX idx_response_time (response_time)
);

-- ============================================
-- 7. EQUIPMENT & ASSET MANAGEMENT
-- ============================================

-- Create equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_code VARCHAR(50) UNIQUE NOT NULL,
    equipment_name VARCHAR(200) NOT NULL,
    equipment_type ENUM('weapon','vehicle','radio','protective','tools','other') NOT NULL,
    serial_number VARCHAR(100),
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    purchase_date DATE,
    purchase_cost DECIMAL(10,2),
    current_status ENUM('available','assigned','maintenance','retired','lost') DEFAULT 'available',
    current_assignment VARCHAR(20) NULL COMMENT 'Assigned to personil NRP',
    location VARCHAR(255),
    maintenance_schedule VARCHAR(100),
    last_maintenance DATE,
    next_maintenance DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_equipment_status (current_status),
    INDEX idx_current_assignment (current_assignment),
    INDEX idx_next_maintenance (next_maintenance)
);

-- Create equipment assignments table
CREATE TABLE IF NOT EXISTS equipment_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    personil_id VARCHAR(20) NOT NULL,
    assignment_date DATETIME NOT NULL,
    return_date DATETIME NULL,
    assignment_purpose VARCHAR(255),
    condition_assigned VARCHAR(100),
    condition_returned VARCHAR(100),
    status ENUM('active','returned','overdue','lost') DEFAULT 'active',
    notes TEXT,
    assigned_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_personil (equipment_id, personil_id),
    INDEX idx_assignment_status (status),
    INDEX idx_assignment_date (assignment_date)
);

-- ============================================
-- 8. NOTIFICATION SYSTEM ENHANCEMENTS
-- ============================================

-- Create enhanced notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_type ENUM('fatigue_warning','certification_expiry','emergency_task','recall_alert','equipment_due','overtime_approval') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    target_personil VARCHAR(20) NULL COMMENT 'Specific personil target',
    target_group JSON NULL COMMENT 'Target groups (bagian, unsur, etc)',
    priority_level ENUM('low','medium','high','critical') DEFAULT 'medium',
    delivery_methods JSON COMMENT 'Delivery methods: in_app, email, sms, push',
    status ENUM('pending','sent','delivered','read','failed') DEFAULT 'pending',
    scheduled_time DATETIME NULL,
    sent_time TIMESTAMP NULL,
    read_time TIMESTAMP NULL,
    expires_at DATETIME NULL,
    action_required BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500),
    action_deadline DATETIME,
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notification_type (notification_type),
    INDEX idx_target_personil (target_personil),
    INDEX idx_status_priority (status, priority_level),
    INDEX idx_scheduled_time (scheduled_time)
);

-- Create notification delivery log
CREATE TABLE IF NOT EXISTS notification_delivery_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    delivery_method ENUM('in_app','email','sms','push') NOT NULL,
    recipient VARCHAR(100) NOT NULL,
    delivery_status ENUM('pending','sent','delivered','failed','bounced') DEFAULT 'pending',
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_notification_method (notification_id, delivery_method)
);

-- ============================================
-- 9. SYSTEM CONFIGURATION
-- ============================================

-- Create system settings table for production features (skip if exists)
-- CREATE TABLE IF NOT EXISTS system_settings (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     setting_key VARCHAR(100) UNIQUE NOT NULL,
--     setting_value TEXT NOT NULL,
--     setting_type ENUM('boolean','integer','decimal','string','json') NOT NULL,
--     description TEXT,
--     category VARCHAR(50) DEFAULT 'general',
--     is_editable BOOLEAN DEFAULT TRUE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

-- ============================================
-- 10. PERFORMANCE OPTIMIZATION
-- ============================================

-- Add indexes for better performance (only if they don't exist)
ALTER TABLE schedules ADD INDEX IF NOT EXISTS idx_fatigue_risk (fatigue_risk, shift_date);
ALTER TABLE schedules ADD INDEX IF NOT EXISTS idx_personil_date (personil_id, shift_date);
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_wellness_score (wellness_score);
ALTER TABLE operations ADD INDEX IF NOT EXISTS idx_status_dates (status, operation_date, operation_date_end);

-- Add full-text search indexes (only if they don't exist)
ALTER TABLE personil ADD FULLTEXT IF NOT EXISTS (nama, nrp);
ALTER TABLE operations ADD FULLTEXT IF NOT EXISTS (operation_name, location);
ALTER TABLE emergency_tasks ADD FULLTEXT IF NOT EXISTS (task_name, description);

-- ============================================
-- MIGRATION COMPLETE
-- ============================================

-- Update version
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('production_features_version', '1.0.0', 'string', 'Production features migration version', 'system')
ON DUPLICATE KEY UPDATE setting_value = '1.0.0';

SELECT 'Production features migration completed successfully!' as status;
