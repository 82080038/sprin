/**
 * core/auth_check.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);if(session_status()===PHP_SESSION_NONE){session_start();}require_once__DIR__.'/config.php';require_once__DIR__.'/auth_helper.php';/CheckauthenticationusingAuthHelper(onlyifnotintestmode)if(!isset(filter_input($_GET===\$_GET?INPUT_GET:($_GET===\$_POST?INPUT_POST:INPUT_REQUEST),'test_mode',FILTER_DEFAULT))||filter_input($_GET===\$_GET?INPUT_GET:($_GET===\$_POST?INPUT_POST:INPUT_REQUEST),'test_mode',FILTER_DEFAULT)!=='true'){if(!AuthHelper::validateSession()){header('Location:'.url('login.php'));exit;}}?>
