<?php
/**
 * Export Manager for SPRIN
 * Enhanced PDF and Excel export with templates
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Mpdf\Mpdf;

class ExportManager {
    
    private $db;
    private $exportPath;
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
        $this->exportPath = __DIR__ . '/../exports/';
        
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0755, true);
        }
    }
    
    /**
     * Export personil data to Excel with professional template
     */
    public function exportPersonilExcel($filters = [], $filename = null) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Personil');
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('SPRIN System')
            ->setLastModifiedBy('SPRIN System')
            ->setTitle('Data Personil POLRES Samosir')
            ->setSubject('Export Data Personil')
            ->setDescription('Export data personil dari sistem SPRIN')
            ->setKeywords('personil, polres, samosir');
        
        // Header styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Arial'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '1A237E']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        // Title row
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'DATA PERSONIL POLRES SAMOSIR');
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1A237E']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        
        // Date row
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d F Y H:i:s'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $headers = [
            'A4' => 'No',
            'B4' => 'NRP',
            'C4' => 'Nama Lengkap',
            'D4' => 'Pangkat',
            'E4' => 'Jabatan',
            'F4' => 'Bagian',
            'G4' => 'Unsur',
            'H4' => 'Status',
            'I4' => 'JK',
            'J4' => 'Status Pernikahan'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        $sheet->getStyle('A4:J4')->applyFromArray($headerStyle);
        $sheet->getRowDimension('4')->setRowHeight(25);
        
        // Get data
        $personil = $this->getPersonilData($filters);
        
        // Fill data
        $row = 5;
        $no = 1;
        
        foreach ($personil as $data) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $data['nrp']);
            $sheet->setCellValue('C' . $row, $data['nama_lengkap'] ?? $data['nama']);
            $sheet->setCellValue('D' . $row, $data['pangkat_singkatan'] ?? $data['nama_pangkat']);
            $sheet->setCellValue('E' . $row, $data['nama_jabatan']);
            $sheet->setCellValue('F' . $row, $data['nama_bagian']);
            $sheet->setCellValue('G' . $row, $data['nama_unsur']);
            $sheet->setCellValue('H' . $row, $data['status_kepegawaian'] ?? 'POLRI');
            $sheet->setCellValue('I' . $row, $data['JK']);
            $sheet->setCellValue('J' . $row, $data['status_nikah'] ?? '-');
            
            // Alternate row coloring
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F5F5');
            }
            
            $row++;
        }
        
        // Set column widths
        $columnWidths = [
            'A' => 5,
            'B' => 15,
            'C' => 30,
            'D' => 12,
            'E' => 25,
            'F' => 20,
            'G' => 15,
            'H' => 12,
            'I' => 5,
            'J' => 15
        ];
        
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        
        // Add borders to all cells
        $lastRow = $row - 1;
        $sheet->getStyle('A4:J' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        // Footer
        $footerRow = $lastRow + 2;
        $sheet->mergeCells('A' . $footerRow . ':J' . $footerRow);
        $sheet->setCellValue('A' . $footerRow, 'Dokumen ini digenerate oleh Sistem SPRIN POLRES Samosir');
        $sheet->getStyle('A' . $footerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $footerRow)->getFont()->setItalic(true);
        
        // Save file
        $filename = $filename ?? 'personil_' . date('Ymd_His') . '.xlsx';
        $filepath = $this->exportPath . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'download_url' => 'exports/' . $filename,
            'record_count' => count($personil)
        ];
    }
    
    /**
     * Export personil data to PDF with professional template
     */
    public function exportPersonilPDF($filters = [], $filename = null) {
        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20
        ]);
        
        // Get data
        $personil = $this->getPersonilData($filters);
        
        // Build HTML
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10pt; }
                .header { text-align: center; margin-bottom: 20px; }
                .title { font-size: 16pt; font-weight: bold; color: #1A237E; margin-bottom: 5px; }
                .subtitle { font-size: 10pt; color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { 
                    background-color: #1A237E; 
                    color: white; 
                    padding: 8px; 
                    text-align: left; 
                    font-size: 9pt;
                    border: 1px solid #333;
                }
                td { 
                    padding: 6px; 
                    border: 1px solid #999; 
                    font-size: 9pt;
                }
                tr:nth-child(even) { background-color: #f5f5f5; }
                .footer { 
                    text-align: center; 
                    margin-top: 20px; 
                    font-size: 8pt; 
                    color: #666; 
                    font-style: italic;
                }
                .badge { 
                    background-color: #1A237E; 
                    color: white; 
                    padding: 2px 6px; 
                    border-radius: 3px; 
                    font-size: 8pt;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">DATA PERSONIL POLRES SAMOSIR</div>
                <div class="subtitle">Tanggal Export: ' . date('d F Y H:i:s') . '</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">NRP</th>
                        <th style="width: 25%">Nama Lengkap</th>
                        <th style="width: 10%">Pangkat</th>
                        <th style="width: 20%">Jabatan</th>
                        <th style="width: 15%">Bagian</th>
                        <th style="width: 10%">Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        $no = 1;
        foreach ($personil as $data) {
            $html .= '
                    <tr>
                        <td style="text-align: center;">' . $no++ . '</td>
                        <td>' . htmlspecialchars($data['nrp']) . '</td>
                        <td>' . htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) . '</td>
                        <td>' . htmlspecialchars($data['pangkat_singkatan'] ?? $data['nama_pangkat']) . '</td>
                        <td>' . htmlspecialchars($data['nama_jabatan']) . '</td>
                        <td>' . htmlspecialchars($data['nama_bagian']) . '</td>
                        <td><span class="badge">' . htmlspecialchars($data['status_kepegawaian'] ?? 'POLRI') . '</span></td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                <p>Dokumen ini digenerate oleh Sistem SPRIN POLRES Samosir | Halaman {PAGENO} dari {nb}</p>
            </div>
        </body>
        </html>';
        
        $mpdf->WriteHTML($html);
        
        // Save file
        $filename = $filename ?? 'personil_' . date('Ymd_His') . '.pdf';
        $filepath = $this->exportPath . $filename;
        
        $mpdf->Output($filepath, 'F');
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'download_url' => 'exports/' . $filename,
            'record_count' => count($personil)
        ];
    }
    
    /**
     * Export unsur statistics
     */
    public function exportUnsurStats($format = 'excel') {
        $sql = "
            SELECT 
                u.nama_unsur,
                u.kode_unsur,
                COUNT(p.id) as total_personil,
                COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri,
                COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn,
                COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k
            FROM unsur u
            LEFT JOIN personil p ON u.id = p.id_unsur AND p.is_deleted = FALSE
            LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
            WHERE u.is_active = TRUE
            GROUP BY u.id, u.nama_unsur, u.kode_unsur
            ORDER BY u.urutan
        ";
        
        $stats = $this->db->fetchAll($sql);
        
        if ($format === 'excel') {
            return $this->createStatsExcel($stats, 'Statistik Unsur');
        } else {
            return $this->createStatsPDF($stats, 'Statistik Unsur');
        }
    }
    
    /**
     * Get personil data with filters
     */
    private function getPersonilData($filters = []) {
        $sql = "
            SELECT 
                p.id,
                p.nama,
                p.nama_lengkap,
                p.gelar_pendidikan,
                p.nrp,
                p.JK,
                p.status_nikah,
                p.status_ket,
                pg.nama_pangkat,
                pg.singkatan as pangkat_singkatan,
                j.nama_jabatan,
                b.nama_bagian,
                u.nama_unsur,
                mjp.nama_jenis as status_kepegawaian
            FROM personil p
            LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN bagian b ON p.id_bagian = b.id
            LEFT JOIN unsur u ON p.id_unsur = u.id
            LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ";
        
        $params = [];
        
        if (!empty($filters['unsur'])) {
            $sql .= " AND u.kode_unsur = :unsur";
            $params['unsur'] = $filters['unsur'];
        }
        
        if (!empty($filters['bagian'])) {
            $sql .= " AND b.id = :bagian";
            $params['bagian'] = $filters['bagian'];
        }
        
        $sql .= " ORDER BY u.urutan, b.nama_bagian, p.nama";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create statistics Excel
     */
    private function createStatsExcel($data, $title) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);
        
        // ... (similar to exportPersonilExcel)
        
        $filename = 'stats_' . date('Ymd_His') . '.xlsx';
        $filepath = $this->exportPath . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    /**
     * Clean old exports
     */
    public function cleanOldExports($days = 7) {
        $files = glob($this->exportPath . '*');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileTime = filemtime($file);
                if (($now - $fileTime) > ($days * 86400)) {
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
}
