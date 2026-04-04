<?php
declare(strict_types=1);
/**
 * Database Health Report Generator
 * Comprehensive database health assessment and reporting
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/SimpleDatabaseChecker.php';
require_once __DIR__ . '/DataIntegrityValidator.php';
require_once __DIR__ . '/ForeignKeyChecker.php';
require_once __DIR__ . '/DataQualityAnalyzer.php';

class DatabaseHealthReporter {
    private $health_data = [];
    
    public function __construct() {
        // Initialize all checkers
        $this->health_data = [
            'structure' => [],
            'integrity' => [],
            'constraints' => [],
            'quality' => []
        ];
    }
    
    /**
     * Generate comprehensive database health report
     */
    public function generateHealthReport() {
        // Run all checks
        $this->runAllChecks();
        
        // Calculate overall health score
        $overall_score = $this->calculateOverallScore();
        
        // Generate recommendations
        $recommendations = $this->generateComprehensiveRecommendations();
        
        // Create summary
        $summary = $this->createSummary($overall_score);
        
        return [
            'summary' => $summary,
            'overall_health_score' => $overall_score,
            'detailed_results' => $this->health_data,
            'recommendations' => $recommendations,
            'action_items' => $this->generateActionItems(),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Run all database health checks
     */
    private function runAllChecks() {
        try {
            // Structure check
            $structure_checker = new SimpleDatabaseChecker();
            $this->health_data['structure'] = $structure_checker->checkDatabase();
            
            // Data integrity check
            $integrity_validator = new DataIntegrityValidator();
            $this->health_data['integrity'] = $integrity_validator->validateAll();
            
            // Foreign key constraints check
            $fk_checker = new ForeignKeyChecker();
            $this->health_data['constraints'] = $fk_checker->checkConstraints();
            
            // Data quality analysis
            $quality_analyzer = new DataQualityAnalyzer();
            $this->health_data['quality'] = $quality_analyzer->analyzeQuality();
            
        } catch (Exception $e) {
            $this->health_data['error'] = [
                'message' => $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * Calculate overall health score
     */
    private function calculateOverallScore() {
        $scores = [];
        
        // Structure score (40% weight)
        $structure_score = $this->calculateStructureScore();
        $scores['structure'] = ['score' => $structure_score, 'weight' => 0.4];
        
        // Integrity score (30% weight)
        $integrity_score = $this->calculateIntegrityScore();
        $scores['integrity'] = ['score' => $integrity_score, 'weight' => 0.3];
        
        // Constraints score (20% weight)
        $constraints_score = $this->calculateConstraintsScore();
        $scores['constraints'] = ['score' => $constraints_score, 'weight' => 0.2];
        
        // Quality score (10% weight)
        $quality_score = $this->calculateQualityScore();
        $scores['quality'] = ['score' => $quality_score, 'weight' => 0.1];
        
        // Calculate weighted average
        $total_score = 0;
        foreach ($scores as $category => $data) {
            $total_score += $data['score'] * $data['weight'];
        }
        
        return [
            'overall' => round($total_score, 2),
            'breakdown' => $scores,
            'grade' => $this->getHealthGrade($total_score),
            'status' => $this->getHealthStatus($total_score)
        ];
    }
    
    /**
     * Calculate structure health score
     */
    private function calculateStructureScore() {
        $structure = $this->health_data['structure'];
        
        if (!isset($structure['tables'])) {
            return 50; // Default score if data missing
        }
        
        $score = 100;
        
        // Check for critical tables
        $critical_tables = ['personil', 'unsur', 'bagian', 'jabatan', 'pangkat'];
        foreach ($critical_tables as $table) {
            if (!isset($structure['tables'][$table])) {
                $score -= 20;
            }
        }
        
        // Check connection status
        if ($structure['connection']['status'] !== 'connected') {
            $score -= 30;
        }
        
        return max(0, $score);
    }
    
    /**
     * Calculate integrity health score
     */
    private function calculateIntegrityScore() {
        $integrity = $this->health_data['integrity'];
        
        if (!isset($integrity['summary'])) {
            return 50;
        }
        
        $summary = $integrity['summary'];
        $total_issues = $summary['total_issues'] ?? 0;
        $high_issues = $summary['severity_breakdown']['high'] ?? 0;
        
        // Base score starts at 100, deduct for issues
        $score = 100;
        
        // Deduct for total issues
        $score -= min($total_issues * 2, 40);
        
        // Deduct more for high severity issues
        $score -= min($high_issues * 10, 50);
        
        return max(0, $score);
    }
    
    /**
     * Calculate constraints health score
     */
    private function calculateConstraintsScore() {
        $constraints = $this->health_data['constraints'];
        
        if (!isset($constraints['summary'])) {
            return 50;
        }
        
        $summary = $constraints['summary'];
        $coverage = $summary['coverage_percentage'] ?? 0;
        $missing = $summary['total_missing_constraints'] ?? 0;
        $issues = $summary['total_issues'] ?? 0;
        
        // Base score from coverage percentage
        $score = $coverage;
        
        // Deduct for missing constraints
        $score -= min($missing * 5, 30);
        
        // Deduct for constraint issues
        $score -= min($issues * 3, 20);
        
        return max(0, $score);
    }
    
    /**
     * Calculate quality health score
     */
    private function calculateQualityScore() {
        $quality = $this->health_data['quality'];
        
        if (!isset($quality['summary'])) {
            return 50;
        }
        
        $summary = $quality['summary'];
        $overall_quality = $summary['overall_quality_score'] ?? 0;
        
        return $overall_quality;
    }
    
    /**
     * Get health grade based on score
     */
    private function getHealthGrade($score) {
        if ($score >= 95) return 'A+';
        if ($score >= 90) return 'A';
        if ($score >= 85) return 'B+';
        if ($score >= 80) return 'B';
        if ($score >= 75) return 'C+';
        if ($score >= 70) return 'C';
        if ($score >= 65) return 'D+';
        if ($score >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Get health status based on score
     */
    private function getHealthStatus($score) {
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Good';
        if ($score >= 70) return 'Fair';
        if ($score >= 60) return 'Poor';
        return 'Critical';
    }
    
    /**
     * Create summary statistics
     */
    private function createSummary($overall_score) {
        $structure = $this->health_data['structure'];
        $integrity = $this->health_data['integrity'];
        $constraints = $this->health_data['constraints'];
        $quality = $this->health_data['quality'];
        
        return [
            'database_info' => [
                'name' => DB_NAME,
                'connection_status' => $structure['connection']['status'] ?? 'unknown',
                'version' => $structure['connection']['version'] ?? 'unknown',
                'total_tables' => count($structure['tables'] ?? []),
                'total_records' => array_sum($structure['data_counts'] ?? [])
            ],
            'health_metrics' => [
                'overall_score' => $overall_score['overall'],
                'grade' => $overall_score['grade'],
                'status' => $overall_score['status'],
                'structure_score' => $overall_score['breakdown']['structure']['score'],
                'integrity_score' => $overall_score['breakdown']['integrity']['score'],
                'constraints_score' => $overall_score['breakdown']['constraints']['score'],
                'quality_score' => $overall_score['breakdown']['quality']['score']
            ],
            'issue_summary' => [
                'total_issues' => ($integrity['summary']['total_issues'] ?? 0) + 
                                ($constraints['summary']['total_issues'] ?? 0),
                'high_severity' => ($integrity['summary']['severity_breakdown']['high'] ?? 0) + 
                                 ($constraints['summary']['severity_breakdown']['high'] ?? 0),
                'missing_constraints' => $constraints['summary']['total_missing_constraints'] ?? 0,
                'quality_issues' => count($quality['metrics']['personil']['format_issues'] ?? [])
            ]
        ];
    }
    
    /**
     * Generate comprehensive recommendations
     */
    private function generateComprehensiveRecommendations() {
        $recommendations = [];
        $overall_score = $this->calculateOverallScore();
        $score = $overall_score['overall'];
        
        // Priority-based recommendations
        if ($score < 70) {
            $recommendations[] = [
                'priority' => 'critical',
                'category' => 'overall',
                'description' => 'Database health is below acceptable level. Immediate action required.',
                'actions' => ['Address high-severity issues first', 'Implement missing constraints', 'Fix data integrity problems']
            ];
        }
        
        // Structure recommendations
        $structure_score = $overall_score['breakdown']['structure']['score'];
        if ($structure_score < 80) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'structure',
                'description' => 'Database structure needs attention',
                'actions' => ['Verify all critical tables exist', 'Check table relationships', 'Review database schema']
            ];
        }
        
        // Integrity recommendations
        $integrity_score = $overall_score['breakdown']['integrity']['score'];
        if ($integrity_score < 80) {
            $integrity = $this->health_data['integrity'];
            $high_issues = $integrity['summary']['severity_breakdown']['high'] ?? 0;
            
            $recommendations[] = [
                'priority' => $high_issues > 0 ? 'critical' : 'high',
                'category' => 'integrity',
                'description' => "Data integrity issues found ({$high_issues} high severity)",
                'actions' => ['Fix orphaned records', 'Resolve duplicate entries', 'Validate data relationships']
            ];
        }
        
        // Constraints recommendations
        $constraints_score = $overall_score['breakdown']['constraints']['score'];
        if ($constraints_score < 80) {
            $constraints = $this->health_data['constraints'];
            $missing = $constraints['summary']['total_missing_constraints'] ?? 0;
            
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'constraints',
                'description' => "Missing {$missing} foreign key constraints",
                'actions' => ['Add missing foreign keys', 'Review cascade rules', 'Test constraint behavior']
            ];
        }
        
        // Quality recommendations
        $quality_score = $overall_score['breakdown']['quality']['score'];
        if ($quality_score < 85) {
            $quality = $this->health_data['quality'];
            $format_issues = $quality['metrics']['personil']['format_issues'] ?? [];
            
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'quality',
                'description' => 'Data quality improvements needed',
                'actions' => ['Fix format issues', 'Improve data completeness', 'Standardize data entry']
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Generate specific action items
     */
    private function generateActionItems() {
        $actions = [];
        
        // From integrity issues
        $integrity = $this->health_data['integrity'];
        if (isset($integrity['issues'])) {
            foreach ($integrity['issues'] as $issue) {
                if ($issue['severity'] === 'high') {
                    $actions[] = [
                        'priority' => 'high',
                        'type' => 'fix_integrity',
                        'description' => $issue['description'],
                        'table' => $issue['table'] ?? 'unknown',
                        'record_id' => $issue['record_id'] ?? null
                    ];
                }
            }
        }
        
        // From constraints issues
        $constraints = $this->health_data['constraints'];
        if (isset($constraints['missing_constraints'])) {
            foreach ($constraints['missing_constraints'] as $missing) {
                $actions[] = [
                    'priority' => 'high',
                    'type' => 'add_constraint',
                    'description' => "Add foreign key: {$missing['table']}.{$missing['column']} -> {$missing['expected']['referenced_table']}.{$missing['expected']['referenced_column']}",
                    'table' => $missing['table'],
                    'column' => $missing['column'],
                    'referenced_table' => $missing['expected']['referenced_table']
                ];
            }
        }
        
        // From quality issues
        $quality = $this->health_data['quality'];
        if (isset($quality['metrics']['personil']['format_issues'])) {
            foreach ($quality['metrics']['personil']['format_issues'] as $issue) {
                $actions[] = [
                    'priority' => 'medium',
                    'type' => 'fix_format',
                    'description' => $issue,
                    'table' => 'personil'
                ];
            }
        }
        
        return $actions;
    }
}

// Generate report if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $reporter = new DatabaseHealthReporter();
        $report = $reporter->generateHealthReport();
        
        echo json_encode([
            'success' => true,
            'data' => $report,
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
