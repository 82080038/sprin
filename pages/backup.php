/**
 * pages/backup.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php

declare(strict_types=1);
/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}require_once'../core/config.php';require_once'../core/auth_helper.php';check_authentication();$page_title='Backup-POLRESSamosir';include'../includes/components/header.php';?><divclass='container'><h1><iclass='fasfa-database'></i>Backup&Restore</h1><divclass='alertalert-info'>Halamaninidalampengembangan</div></div><?phpinclude'../includes/components/footer.php';?>
