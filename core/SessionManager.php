/**
 * core/SessionManager.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?phpdeclare(strict_types=1);/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}/***SessionManager-Centralizedsessionhandling*Solvessessionconflictsandmultiplesession_start()issues*/classSessionManager{privatestatic$started=false;/***Startsessionsafely-onlyonce*/publicstaticfunctionstart(){if(!self::$started&&session_status()===PHP_SESSION_NONE){/Setsessionparametersbeforestartini_set('session.cookie_httponly',1);ini_set('session.cookie_secure',0);/Setto1ifusingHTTPSini_set('session.use_strict_mode',1);ini_set('session.cookie_samesite','Lax');ini_set('session.gc_maxlifetime',3600);/1hoursession_start();self::$started=true;/RegeneratesessionIDforsecurityif(!isset($_SESSION['regenerated'])){session_regenerate_id(true);$_SESSION['regenerated']=true;}}}/***Checkifsessionisactive*/publicstaticfunctionisActive(){returnself::$started||session_status()===PHP_SESSION_ACTIVE;}/***Destroysession*/publicstaticfunctiondestroy(){if(self::isActive()){session_unset();session_destroy();self::$started=false;}}/***Clearallsessiondata*/publicstaticfunctionclear(){if(self::isActive()){$_SESSION=array();}}}?>
