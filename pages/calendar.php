/**
 * pages/calendar.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?phpdeclare(strict_types=1);/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}require_once'../core/config.php';require_once'../core/auth_check.php';$page_title='Kalender-POLRESSamosir';include'../includes/components/header.php';?><divclass="container"><divclass="page-header"><h1><iclass="fasfa-calendar-altme-2"></i>Kalender</h1><pclass="text-muted">Manajemenjadwaldankalenderkegiatan</p></div><divclass="alertalert-info"><iclass="fasfa-info-circleme-2"></i>Halamaninidalampengembangan</div><divclass="card"><divclass="card-header"><h5class="mb-0">KalenderKegiatan</h5></div><divclass="card-body"><pclass="text-muted">Fiturkalenderakansegeratersedia.</p></div></div></div><?phpinclude'../includes/components/footer.php';?>
