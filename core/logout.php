/**
 * core/logout.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);session_start();require_once__DIR__.'/config.php';require_once__DIR__.'/auth_helper.php';/UseAuthHelperforproperlogoutAuthHelper::logout();/Redirecttologinheader('Location:'.url('login.php'));exit;?>
