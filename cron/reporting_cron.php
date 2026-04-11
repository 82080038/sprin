<?php
/**
 * Automated Reporting Cron Job
 * Runs scheduled report generation and cleanup
 * Should be scheduled to run daily at midnight
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/ReportingService.php';
require_once __DIR__ . '/../core/ActivityLog.php';

// Set execution time limit
set_time_limit(600); // 10 minutes

// Log cron start
error_log('[reporting_cron] Starting automated reporting cron job at ' . date('Y-m-d H:i:s'));

try {
    // Database connection
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if reporting is enabled
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'reporting_enabled'");
    $stmt->execute();
    $reportingEnabled = $stmt->fetchColumn() === 'true';
    
    if (!$reportingEnabled) {
        error_log('[reporting_cron] Reporting is disabled, skipping');
        echo json_encode(['success' => true, 'message' => 'Reporting disabled', 'timestamp' => date('Y-m-d H:i:s')]);
        exit;
    }

    // Initialize reporting service
    $reportingService = new ReportingService($pdo);

    // Process scheduled reports
    $scheduledResults = processScheduledReports($pdo, $reportingService);

    // Process user subscriptions
    $subscriptionResults = processReportSubscriptions($pdo, $reportingService);

    // Cleanup old reports
    $cleanupResult = cleanupOldReports($pdo);

    // Update report statistics
    updateReportStats($pdo);

    // Send email notifications for new reports
    $emailResults = sendReportEmailNotifications($pdo);

    error_log('[reporting_cron] Completed successfully');
    error_log('[reporting_cron] Scheduled reports: ' . json_encode($scheduledResults));
    error_log('[reporting_cron] Subscriptions: ' . json_encode($subscriptionResults));
    error_log('[reporting_cron] Cleanup: ' . json_encode($cleanupResult));
    error_log('[reporting_cron] Email notifications: ' . json_encode($emailResults));

    echo json_encode([
        'success' => true,
        'scheduled_reports' => $scheduledResults,
        'subscriptions' => $subscriptionResults,
        'cleanup' => $cleanupResult,
        'email_notifications' => $emailResults,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('[reporting_cron] Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Process scheduled reports
 */
