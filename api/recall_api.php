<?php
/**
 * Emergency Response & Recall System API
 * Manage recall campaigns, emergency notifications, and personnel response tracking
 */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

// CSRF protection for POST (skip read-only GET actions)
$readOnlyActions = [
    'get_recall_campaigns','get_campaign_details','get_recall_responses',
    'get_active_campaigns','get_recall_statistics','get_personil_recall_status'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Recall campaigns list
    if ($action === 'get_recall_campaigns') {
        $status = $_GET['status'] ?? '';
        $campaignType = $_GET['campaign_type'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($status) {
            $whereClause .= " AND rc.status = ?";
            $params[] = $status;
        }
        
        if ($campaignType) {
            $whereClause .= " AND rc.campaign_type = ?";
            $params[] = $campaignType;
        }
        
        $whereClause .= " AND rc.start_time BETWEEN ? AND ?";
        $params[] = $startDate . ' 00:00:00';
        $params[] = $endDate . ' 23:59:59';
        
        $stmt = $pdo->prepare("
            SELECT rc.*, 
                   u.username as creator_name,
                   CASE rc.status
                       WHEN 'draft' THEN 'Draft'
                       WHEN 'active' THEN 'Active'
                       WHEN 'completed' THEN 'Completed'
                       WHEN 'cancelled' THEN 'Cancelled'
                   END as status_label,
                   CASE rc.priority_level
                       WHEN 'low' THEN 'Low'
                       WHEN 'medium' THEN 'Medium'
                       WHEN 'high' THEN 'High'
                       WHEN 'critical' THEN 'Critical'
                   END as priority_label,
                   TIMESTAMPDIFF(MINUTE, rc.start_time, rc.end_time) as duration_minutes,
                   ROUND((rc.total_responded / NULLIF(rc.total_sent, 0)) * 100, 2) as response_rate
            FROM recall_campaigns rc
            LEFT JOIN users u ON u.id = rc.created_by
            $whereClause
            ORDER BY rc.start_time DESC
        ");
        $stmt->execute($params);
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$campaigns]);
        exit;
    }

    // GET: Campaign details with responses
    if ($action === 'get_campaign_details') {
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        
        if (!$campaignId) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID required']); exit;
        }
        
        // Get campaign info
        $stmt = $pdo->prepare("
            SELECT rc.*, u.username as creator_name
            FROM recall_campaigns rc
            LEFT JOIN users u ON u.id = rc.created_by
            WHERE rc.id = ?
        ");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$campaign) {
            echo json_encode(['success'=>false,'error'=>'Campaign not found']); exit;
        }
        
        // Get responses breakdown
        $stmt = $pdo->prepare("
            SELECT 
                rr.response_status,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM recall_responses WHERE campaign_id = ?), 2) as percentage
            FROM recall_responses rr
            WHERE rr.campaign_id = ?
            GROUP BY rr.response_status
        ");
        $stmt->execute([$campaignId, $campaignId]);
        $responseBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get detailed responses
        $stmt = $pdo->prepare("
            SELECT rr.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   TIMESTAMPDIFF(MINUTE, rc.start_time, rr.response_time) as response_time_minutes
            FROM recall_responses rr
            JOIN personil p ON p.nrp = rr.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            JOIN recall_campaigns rc ON rc.id = rr.campaign_id
            WHERE rr.campaign_id = ?
            ORDER BY rr.response_time ASC
        ");
        $stmt->execute([$campaignId]);
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'campaign' => $campaign,
                'response_breakdown' => $responseBreakdown,
                'responses' => $responses
            ]
        ]);
        exit;
    }

    // GET: Recall responses for specific campaign
    if ($action === 'get_recall_responses') {
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        $status = $_GET['status'] ?? '';
        
        if (!$campaignId) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID required']); exit;
        }
        
        $whereClause = "WHERE rr.campaign_id = ?";
        $params = [$campaignId];
        
        if ($status) {
            $whereClause .= " AND rr.response_status = ?";
            $params[] = $status;
        }
        
        $stmt = $pdo->prepare("
            SELECT rr.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   TIMESTAMPDIFF(MINUTE, rc.start_time, rr.response_time) as response_time_minutes
            FROM recall_responses rr
            JOIN personil p ON p.nrp = rr.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            JOIN recall_campaigns rc ON rc.id = rr.campaign_id
            $whereClause
            ORDER BY rr.response_time ASC
        ");
        $stmt->execute($params);
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$responses]);
        exit;
    }

    // GET: Active campaigns
    if ($action === 'get_active_campaigns') {
        $stmt = $pdo->prepare("
            SELECT rc.*, 
                   u.username as creator_name,
                   COUNT(rr.id) as responses_count,
                   COUNT(CASE WHEN rr.response_status = 'confirmed' THEN 1 END) as confirmed_count,
                   ROUND((COUNT(CASE WHEN rr.response_status = 'confirmed' THEN 1 END) / NULLIF(COUNT(rr.id), 0)) * 100, 2) as confirmation_rate
            FROM recall_campaigns rc
            LEFT JOIN users u ON u.id = rc.created_by
            LEFT JOIN recall_responses rr ON rr.campaign_id = rc.id
            WHERE rc.status = 'active'
            GROUP BY rc.id
            ORDER BY rc.priority_level DESC, rc.start_time ASC
        ");
        $stmt->execute();
        $activeCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$activeCampaigns]);
        exit;
    }

    // GET: Recall statistics
    if ($action === 'get_recall_statistics') {
        $period = $_GET['period'] ?? '30'; // days
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        // Overall statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_campaigns,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_campaigns,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_campaigns,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_campaigns,
                SUM(total_sent) as total_notifications_sent,
                SUM(total_responded) as total_responses,
                SUM(total_confirmed) as total_confirmations,
                ROUND(AVG(total_sent), 2) as avg_notifications_per_campaign
            FROM recall_campaigns 
            WHERE start_time BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Campaign type breakdown
        $stmt = $pdo->prepare("
            SELECT 
                campaign_type,
                COUNT(*) as count,
                SUM(total_sent) as total_sent,
                SUM(total_responded) as total_responded,
                ROUND(AVG(total_sent), 2) as avg_sent,
                ROUND((SUM(total_responded) / NULLIF(SUM(total_sent), 0)) * 100, 2) as avg_response_rate
            FROM recall_campaigns 
            WHERE start_time BETWEEN ? AND ?
            GROUP BY campaign_type
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Priority level breakdown
        $stmt = $pdo->prepare("
            SELECT 
                priority_level,
                COUNT(*) as count,
                AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration_minutes
            FROM recall_campaigns 
            WHERE start_time BETWEEN ? AND ?
            AND end_time IS NOT NULL
            GROUP BY priority_level
            ORDER BY priority_level DESC
        ");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $byPriority = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Response time trends
        $stmt = $pdo->prepare("
            SELECT 
                DATE(rc.start_time) as date,
                AVG(TIMESTAMPDIFF(MINUTE, rc.start_time, rr.response_time)) as avg_response_time,
                COUNT(*) as campaign_count
            FROM recall_campaigns rc
            JOIN recall_responses rr ON rr.campaign_id = rc.id
            WHERE rc.start_time BETWEEN ? AND ?
            AND rr.response_time IS NOT NULL
            GROUP BY DATE(rc.start_time)
            ORDER BY date
        ");
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $responseTimeTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'overall_stats' => $overallStats,
                'by_type' => $byType,
                'by_priority' => $byPriority,
                'response_time_trends' => $responseTimeTrends
            ]
        ]);
        exit;
    }

    // GET: Personil recall status
    if ($action === 'get_personil_recall_status') {
        $personilId = trim($_GET['personil_id'] ?? '');
        $days = (int)($_GET['days'] ?? 30);
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        // Get personil info
        $stmt = $pdo->prepare("
            SELECT p.*, pk.nama_pangkat, b.nama_bagian, u.nama_unsur
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE p.nrp = ?
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil) {
            echo json_encode(['success'=>false,'error'=>'Personil not found']); exit;
        }
        
        // Get recall history
        $stmt = $pdo->prepare("
            SELECT rr.*, 
                   rc.campaign_name,
                   rc.campaign_type,
                   rc.priority_level,
                   rc.start_time as campaign_start_time,
                   TIMESTAMPDIFF(MINUTE, rc.start_time, rr.response_time) as response_time_minutes
            FROM recall_responses rr
            JOIN recall_campaigns rc ON rc.id = rr.campaign_id
            WHERE rr.personil_id = ?
            AND rc.start_time >= ?
            ORDER BY rc.start_time DESC
        ");
        $stmt->execute([$personilId, $startDate . ' 00:00:00']);
        $recallHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate statistics
        $totalRecalls = count($recallHistory);
        $respondedRecalls = count(array_filter($recallHistory, fn($r) => $r['response_status'] !== 'pending'));
        $confirmedRecalls = count(array_filter($recallHistory, fn($r) => $r['response_status'] === 'confirmed'));
        $avgResponseTime = $respondedRecalls > 0 ? 
            array_sum(array_column(array_filter($recallHistory, fn($r) => $r['response_time_minutes']), 'response_time_minutes')) / $respondedRecalls : 0;
        
        $statistics = [
            'total_recalls' => $totalRecalls,
            'responded_recalls' => $respondedRecalls,
            'confirmed_recalls' => $confirmedRecalls,
            'response_rate' => $totalRecalls > 0 ? round(($respondedRecalls / $totalRecalls) * 100, 2) : 0,
            'confirmation_rate' => $totalRecalls > 0 ? round(($confirmedRecalls / $totalRecalls) * 100, 2) : 0,
            'avg_response_time_minutes' => round($avgResponseTime, 1)
        ];
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'recall_history' => $recallHistory,
                'statistics' => $statistics
            ]
        ]);
        exit;
    }

    // POST: Create recall campaign
    if ($action === 'create_recall_campaign') {
        $campaignName = trim($_POST['campaign_name'] ?? '');
        $campaignType = $_POST['campaign_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $priorityLevel = $_POST['priority_level'] ?? 'high';
        $targetGroups = $_POST['target_groups'] ?? '[]';
        $messageTemplate = trim($_POST['message_template'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        
        if (!$campaignName || !$campaignType || !$startTime) {
            echo json_encode(['success'=>false,'error'=>'Campaign name, type, and start time required']); exit;
        }
        
        // Validate campaign type
        $validTypes = ['emergency', 'recall', 'standby', 'alert'];
        if (!in_array($campaignType, $validTypes)) {
            echo json_encode(['success'=>false,'error'=>'Invalid campaign type']); exit;
        }
        
        // Validate priority level
        $validPriorities = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priorityLevel, $validPriorities)) {
            echo json_encode(['success'=>false,'error'=>'Invalid priority level']); exit;
        }
        
        // Generate unique campaign code
        $campaignCode = 'RC' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $pdo->beginTransaction();
        
        // Create campaign
        $stmt = $pdo->prepare("
            INSERT INTO recall_campaigns 
            (campaign_code, campaign_name, campaign_type, description, priority_level, 
             target_groups, message_template, start_time, end_time, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)
        ");
        $stmt->execute([
            $campaignCode, $campaignName, $campaignType, $description, $priorityLevel,
            $targetGroups, $messageTemplate, $startTime, $endTime, $_SESSION['username']
        ]);
        
        $campaignId = $pdo->lastInsertId();
        
        $pdo->commit();
        
        ActivityLog::logCreate('recall_campaign', $campaignId, "Created recall campaign: $campaignName ($campaignCode)");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Recall campaign created successfully',
            'campaign_id' => $campaignId,
            'campaign_code' => $campaignCode
        ]);
        exit;
    }

    // POST: Activate recall campaign
    if ($action === 'activate_recall_campaign') {
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        
        if (!$campaignId) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID required']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Get campaign details
        $stmt = $pdo->prepare("SELECT * FROM recall_campaigns WHERE id = ?");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$campaign) {
            echo json_encode(['success'=>false,'error'=>'Campaign not found']); exit;
        }
        
        // Update campaign status
        $stmt = $pdo->prepare("UPDATE recall_campaigns SET status = 'active' WHERE id = ?");
        $stmt->execute([$campaignId]);
        
        // Get target personnel based on target groups
        $targetPersonnel = getTargetPersonnel($pdo, $campaign['target_groups']);
        
        // Create response records for all target personnel
        $responseCount = 0;
        foreach ($targetPersonnel as $personilId) {
            $stmt = $pdo->prepare("
                INSERT INTO recall_responses 
                (campaign_id, personil_id, response_status)
                VALUES (?, ?, 'pending')
                ON DUPLICATE KEY UPDATE response_status = 'pending'
            ");
            $stmt->execute([$campaignId, $personilId]);
            $responseCount++;
        }
        
        // Update campaign totals
        $stmt = $pdo->prepare("
            UPDATE recall_campaigns 
            SET total_sent = ? 
            WHERE id = ?
        ");
        $stmt->execute([$responseCount, $campaignId]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('recall_campaign', $campaignId, "Activated recall campaign: {$campaign['campaign_name']}");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Recall campaign activated successfully',
            'personnel_notified' => $responseCount
        ]);
        exit;
    }

    // POST: Submit recall response
    if ($action === 'submit_recall_response') {
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        $personilId = trim($_POST['personil_id'] ?? '');
        $responseStatus = $_POST['response_status'] ?? '';
        $responseNote = trim($_POST['response_note'] ?? '');
        $etaTime = trim($_POST['eta_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (!$campaignId || !$personilId || !$responseStatus) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID, personnel ID, and response status required']); exit;
        }
        
        $validStatuses = ['pending', 'acknowledged', 'confirmed', 'declined', 'unable'];
        if (!in_array($responseStatus, $validStatuses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid response status']); exit;
        }
        
        // Check if response record exists
        $stmt = $pdo->prepare("
            SELECT id FROM recall_responses 
            WHERE campaign_id = ? AND personil_id = ?
        ");
        $stmt->execute([$campaignId, $personilId]);
        $existingResponse = $stmt->fetch();
        
        if ($existingResponse) {
            // Update existing response
            $stmt = $pdo->prepare("
                UPDATE recall_responses 
                SET response_status = ?, response_time = NOW(), response_note = ?, 
                    eta_time = ?, location = ?, updated_at = NOW()
                WHERE campaign_id = ? AND personil_id = ?
            ");
            $stmt->execute([$responseStatus, $responseNote, $etaTime, $location, $campaignId, $personilId]);
        } else {
            // Create new response
            $stmt = $pdo->prepare("
                INSERT INTO recall_responses 
                (campaign_id, personil_id, response_status, response_time, response_note, eta_time, location)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ");
            $stmt->execute([$campaignId, $personilId, $responseStatus, $responseNote, $etaTime, $location]);
        }
        
        // Update campaign totals
        updateCampaignTotals($pdo, $campaignId);
        
        ActivityLog::logUpdate('recall_response', $campaignId, "Personil $personilId responded: $responseStatus");
        
        echo json_encode(['success'=>true,'message'=>'Recall response submitted successfully']);
        exit;
    }

    // POST: Complete recall campaign
    if ($action === 'complete_recall_campaign') {
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        $completionNote = trim($_POST['completion_note'] ?? '');
        
        if (!$campaignId) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID required']); exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE recall_campaigns 
            SET status = 'completed', end_time = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$campaignId]);
        
        ActivityLog::logUpdate('recall_campaign', $campaignId, "Completed recall campaign");
        
        echo json_encode(['success'=>true,'message'=>'Recall campaign completed successfully']);
        exit;
    }

    // POST: Cancel recall campaign
    if ($action === 'cancel_recall_campaign') {
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        $cancellationReason = trim($_POST['cancellation_reason'] ?? '');
        
        if (!$campaignId) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID required']); exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE recall_campaigns 
            SET status = 'cancelled', end_time = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$campaignId]);
        
        ActivityLog::logUpdate('recall_campaign', $campaignId, "Cancelled recall campaign");
        
        echo json_encode(['success'=>true,'message'=>'Recall campaign cancelled successfully']);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[recall_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

// Helper Functions

function getTargetPersonnel($pdo, $targetGroupsJson) {
    $targetGroups = json_decode($targetGroupsJson, true) ?: [];
    $personnel = [];
    
    if (empty($targetGroups)) {
        // If no specific groups, get all active personnel
        $stmt = $pdo->prepare("SELECT nrp FROM personil WHERE is_active = 1 AND is_deleted = 0");
        $stmt->execute();
        $personnel = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Filter by target groups
        $whereClause = "WHERE is_active = 1 AND is_deleted = 0 AND (";
        $params = [];
        $conditions = [];
        
        foreach ($targetGroups as $group) {
            if (isset($group['type']) && isset($group['id'])) {
                switch ($group['type']) {
                    case 'bagian':
                        $conditions[] = "id_bagian = ?";
                        $params[] = $group['id'];
                        break;
                    case 'unsur':
                        $conditions[] = "id_bagian IN (SELECT id FROM bagian WHERE id_unsur = ?)";
                        $params[] = $group['id'];
                        break;
                    case 'personil':
                        $conditions[] = "nrp = ?";
                        $params[] = $group['id'];
                        break;
                }
            }
        }
        
        if (!empty($conditions)) {
            $whereClause .= implode(' OR ', $conditions) . ")";
            $stmt = $pdo->prepare("SELECT nrp FROM personil $whereClause");
            $stmt->execute($params);
            $personnel = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    
    return $personnel;
}

function updateCampaignTotals($pdo, $campaignId) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_responses,
            COUNT(CASE WHEN response_status = 'confirmed' THEN 1 END) as total_confirmed
        FROM recall_responses 
        WHERE campaign_id = ?
    ");
    $stmt->execute([$campaignId]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        UPDATE recall_campaigns 
        SET total_responded = ?, total_confirmed = ?
        WHERE id = ?
    ");
    $stmt->execute([$totals['total_responses'], $totals['total_confirmed'], $campaignId]);
}
?>
