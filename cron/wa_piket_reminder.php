<?php
/**
 * Cron Job: WhatsApp Piket Reminder H-1
 * Sends WhatsApp notification to personil 1 day before their piket schedule
 * 
 * Usage: Run this cron daily (e.g., via crontab: 0 20 * * * php /path/to/cron/wa_piket_reminder.php)
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/WANotification.php';

// Check if WA is configured
if (!defined('WA_API_KEY') || empty(WA_API_KEY)) {
    echo "WhatsApp API key not configured. Set WA_API_KEY in config.php.\n";
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get tomorrow's date
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    echo "=== WA Piket Reminder H-1 for $tomorrow ===\n";
    
    // Get all schedules for tomorrow with personil phone numbers
    $stmt = $pdo->prepare("
        SELECT 
            s.id as schedule_id,
            s.personil_id,
            s.shift_type,
            s.location,
            p.nama as personil_name,
            p.no_hp,
            t.nama_tim,
            b.nama_bagian
        FROM schedules s
        JOIN personil p ON p.nrp = s.personil_id
        LEFT JOIN tim_piket t ON t.id = s.tim_id
        LEFT JOIN bagian b ON b.id = p.id_bagian
        WHERE s.shift_date = ?
        ORDER BY s.shift_type, p.nama
    ");
    
    $stmt->execute([$tomorrow]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($schedules)) {
        echo "No piket schedules found for tomorrow ($tomorrow).\n";
        exit;
    }
    
    echo "Found " . count($schedules) . " piket schedules.\n";
    
    $sent = 0;
    $failed = 0;
    $noPhone = 0;
    
    foreach ($schedules as $schedule) {
        $phone = $schedule['no_hp'];
        $name = $schedule['personil_name'];
        $shift = strtoupper($schedule['shift_type']);
        $location = $schedule['location'] ?: 'Markas Polres Samosir';
        
        if (empty($phone)) {
            echo "⚠️  $name has no phone number. Skipping.\n";
            $noPhone++;
            continue;
        }
        
        $result = WANotification::sendPiketReminder($phone, $name, $tomorrow, $shift, $location);
        
        if ($result['success']) {
            echo "✅ Sent to $name ($phone)\n";
            $sent++;
        } else {
            echo "❌ Failed to send to $name ($phone): " . $result['message'] . "\n";
            $failed++;
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Sent: $sent\n";
    echo "Failed: $failed\n";
    echo "No phone: $noPhone\n";
    echo "Total: " . count($schedules) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
