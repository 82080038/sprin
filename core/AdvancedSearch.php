<?php
declare(strict_types=1);
/**
 * Advanced Search Engine for SPRIN
 * Full-text search with multiple filters
 */

class AdvancedSearch {
    
    private $db;
    private $searchableFields = [
        'personil' => [
            'fields' => ['nama', 'nrp', 'gelar_pendidikan', 'status_ket'],
            'joins' => [
                'pangkat' => ['field' => 'nama_pangkat', 'fk' => 'id_pangkat'],
                'jabatan' => ['field' => 'nama_jabatan', 'fk' => 'id_jabatan'],
                'bagian' => ['field' => 'nama_bagian', 'fk' => 'id_bagian'],
                'unsur' => ['field' => 'nama_unsur', 'fk' => 'id_unsur']
            ]
        ]
    ];
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Perform advanced search on personil
     */
    public function searchPersonil($query, $filters = [], $options = []) {
        $limit = $options['limit'] ?? 100;
        $offset = $options['offset'] ?? 0;
        $sortBy = $options['sort_by'] ?? 'nama';
        $sortOrder = $options['sort_order'] ?? 'ASC';
        
        // Build base query
        $sql = "
            SELECT 
                p.id,
                p.nama,
                p.nama_lengkap,
                p.gelar_pendidikan,
                p.nrp,
                p.JK,
                p.status_ket,
                p.status_nikah,
                p.tanggal_lahir,
                p.tempat_lahir,
                mjp.nama_jenis as status_kepegawaian,
                mjp.kode_jenis as kode_kepegawaian,
                pg.nama_pangkat,
                pg.singkatan as pangkat_singkatan,
                j.nama_jabatan,
                b.nama_bagian,
                u.nama_unsur,
                u.kode_unsur,
                p.created_at,
                p.updated_at
            FROM personil p
            LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
            LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN bagian b ON p.id_bagian = b.id
            LEFT JOIN unsur u ON p.id_unsur = u.id
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ";
        
        $params = [];
        $whereConditions = [];
        
        // Full-text search
        if (!empty($query)) {
            $searchTerm = "%{$query}%";
            $whereConditions[] = "(
                p.nama LIKE :search OR
                p.nrp LIKE :search OR
                p.gelar_pendidikan LIKE :search OR
                p.status_ket LIKE :search OR
                pg.nama_pangkat LIKE :search OR
                pg.singkatan LIKE :search OR
                j.nama_jabatan LIKE :search OR
                b.nama_bagian LIKE :search OR
                u.nama_unsur LIKE :search
            )";
            $params['search'] = $searchTerm;
        }
        
        // Apply filters
        if (!empty($filters['unsur'])) {
            $whereConditions[] = "u.kode_unsur = :unsur";
            $params['unsur'] = $filters['unsur'];
        }
        
        if (!empty($filters['bagian'])) {
            $whereConditions[] = "b.id = :bagian";
            $params['bagian'] = $filters['bagian'];
        }
        
        if (!empty($filters['pangkat'])) {
            $whereConditions[] = "pg.id = :pangkat";
            $params['pangkat'] = $filters['pangkat'];
        }
        
        if (!empty($filters['jabatan'])) {
            $whereConditions[] = "j.id = :jabatan";
            $params['jabatan'] = $filters['jabatan'];
        }
        
        if (!empty($filters['jenis_pegawai'])) {
            $whereConditions[] = "mjp.kode_jenis = :jenis_pegawai";
            $params['jenis_pegawai'] = $filters['jenis_pegawai'];
        }
        
        if (!empty($filters['jk'])) {
            $whereConditions[] = "p.JK = :jk";
            $params['jk'] = $filters['jk'];
        }
        
        if (!empty($filters['status_nikah'])) {
            $whereConditions[] = "p.status_nikah = :status_nikah";
            $params['status_nikah'] = $filters['status_nikah'];
        }
        
        // Date range filters
        if (!empty($filters['tanggal_lahir_from'])) {
            $whereConditions[] = "p.tanggal_lahir >= :tanggal_lahir_from";
            $params['tanggal_lahir_from'] = $filters['tanggal_lahir_from'];
        }
        
        if (!empty($filters['tanggal_lahir_to'])) {
            $whereConditions[] = "p.tanggal_lahir <= :tanggal_lahir_to";
            $params['tanggal_lahir_to'] = $filters['tanggal_lahir_to'];
        }
        
