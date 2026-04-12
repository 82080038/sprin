-- Report Email Log Table
-- Created: 2026-04-11
-- Purpose: Track email notifications for reports

CREATE TABLE IF NOT EXISTS report_email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent','failed','bounced') DEFAULT 'sent',
    error_message TEXT NULL,
    INDEX idx_report_id (report_id),
    INDEX idx_user_id (user_id),
    INDEX idx_sent_at (sent_at)
);

-- Report Statistics Table
CREATE TABLE IF NOT EXISTS report_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    reports_generated INT DEFAULT 0,
    total_size_mb DECIMAL(10,2) DEFAULT 0,
    unique_users INT DEFAULT 0,
    downloads_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date)
);

SELECT 'Report email log and statistics tables created successfully!' as status;