function processScheduledReports($pdo, $reportingService) {
    $results = [];
    
    // Get due scheduled reports
    $stmt = $pdo->prepare("
        SELECT * FROM scheduled_reports 
        WHERE is_active = TRUE 
        AND next_run <= NOW()
        ORDER BY priority_level DESC, next_run ASC
    ");
    $stmt->execute();
    $scheduledReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($scheduledReports as $scheduledReport) {
        try {
            $parameters = json_decode($scheduledReport['parameters'] ?: '{}', true);
            $parameters['format'] = $scheduledReport['format'];
            
            // Generate report
            $result = $reportingService->generateReport($scheduledReport['report_type'], $parameters);
            
            if ($result['success']) {
                // Update scheduled report
                $nextRun = calculateNextRun($scheduledReport['frequency']);
                
                $stmt = $pdo->prepare("
                    UPDATE scheduled_reports 
                    SET last_run = NOW(), next_run = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nextRun, $scheduledReport['id']]);
                
                // Log access
                logReportAccess($pdo, $result['filename'], $scheduledReport['created_by'], 'generated');
                
                $results[] = [
                    'scheduled_report_id' => $scheduledReport['id'],
                    'report_type' => $scheduledReport['report_type'],
                    'status' => 'success',
                    'filename' => $result['filename'],
                    'next_run' => $nextRun
                ];
                
                error_log("[reporting_cron] Generated scheduled report: {$scheduledReport['report_type']} - {$result['filename']}");
            } else {
                $results[] = [
                    'scheduled_report_id' => $scheduledReport['id'],
                    'report_type' => $scheduledReport['report_type'],
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error'
                ];
            }
            
        } catch (Exception $e) {
            error_log("[reporting_cron] Failed to generate scheduled report {$scheduledReport['id']}: " . $e->getMessage());
            $results[] = [
                'scheduled_report_id' => $scheduledReport['id'],
                'report_type' => $scheduledReport['report_type'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $results;
}

/**
 * Process user report subscriptions
 */
function processReportSubscriptions($pdo, $reportingService) {
    $results = [];
    
    // Get active subscriptions due for processing
    $stmt = $pdo->prepare("
        SELECT rs.*, u.email, u.username
        FROM report_subscriptions rs
        JOIN users u ON u.id = rs.user_id
        WHERE rs.is_active = TRUE
        AND (
            (rs.frequency = 'daily')
            OR (rs.frequency = 'weekly' AND DAYOFWEEK(NOW()) = 1) -- Monday
            OR (rs.frequency = 'monthly' AND DAY(NOW()) = 1)
        )
    ");
    $stmt->execute();
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subscriptions as $subscription) {
        try {
            $parameters = json_decode($subscription['parameters'] ?: '{}', true);
            $parameters['format'] = $subscription['format'];
            
            // Generate report
            $result = $reportingService->generateReport($subscription['report_type'], $parameters);
            
            if ($result['success']) {
                // Handle delivery based on method
                if ($subscription['delivery_method'] === 'email' || $subscription['delivery_method'] === 'both') {
                    $emailSent = sendReportEmail($subscription['email'], $result, $subscription);
                }
                
                $results[] = [
                    'subscription_id' => $subscription['id'],
                    'user_id' => $subscription['user_id'],
                    'report_type' => $subscription['report_type'],
                    'status' => 'success',
                    'filename' => $result['filename'],
                    'email_sent' => $emailSent ?? false
                ];
                
                error_log("[reporting_cron] Generated subscription report for user {$subscription['user_id']}: {$subscription['report_type']}");
            } else {
                $results[] = [
                    'subscription_id' => $subscription['id'],
                    'user_id' => $subscription['user_id'],
                    'report_type' => $subscription['report_type'],
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error'
                ];
            }
            
        } catch (Exception $e) {
            error_log("[reporting_cron] Failed to generate subscription report {$subscription['id']}: " . $e->getMessage());
            $results[] = [
                'subscription_id' => $subscription['id'],
                'user_id' => $subscription['user_id'],
                'report_type' => $subscription['report_type'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $results;
}

/**
 * Cleanup old reports
 */
function cleanupOldReports($pdo) {
    $cleanupResult = [
        'reports_deleted' => 0,
        'access_logs_deleted' => 0,
        'space_freed' => 0
    ];
    
    // Get retention days
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'report_retention_days'");
    $stmt->execute();
    $retentionDays = (int)$stmt->fetchColumn();
    
    if ($retentionDays <= 0) {
        $retentionDays = 90; // Default
    }
    
    // Get expired reports
    $stmt = $pdo->prepare("
        SELECT id, filename, filepath, file_size 
        FROM generated_reports 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        OR (expires_at IS NOT NULL AND expires_at < NOW())
    ");
    $stmt->execute([$retentionDays]);
    $expiredReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($expiredReports as $report) {
        // Delete file
        if (file_exists($report['filepath'])) {
            $cleanupResult['space_freed'] += filesize($report['filepath']);
            unlink($report['filepath']);
        }
        
        // Delete database record
        $stmt = $pdo->prepare("DELETE FROM generated_reports WHERE id = ?");
        $stmt->execute([$report['id']]);
        $cleanupResult['reports_deleted']++;
    }
    
    // Clean up old access logs (older than 90 days)
    $stmt = $pdo->prepare("
        DELETE FROM report_access_log 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    ");
    $stmt->execute();
    $cleanupResult['access_logs_deleted'] = $stmt->rowCount();
    
    return $cleanupResult;
}

/**
 * Update report statistics
 */
function updateReportStats($pdo) {
    $today = date('Y-m-d');
    
    // Update daily report generation stats
    $stmt = $pdo->prepare("
        INSERT INTO report_stats (date, reports_generated, total_size_mb, unique_users)
        SELECT 
            CURDATE() as date,
            COUNT(*) as reports_generated,
            ROUND(SUM(file_size) / 1024 / 1024, 2) as total_size_mb,
            COUNT(DISTINCT generated_by) as unique_users
        FROM generated_reports 
        WHERE DATE(generated_at) = CURDATE()
        ON DUPLICATE KEY UPDATE
        reports_generated = VALUES(reports_generated),
        total_size_mb = VALUES(total_size_mb),
        unique_users = VALUES(unique_users)
    ");
    $stmt->execute();

    // Update system health metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reports,
            SUM(file_size) as total_size,
            COUNT(DISTINCT generated_by) as unique_users,
            COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as reports_this_week
        FROM generated_reports
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("[reporting_cron] Report statistics: " . json_encode($stats));

    return true;
}

/**
 * Send report email notifications
 */
function sendReportEmailNotifications($pdo) {
    $emailResults = [
        'emails_sent' => 0,
        'emails_failed' => 0
    ];
    
    // Check if email reports are enabled
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'email_reports_enabled'");
    $stmt->execute();
    $emailEnabled = $stmt->fetchColumn() === 'true';
    
    if (!$emailEnabled) {
        return $emailResults;
    }
    
    // Get reports generated today that haven't been emailed
    $stmt = $pdo->prepare("
        SELECT gr.*, u.email, u.username
        FROM generated_reports gr
        JOIN users u ON u.id = gr.generated_by
        WHERE DATE(gr.generated_at) = CURDATE()
        AND gr.id NOT IN (
            SELECT DISTINCT report_id FROM report_email_log 
            WHERE DATE(sent_at) = CURDATE()
        )
        AND u.email IS NOT NULL AND u.email != ''
    ");
    $stmt->execute();
    $reportsToEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($reportsToEmail as $report) {
        try {
            $subject = "SPRIN Report Generated: {$report['report_name']}";
            $message = generateReportEmailMessage($report);
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@sprin.polri.go.id\r\n";
            
            $sent = mail($report['email'], $subject, $message, $headers);
            
            if ($sent) {
                // Log email sent
                $stmt = $pdo->prepare("
                    INSERT INTO report_email_log (report_id, user_id, email, sent_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$report['id'], $report['generated_by'], $report['email']]);
                
                $emailResults['emails_sent']++;
            } else {
                $emailResults['emails_failed']++;
            }
            
        } catch (Exception $e) {
            error_log("Failed to send report email: " . $e->getMessage());
            $emailResults['emails_failed']++;
        }
    }
    
    return $emailResults;
}

/**
 * Calculate next run time for scheduled reports
 */
function calculateNextRun($frequency) {
    switch ($frequency) {
        case 'daily':
            return date('Y-m-d H:i:s', strtotime('+1 day midnight'));
        case 'weekly':
            return date('Y-m-d H:i:s', strtotime('next monday midnight'));
        case 'monthly':
            return date('Y-m-d H:i:s', strtotime('first day of next month midnight'));
        case 'quarterly':
            return date('Y-m-d H:i:s', strtotime('first day of next quarter midnight'));
        case 'yearly':
            return date('Y-m-d H:i:s', strtotime('first day of next year midnight'));
        default:
            return date('Y-m-d H:i:s', strtotime('+1 day'));
    }
}

/**
 * Log report access
 */
function logReportAccess($pdo, $filename, $userId, $action) {
    $stmt = $pdo->prepare("
        INSERT INTO report_access_log (report_id, user_id, action, ip_address, user_agent)
        SELECT id, ?, ?, ?, ?
        FROM generated_reports 
        WHERE filename = ?
    ");
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Cron Job',
        $filename
    ]);
}

/**
 * Send report email to user
 */
function sendReportEmail($email, $reportResult, $subscription) {
    $subject = "Your SPRIN Report: {$reportResult['filename']}";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background: #007bff; color: white; padding: 20px; }
            .content { padding: 20px; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>SPRIN - Sistem Personil Polri</h2>
        </div>
        <div class='content'>
            <h3>Your Automated Report</h3>
            <p>Dear {$subscription['username']},</p>
            <p>Your scheduled report has been generated and is ready for download:</p>
            <p><strong>Report:</strong> {$reportResult['filename']}</p>
            <p><strong>Format:</strong> {$reportResult['format']}</p>
            <p><strong>Size:</strong> " . round($reportResult['size'] / 1024 / 1024, 2) . " MB</p>
            <p><strong>Generated:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p>You can download this report from the SPRIN system.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from SPRIN System</p>
            <p>© 2026 Kepolisian Negara Republik Indonesia</p>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@sprin.polri.go.id\r\n";
    
    return mail($email, $subject, $message, $headers);
}

/**
 * Generate report email message
 */
function generateReportEmailMessage($report) {
    $template = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background: #007bff; color: white; padding: 20px; }
            .content { padding: 20px; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>SPRIN - Sistem Personil Polri</h2>
        </div>
        <div class='content'>
            <h3>Report Generated</h3>
            <p>Dear {$report['username']},</p>
            <p>Your report has been generated and is available:</p>
            <p><strong>Report:</strong> {$report['report_name']}</p>
            <p><strong>Type:</strong> {$report['report_type']}</p>
            <p><strong>Format:</strong> {$report['format']}</p>
            <p><strong>Size:</strong> " . round($report['file_size'] / 1024 / 1024, 2) . " MB</p>
            <p><strong>Generated:</strong> {$report['generated_at']}</p>
            <p>Please log in to the SPRIN system to download this report.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from SPRIN System</p>
            <p>© 2026 Kepolisian Negara Republik Indonesia</p>
        </div>
    </body>
    </html>";
    
    return $template;
}
?>