        // Add where conditions to SQL
        if (!empty($whereConditions)) {
            $sql .= " AND " . implode(" AND ", $whereConditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_table";
        $totalResult = $this->db->fetchOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Add sorting
        $allowedSortFields = ['nama', 'nrp', 'tanggal_lahir', 'created_at'];
        $sortField = in_array($sortBy, $allowedSortFields) ? "p.{$sortBy}" : 'p.nama';
        $sortDirection = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql .= " ORDER BY {$sortField} {$sortDirection}";
        
        // Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        // Execute query
        $results = $this->db->fetchAll($sql, $params);
        
        // Enhance results with highlights
        $enhancedResults = [];
        foreach ($results as $row) {
            $highlighted = $this->highlightSearchTerms($row, $query);
            $enhancedResults[] = $highlighted;
        }
        
        return [
            'data' => $enhancedResults,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'current_page' => floor($offset / $limit) + 1,
                'total_pages' => ceil($total / $limit),
                'has_next' => ($offset + $limit) < $total,
                'has_prev' => $offset > 0
            ],
            'filters_applied' => $filters,
            'search_query' => $query
        ];
    }
    
    /**
     * Highlight search terms in results
     */
    private function highlightSearchTerms($row, $query) {
        if (empty($query)) {
            return $row;
        }
        
        $highlightFields = ['nama', 'nrp', 'gelar_pendidikan', 'status_ket', 'nama_pangkat', 'nama_jabatan', 'nama_bagian', 'nama_unsur'];
        $highlighted = [];
        
        foreach ($row as $key => $value) {
            if (in_array($key, $highlightFields) && is_string($value)) {
                $pattern = '/(' . preg_quote($query, '/') . ')/i';
                $highlighted[$key . '_highlighted'] = preg_replace($pattern, '<mark>$1</mark>', htmlspecialchars($value));
            }
            $highlighted[$key] = $value;
        }
        
        return $highlighted;
    }
    
    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $limit = 10) {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }
        
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT DISTINCT nama as suggestion, 'nama' as type
            FROM personil
            WHERE is_deleted = FALSE AND nama LIKE :search
            LIMIT :limit
        ";
        
        $results = $this->db->fetchAll($sql, [
            'search' => $searchTerm,
            'limit' => $limit
        ]);
        
        return $results;
    }
    
    /**
     * Quick search (for live search)
     */
    public function quickSearch($query, $limit = 20) {
        if (empty($query)) {
            return [];
        }
        
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT 
                p.id,
                p.nama,
                p.nama_lengkap,
                p.nrp,
                pg.singkatan as pangkat_singkatan,
                j.nama_jabatan,
                b.nama_bagian,
                u.nama_unsur
            FROM personil p
            LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN bagian b ON p.id_bagian = b.id
            LEFT JOIN unsur u ON p.id_unsur = u.id
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            AND (
                p.nama LIKE :search OR
                p.nrp LIKE :search OR
                p.gelar_pendidikan LIKE :search
            )
            ORDER BY p.nama
            LIMIT :limit
        ";
        
        return $this->db->fetchAll($sql, [
            'search' => $searchTerm,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get available filters
     */
    public function getAvailableFilters() {
        $filters = [];
        
        // Unsur options
        $filters['unsur'] = $this->db->fetchAll(
            "SELECT id, kode_unsur, nama_unsur FROM unsur WHERE is_active = TRUE ORDER BY urutan"
        );
        
        // Bagian options
        $filters['bagian'] = $this->db->fetchAll(
            "SELECT id, nama_bagian, id_unsur FROM bagian WHERE is_active = TRUE ORDER BY nama_bagian"
        );
        
        // Pangkat options
        $filters['pangkat'] = $this->db->fetchAll(
            "SELECT id, nama_pangkat, singkatan FROM pangkat ORDER BY level_pangkat"
        );
        
        // Jabatan options
        $filters['jabatan'] = $this->db->fetchAll(
            "SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan"
        );
        
        // Jenis pegawai options
        $filters['jenis_pegawai'] = $this->db->fetchAll(
            "SELECT id, kode_jenis, nama_jenis FROM master_jenis_pegawai ORDER BY kode_jenis"
        );
        
        return $filters;
    }
    
    /**
     * Search with autocomplete
     */
    public function autocomplete($query, $field = 'nama', $limit = 10) {
        $allowedFields = ['nama', 'nrp'];
        
        if (!in_array($field, $allowedFields) || empty($query)) {
            return [];
        }
        
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT DISTINCT {$field} as value, COUNT(*) as frequency
            FROM personil
            WHERE is_deleted = FALSE AND {$field} LIKE :search
            GROUP BY {$field}
            ORDER BY frequency DESC, {$field}
            LIMIT :limit
        ";
        
        return $this->db->fetchAll($sql, [
            'search' => $searchTerm,
            'limit' => $limit
        ]);
    }
}

?>