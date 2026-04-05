/**
 * core/calendar_config.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);/GoogleCalendarAPIConfigurationif(!defined('GOOGLE_CLIENT_ID')){define('GOOGLE_CLIENT_ID','YOUR_GOOGLE_CLIENT_ID');}if(!defined('GOOGLE_CLIENT_SECRET')){define('GOOGLE_CLIENT_SECRET','YOUR_GOOGLE_CLIENT_SECRET');}if(!defined('GOOGLE_REDIRECT_URI')){define('GOOGLE_REDIRECT_URI',base_url('oauth_callback.php');}/RequiredGoogleAPIscopesforCalendarif(!defined('GOOGLE_SCOPES')){define('GOOGLE_SCOPES',implode('',['https://www.googleapis.com/auth/calendar.readonly','https://www.googleapis.com/auth/calendar.events']));}/Databaseconfigurationforschedulesif(!defined('DB_HOST')){define('DB_HOST','localhost');}if(!defined('DB_NAME')){define('DB_NAME','bagops');}if(!defined('DB_USER')){define('DB_USER','root');}if(!defined('DB_PASS')){define('DB_PASS','root');}/Scheduletypesif(!defined('SHIFT_TYPES')){define('SHIFT_TYPES',['PAGI'=>'06:00-14:00','SIANG'=>'14:00-22:00','MALAM'=>'22:00-06:00','FULL_DAY'=>'00:00-23:59']);}/Eventcolorsforcalendarif(!defined('EVENT_COLORS')){define('EVENT_COLORS',['PAGI'=>'#4285F4','SIANG'=>'#EA4335','MALAM'=>'#FBBC04','FULL_DAY'=>'#34A853','CUTI'=>'#FF6F00','LEMBUR'=>'#9E9E9E']);}?>
