<?php
/**
 * PDF Export Helper
 * Uses browser's native print-to-PDF with CSS print media queries
 * This is the most reliable approach for local XAMPP installations
 */

class PDFExport {
    
    /**
     * Generate print-friendly HTML with print CSS
     * @param string $title Document title
     * @param string $content HTML content
     * @param string $css Custom CSS for print
     * @return string Complete HTML document ready for printing
     */
    public static function generatePrintView($title, $content, $css = '') {
        $defaultCSS = "
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            @media print {
                body {
                    font-family: 'Times New Roman', serif;
                    font-size: 12pt;
                    line-height: 1.5;
                }
                .no-print { display: none !important; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    border: 1px solid #000;
                    padding: 8px;
                    text-align: left;
                }
                table th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                }
                .print-footer {
                    text-align: center;
                    margin-top: 30px;
                    border-top: 1px solid #000;
                    padding-top: 10px;
                    font-size: 10pt;
                }
                .page-break { page-break-before: always; }
            }
            .print-header img { height: 60px; }
            .kop-surat { text-align: center; margin-bottom: 20px; }
        ";
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>$title</title>
    <style>
        $defaultCSS
        $css
    </style>
</head>
<body>
    <div class='print-header'>
        <h2 style='margin:0'>POLRES SAMOSIR</h2>
        <h3 style='margin:5px 0'>BAGIAN OPERASIONAL</h3>
        <p style='margin:0; font-size:11pt'>Jalan Raya Pangururan, Samosir, Sumatera Utara</p>
    </div>
    $content
    <div class='print-footer'>
        <p>Dicetak pada: " . date('d/m/Y H:i') . " oleh " . ($_SESSION['username'] ?? 'system') . "</p>
    </div>
</body>
</html>
        ";
    }
    
    /**
     * Trigger browser print dialog
     * Call this from JavaScript after generating print view
     */
    public static function printView() {
        echo "<script>window.print(); window.onafterprint = function() { window.close(); };</script>";
    }
    
    /**
     * Generate Polri-style document header
     * @param string $nomor Document number
     * @param string $perihal Subject
     * @param string $tanggal Date
     * @return string HTML header
     */
    public static function generatePolriHeader($nomor, $perihal, $tanggal = null) {
        $tanggal = $tanggal ?: date('d/m/Y');
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $bulan = $bulanRomawi[date('n') - 1];
        $tahun = date('Y');
        
        return "
        <div class='kop-surat'>
            <table style='width:100%; border:none; margin:0'>
                <tr>
                    <td style='width:15%; text-align:center; border:none; padding:0'>
                        <img src='/public/assets/images/logo-polri.png' style='height:80px' onerror=\"this.style.display='none'\" />
                    </td>
                    <td style='width:70%; text-align:center; border:none; padding:0'>
                        <h3 style='margin:5px 0; font-size:14pt'>KEPOLISIAN RESOR SAMOSIR</h3>
                        <h2 style='margin:5px 0; font-size:16pt'>BAGIAN OPERASIONAL</h2>
                        <p style='margin:0; font-size:10pt'>Jalan Raya Pangururan, Samosir, Sumatera Utara</p>
                    </td>
                    <td style='width:15%; text-align:center; border:none; padding:0'></td>
                </tr>
            </table>
            <hr style='border:2px solid #000; margin:10px 0'>
        </div>
        
        <div style='text-align:right; margin:20px 0'>
            <p style='margin:0'>Nomor: <strong>$nomor</strong></p>
            <p style='margin:0'>Tanggal: <strong>$tanggal</strong></p>
            <p style='margin:0'>Perihal: <strong>$perihal</strong></p>
        </div>
        ";
    }
    
    /**
     * Generate Polri-style document footer with signature
     * @param string $penandatangan Name of signatory
     * @param string $jabatan Position
     * @param string $nip NIP
     * @return string HTML footer
     */
    public static function generatePolriFooter($penandatangan, $jabatan, $nip = '') {
        return "
        <div style='text-align:right; margin-top:50px'>
            <p style='margin:0'>Samosir, " . date('d') . " " . ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][date('n')-1] . " " . date('Y') . "</p>
            <p style='margin:0'>$jabatan</p>
            <p style='margin:50px 0 0 0; font-weight:bold'>$penandatangan</p>
            " . ($nip ? "<p style='margin:0'>NIP. $nip</p>" : "") . "
        </div>
        ";
    }
}
