<?php
declare(strict_types=1);
/**
 * Data Quality Analyzer
 * Comprehensive analysis of data quality issues and patterns
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

class DataQualityAnalyzer {
    private $pdo;
    private $issues = [];
    private $metrics = [];
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Analyze data quality across all tables
     */
    public function analyzeQuality() {
        $this->issues = [];
        $this->metrics = [];
        
        $this->analyzePersonilQuality();
        $this->analyzeUnsurQuality();
        $this->analyzeBagianQuality();
        $this->analyzeJabatanQuality();
        $this->analyzePangkatQuality();
        $this->analyzeCompleteness();
        $this->analyzeConsistency();
        $this->analyzeAccuracy();
        
        return $this->generateQualityReport();
    }
    
    /**
     * Analyze personil data quality
     */
    private function analyzePersonilQuality() {
        // Data completeness metrics
        $completeness = [];
        
        $fields = ['nama', 'nrp', 'nip', 'JK', 'tanggal_lahir', 'tempat_lahir', 'tanggal_masuk', 'no_karpeg'];
        foreach ($fields as $field) {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT($field) as filled,
                    ROUND(COUNT($field) * 100.0 / COUNT(*), 2) as completeness_pct
                FROM personil 
                WHERE is_deleted = 0
            ");
            $result = $stmt->fetch();
            
            $completeness[$field] = [
                'total' => (int)$result['total'],
                'filled' => (int)$result['filled'],
                'completeness_pct' => (float)$result['completeness_pct']
            ];
        }
        
        $this->metrics['personil']['completeness'] = $completeness;
        
        // Data format validation
        $format_issues = [];
        
        // Check NRP format
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0 
            AND nrp IS NOT NULL 
            AND nrp != '' 
            AND nrp NOT REGEXP '^[0-9]{8,9}$'
        ");
        $nrp_format_issues = $stmt->fetch()['count'];
        if ($nrp_format_issues > 0) {
            $format_issues[] = "Found {$nrp_format_issues} personil with invalid NRP format";
        }
        
        // Check NIP format
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0 
            AND nip IS NOT NULL 
            AND nip != '' 
            AND nip NOT REGEXP '^[0-9]{18}$'
        ");
        $nip_format_issues = $stmt->fetch()['count'];
        if ($nip_format_issues > 0) {
            $format_issues[] = "Found {$nip_format_issues} personil with invalid NIP format";
        }
        
        // Check JK values
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0 
            AND JK IS NOT NULL 
            AND JK NOT IN ('L', 'P')
        ");
        $jk_format_issues = $stmt->fetch()['count'];
        if ($jk_format_issues > 0) {
            $format_issues[] = "Found {$jk_format_issues} personil with invalid JK values";
        }
        
        $this->metrics['personil']['format_issues'] = $format_issues;
        
        // Data distribution analysis
        $distribution = [];
        
        // Status distribution
        $stmt = $this->pdo->query("
            SELECT status_ket, COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0
            GROUP BY status_ket
        ");
        $distribution['status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Gender distribution
        $stmt = $this->pdo->query("
            SELECT JK, COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0
            GROUP BY JK
        ");
        $distribution['gender'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Age distribution
        $stmt = $this->pdo->query("
            SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 25 THEN '< 25'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 35 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 45 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 55 THEN '45-54'
                    ELSE '55+'
                END as age_group,
                COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0 AND tanggal_lahir IS NOT NULL
            GROUP BY age_group
            ORDER BY age_group
        ");
        $distribution['age'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->metrics['personil']['distribution'] = $distribution;
    }
    
    /**
     * Analyze unsur data quality
     */
    private function analyzeUnsurQuality() {
        // Check for empty descriptions
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as total, COUNT(deskripsi) as with_desc
            FROM unsur
        ");
        $result = $stmt->fetch();
        
        $this->metrics['unsur']['description_completeness'] = [
            'total' => (int)$result['total'],
            'with_description' => (int)$result['with_desc'],
            'completeness_pct' => $result['total'] > 0 ? round(($result['with_desc'] / $result['total']) * 100, 2) : 0
        ];
        
        // Check urutan sequence
        $stmt = $this->pdo->query("
            SELECT urutan, COUNT(*) as count
            FROM unsur
            GROUP BY urutan
            HAVING count > 1
        ");
        $duplicate_urutan = $stmt->fetchAll();
        
        $this->metrics['unsur']['duplicate_urutan'] = $duplicate_urutan;
    }
    
    /**
     * Analyze bagian data quality
     */
    private function analyzeBagianQuality() {
        // Check bagian distribution across unsur
        $stmt = $this->pdo->query("
            SELECT u.nama_unsur, COUNT(b.id) as bagian_count
            FROM unsur u
            LEFT JOIN bagian b ON u.id = b.id_unsur
            GROUP BY u.id, u.nama_unsur
            ORDER BY u.urutan
        ");
        $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->metrics['bagian']['distribution'] = $distribution;
        
        // Check for bagian without personil
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM bagian b
            LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
            WHERE p.id IS NULL
        ");
        $empty_bagian = $stmt->fetch()['count'];
        
        $this->metrics['bagian']['empty_count'] = (int)$empty_bagian;
    }
    
    /**
     * Analyze jabatan data quality
     */
    private function analyzeJabatanQuality() {
        // Check jabatan distribution across unsur
        $stmt = $this->pdo->query("
            SELECT u.nama_unsur, COUNT(j.id) as jabatan_count
            FROM unsur u
            LEFT JOIN jabatan j ON u.id = j.id_unsur
            GROUP BY u.id, u.nama_unsur
            ORDER BY u.urutan
        ");
        $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->metrics['jabatan']['distribution'] = $distribution;
        
        // Check for jabatan without personil
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM jabatan j
            LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
            WHERE p.id IS NULL
        ");
        $empty_jabatan = $stmt->fetch()['count'];
        
        $this->metrics['jabatan']['empty_count'] = (int)$empty_jabatan;
    }
    
    /**
     * Analyze pangkat data quality
     */
    private function analyzePangkatQuality() {
        // Check level sequence
        $stmt = $this->pdo->query("
            SELECT level_pangkat, COUNT(*) as count
            FROM pangkat
            GROUP BY level_pangkat
            HAVING count > 1
        ");
        $duplicate_levels = $stmt->fetchAll();
        
        $this->metrics['pangkat']['duplicate_levels'] = $duplicate_levels;
        
        // Check personil distribution across pangkat
        $stmt = $this->pdo->query("
            SELECT pg.nama_pangkat, COUNT(p.id) as personil_count
            FROM pangkat pg
            LEFT JOIN personil p ON pg.id = p.id_pangkat AND p.is_deleted = 0
            GROUP BY pg.id, pg.nama_pangkat
            ORDER BY pg.level_pangkat
        ");
        $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->metrics['pangkat']['distribution'] = $distribution;
    }
    
    /**
     * Analyze data completeness across all tables
     */
    private function analyzeCompleteness() {
        $completeness_scores = [];
        
        // Personil completeness score
        $personil_fields = ['nama', 'nrp', 'JK', 'status_ket'];
        $personil_total = 0;
        $personil_filled = 0;
        
        foreach ($personil_fields as $field) {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total, COUNT($field) as filled
                FROM personil 
                WHERE is_deleted = 0
            ");
            $result = $stmt->fetch();
            $personil_total += (int)$result['total'];
            $personil_filled += (int)$result['filled'];
        }
        
        $completeness_scores['personil'] = $personil_total > 0 ? round(($personil_filled / $personil_total) * 100, 2) : 0;
        
        // Unsur completeness score
        $unsur_fields = ['nama_unsur', 'kode_unsur', 'urutan'];
        $unsur_total = 0;
        $unsur_filled = 0;
        
        foreach ($unsur_fields as $field) {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total, COUNT($field) as filled
                FROM unsur
            ");
            $result = $stmt->fetch();
            $unsur_total += (int)$result['total'];
            $unsur_filled += (int)$result['filled'];
        }
        
        $completeness_scores['unsur'] = $unsur_total > 0 ? round(($unsur_filled / $unsur_total) * 100, 2) : 0;
        
        // Bagian completeness score
        $bagian_fields = ['nama_bagian', 'id_unsur', 'urutan'];
        $bagian_total = 0;
        $bagian_filled = 0;
        
        foreach ($bagian_fields as $field) {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total, COUNT($field) as filled
                FROM bagian
            ");
            $result = $stmt->fetch();
            $bagian_total += (int)$result['total'];
            $bagian_filled += (int)$result['filled'];
        }
        
        $completeness_scores['bagian'] = $bagian_total > 0 ? round(($bagian_filled / $bagian_total) * 100, 2) : 0;
        
        // Jabatan completeness score
        $jabatan_fields = ['nama_jabatan', 'id_unsur'];
        $jabatan_total = 0;
        $jabatan_filled = 0;
        
        foreach ($jabatan_fields as $field) {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total, COUNT($field) as filled
                FROM jabatan
            ");
            $result = $stmt->fetch();
            $jabatan_total += (int)$result['total'];
            $jabatan_filled += (int)$result['filled'];
        }
        
        $completeness_scores['jabatan'] = $jabatan_total > 0 ? round(($jabatan_filled / $jabatan_total) * 100, 2) : 0;
        
        // Overall completeness
        $overall_total = $personil_total + $unsur_total + $bagian_total + $jabatan_total;
        $overall_filled = $personil_filled + $unsur_filled + $bagian_filled + $jabatan_filled;
        $completeness_scores['overall'] = $overall_total > 0 ? round(($overall_filled / $overall_total) * 100, 2) : 0;
        
        $this->metrics['completeness_scores'] = $completeness_scores;
    }
    
    /**
     * Analyze data consistency
     */
    private function analyzeConsistency() {
        $consistency_issues = [];
        
        // Check for inconsistent naming patterns
        $stmt = $this->pdo->query("
            SELECT nama_jabatan, COUNT(*) as count
            FROM jabatan
            GROUP BY nama_jabatan
            HAVING count > 1
        ");
        $duplicate_jabatan_names = $stmt->fetchAll();
        
        if (!empty($duplicate_jabatan_names)) {
            $consistency_issues[] = "Found duplicate jabatan names across different unsur";
        }
        
        // Check for inconsistent status values
        $stmt = $this->pdo->query("
            SELECT DISTINCT status_ket
            FROM personil
            WHERE is_deleted = 0
            ORDER BY status_ket
        ");
        $status_values = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $expected_status = ['aktif', 'nonaktif'];
        $unexpected_status = array_diff($status_values, $expected_status);
        
        if (!empty($unexpected_status)) {
            $consistency_issues[] = "Found unexpected status values: " . implode(', ', $unexpected_status);
        }
        
        $this->metrics['consistency_issues'] = $consistency_issues;
    }
    
    /**
     * Analyze data accuracy
     */
    private function analyzeAccuracy() {
        $accuracy_issues = [];
        
        // Check for future dates
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil
            WHERE is_deleted = 0
            AND (
                (tanggal_lahir IS NOT NULL AND tanggal_lahir > CURDATE())
                OR (tanggal_masuk IS NOT NULL AND tanggal_masuk > CURDATE())
            )
        ");
        $future_dates = $stmt->fetch()['count'];
        
        if ($future_dates > 0) {
            $accuracy_issues[] = "Found {$future_dates} records with future dates";
        }
        
        // Check for very old dates (likely errors)
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil
            WHERE is_deleted = 0
            AND (
                (tanggal_lahir IS NOT NULL AND tanggal_lahir < '1900-01-01')
                OR (tanggal_masuk IS NOT NULL AND tanggal_masuk < '1950-01-01')
            )
        ");
        $old_dates = $stmt->fetch()['count'];
        
        if ($old_dates > 0) {
            $accuracy_issues[] = "Found {$old_dates} records with suspiciously old dates";
        }
        
        // Check for logical inconsistencies
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count
            FROM personil
            WHERE is_deleted = 0
            AND tanggal_lahir IS NOT NULL
            AND tanggal_masuk IS NOT NULL
            AND tanggal_lahir > tanggal_masuk
        ");
        $birth_after_join = $stmt->fetch()['count'];
        
        if ($birth_after_join > 0) {
            $accuracy_issues[] = "Found {$birth_after_join} records where birth date is after join date";
        }
        
        $this->metrics['accuracy_issues'] = $accuracy_issues;
    }
    
    /**
     * Generate quality report
     */
    private function generateQualityReport() {
        // Calculate overall quality score
        $completeness_score = $this->metrics['completeness_scores']['overall'] ?? 0;
        $consistency_score = empty($this->metrics['consistency_issues']) ? 100 : 50;
        $accuracy_score = empty($this->metrics['accuracy_issues']) ? 100 : 70;
        
        $overall_quality = round(($completeness_score + $consistency_score + $accuracy_score) / 3, 2);
        
        // Determine quality grade
        if ($overall_quality >= 90) {
            $grade = 'A';
            $status = 'Excellent';
        } elseif ($overall_quality >= 80) {
            $grade = 'B';
            $status = 'Good';
        } elseif ($overall_quality >= 70) {
            $grade = 'C';
            $status = 'Fair';
        } elseif ($overall_quality >= 60) {
            $grade = 'D';
            $status = 'Poor';
        } else {
            $grade = 'F';
            $status = 'Very Poor';
        }
        
        return [
            'summary' => [
                'overall_quality_score' => $overall_quality,
                'quality_grade' => $grade,
                'quality_status' => $status,
                'completeness_score' => $completeness_score,
                'consistency_score' => $consistency_score,
                'accuracy_score' => $accuracy_score
            ],
            'metrics' => $this->metrics,
            'recommendations' => $this->generateQualityRecommendations($overall_quality),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Generate quality improvement recommendations
     */
    private function generateQualityRecommendations($overall_quality) {
        $recommendations = [];
        
        if ($overall_quality < 80) {
            $recommendations[] = 'Data quality needs improvement - focus on completeness and accuracy';
        }
        
        // Completeness recommendations
        $personil_completeness = $this->metrics['personil']['completeness'] ?? [];
        foreach ($personil_completeness as $field => $data) {
            if ($data['completeness_pct'] < 80) {
                $recommendations[] = "Improve {$field} completeness (currently {$data['completeness_pct']}%)";
            }
        }
        
        // Format recommendations
        $format_issues = $this->metrics['personil']['format_issues'] ?? [];
        if (!empty($format_issues)) {
            $recommendations[] = 'Fix data format issues in personil records';
        }
        
        // Consistency recommendations
        $consistency_issues = $this->metrics['consistency_issues'] ?? [];
        if (!empty($consistency_issues)) {
            $recommendations[] = 'Address data consistency issues across tables';
        }
        
        // Accuracy recommendations
        $accuracy_issues = $this->metrics['accuracy_issues'] ?? [];
        if (!empty($accuracy_issues)) {
            $recommendations[] = 'Review and correct data accuracy issues';
        }
        
        // Distribution recommendations
        $empty_bagian = $this->metrics['bagian']['empty_count'] ?? 0;
        if ($empty_bagian > 0) {
            $recommendations[] = "Review {$empty_bagian} bagian without assigned personil";
        }
        
        $empty_jabatan = $this->metrics['jabatan']['empty_count'] ?? 0;
        if ($empty_jabatan > 0) {
            $recommendations[] = "Review {$empty_jabatan} jabatan without assigned personil";
        }
        
        return $recommendations;
    }
}

// Run analysis if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $analyzer = new DataQualityAnalyzer();
        $result = $analyzer->analyzeQuality();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    }
}
?>
