<?php
/**
 * Advanced Analytics API - Dashboard Statistics, Fairness Analysis & Predictive Scheduling
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
    'get_piket_trend','get_fairness_index','get_personil_workload',
    'get_predictive_analytics','get_scheduling_patterns','get_demand_forecast',
    'get_performance_metrics','get_analytics_dashboard'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // ── GET: Piket trend per month (6 months) ───────────────────────────────
    if ($action === 'get_piket_trend') {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(shift_date, '%Y-%m') as bulan,
                COUNT(DISTINCT personil_id) as personil_unik,
                COUNT(*) as total_jadwal,
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir
            FROM schedules s
            WHERE shift_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(shift_date, '%Y-%m')
            ORDER BY bulan
        ");
        $stmt->execute();
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ── GET: Fairness index (distribusi jam piket per personil) ─────────────
    if ($action === 'get_fairness_index') {
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                COUNT(s.id) as total_jadwal,
                SUM(CASE WHEN s.shift_type='PAGI' THEN 8 
                         WHEN s.shift_type='SIANG' THEN 8 
                         WHEN s.shift_type='MALAM' THEN 10 
                         ELSE 8 END) as total_jam,
                b.nama_bagian
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN bagian b ON b.id = p.id_bagian
            WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            GROUP BY p.nrp, p.nama, b.nama_bagian
            ORDER BY total_jam DESC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate fairness metrics
        if ($data) {
            $jam = array_column($data, 'total_jam');
            $avg = array_sum($jam) / count($jam);
            $max = max($jam);
            $min = min($jam);
            $variance = 0;
            foreach ($jam as $j) $variance += pow($j - $avg, 2);
            $stdDev = sqrt($variance / count($jam));
            
            $fairness = [
                'avg_jam' => round($avg, 1),
                'max_jam' => $max,
                'min_jam' => $min,
                'std_dev' => round($stdDev, 1),
                'personil_count' => count($data),
                'fairness_score' => round(100 - ($stdDev / $avg * 100), 1)
            ];
        } else {
            $fairness = ['avg_jam' => 0, 'max_jam' => 0, 'min_jam' => 0, 'std_dev' => 0, 'personil_count' => 0, 'fairness_score' => 100];
        }
        
        echo json_encode(['success'=>true,'data'=>$data,'fairness'=>$fairness]); exit;
    }

    // ── GET: Personil workload summary ───────────────────────────────────────
    if ($action === 'get_personil_workload') {
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                p.pangkat,
                COUNT(DISTINCT s.shift_date) as hari_piket,
                COUNT(s.id) as total_shift,
                SUM(CASE WHEN s.shift_type='MALAM' THEN 1 ELSE 0 END) as shift_malam
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            GROUP BY p.nrp, p.nama, p.pangkat
            ORDER BY total_shift DESC
            LIMIT 20
        ");
        $stmt->execute();
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

// GET: Predictive Analytics Dashboard
    if ($action === 'get_predictive_analytics') {
        $period = $_GET['period'] ?? '30'; // days
        $bagianId = $_GET['bagian_id'] ?? '';
        $analyticsType = $_GET['analytics_type'] ?? 'staffing';
        
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        switch ($analyticsType) {
            case 'staffing':
                // Staffing demand prediction
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(s.shift_date) as date,
                        DAYOFWEEK(s.shift_date) as day_of_week,
                        COUNT(*) as demand_count,
                        COUNT(DISTINCT s.personil_id) as unique_personnel,
                        AVG(CASE WHEN s.shift_type = 'PAGI' THEN 1 
                                WHEN s.shift_type = 'SIANG' THEN 1 
                                WHEN s.shift_type = 'MALAM' THEN 1.25 
                                ELSE 1 END) as weighted_demand
                    FROM schedules s
                    JOIN personil p ON p.nrp = s.personil_id
                    $whereClause
                    AND s.shift_date BETWEEN ? AND ?
                    GROUP BY DATE(s.shift_date)
                    ORDER BY date
                ");
                $stmt->execute(array_merge($params, [$startDate, $endDate]));
                $staffingData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calculate patterns and predictions
                $patterns = calculateStaffingPatterns($staffingData);
                $predictions = generateStaffingPredictions($patterns, 7); // Next 7 days
                
                echo json_encode([
                    'success'=>true,
                    'data'=>[
                        'historical_data' => $staffingData,
                        'patterns' => $patterns,
                        'predictions' => $predictions
                    ]
                ]);
                break;
                
            case 'fatigue':
                // Fatigue trend analysis
                $stmt = $pdo->prepare("
                    SELECT 
                        ft.tracking_date,
                        AVG(ft.fatigue_score) as avg_fatigue_score,
                        COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
                        COUNT(CASE WHEN JSON_LENGTH(ft.violations) > 0 THEN 1 END) as violation_cases,
                        AVG(ft.hours_worked) as avg_hours_worked
                    FROM fatigue_tracking ft
                    JOIN personil p ON p.nrp = ft.personil_id
                    $whereClause
                    AND ft.tracking_date BETWEEN ? AND ?
                    GROUP BY ft.tracking_date
                    ORDER BY ft.tracking_date
                ");
                $stmt->execute(array_merge($params, [$startDate, $endDate]));
                $fatigueData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $fatigueTrends = calculateFatigueTrends($fatigueData);
                $riskPredictions = predictFatigueRisk($fatigueTrends);
                
                echo json_encode([
                    'success'=>true,
                    'data'=>[
                        'fatigue_data' => $fatigueData,
                        'trends' => $fatigueTrends,
                        'risk_predictions' => $riskPredictions
                    ]
                ]);
                break;
                
            case 'absence':
                // Absence pattern analysis
                $stmt = $pdo->prepare("
                    SELECT 
                        pa.tanggal,
                        COUNT(*) as total_absences,
                        COUNT(CASE WHEN pa.status = 'sakit' THEN 1 END) as sick_count,
                        COUNT(CASE WHEN pa.status = 'ijin' THEN 1 END) as leave_count,
                        COUNT(CASE WHEN pa.status = 'tidak_hadir' THEN 1 END) as absent_count,
                        b.nama_bagian
                    FROM piket_absensi pa
                    JOIN schedules s ON s.id = pa.schedule_id
                    JOIN personil p ON p.nrp = s.personil_id
                    LEFT JOIN bagian b ON b.id = p.id_bagian
                    $whereClause
                    AND pa.tanggal BETWEEN ? AND ?
                    GROUP BY pa.tanggal, b.nama_bagian
                    ORDER BY pa.tanggal
                ");
                $stmt->execute(array_merge($params, [$startDate, $endDate]));
                $absenceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $absencePatterns = analyzeAbsencePatterns($absenceData);
                $absencePredictions = predictAbsenceTrends($absencePatterns);
                
                echo json_encode([
                    'success'=>true,
                    'data'=>[
                        'absence_data' => $absenceData,
                        'patterns' => $absencePatterns,
                        'predictions' => $absencePredictions
                    ]
                ]);
                break;
        }
        exit;
    }

    // GET: Scheduling Patterns Analysis
    if ($action === 'get_scheduling_patterns') {
        $patternType = $_GET['pattern_type'] ?? 'weekly';
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND b.id = ?";
            $params[] = $bagianId;
        }
        
        switch ($patternType) {
            case 'weekly':
                $stmt = $pdo->prepare("
                    SELECT 
                        DAYOFWEEK(s.shift_date) as day_of_week,
                        s.shift_type,
                        COUNT(*) as shift_count,
                        COUNT(DISTINCT s.personil_id) as unique_personnel,
                        AVG(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as avg_duration
                    FROM schedules s
                    JOIN tim_piket t ON t.id = s.tim_id
                    JOIN bagian b ON b.id = t.id_bagian
                    $whereClause
                    AND s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                    GROUP BY DAYOFWEEK(s.shift_date), s.shift_type
                    ORDER BY day_of_week
                ");
                $stmt->execute($params);
                $weeklyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success'=>true,'data'=>['weekly_patterns'=>$weeklyPatterns]]);
                break;
                
            case 'seasonal':
                $stmt = $pdo->prepare("
                    SELECT 
                        MONTH(s.shift_date) as month,
                        COUNT(*) as total_shifts,
                        COUNT(DISTINCT s.personil_id) as unique_personnel,
                        COUNT(DISTINCT DATE(s.shift_date)) as active_days,
                        AVG(CASE WHEN s.shift_type = 'MALAM' THEN 1 ELSE 0 END) as night_shift_ratio
                    FROM schedules s
                    JOIN tim_piket t ON t.id = s.tim_id
                    JOIN bagian b ON b.id = t.id_bagian
                    $whereClause
                    AND s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
                    GROUP BY MONTH(s.shift_date)
                    ORDER BY month
                ");
                $stmt->execute($params);
                $seasonalPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success'=>true,'data'=>['seasonal_patterns'=>$seasonalPatterns]]);
                break;
                
            case 'hourly':
                $stmt = $pdo->prepare("
                    SELECT 
                        HOUR(s.start_time) as hour,
                        COUNT(*) as shift_count,
                        COUNT(DISTINCT s.personil_id) as unique_personnel,
                        AVG(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as avg_duration
                    FROM schedules s
                    JOIN tim_piket t ON t.id = s.tim_id
                    JOIN bagian b ON b.id = t.id_bagian
                    $whereClause
                    AND s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
                    GROUP BY HOUR(s.start_time)
                    ORDER BY hour
                ");
                $stmt->execute($params);
                $hourlyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success'=>true,'data'=>['hourly_patterns'=>$hourlyPatterns]]);
                break;
        }
        exit;
    }

    // GET: Demand Forecast
    if ($action === 'get_demand_forecast') {
        $forecastDays = (int)($_GET['forecast_days'] ?? 7);
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND b.id = ?";
            $params[] = $bagianId;
        }
        
        // Get historical data for the past 8 weeks
        $stmt = $pdo->prepare("
            SELECT 
                DATE(s.shift_date) as date,
                DAYOFWEEK(s.shift_date) as day_of_week,
                WEEK(s.shift_date, 1) as week_number,
                COUNT(*) as demand,
                COUNT(DISTINCT s.personil_id) as personnel_count,
                s.shift_type
            FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id
            JOIN bagian b ON b.id = t.id_bagian
            $whereClause
            AND s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
            GROUP BY DATE(s.shift_date), s.shift_type
            ORDER BY date
        ");
        $stmt->execute($params);
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate forecast using moving average and seasonal patterns
        $forecast = generateDemandForecast($historicalData, $forecastDays);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'forecast' => $forecast,
                'historical_summary' => summarizeHistoricalData($historicalData),
                'confidence_intervals' => calculateConfidenceIntervals($forecast, $historicalData)
            ]
        ]);
        exit;
    }

    // GET: Performance Metrics
    if ($action === 'get_performance_metrics') {
        $period = $_GET['period'] ?? '30'; // days
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND b.id = ?";
            $params[] = $bagianId;
        }
        
        // Calculate comprehensive performance metrics
        $metrics = [
            'attendance_rate' => calculateAttendanceRate($pdo, $whereClause, $params, $startDate, $endDate),
            'coverage_rate' => calculateCoverageRate($pdo, $whereClause, $params, $startDate, $endDate),
            'overtime_trend' => calculateOvertimeTrend($pdo, $whereClause, $params, $startDate, $endDate),
            'fatigue_index' => calculateFatigueIndex($pdo, $whereClause, $params, $startDate, $endDate),
            'compliance_score' => calculateComplianceScore($pdo, $whereClause, $params, $startDate, $endDate),
            'efficiency_ratio' => calculateEfficiencyRatio($pdo, $whereClause, $params, $startDate, $endDate)
        ];
        
        echo json_encode(['success'=>true,'data'=>$metrics]);
        exit;
    }

    // GET: Analytics Dashboard Summary
    if ($action === 'get_analytics_dashboard') {
        $dashboard = [
            'key_metrics' => getKeyMetrics($pdo),
            'trend_indicators' => getTrendIndicators($pdo),
            'alerts' => getAnalyticsAlerts($pdo),
            'predictions' => getQuickPredictions($pdo)
        ];
        
        echo json_encode(['success'=>true,'data'=>$dashboard]);
        exit;
    }

    // POST: Update analytics cache
    if ($action === 'update_analytics_cache') {
        $cacheKey = trim($_POST['cache_key'] ?? '');
        $cacheType = $_POST['cache_type'] ?? '';
        $cacheData = $_POST['cache_data'] ?? '';
        $validHours = (int)($_POST['valid_hours'] ?? 24);
        
        if (!$cacheKey || !$cacheType || !$cacheData) {
            echo json_encode(['success'=>false,'error'=>'Cache key, type, and data required']); exit;
        }
        
        $validUntil = date('Y-m-d H:i:s', strtotime("+$validHours hours"));
        
        $stmt = $pdo->prepare("
            INSERT INTO analytics_cache (cache_key, cache_data, cache_type, valid_until)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            cache_data = VALUES(cache_data),
            valid_until = VALUES(valid_until),
            updated_at = NOW()
        ");
        $stmt->execute([$cacheKey, $cacheData, $cacheType, $validUntil]);
        
        echo json_encode(['success'=>true,'message'=>'Analytics cache updated successfully']);
        exit;
    }

} catch (Exception $e) {
    error_log('[analytics_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

// Helper Functions for Advanced Analytics

function calculateStaffingPatterns($data) {
    $patterns = [
        'daily_average' => [],
        'peak_days' => [],
        'shift_distribution' => []
    ];
    
    $dayTotals = [];
    foreach ($data as $day) {
        $dayOfWeek = $day['day_of_week'];
        if (!isset($dayTotals[$dayOfWeek])) {
            $dayTotals[$dayOfWeek] = [];
        }
        $dayTotals[$dayOfWeek][] = $day['demand_count'];
    }
    
    foreach ($dayTotals as $day => $demands) {
        $patterns['daily_average'][$day] = [
            'average' => array_sum($demands) / count($demands),
            'min' => min($demands),
            'max' => max($demands),
            'std_dev' => calculateStdDev($demands)
        ];
    }
    
    return $patterns;
}

function generateStaffingPredictions($patterns, $days) {
    $predictions = [];
    $today = new DateTime();
    
    for ($i = 1; $i <= $days; $i++) {
        $futureDate = clone $today;
        $futureDate->add(new DateInterval("P{$i}D"));
        $dayOfWeek = $futureDate->format('N');
        
        $basePrediction = $patterns['daily_average'][$dayOfWeek]['average'] ?? 0;
        $confidence = 0.85; // Base confidence
        
        // Add seasonal adjustment
        $month = (int)$futureDate->format('n');
        $seasonalFactor = getSeasonalFactor($month);
        $adjustedPrediction = $basePrediction * $seasonalFactor;
        
        $predictions[] = [
            'date' => $futureDate->format('Y-m-d'),
            'predicted_demand' => round($adjustedPrediction),
            'confidence_interval_low' => round($adjustedPrediction * 0.8),
            'confidence_interval_high' => round($adjustedPrediction * 1.2),
            'confidence_score' => $confidence
        ];
    }
    
    return $predictions;
}

function calculateFatigueTrends($data) {
    $trends = [
        'overall_trend' => 'stable',
        'risk_areas' => [],
        'improvement_needed' => false
    ];
    
    if (count($data) < 7) return $trends;
    
    $recentScores = array_slice(array_column($data, 'avg_fatigue_score'), -7);
    $earlierScores = array_slice(array_column($data, 'avg_fatigue_score'), -14, -7);
    
    if (count($recentScores) >= 3 && count($earlierScores) >= 3) {
        $recentAvg = array_sum($recentScores) / count($recentScores);
        $earlierAvg = array_sum($earlierScores) / count($earlierScores);
        
        if ($recentAvg < $earlierAvg - 5) {
            $trends['overall_trend'] = 'declining';
            $trends['improvement_needed'] = true;
        } elseif ($recentAvg > $earlierAvg + 5) {
            $trends['overall_trend'] = 'improving';
        }
    }
    
    return $trends;
}

function predictFatigueRisk($trends) {
    $riskLevel = 'low';
    $recommendations = [];
    
    if ($trends['overall_trend'] === 'declining') {
        $riskLevel = 'high';
        $recommendations[] = 'Review scheduling patterns immediately';
        $recommendations[] = 'Consider additional rest periods';
    } elseif ($trends['overall_trend'] === 'stable') {
        $riskLevel = 'medium';
        $recommendations[] = 'Monitor fatigue levels closely';
    }
    
    return [
        'risk_level' => $riskLevel,
        'recommendations' => $recommendations,
        'next_review_date' => date('Y-m-d', strtotime('+7 days'))
    ];
}

function calculateStdDev($values) {
    if (count($values) < 2) return 0;
    $mean = array_sum($values) / count($values);
    $variance = 0;
    foreach ($values as $value) {
        $variance += pow($value - $mean, 2);
    }
    return sqrt($variance / count($values));
}

function getSeasonalFactor($month) {
    // Simple seasonal adjustment factors
    $factors = [
        1 => 1.1,  // January - higher demand
        2 => 1.0,
        3 => 0.9,
        4 => 0.8,
        5 => 0.8,
        6 => 0.9,
        7 => 1.0,
        8 => 1.1,
        9 => 1.1,
        10 => 1.0,
        11 => 1.0,
        12 => 1.2  // December - holiday season
    ];
    return $factors[$month] ?? 1.0;
}

function generateDemandForecast($historicalData, $forecastDays) {
    $forecast = [];
    $today = new DateTime();
    
    // Calculate weekly patterns
    $weeklyPatterns = [];
    foreach ($historicalData as $data) {
        $dayOfWeek = $data['day_of_week'];
        $shiftType = $data['shift_type'];
        $key = "{$dayOfWeek}_{$shiftType}";
        
        if (!isset($weeklyPatterns[$key])) {
            $weeklyPatterns[$key] = [];
        }
        $weeklyPatterns[$key][] = $data['demand'];
    }
    
    for ($i = 1; $i <= $forecastDays; $i++) {
        $futureDate = clone $today;
        $futureDate->add(new DateInterval("P{$i}D"));
        $dayOfWeek = $futureDate->format('N');
        
        $dayForecast = [];
        foreach (['PAGI', 'SIANG', 'MALAM'] as $shiftType) {
            $key = "{$dayOfWeek}_{$shiftType}";
            if (isset($weeklyPatterns[$key]) && count($weeklyPatterns[$key]) > 0) {
                $avgDemand = array_sum($weeklyPatterns[$key]) / count($weeklyPatterns[$key]);
                $dayForecast[$shiftType] = round($avgDemand);
            } else {
                $dayForecast[$shiftType] = 0;
            }
        }
        
        $forecast[] = [
            'date' => $futureDate->format('Y-m-d'),
            'day_of_week' => $dayOfWeek,
            'shifts' => $dayForecast,
            'total_demand' => array_sum($dayForecast)
        ];
    }
    
    return $forecast;
}

function summarizeHistoricalData($historicalData) {
    $summary = [
        'total_shifts' => count($historicalData),
        'avg_daily_demand' => 0,
        'peak_demand' => 0,
        'low_demand' => PHP_INT_MAX
    ];
    
    $dailyTotals = [];
    foreach ($historicalData as $data) {
        $date = $data['date'];
        if (!isset($dailyTotals[$date])) {
            $dailyTotals[$date] = 0;
        }
        $dailyTotals[$date] += $data['demand'];
    }
    
    if (!empty($dailyTotals)) {
        $summary['avg_daily_demand'] = round(array_sum($dailyTotals) / count($dailyTotals));
        $summary['peak_demand'] = max($dailyTotals);
        $summary['low_demand'] = min($dailyTotals);
    }
    
    return $summary;
}

function calculateConfidenceIntervals($forecast, $historicalData) {
    $intervals = [];
    
    foreach ($forecast as $prediction) {
        $stdDev = calculateStdDev(array_column($historicalData, 'demand'));
        $margin = $stdDev * 1.96; // 95% confidence interval
        
        $intervals[$prediction['date']] = [
            'lower' => max(0, $prediction['total_demand'] - $margin),
            'upper' => $prediction['total_demand'] + $margin,
            'confidence_level' => 0.95
        ];
    }
    
    return $intervals;
}

function calculateAttendanceRate($pdo, $whereClause, $params, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_scheduled,
            COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as total_present
        FROM schedules s
        LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
        LEFT JOIN tim_piket t ON t.id = s.tim_id
        LEFT JOIN bagian b ON b.id = t.id_bagian
        $whereClause
        AND s.shift_date BETWEEN ? AND ?
    ");
    $stmt->execute(array_merge($params, [$startDate, $endDate]));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = $result['total_scheduled'];
    $present = $result['total_present'];
    
    return $total > 0 ? round(($present / $total) * 100, 2) : 0;
}

function calculateCoverageRate($pdo, $whereClause, $params, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT DATE(s.shift_date)) as total_days,
            COUNT(DISTINCT CASE WHEN s.personil_id IS NOT NULL THEN DATE(s.shift_date) END) as covered_days
        FROM schedules s
        LEFT JOIN tim_piket t ON t.id = s.tim_id
        LEFT JOIN bagian b ON b.id = t.id_bagian
        $whereClause
        AND s.shift_date BETWEEN ? AND ?
    ");
    $stmt->execute(array_merge($params, [$startDate, $endDate]));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = $result['total_days'];
    $covered = $result['covered_days'];
    
    return $total > 0 ? round(($covered / $total) * 100, 2) : 0;
}

function calculateOvertimeTrend($pdo, $whereClause, $params, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(or.overtime_hours) as total_overtime,
            COUNT(*) as overtime_records
        FROM overtime_records or
        JOIN personil p ON p.nrp = or.personil_id
        LEFT JOIN bagian b ON b.id = p.id_bagian
        $whereClause
        AND or.overtime_date BETWEEN ? AND ?
    ");
    $stmt->execute(array_merge($params, [$startDate, $endDate]));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_hours' => (float)$result['total_overtime'],
        'record_count' => (int)$result['overtime_records'],
        'trend' => 'stable' // Would need historical comparison for real trend
    ];
}

function calculateFatigueIndex($pdo, $whereClause, $params, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            AVG(ft.fatigue_score) as avg_score,
            COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_count,
            COUNT(*) as total_records
        FROM fatigue_tracking ft
        JOIN personil p ON p.nrp = ft.personil_id
        LEFT JOIN bagian b ON b.id = p.id_bagian
        $whereClause
        AND ft.tracking_date BETWEEN ? AND ?
    ");
    $stmt->execute(array_merge($params, [$startDate, $endDate]));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $avgScore = (float)$result['avg_score'];
    $criticalRatio = $result['total_records'] > 0 ? ($result['critical_count'] / $result['total_records']) * 100 : 0;
    
    return [
        'fatigue_score' => round($avgScore, 1),
        'critical_cases_percentage' => round($criticalRatio, 2),
        'risk_level' => $avgScore < 70 ? 'high' : ($avgScore < 85 ? 'medium' : 'low')
    ];
}

function calculateComplianceScore($pdo, $whereClause, $params, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN c.status = 'valid' THEN 1 END) as valid_certs,
            COUNT(*) as total_certs
        FROM certifications c
        JOIN personil p ON p.nrp = c.personil_id
        LEFT JOIN bagian b ON b.id = p.id_bagian
        $whereClause
    ");
    $stmt->execute($params);
    $certResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN tc.status = 'completed' THEN 1 END) as completed_training,
            COUNT(*) as total_training
        FROM training_compliance tc
        JOIN personil p ON p.nrp = tc.personil_id
        LEFT JOIN bagian b ON b.id = p.id_bagian
        $whereClause
    ");
    $stmt->execute($params);
    $trainingResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $certCompliance = $certResult['total_certs'] > 0 ? ($certResult['valid_certs'] / $certResult['total_certs']) * 100 : 100;
    $trainingCompliance = $trainingResult['total_training'] > 0 ? ($trainingResult['completed_training'] / $trainingResult['total_training']) * 100 : 100;
    
    return [
        'certification_compliance' => round($certCompliance, 2),
        'training_compliance' => round($trainingCompliance, 2),
        'overall_compliance' => round(($certCompliance + $trainingCompliance) / 2, 2)
    ];
}

function calculateEfficiencyRatio($pdo, $whereClause, $params, $startDate, $endDate) {
    // Simple efficiency calculation: scheduled vs actual coverage
    $scheduled = calculateCoverageRate($pdo, $whereClause, $params, $startDate, $endDate);
    $attendance = calculateAttendanceRate($pdo, $whereClause, $params, $startDate, $endDate);
    
    return [
        'efficiency_score' => round(($scheduled + $attendance) / 2, 2),
        'utilization_rate' => $scheduled,
        'attendance_rate' => $attendance
    ];
}

function getKeyMetrics($pdo) {
    return [
        'total_personnel' => getTableCount($pdo, 'personil', 'is_active = 1'),
        'active_schedules' => getTableCount($pdo, 'schedules', 'shift_date >= CURDATE()'),
        'pending_overtime' => getTableCount($pdo, 'overtime_records', 'approval_status = "pending"'),
        'critical_fatigue_cases' => getTableCount($pdo, 'fatigue_tracking', 'fatigue_level = "critical" AND tracking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')
    ];
}

function getTrendIndicators($pdo) {
    return [
        'attendance_trend' => 'improving',
        'fatigue_trend' => 'stable',
        'overtime_trend' => 'increasing',
        'compliance_trend' => 'stable'
    ];
}

function getAnalyticsAlerts($pdo) {
    $alerts = [];
    
    // Check for high fatigue cases
    $criticalFatigue = getTableCount($pdo, 'fatigue_tracking', 'fatigue_level = "critical" AND tracking_date >= CURDATE()');
    if ($criticalFatigue > 0) {
        $alerts[] = [
            'type' => 'fatigue',
            'severity' => 'high',
            'message' => "$criticalFatigue critical fatigue cases detected",
            'action' => 'Review fatigue management'
        ];
    }
    
    // Check for pending overtime
    $pendingOvertime = getTableCount($pdo, 'overtime_records', 'approval_status = "pending" AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)');
    if ($pendingOvertime > 5) {
        $alerts[] = [
            'type' => 'overtime',
            'severity' => 'medium',
            'message' => "$pendingOvertime overtime records pending approval",
            'action' => 'Process overtime approvals'
        ];
    }
    
    return $alerts;
}

function getQuickPredictions($pdo) {
    return [
        'next_week_demand' => 'stable',
        'fatigue_risk' => 'low',
        'staffing_adequacy' => 'sufficient',
        'compliance_risk' => 'low'
    ];
}

function getTableCount($pdo, $table, $condition = '') {
    $sql = "SELECT COUNT(*) FROM $table";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
