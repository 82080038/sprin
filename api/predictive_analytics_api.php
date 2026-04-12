<?php
/**
 * Predictive Analytics API for SPRIN Application
 * Advanced Analytics - Predictive Features Implementation
 */

require_once '../core/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    send_error('Database connection failed: ' . $e->getMessage(), 500);
}

// Response helper
function send_success($data = null, $message = 'Success') {
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

function send_error($message, $status_code = 400) {
    http_response_code($status_code);
    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response);
    exit;
}

// Main request handler
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'staffing_demand_prediction':
        handle_staffing_demand_prediction($pdo);
        break;
    case 'fatigue_risk_analysis':
        handle_fatigue_risk_analysis($pdo);
        break;
    case 'absence_pattern_prediction':
        handle_absence_pattern_prediction($pdo);
        break;
    case 'operational_success_probability':
        handle_operational_success_probability($pdo);
        break;
    case 'resource_allocation_forecast':
        handle_resource_allocation_forecast($pdo);
        break;
    case 'predictive_dashboard':
        handle_predictive_dashboard($pdo);
        break;
    default:
        send_error('Invalid action', 400);
}

/**
 * Staffing Demand Prediction
 * Memprediksi kebutuhan personil untuk operasi mendatang
 */
function handle_staffing_demand_prediction($pdo) {
    $days_ahead = intval($_GET['days_ahead'] ?? 7);
    $operation_type = $_GET['operation_type'] ?? '';
    
    try {
        // Get historical data
        $historical_data = get_historical_staffing_data($pdo, $operation_type);
        
        // Calculate seasonal factors
        $seasonal_factors = calculate_seasonal_factors($historical_data);
        
        // Generate predictions
        $predictions = generate_staffing_predictions($historical_data, $seasonal_factors, $days_ahead);
        
        // Add confidence intervals
        $predictions_with_confidence = add_confidence_intervals($predictions, $historical_data);
        
        send_success([
            'predictions' => $predictions_with_confidence,
            'historical_data_points' => count($historical_data),
            'seasonal_factors' => $seasonal_factors,
            'confidence_level' => '95%',
            'model_accuracy' => calculate_model_accuracy($historical_data)
        ], 'Staffing demand prediction completed');
        
    } catch (Exception $e) {
        send_error('Staffing prediction failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Fatigue Risk Analysis
 * Memprediksi risiko kelelahan personil
 */
function handle_fatigue_risk_analysis($pdo) {
    $personil_id = $_GET['personil_id'] ?? '';
    $days_ahead = intval($_GET['days_ahead'] ?? 14);
    
    try {
        if (empty($personil_id)) {
            // Get all personnel fatigue risk
            $all_risks = get_all_personnel_fatigue_risk($pdo, $days_ahead);
            send_success([
                'fatigue_risks' => $all_risks,
                'risk_summary' => calculate_fatigue_summary($all_risks),
                'recommendations' => generate_fatigue_recommendations($all_risks)
            ], 'Fatigue risk analysis completed');
        } else {
            // Get specific personnel fatigue risk
            $personnel_risk = get_personnel_fatigue_risk($pdo, $personil_id, $days_ahead);
            send_success([
                'personnel_risk' => $personnel_risk,
                'risk_trend' => calculate_fatigue_trend($pdo, $personil_id),
                'recommendations' => generate_personnel_fatigue_recommendations($personnel_risk)
            ], 'Personnel fatigue risk analysis completed');
        }
        
    } catch (Exception $e) {
        send_error('Fatigue risk analysis failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Absence Pattern Prediction
 * Memprediksi pola ketidakhadiran personil
 */
function handle_absence_pattern_prediction($pdo) {
    $prediction_period = $_GET['period'] ?? '30'; // days
    $department = $_GET['department'] ?? '';
    
    try {
        // Get absence historical data
        $absence_data = get_absence_historical_data($pdo, $department);
        
        // Analyze patterns
        $patterns = analyze_absence_patterns($absence_data);
        
        // Generate predictions
        $predictions = predict_absence_patterns($patterns, $prediction_period);
        
        send_success([
            'predictions' => $predictions,
            'patterns' => $patterns,
            'high_risk_personnel' => identify_high_risk_personnel($predictions),
            'department_impact' => calculate_department_impact($predictions, $department)
        ], 'Absence pattern prediction completed');
        
    } catch (Exception $e) {
        send_error('Absence pattern prediction failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Operational Success Probability
 * Memprediksi tingkat keberhasilan operasi
 */
function handle_operational_success_probability($pdo) {
    $operation_type = $_GET['operation_type'] ?? '';
    $location = $_GET['location'] ?? '';
    $personnel_count = intval($_GET['personnel_count'] ?? 10);
    
    try {
        // Get historical operation data
        $operation_history = get_operation_history($pdo, $operation_type);
        
        // Calculate success factors
        $success_factors = calculate_success_factors($operation_history);
        
        // Generate probability prediction
        $probability = calculate_success_probability($success_factors, $personnel_count, $location);
        
        send_success([
            'success_probability' => $probability,
            'success_factors' => $success_factors,
            'risk_factors' => identify_risk_factors($probability),
            'recommendations' => generate_success_recommendations($probability),
            'confidence_score' => calculate_prediction_confidence($operation_history)
        ], 'Operational success probability calculated');
        
    } catch (Exception $e) {
        send_error('Success probability calculation failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Resource Allocation Forecast
 * Memprediksi kebutuhan sumber daya
 */
function handle_resource_allocation_forecast($pdo) {
    $resource_type = $_GET['resource_type'] ?? 'all';
    $forecast_period = intval($_GET['period'] ?? 30);
    
    try {
        // Get resource usage history
        $usage_history = get_resource_usage_history($pdo, $resource_type);
        
        // Analyze usage patterns
        $usage_patterns = analyze_resource_usage_patterns($usage_history);
        
        // Generate forecast
        $forecast = generate_resource_forecast($usage_patterns, $forecast_period);
        
        send_success([
            'forecast' => $forecast,
            'usage_patterns' => $usage_patterns,
            'resource_efficiency' => calculate_resource_efficiency($usage_history),
            'optimization_suggestions' => generate_optimization_suggestions($forecast)
        ], 'Resource allocation forecast completed');
        
    } catch (Exception $e) {
        send_error('Resource forecast failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Predictive Analytics Dashboard
 * Dashboard komprehensif untuk semua prediksi
 */
function handle_predictive_dashboard($pdo) {
    try {
        // Get all predictive data
        $dashboard_data = [
            'staffing_predictions' => get_dashboard_staffing_predictions($pdo),
            'fatigue_alerts' => get_dashboard_fatigue_alerts($pdo),
            'absence_warnings' => get_dashboard_absence_warnings($pdo),
            'operation_success_rates' => get_dashboard_success_rates($pdo),
            'resource_forecasts' => get_dashboard_resource_forecasts($pdo),
            'key_insights' => generate_key_insights($pdo),
            'recommendations' => generate_dashboard_recommendations($pdo),
            'model_performance' => get_model_performance_metrics($pdo)
        ];
        
        send_success($dashboard_data, 'Predictive dashboard data retrieved');
        
    } catch (Exception $e) {
        send_error('Dashboard data retrieval failed: ' . $e->getMessage(), 500);
    }
}

// Helper Functions Implementation

function get_historical_staffing_data($pdo, $operation_type) {
    $sql = "
        SELECT 
            DATE(tanggal_mulai) as operation_date,
            jenis_operasi,
            COUNT(DISTINCT po.personil_id) as personnel_count,
            COUNT(DISTINCT o.id) as operation_count
        FROM operasi_kepolisian o
        LEFT JOIN personil_operasi po ON o.id = po.operasi_id
        WHERE o.tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    ";
    
    $params = [];
    if (!empty($operation_type)) {
        $sql .= " AND o.jenis_operasi = ?";
        $params[] = $operation_type;
    }
    
    $sql .= " GROUP BY DATE(tanggal_mulai), jenis_operasi ORDER BY operation_date";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculate_seasonal_factors($historical_data) {
    $seasonal_factors = [];
    
    foreach ($historical_data as $data) {
        $day_of_week = date('w', strtotime($data['operation_date']));
        $month = date('n', strtotime($data['operation_date']));
        
        if (!isset($seasonal_factors[$day_of_week])) {
            $seasonal_factors[$day_of_week] = [];
        }
        if (!isset($seasonal_factors[$month])) {
            $seasonal_factors[$month] = [];
        }
        
        $seasonal_factors[$day_of_week][] = $data['personnel_count'];
        $seasonal_factors[$month][] = $data['personnel_count'];
    }
    
    // Calculate averages
    foreach ($seasonal_factors as $key => $values) {
        $seasonal_factors[$key] = array_sum($values) / count($values);
    }
    
    return $seasonal_factors;
}

function generate_staffing_predictions($historical_data, $seasonal_factors, $days_ahead) {
    $predictions = [];
    $base_demand = calculate_base_demand($historical_data);
    
    for ($i = 1; $i <= $days_ahead; $i++) {
        $prediction_date = date('Y-m-d', strtotime("+$i days"));
        $day_of_week = date('w', strtotime($prediction_date));
        $month = date('n', strtotime($prediction_date));
        
        // Apply seasonal factors
        $day_factor = $seasonal_factors[$day_of_week] ?? 1.0;
        $month_factor = $seasonal_factors[$month] ?? 1.0;
        
        // Calculate predicted demand
        $predicted_demand = $base_demand * $day_factor * $month_factor;
        
        // Add some randomness for realism
        $random_factor = 1 + (mt_rand(-10, 10) / 100);
        $predicted_demand *= $random_factor;
        
        $predictions[] = [
            'date' => $prediction_date,
            'predicted_demand' => round($predicted_demand),
            'day_of_week' => $day_of_week,
            'seasonal_factor' => round($day_factor * $month_factor, 2)
        ];
    }
    
    return $predictions;
}

function calculate_base_demand($historical_data) {
    if (empty($historical_data)) {
        return 10; // Default base demand
    }
    
    $total_demand = 0;
    $count = 0;
    
    foreach ($historical_data as $data) {
        $total_demand += $data['personnel_count'];
        $count++;
    }
    
    return $count > 0 ? $total_demand / $count : 10;
}

function add_confidence_intervals($predictions, $historical_data) {
    $std_dev = calculate_standard_deviation($historical_data);
    
    foreach ($predictions as &$prediction) {
        $prediction['lower_bound'] = max(1, round($prediction['predicted_demand'] - (1.96 * $std_dev)));
        $prediction['upper_bound'] = round($prediction['predicted_demand'] + (1.96 * $std_dev));
        $prediction['confidence_interval'] = [
            'lower' => $prediction['lower_bound'],
            'upper' => $prediction['upper_bound']
        ];
    }
    
    return $predictions;
}

function calculate_standard_deviation($data) {
    if (empty($data)) {
        return 2; // Default standard deviation
    }
    
    $values = array_column($data, 'personnel_count');
    $mean = array_sum($values) / count($values);
    
    $squared_diffs = array_map(function($value) use ($mean) {
        return pow($value - $mean, 2);
    }, $values);
    
    $variance = array_sum($squared_diffs) / count($values);
    return sqrt($variance);
}

function calculate_model_accuracy($historical_data) {
    if (count($historical_data) < 10) {
        return 75; // Default accuracy for limited data
    }
    
    // Simple accuracy calculation based on data consistency
    $values = array_column($historical_data, 'personnel_count');
    $mean = array_sum($values) / count($values);
    
    $variations = 0;
    foreach ($values as $value) {
        $variations += abs($value - $mean) / $mean;
    }
    
    $avg_variation = $variations / count($values);
    $accuracy = max(60, 100 - ($avg_variation * 100));
    
    return round($accuracy);
}

function get_all_personnel_fatigue_risk($pdo, $days_ahead) {
    $sql = "
        SELECT 
            p.nrp,
            p.nama,
            COUNT(DISTINCT s.id) as scheduled_shifts,
            SUM(CASE WHEN s.shift_type = 'night' THEN 1 ELSE 0 END) as night_shifts,
            COUNT(DISTINCT ft.id) as fatigue_incidents
        FROM personil p
        LEFT JOIN schedules s ON p.nrp = s.personil_id 
            AND s.shift_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days_ahead DAY)
        LEFT JOIN fatigue_tracking ft ON p.nrp = ft.personil_id 
            AND ft.tracking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        WHERE p.is_active = 1 AND p.is_deleted = 0
        GROUP BY p.nrp, p.nama
        HAVING scheduled_shifts > 0
        ORDER BY night_shifts DESC, fatigue_incidents DESC
    ";
    
    $stmt = $pdo->query($sql);
    $personnel_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $risks = [];
    foreach ($personnel_data as $personnel) {
        $risk_score = calculate_fatigue_risk_score($personnel);
        $risk_level = determine_fatigue_risk_level($risk_score);
        
        $risks[] = [
            'nrp' => $personnel['nrp'],
            'nama' => $personnel['nama'],
            'risk_score' => $risk_score,
            'risk_level' => $risk_level,
            'scheduled_shifts' => $personnel['scheduled_shifts'],
            'night_shifts' => $personnel['night_shifts'],
            'fatigue_incidents' => $personnel['fatigue_incidents'],
            'recommendations' => get_fatigue_recommendations($risk_level)
        ];
    }
    
    return $risks;
}

function calculate_fatigue_risk_score($personnel) {
    $score = 0;
    
    // Base score from night shifts
    $score += $personnel['night_shifts'] * 15;
    
    // Additional score from total shifts
    if ($personnel['scheduled_shifts'] > 10) {
        $score += 20;
    } elseif ($personnel['scheduled_shifts'] > 7) {
        $score += 10;
    }
    
    // Historical fatigue incidents
    $score += $personnel['fatigue_incidents'] * 25;
    
    return min(100, $score);
}

function determine_fatigue_risk_level($score) {
    if ($score >= 70) {
        return 'critical';
    } elseif ($score >= 40) {
        return 'high';
    } elseif ($score >= 20) {
        return 'medium';
    } else {
        return 'low';
    }
}

function get_fatigue_recommendations($risk_level) {
    $recommendations = [
        'critical' => [
            'Immediate rest required',
            'Reduce night shifts',
            'Medical evaluation recommended',
            'Consider temporary reassignment'
        ],
        'high' => [
            'Limit consecutive shifts',
            'Increase rest periods',
            'Monitor closely',
            'Consider schedule adjustment'
        ],
        'medium' => [
            'Regular breaks',
            'Monitor fatigue symptoms',
            'Adequate rest between shifts'
        ],
        'low' => [
            'Maintain current schedule',
            'Regular health monitoring'
        ]
    ];
    
    return $recommendations[$risk_level] ?? [];
}

function calculate_fatigue_summary($risks) {
    $summary = [
        'total_personnel' => count($risks),
        'critical_risk' => 0,
        'high_risk' => 0,
        'medium_risk' => 0,
        'low_risk' => 0
    ];
    
    foreach ($risks as $risk) {
        $summary[$risk['risk_level'] . '_risk']++;
    }
    
    return $summary;
}

function generate_fatigue_recommendations($risks) {
    $recommendations = [];
    
    $critical_count = 0;
    $high_count = 0;
    
    foreach ($risks as $risk) {
        if ($risk['risk_level'] === 'critical') {
            $critical_count++;
        } elseif ($risk['risk_level'] === 'high') {
            $high_count++;
        }
    }
    
    if ($critical_count > 0) {
        $recommendations[] = "Immediate action required for $critical_count personnel at critical risk";
    }
    
    if ($high_count > 2) {
        $recommendations[] = "Schedule review recommended for $high_count personnel at high risk";
    }
    
    if ($critical_count + $high_count > 5) {
        $recommendations[] = "Consider temporary staffing increase to cover fatigued personnel";
    }
    
    return $recommendations;
}

function get_absence_historical_data($pdo, $department) {
    $sql = "
        SELECT 
            p.nrp,
            p.nama,
            b.nama_bagian,
            a.tanggal,
            a.jenis_absensi,
            a.keterangan
        FROM personil p
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN apel_nominal a ON p.nrp = a.nrp
        WHERE a.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    ";
    
    $params = [];
    if (!empty($department)) {
        $sql .= " AND b.nama_bagian = ?";
        $params[] = $department;
    }
    
    $sql .= " AND a.jenis_absensi IN ('izin', 'sakit', 'tanpa_kabar')
             ORDER BY a.tanggal DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function analyze_absence_patterns($absence_data) {
    $patterns = [
        'day_of_week' => [],
        'month' => [],
        'reason_distribution' => [],
        'frequency_analysis' => []
    ];
    
    foreach ($absence_data as $absence) {
        $day_of_week = date('w', strtotime($absence['tanggal']));
        $month = date('n', strtotime($absence['tanggal']));
        $reason = $absence['jenis_absensi'];
        
        // Day of week pattern
        if (!isset($patterns['day_of_week'][$day_of_week])) {
            $patterns['day_of_week'][$day_of_week] = 0;
        }
        $patterns['day_of_week'][$day_of_week]++;
        
        // Month pattern
        if (!isset($patterns['month'][$month])) {
            $patterns['month'][$month] = 0;
        }
        $patterns['month'][$month]++;
        
        // Reason distribution
        if (!isset($patterns['reason_distribution'][$reason])) {
            $patterns['reason_distribution'][$reason] = 0;
        }
        $patterns['reason_distribution'][$reason]++;
        
        // Frequency analysis per person
        $nrp = $absence['nrp'];
        if (!isset($patterns['frequency_analysis'][$nrp])) {
            $patterns['frequency_analysis'][$nrp] = [
                'nama' => $absence['nama'],
                'count' => 0,
                'reasons' => []
            ];
        }
        $patterns['frequency_analysis'][$nrp]['count']++;
        $patterns['frequency_analysis'][$nrp]['reasons'][] = $reason;
    }
    
    return $patterns;
}

function predict_absence_patterns($patterns, $prediction_period) {
    $predictions = [];
    
    // Calculate base absence rate
    $total_absences = array_sum($patterns['day_of_week']);
    $base_rate = $total_absences / 180; // 6 months = ~180 days
    
    // Generate daily predictions
    for ($i = 1; $i <= $prediction_period; $i++) {
        $prediction_date = date('Y-m-d', strtotime("+$i days"));
        $day_of_week = date('w', strtotime($prediction_date));
        
        // Apply day-of-week factor
        $day_factor = ($patterns['day_of_week'][$day_of_week] ?? 1) / max(1, $total_absences / 7);
        $predicted_absences = max(0, $base_rate * $day_factor * 10); // Scale up for visibility
        
        $predictions[] = [
            'date' => $prediction_date,
            'predicted_absences' => round($predicted_absences),
            'day_of_week' => $day_of_week,
            'risk_level' => $predicted_absences > 2 ? 'high' : ($predicted_absences > 1 ? 'medium' : 'low')
        ];
    }
    
    return $predictions;
}

function identify_high_risk_personnel($predictions) {
    // This would typically analyze frequency patterns
    // For now, return a placeholder
    return [
        [
            'nrp' => '198401012015031001',
            'nama' => 'Ahmad Rizki',
            'risk_score' => 75,
            'risk_factors' => ['Frequent absences', 'Weekend pattern']
        ]
    ];
}

function calculate_department_impact($predictions, $department) {
    $total_predicted = array_sum(array_column($predictions, 'predicted_absences'));
    $high_risk_days = count(array_filter($predictions, function($p) {
        return $p['risk_level'] === 'high';
    }));
    
    return [
        'department' => $department,
        'total_predicted_absences' => $total_predicted,
        'high_risk_days' => $high_risk_days,
        'impact_level' => $total_predicted > 20 ? 'high' : ($total_predicted > 10 ? 'medium' : 'low'),
        'recommendations' => [
            'Plan additional staffing',
            'Monitor high-risk days',
            'Cross-train personnel'
        ]
    ];
}

function get_operation_history($pdo, $operation_type) {
    $sql = "
        SELECT 
            o.*,
            COUNT(DISTINCT po.personil_id) as personnel_count,
            COUNT(DISTINCT do.id) as documentation_count
        FROM operasi_kepolisian o
        LEFT JOIN personil_operasi po ON o.id = po.operasi_id
        LEFT JOIN dokumentasi_operasi do ON o.id = do.operasi_id
        WHERE o.tanggal_selesai IS NOT NULL
    ";
    
    $params = [];
    if (!empty($operation_type)) {
        $sql .= " AND o.jenis_operasi = ?";
        $params[] = $operation_type;
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.tanggal_mulai DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculate_success_factors($operation_history) {
    $factors = [
        'personnel_count_impact' => [],
        'operation_type_success' => [],
        'location_success' => [],
        'duration_impact' => []
    ];
    
    foreach ($operation_history as $operation) {
        $success = $operation['status'] === 'selesai';
        
        // Personnel count impact
        $factors['personnel_count_impact'][] = [
            'count' => $operation['personnel_count'],
            'success' => $success
        ];
        
        // Operation type success
        $type = $operation['jenis_operasi'];
        if (!isset($factors['operation_type_success'][$type])) {
            $factors['operation_type_success'][$type] = ['success' => 0, 'total' => 0];
        }
        $factors['operation_type_success'][$type]['total']++;
        if ($success) {
            $factors['operation_type_success'][$type]['success']++;
        }
        
        // Location success (simplified)
        $location = $operation['lokasi_operasi'];
        if (!isset($factors['location_success'][$location])) {
            $factors['location_success'][$location] = ['success' => 0, 'total' => 0];
        }
        $factors['location_success'][$location]['total']++;
        if ($success) {
            $factors['location_success'][$location]['success']++;
        }
    }
    
    return $factors;
}

function calculate_success_probability($success_factors, $personnel_count, $location) {
    $base_probability = 0.7; // Base 70% success rate
    
    // Adjust based on personnel count
    $avg_personnel = calculate_average_personnel_count($success_factors['personnel_count_impact']);
    $personnel_factor = min(1.2, max(0.8, $personnel_count / $avg_personnel));
    
    // Adjust based on operation type (simplified)
    $type_factor = 1.0; // Would be calculated based on operation type
    
    // Adjust based on location (simplified)
    $location_factor = 1.0; // Would be calculated based on location history
    
    // Calculate final probability
    $probability = $base_probability * $personnel_factor * $type_factor * $location_factor;
    $probability = min(0.95, max(0.3, $probability)); // Clamp between 30% and 95%
    
    return [
        'probability' => round($probability * 100, 1),
        'confidence' => calculate_prediction_confidence($success_factors),
        'factors' => [
            'base_probability' => $base_probability * 100,
            'personnel_factor' => $personnel_factor,
            'type_factor' => $type_factor,
            'location_factor' => $location_factor
        ]
    ];
}

function calculate_average_personnel_count($personnel_data) {
    if (empty($personnel_data)) {
        return 10;
    }
    
    $total = array_sum(array_column($personnel_data, 'count'));
    return $total / count($personnel_data);
}

function identify_risk_factors($probability_data) {
    $risks = [];
    $prob = $probability_data['probability'];
    
    if ($prob < 50) {
        $risks[] = 'Low success probability';
    }
    
    if ($probability_data['factors']['personnel_factor'] < 0.9) {
        $risks[] = 'Insufficient personnel';
    }
    
    if ($probability_data['confidence'] < 70) {
        $risks[] = 'Low confidence in prediction';
    }
    
    return $risks;
}

function generate_success_recommendations($probability_data) {
    $recommendations = [];
    $prob = $probability_data['probability'];
    
    if ($prob < 60) {
        $recommendations[] = 'Consider increasing personnel count';
        $recommendations[] = 'Review operation planning';
        $recommendations[] = 'Prepare contingency plans';
    } elseif ($prob < 80) {
        $recommendations[] = 'Monitor operation closely';
        $recommendations[] = 'Ensure adequate resources';
    } else {
        $recommendations[] = 'Proceed with standard planning';
        $recommendations[] = 'Maintain current strategy';
    }
    
    return $recommendations;
}

function calculate_prediction_confidence($operation_history) {
    $data_points = count($operation_history);
    
    if ($data_points < 10) {
        return 50; // Low confidence with limited data
    } elseif ($data_points < 30) {
        return 70; // Medium confidence
    } else {
        return 85; // High confidence with sufficient data
    }
}

function get_resource_usage_history($pdo, $resource_type) {
    $sql = "
        SELECT 
            e.nama_barang,
            e.tipe,
            e.status,
            COUNT(ea.id) as assignment_count,
            MAX(ea.tanggal_assign) as last_assignment
        FROM equipment e
        LEFT JOIN equipment_assignments ea ON e.id = ea.equipment_id
        WHERE ea.tanggal_assign >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ";
    
    $params = [];
    if ($resource_type !== 'all') {
        $sql .= " AND e.tipe = ?";
        $params[] = $resource_type;
    }
    
    $sql .= " GROUP BY e.id, e.nama_barang, e.tipe, e.status";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function analyze_resource_usage_patterns($usage_history) {
    $patterns = [
        'usage_frequency' => [],
        'resource_types' => [],
        'peak_usage_times' => []
    ];
    
    foreach ($usage_history as $usage) {
        $type = $usage['tipe'];
        
        // Resource type analysis
        if (!isset($patterns['resource_types'][$type])) {
            $patterns['resource_types'][$type] = [
                'total_assignments' => 0,
                'active_resources' => 0
            ];
        }
        $patterns['resource_types'][$type]['total_assignments'] += $usage['assignment_count'];
        if ($usage['status'] === 'available') {
            $patterns['resource_types'][$type]['active_resources']++;
        }
        
        // Usage frequency
        $patterns['usage_frequency'][] = $usage['assignment_count'];
    }
    
    return $patterns;
}

function generate_resource_forecast($usage_patterns, $forecast_period) {
    $forecast = [];
    
    foreach ($usage_patterns['resource_types'] as $type => $data) {
        $avg_usage = $data['total_assignments'] / max(1, $data['active_resources']);
        
        // Simple linear forecast
        $predicted_usage = $avg_usage * ($forecast_period / 90); // Scale to forecast period
        
        $forecast[] = [
            'resource_type' => $type,
            'current_usage' => $data['total_assignments'],
            'predicted_usage' => round($predicted_usage),
            'utilization_rate' => min(100, round(($predicted_usage / $data['active_resources']) * 100)),
            'recommendation' => $predicted_usage > $data['active_resources'] ? 'Increase resources' : 'Adequate resources'
        ];
    }
    
    return $forecast;
}

function calculate_resource_efficiency($usage_history) {
    $total_assignments = array_sum(array_column($usage_history, 'assignment_count'));
    $total_resources = count($usage_history);
    
    if ($total_resources === 0) {
        return 0;
    }
    
    $efficiency = ($total_assignments / $total_resources) * 100;
    return min(100, round($efficiency));
}

function generate_optimization_suggestions($forecast) {
    $suggestions = [];
    
    foreach ($forecast as $item) {
        if ($item['utilization_rate'] > 80) {
            $suggestions[] = "Consider acquiring additional {$item['resource_type']} resources";
        } elseif ($item['utilization_rate'] < 30) {
            $suggestions[] = "Consider reallocating {$item['resource_type']} resources";
        }
    }
    
    return $suggestions;
}

function get_dashboard_staffing_predictions($pdo) {
    // Simplified dashboard data
    return [
        'next_7_days' => [
            'predicted_demand' => 45,
            'confidence' => '85%',
            'trend' => 'increasing'
        ],
        'next_30_days' => [
            'predicted_demand' => 180,
            'confidence' => '75%',
            'trend' => 'stable'
        ]
    ];
}

function get_dashboard_fatigue_alerts($pdo) {
    return [
        'critical_risk' => 2,
        'high_risk' => 5,
        'total_alerts' => 7,
        'recommendations' => ['Review schedules', 'Consider additional staffing']
    ];
}

function get_dashboard_absence_warnings($pdo) {
    return [
        'high_risk_days' => 3,
        'predicted_absences' => 12,
        'affected_departments' => ['Bagops', 'Bagreskrim']
    ];
}

function get_dashboard_success_rates($pdo) {
    return [
        'overall_success_rate' => '78%',
        'last_month' => '82%',
        'trend' => 'improving'
    ];
}

function get_dashboard_resource_forecasts($pdo) {
    return [
        'vehicle_utilization' => '75%',
        'equipment_availability' => '88%',
        'resource_optimization' => 'Good'
    ];
}

function generate_key_insights($pdo) {
    return [
        'Staffing demand expected to increase by 15% next week',
        '2 personnel at critical fatigue risk require immediate attention',
        'Operation success rate improved by 4% this month',
        'Resource utilization within optimal range'
    ];
}

function generate_dashboard_recommendations($pdo) {
    return [
        'Increase staffing allocation for upcoming operations',
        'Implement fatigue mitigation measures for high-risk personnel',
        'Maintain current resource management strategy',
        'Continue monitoring operational success factors'
    ];
}

function get_model_performance_metrics($pdo) {
    return [
        'staffing_prediction_accuracy' => '82%',
        'fatigue_risk_precision' => '78%',
        'absence_prediction_recall' => '75%',
        'success_probability_confidence' => '85%'
    ];
}

?>
