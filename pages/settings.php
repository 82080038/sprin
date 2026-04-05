/**
 * pages/settings.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?phpdeclare(strict_types=1);/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}require_once__DIR__.'/../core/config.php';require_once__DIR__.'/../core/auth_check.php';$page_title='Settings-POLRESSamosir';include__DIR__.'/../includes/components/header.php';?><divclass="container"><divclass="page-header"><h1><iclass="fasfa-cogme-2"></i>Pengaturan</h1><pclass="text-muted">Konfigurasisistemdanpreferensiaplikasi</p></div><divclass="alertalert-info"><iclass="fasfa-info-circleme-2"></i>Halamaninidalampengembangan</div><divclass="card"><divclass="card-header"><h5class="mb-0">PengaturanSistem</h5></div><divclass="card-body"><pclass="text-muted">Fiturpengaturanakansegeratersedia.</p></div></div></div><?phpinclude__DIR__.'/../includes/components/footer.php';?>?>
