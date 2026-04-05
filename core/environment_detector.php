/**
 * core/environment_detector.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php

declare(strict_types=1);
/***EnvironmentDetectorbasedonbestpractices*Automaticallydetectsdevelopmentvsproductionenvironment*/classEnvironmentDetector{privatestatic$environment=null;publicstaticfunctiongetEnvironment(){if(self::$environment!==null){returnself::$environment;}/Checkforcommondevelopmentindicators$isDevelopment=($_SERVER['SERVER_NAME']==='localhost'||$_SERVER['SERVER_NAME']==='127.0.0.1'||$_SERVER['SERVER_ADDR']==='127.0.0.1'||$_SERVER['SERVER_ADDR']==='::1'||strpos($_SERVER['SERVER_NAME'],'.local')!==false||strpos($_SERVER['SERVER_NAME'],'.dev')!==false||strpos($_SERVER['SERVER_NAME'],'.test')!==false||isset($_ENV['DEV'])||isset($_ENV['DEVELOPMENT'])||isset($_ENV['APP_ENV'])&&$_ENV['APP_ENV']==='development');self::$environment=$isDevelopment?'development':'production';returnself::$environment;}publicstaticfunctionisDevelopment(){returnself::getEnvironment()==='development';}publicstaticfunctionisProduction(){returnself::getEnvironment()==='production';}publicstaticfunctionconfigureErrorReporting(){if(self::isDevelopment()){/Development:Showallerrorserror_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);ini_set('log_errors',1);ini_set('track_errors',1);ini_set('html_errors',1);}else{/Production:Logerrorsbutdon'tdisplaythemerror_reporting(E_ALL&~E_DEPRECATED&~E_STRICT);ini_set('display_errors',0);ini_set('display_startup_errors',0);ini_set('log_errors',1);ini_set('track_errors',1);ini_set('html_errors',0);}}}/Auto-configureerrorreportingEnvironmentDetector::configureErrorReporting();/Defineconstantsforbackwardcompatibilitydefine('DEVELOPMENT_MODE',EnvironmentDetector::isDevelopment());define('PRODUCTION_MODE',EnvironmentDetector::isProduction());?>
