<?php
/**
 * Automated Reporting Service
 * Generates comprehensive reports for SPRIN system
 * Supports PDF, Excel, and scheduled reports
 */

class ReportingService {
    private $pdo;
    private $reportTemplates;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeReportTemplates();
    }
    
    /**
     * Initialize report templates
     */
    private function initializeReportTemplates() {
        $this->reportTemplates = [
            'personnel_summary' => [
                'name' => 'Personnel Summary Report',
                'description' => 'Comprehensive personnel statistics and status',
                'category' => 'personnel',
                'frequency' => 'weekly',
                'format' => ['pdf', 'excel']
            ],
            'attendance_report' => [
                'name' => 'Attendance Report',
                'description' => 'Daily/weekly/monthly attendance statistics',
                'category' => 'attendance',
                'frequency' => 'daily',
                'format' => ['pdf', 'excel']
            ],
            'fatigue_analysis' => [
                'name' => 'Fatigue Analysis Report',
                'description' => 'Fatigue levels and wellness analysis',
                'category' => 'health',
                'frequency' => 'weekly',
                'format' => ['pdf']
            ],
            'certification_compliance' => [
                'name' => 'Certification Compliance Report',
                'description' => 'Certification status and compliance metrics',
                'category' => 'compliance',
                'frequency' => 'monthly',
                'format' => ['pdf', 'excel']
            ],
            'emergency_tasks' => [
                'name' => 'Emergency Tasks Report',
                'description' => 'Emergency task assignments and responses',
                'category' => 'operations',
                'frequency' => 'daily',
                'format' => ['pdf']
            ],
            'equipment_status' => [
                'name' => 'Equipment Status Report',
                'description' => 'Equipment assignments and maintenance status',
                'category' => 'assets',
                'frequency' => 'weekly',
                'format' => ['pdf', 'excel']
            ],
            'overtime_summary' => [
                'name' => 'Overtime Summary Report',
                'description' => 'Overtime hours and compensation summary',
                'category' => 'compensation',
                'frequency' => 'monthly',
                'format' => ['pdf', 'excel']
            ],
            'recall_campaigns' => [
                'name' => 'Recall Campaigns Report',
                'description' => 'Recall campaign effectiveness and response rates',
                'category' => 'operations',
                'frequency' => 'weekly',
                'format' => ['pdf']
            ]
        ];
    }
    
    /**
     * Generate report
     */
    public function generateReport($reportType, $parameters = []) {
        if (!isset($this->reportTemplates[$reportType])) {
            throw new Exception("Invalid report type: $reportType");
        }
        
        $template = $this->reportTemplates[$reportType];
        
        // Get report data
        $data = $this->getReportData($reportType, $parameters);
        
        // Generate report based on format
        $format = $parameters['format'] ?? 'pdf';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDFReport($reportType, $data, $parameters);
            case 'excel':
                return $this->generateExcelReport($reportType, $data, $parameters);
            default:
                throw new Exception("Unsupported format: $format");
        }
    }
    
    /**
     * Get report data
     */
    private function getReportData($reportType, $parameters) {
        switch ($reportType) {
            case 'personnel_summary':
                return $this->getPersonnelSummaryData($parameters);
            case 'attendance_report':
                return $this->getAttendanceReportData($parameters);
            case 'fatigue_analysis':
                return $this->getFatigueAnalysisData($parameters);
            case 'certification_compliance':
                return $this->getCertificationComplianceData($parameters);
            case 'emergency_tasks':
                return $this->getEmergencyTasksData($parameters);
            case 'equipment_status':
                return $this->getEquipmentStatusData($parameters);
            case 'overtime_summary':
                return $this->getOvertimeSummaryData($parameters);
            case 'recall_campaigns':
                return $this->getRecallCampaignsData($parameters);
            default:
                throw new Exception("Report data not available for: $reportType");
        }
    }
    
    /**
     * Personnel Summary Data
     */
    private function getPersonnelSummaryData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-01');
        $endDate = $parameters['end_date'] ?? date('Y-m-t');
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE p.is_deleted = 0";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Basic personnel statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_personnel,
                COUNT(CASE WHEN p.is_active = 1 THEN 1 END) as active_personnel,
                COUNT(CASE WHEN p.JK = 'L' THEN 1 END) as male_personnel,
                COUNT(CASE WHEN p.JK = 'P' THEN 1 END) as female_personnel,
                AVG(YEAR(CURDATE()) - YEAR(p.tanggal_lahir)) as avg_age,
                COUNT(CASE WHEN p.tanggal_masuk >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 1 END) as new_personnel
            FROM personil p
            $whereClause
        ");
        $stmt->execute($params);
        $basicStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Personnel by bagian
        $stmt = $this->pdo->prepare("
            SELECT b.nama_bagian, COUNT(*) as count
            FROM personil p
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY count DESC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Personnel by pangkat
        $stmt = $this->pdo->prepare("
            SELECT pk.nama_pangkat, COUNT(*) as count
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            $whereClause
            GROUP BY pk.id, pk.nama_pangkat
            ORDER BY pk.urutan ASC
        ");
        $stmt->execute($params);
        $byPangkat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Wellness and fatigue summary
        $stmt = $this->pdo->prepare("
            SELECT 
                AVG(p.wellness_score) as avg_wellness,
                COUNT(CASE WHEN p.fatigue_level = 'critical' THEN 1 END) as critical_fatigue,
                COUNT(CASE WHEN p.fatigue_level = 'high' THEN 1 END) as high_fatigue,
                COUNT(CASE WHEN p.fatigue_level = 'medium' THEN 1 END) as medium_fatigue,
                COUNT(CASE WHEN p.fatigue_level = 'low' THEN 1 END) as low_fatigue
            FROM personil p
            $whereClause AND p.is_active = 1
        ");
        $stmt->execute($params);
        $wellnessStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'basic_stats' => $basicStats,
            'by_bagian' => $byBagian,
            'by_pangkat' => $byPangkat,
            'wellness_stats' => $wellnessStats,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Attendance Report Data
     */
    private function getAttendanceReportData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $parameters['end_date'] ?? date('Y-m-d');
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE s.shift_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Attendance statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_scheduled,
                COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN pa.status = 'sakit' THEN 1 END) as sick,
                COUNT(CASE WHEN pa.status = 'ijin' THEN 1 END) as permitted,
                COUNT(CASE WHEN pa.status = 'tidak_hadir' THEN 1 END) as absent,
                ROUND(COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) * 100.0 / COUNT(*), 2) as attendance_rate
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
            $whereClause
        ");
        $stmt->execute($params);
        $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Daily attendance trend
        $stmt = $this->pdo->prepare("
            SELECT 
                s.shift_date,
                COUNT(*) as total_scheduled,
                COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as present,
                ROUND(COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) * 100.0 / COUNT(*), 2) as attendance_rate
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
            $whereClause
            GROUP BY s.shift_date
            ORDER BY s.shift_date
        ");
        $stmt->execute($params);
        $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Attendance by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(*) as total_scheduled,
                COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as present,
                ROUND(COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) * 100.0 / COUNT(*), 2) as attendance_rate
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY attendance_rate DESC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top absentees
        $stmt = $this->pdo->prepare("
            SELECT 
                p.nrp, p.nama, b.nama_bagian,
                COUNT(*) as total_scheduled,
                COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN pa.status IN ('sakit', 'ijin', 'tidak_hadir') THEN 1 END) as absent,
                ROUND(COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) * 100.0 / COUNT(*), 2) as attendance_rate
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
            $whereClause
            GROUP BY p.nrp, p.nama, b.nama_bagian
            HAVING absent > 0
            ORDER BY absent DESC, attendance_rate ASC
            LIMIT 20
        ");
        $stmt->execute($params);
        $topAbsentees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'attendance_stats' => $attendanceStats,
            'daily_trend' => $dailyTrend,
            'by_bagian' => $byBagian,
            'top_absentees' => $topAbsentees,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Fatigue Analysis Data
     */
    private function getFatigueAnalysisData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $parameters['end_date'] ?? date('Y-m-d');
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE ft.tracking_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Fatigue statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                AVG(ft.fatigue_score) as avg_fatigue_score,
                COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
                COUNT(CASE WHEN ft.fatigue_level = 'high' THEN 1 END) as high_cases,
                COUNT(CASE WHEN ft.fatigue_level = 'medium' THEN 1 END) as medium_cases,
                COUNT(CASE WHEN ft.fatigue_level = 'low' THEN 1 END) as low_cases,
                AVG(ft.hours_worked) as avg_hours_worked,
                AVG(ft.rest_hours) as avg_rest_hours
            FROM fatigue_tracking ft
            JOIN personil p ON p.nrp = ft.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $fatigueStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Daily fatigue trend
        $stmt = $this->pdo->prepare("
            SELECT 
                ft.tracking_date,
                AVG(ft.fatigue_score) as avg_score,
                COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
                COUNT(CASE WHEN ft.fatigue_level = 'high' THEN 1 END) as high_cases
            FROM fatigue_tracking ft
            JOIN personil p ON p.nrp = ft.personil_id
            $whereClause
            GROUP BY ft.tracking_date
            ORDER BY ft.tracking_date
        ");
        $stmt->execute($params);
        $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fatigue by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                AVG(ft.fatigue_score) as avg_score,
                COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
                COUNT(*) as total_records
            FROM fatigue_tracking ft
            JOIN personil p ON p.nrp = ft.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY avg_score ASC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Personnel with critical fatigue
        $stmt = $this->pdo->prepare("
            SELECT 
                p.nrp, p.nama, b.nama_bagian,
                AVG(ft.fatigue_score) as avg_score,
                COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_days,
                COUNT(*) as total_days
            FROM fatigue_tracking ft
            JOIN personil p ON p.nrp = ft.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY p.nrp, p.nama, b.nama_bagian
            HAVING critical_days > 0
            ORDER BY critical_days DESC, avg_score ASC
            LIMIT 20
        ");
        $stmt->execute($params);
        $criticalPersonnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'fatigue_stats' => $fatigueStats,
            'daily_trend' => $dailyTrend,
            'by_bagian' => $byBagian,
            'critical_personnel' => $criticalPersonnel,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Certification Compliance Data
     */
    private function getCertificationComplianceData($parameters) {
        $bagianId = $parameters['bagian_id'] ?? null;
        $expiringDays = $parameters['expiring_days'] ?? 90;
        
        $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Compliance statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT p.nrp) as total_personnel,
                COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) as valid_certifications,
                COUNT(DISTINCT CASE WHEN c.status = 'expired' THEN p.nrp END) as expired_certifications,
                COUNT(DISTINCT CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND c.expiry_date >= CURDATE() THEN p.nrp END) as expiring_soon,
                COUNT(DISTINCT CASE WHEN c.expiry_date < CURDATE() THEN p.nrp END) as overdue_certifications
            FROM personil p
            LEFT JOIN certifications c ON c.personil_id = p.nrp
            $whereClause
        ");
        $params[] = $expiringDays;
        $stmt->execute($params);
        $complianceStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Compliance by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(DISTINCT p.nrp) as total_personnel,
                COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) as valid_certifications,
                COUNT(DISTINCT CASE WHEN c.status = 'expired' THEN p.nrp END) as expired_certifications,
                ROUND(COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) * 100.0 / COUNT(DISTINCT p.nrp), 2) as compliance_rate
            FROM personil p
            JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN certifications c ON c.personil_id = p.nrp
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY compliance_rate DESC
        ");
        $stmt->execute(array_slice($params, 0, -1)); // Remove expiringDays parameter
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Expiring certifications
        $stmt = $this->pdo->prepare("
            SELECT 
                c.personil_id, p.nama, b.nama_bagian,
                c.certification_name, c.issuing_authority,
                c.expiry_date, DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND c.status = 'valid'
            " . ($bagianId ? "AND p.id_bagian = ?" : "") . "
            ORDER BY c.expiry_date ASC
        ");
        $expiringParams = [$expiringDays];
        if ($bagianId) $expiringParams[] = $bagianId;
        $stmt->execute($expiringParams);
        $expiringCerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Overdue certifications
        $stmt = $this->pdo->prepare("
            SELECT 
                c.personil_id, p.nama, b.nama_bagian,
                c.certification_name, c.issuing_authority,
                c.expiry_date, DATEDIFF(CURDATE(), c.expiry_date) as days_overdue
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            WHERE c.expiry_date < CURDATE()
            AND c.status = 'expired'
            " . ($bagianId ? "AND p.id_bagian = ?" : "") . "
            ORDER BY days_overdue DESC
        ");
        $overdueParams = [];
        if ($bagianId) $overdueParams[] = $bagianId;
        $stmt->execute($overdueParams);
        $overdueCerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'compliance_stats' => $complianceStats,
            'by_bagian' => $byBagian,
            'expiring_certifications' => $expiringCerts,
            'overdue_certifications' => $overdueCerts,
            'report_period' => [
                'expiring_days' => $expiringDays,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Emergency Tasks Data
     */
    private function getEmergencyTasksData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $parameters['end_date'] ?? date('Y-m-d');
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE et.start_time BETWEEN ? AND ?";
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Task statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                COUNT(CASE WHEN et.status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN et.status = 'cancelled' THEN 1 END) as cancelled,
                COUNT(CASE WHEN et.status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN et.status = 'assigned' THEN 1 END) as assigned,
                COUNT(CASE WHEN et.status = 'in_progress' THEN 1 END) as in_progress,
                AVG(TIMESTAMPDIFF(MINUTE, et.start_time, COALESCE(et.end_time, NOW()))) as avg_duration_minutes
            FROM emergency_tasks et
            JOIN personil p ON p.nrp = et.assigned_to
            $whereClause
        ");
        $stmt->execute($params);
        $taskStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Tasks by priority
        $stmt = $this->pdo->prepare("
            SELECT 
                et.priority_level,
                COUNT(*) as count,
                COUNT(CASE WHEN et.status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN et.status = 'cancelled' THEN 1 END) as cancelled
            FROM emergency_tasks et
            JOIN personil p ON p.nrp = et.assigned_to
            $whereClause
            GROUP BY et.priority_level
        ");
        $stmt->execute($params);
        $byPriority = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tasks by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(*) as total_tasks,
                COUNT(CASE WHEN et.status = 'completed' THEN 1 END) as completed,
                AVG(TIMESTAMPDIFF(MINUTE, et.start_time, COALESCE(et.end_time, NOW()))) as avg_duration_minutes
            FROM emergency_tasks et
            JOIN personil p ON p.nrp = et.assigned_to
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY total_tasks DESC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top task performers
        $stmt = $this->pdo->prepare("
            SELECT 
                p.nrp, p.nama, b.nama_bagian,
                COUNT(*) as total_tasks,
                COUNT(CASE WHEN et.status = 'completed' THEN 1 END) as completed,
                AVG(TIMESTAMPDIFF(MINUTE, et.start_time, COALESCE(et.end_time, NOW()))) as avg_response_time
            FROM emergency_tasks et
            JOIN personil p ON p.nrp = et.assigned_to
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY p.nrp, p.nama, b.nama_bagian
            HAVING total_tasks >= 3
            ORDER BY completed DESC, avg_response_time ASC
            LIMIT 20
        ");
        $stmt->execute($params);
        $topPerformers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'task_stats' => $taskStats,
            'by_priority' => $byPriority,
            'by_bagian' => $byBagian,
            'top_performers' => $topPerformers,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Equipment Status Data
     */
    private function getEquipmentStatusData($parameters) {
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Equipment statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_equipment,
                COUNT(CASE WHEN e.current_status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN e.current_status = 'assigned' THEN 1 END) as assigned,
                COUNT(CASE WHEN e.current_status = 'maintenance' THEN 1 END) as maintenance,
                COUNT(CASE WHEN e.current_status = 'retired' THEN 1 END) as retired,
                COUNT(CASE WHEN e.next_maintenance < CURDATE() THEN 1 END) as maintenance_overdue
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            $whereClause
        ");
        $stmt->execute($params);
        $equipmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Equipment by type
        $stmt = $this->pdo->prepare("
            SELECT 
                e.equipment_type,
                COUNT(*) as total,
                COUNT(CASE WHEN e.current_status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN e.current_status = 'assigned' THEN 1 END) as assigned,
                COUNT(CASE WHEN e.current_status = 'maintenance' THEN 1 END) as maintenance
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            $whereClause
            GROUP BY e.equipment_type
        ");
        $stmt->execute($params);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Equipment by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(*) as total_assigned,
                COUNT(CASE WHEN e.equipment_type = 'weapon' THEN 1 END) as weapons,
                COUNT(CASE WHEN e.equipment_type = 'vehicle' THEN 1 END) as vehicles,
                COUNT(CASE WHEN e.equipment_type = 'radio' THEN 1 END) as radios
            FROM equipment e
            JOIN personil p ON p.nrp = e.current_assignment
            JOIN bagian b ON b.id = p.id_bagian
            WHERE e.current_status = 'assigned'
            " . ($bagianId ? "AND p.id_bagian = ?" : "") . "
            GROUP BY b.id, b.nama_bagian
            ORDER BY total_assigned DESC
        ");
        $assignmentParams = [];
        if ($bagianId) $assignmentParams[] = $bagianId;
        $stmt->execute($assignmentParams);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Maintenance schedule
        $stmt = $this->pdo->prepare("
            SELECT 
                e.equipment_name, e.equipment_type, e.next_maintenance,
                p.nama as assigned_to, b.nama_bagian,
                DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            LEFT JOIN bagian b ON b.id = p.id_bagian
            WHERE e.next_maintenance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND e.current_status != 'retired'
            ORDER BY e.next_maintenance ASC
        ");
        $stmt->execute();
        $upcomingMaintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'equipment_stats' => $equipmentStats,
            'by_type' => $byType,
            'by_bagian' => $byBagian,
            'upcoming_maintenance' => $upcomingMaintenance,
            'report_period' => [
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Overtime Summary Data
     */
    private function getOvertimeSummaryData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-01');
        $endDate = $parameters['end_date'] ?? date('Y-m-t');
        $bagianId = $parameters['bagian_id'] ?? null;
        
        $whereClause = "WHERE or.overtime_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Overtime statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT or.personil_id) as unique_personnel,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(CASE WHEN or.approval_status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN or.approval_status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN or.approval_status = 'rejected' THEN 1 END) as rejected,
                AVG(or.overtime_hours) as avg_hours_per_record,
                AVG(or.total_compensation) as avg_compensation
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $overtimeStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Overtime by bagian
        $stmt = $this->pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(DISTINCT or.personil_id) as unique_personnel,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                AVG(or.overtime_hours) as avg_hours
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY total_hours DESC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top overtime personnel
        $stmt = $this->pdo->prepare("
            SELECT 
                p.nrp, p.nama, b.nama_bagian,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(*) as record_count
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY p.nrp, p.nama, b.nama_bagian
            HAVING total_hours > 0
            ORDER BY total_hours DESC
            LIMIT 20
        ");
        $stmt->execute($params);
        $topPersonnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Overtime by rate type
        $stmt = $this->pdo->prepare("
            SELECT 
                or.overtime_rate,
                COUNT(*) as record_count,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
            GROUP BY or.overtime_rate
        ");
        $stmt->execute($params);
        $byRate = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'overtime_stats' => $overtimeStats,
            'by_bagian' => $byBagian,
            'top_personnel' => $topPersonnel,
            'by_rate' => $byRate,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Recall Campaigns Data
     */
    private function getRecallCampaignsData($parameters) {
        $startDate = $parameters['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $parameters['end_date'] ?? date('Y-m-d');
        
        $whereClause = "WHERE rc.start_time BETWEEN ? AND ?";
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        // Campaign statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_campaigns,
                COUNT(CASE WHEN rc.status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN rc.status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN rc.status = 'cancelled' THEN 1 END) as cancelled,
                SUM(rc.total_sent) as total_sent,
                SUM(rc.total_responded) as total_responded,
                SUM(rc.total_confirmed) as total_confirmed,
                ROUND(AVG(CASE WHEN rc.total_sent > 0 THEN (rc.total_responded / rc.total_sent) * 100 END), 2) as avg_response_rate
            FROM recall_campaigns rc
            $whereClause
        ");
        $stmt->execute($params);
        $campaignStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Campaigns by type
        $stmt = $this->pdo->prepare("
            SELECT 
                rc.campaign_type,
                COUNT(*) as count,
                SUM(rc.total_sent) as total_sent,
                SUM(rc.total_responded) as total_responded,
                ROUND(AVG(CASE WHEN rc.total_sent > 0 THEN (rc.total_responded / rc.total_sent) * 100 END), 2) as avg_response_rate
            FROM recall_campaigns rc
            $whereClause
            GROUP BY rc.campaign_type
        ");
        $stmt->execute($params);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent campaigns
        $stmt = $this->pdo->prepare("
            SELECT 
                rc.*, u.username as creator_name,
                ROUND(CASE WHEN rc.total_sent > 0 THEN (rc.total_responded / rc.total_sent) * 100 END, 2) as response_rate
            FROM recall_campaigns rc
            LEFT JOIN users u ON u.id = rc.created_by
            $whereClause
            ORDER BY rc.start_time DESC
        ");
        $stmt->execute($params);
        $recentCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'campaign_stats' => $campaignStats,
            'by_type' => $byType,
            'recent_campaigns' => $recentCampaigns,
            'report_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Generate PDF Report
     */
    private function generatePDFReport($reportType, $data, $parameters) {
        // This would use a PDF library like TCPDF or DomPDF
        // For now, return a placeholder implementation
        
        $template = $this->reportTemplates[$reportType];
        $filename = $template['name'] . '_' . date('Y-m-d') . '.pdf';
        $filepath = '../file/reports/' . $filename;
        
        // Create reports directory if it doesn't exist
        if (!is_dir('../file/reports')) {
            mkdir('../file/reports', 0777, true);
        }
        
        // Generate PDF content (simplified - would use actual PDF library)
        $content = $this->generatePDFContent($reportType, $data, $parameters);
        file_put_contents($filepath, $content);
        
        // Save report record
        $this->saveReportRecord($reportType, $filename, 'pdf', $parameters);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'format' => 'pdf',
            'size' => filesize($filepath),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate Excel Report
     */
    private function generateExcelReport($reportType, $data, $parameters) {
        // This would use a library like PhpSpreadsheet
        // For now, return a placeholder implementation
        
        $template = $this->reportTemplates[$reportType];
        $filename = $template['name'] . '_' . date('Y-m-d') . '.xlsx';
        $filepath = '../file/reports/' . $filename;
        
        // Create reports directory if it doesn't exist
        if (!is_dir('../file/reports')) {
            mkdir('../file/reports', 0777, true);
        }
        
        // Generate Excel content (simplified - would use actual Excel library)
        $content = $this->generateExcelContent($reportType, $data, $parameters);
        file_put_contents($filepath, $content);
        
        // Save report record
        $this->saveReportRecord($reportType, $filename, 'excel', $parameters);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'format' => 'excel',
            'size' => filesize($filepath),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate PDF content (placeholder)
     */
    private function generatePDFContent($reportType, $data, $parameters) {
        $template = $this->reportTemplates[$reportType];
        
        $content = "<html><head><title>{$template['name']}</title>";
        $content .= "<style>body{font-family:Arial,sans-serif;margin:20px;}";
        $content .= "h1{color:#007bff;}h2{color:#28a745;}";
        $content .= "table{border-collapse:collapse;width:100%;}";
        $content .= "th,td{border:1px solid #ddd;padding:8px;text-align:left;}";
        $content .= "th{background-color:#f2f2f2;}</style></head><body>";
        
        $content .= "<h1>{$template['name']}</h1>";
        $content .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";
        $content .= "<p>Period: {$data['report_period']['start_date']} to {$data['report_period']['end_date']}</p>";
        
        // Add data content based on report type
        $content .= $this->formatDataForPDF($reportType, $data);
        
        $content .= "</body></html>";
        
        return $content;
    }
    
    /**
     * Generate Excel content (placeholder)
     */
    private function generateExcelContent($reportType, $data, $parameters) {
        // Simplified CSV format as placeholder
        $template = $this->reportTemplates[$reportType];
        
        $content = "{$template['name']}\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Period: {$data['report_period']['start_date']} to {$data['report_period']['end_date']}\n\n";
        
        // Add data content based on report type
        $content .= $this->formatDataForExcel($reportType, $data);
        
        return $content;
    }
    
    /**
     * Format data for PDF
     */
    private function formatDataForPDF($reportType, $data) {
        $content = "";
        
        switch ($reportType) {
            case 'personnel_summary':
                $content .= "<h2>Basic Statistics</h2>";
                $content .= "<table>";
                foreach ($data['basic_stats'] as $key => $value) {
                    $content .= "<tr><td>" . ucwords(str_replace('_', ' ', $key)) . "</td><td>$value</td></tr>";
                }
                $content .= "</table>";
                
                $content .= "<h2>Personnel by Bagian</h2>";
                $content .= "<table><tr><th>Bagian</th><th>Count</th></tr>";
                foreach ($data['by_bagian'] as $row) {
                    $content .= "<tr><td>{$row['nama_bagian']}</td><td>{$row['count']}</td></tr>";
                }
                $content .= "</table>";
                break;
                
            // Add other report types as needed
        }
        
        return $content;
    }
    
    /**
     * Format data for Excel
     */
    private function formatDataForExcel($reportType, $data) {
        $content = "";
        
        switch ($reportType) {
            case 'personnel_summary':
                $content .= "Basic Statistics\n";
                foreach ($data['basic_stats'] as $key => $value) {
                    $content .= ucwords(str_replace('_', ' ', $key)) . ",$value\n";
                }
                
                $content .= "\nPersonnel by Bagian\n";
                $content .= "Bagian,Count\n";
                foreach ($data['by_bagian'] as $row) {
                    $content .= "{$row['nama_bagian']},{$row['count']}\n";
                }
                break;
                
            // Add other report types as needed
        }
        
        return $content;
    }
    
    /**
     * Save report record
     */
    private function saveReportRecord($reportType, $filename, $format, $parameters) {
        $stmt = $this->pdo->prepare("
            INSERT INTO generated_reports 
            (report_type, filename, format, parameters, generated_by, generated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $reportType,
            $filename,
            $format,
            json_encode($parameters),
            'system'
        ]);
    }
    
    /**
     * Get available report templates
     */
    public function getReportTemplates() {
        return $this->reportTemplates;
    }
    
    /**
     * Get generated reports
     */
    public function getGeneratedReports($limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT gr.*, u.username as generated_by_name
            FROM generated_reports gr
            LEFT JOIN users u ON u.id = gr.generated_by
            ORDER BY gr.generated_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Schedule automatic reports
     */
    public function scheduleAutomaticReports() {
        $scheduledReports = [];
        
        foreach ($this->reportTemplates as $reportType => $template) {
            if ($template['frequency'] === 'daily' || 
                ($template['frequency'] === 'weekly' && date('N') === 1) || // Monday
                ($template['frequency'] === 'monthly' && date('j') === 1)) { // 1st of month
                
                $parameters = $this->getDefaultParameters($reportType);
                
                foreach ($template['format'] as $format) {
                    $parameters['format'] = $format;
                    
                    try {
                        $result = $this->generateReport($reportType, $parameters);
                        $scheduledReports[] = [
                            'report_type' => $reportType,
                            'format' => $format,
                            'result' => $result,
                            'scheduled_at' => date('Y-m-d H:i:s')
                        ];
                    } catch (Exception $e) {
                        error_log("Failed to generate scheduled report $reportType: " . $e->getMessage());
                    }
                }
            }
        }
        
        return $scheduledReports;
    }
    
    /**
     * Get default parameters for report type
     */
    private function getDefaultParameters($reportType) {
        $defaults = [
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t')
        ];
        
        switch ($reportType) {
            case 'attendance_report':
                $defaults['start_date'] = date('Y-m-d', strtotime('-7 days'));
                $defaults['end_date'] = date('Y-m-d');
                break;
                
            case 'fatigue_analysis':
                $defaults['start_date'] = date('Y-m-d', strtotime('-30 days'));
                $defaults['end_date'] = date('Y-m-d');
                break;
                
            case 'certification_compliance':
                $defaults['expiring_days'] = 90;
                break;
                
            case 'emergency_tasks':
                $defaults['start_date'] = date('Y-m-d', strtotime('-7 days'));
                $defaults['end_date'] = date('Y-m-d');
                break;
                
            case 'recall_campaigns':
                $defaults['start_date'] = date('Y-m-d', strtotime('-30 days'));
                $defaults['end_date'] = date('Y-m-d');
                break;
        }
        
        return $defaults;
    }
}
?>
