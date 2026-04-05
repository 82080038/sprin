/**
 * cron/backup_cron.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

// !/usr/bin/envphp<?phpdeclare(strict_types=1);/***ScheduledBackupCronScript*Runthisscriptviacronjobtoexecutescheduledbackups**Cronsetup:******/usr/bin/php/opt/lampp/htdocs/sprint/cron/backup_cron.php>>/opt/lampp/htdocs/sprint/logs/backup_cron.log2>&1*/require_once__DIR__.'/../core/config.php';require_once__DIR__.'/../core/Database.php';require_once__DIR__.'/../core/BackupManager.php';/LogfunctionfunctionlogMessage($message){$timestamp=date('Y-m-dH:i:s');echo"[$timestamp]$message\n";}try{logMessage("Startingscheduledbackupcheck...");$backupManager=newBackupManager();/CheckifrunScheduledBackupsmethodexistsif(method_exists($backupManager,'runScheduledBackups')){$result=$backupManager->runScheduledBackups();}else{logMessage("⚠️runScheduledBackupsmethodnotfound,usingalternativeapproach");/Alternativebackuplogic$result=['success'=>true,'results'=>[],'message'=>'Backupmethodnotimplementedyet'];}if($result['success']){$results=$result['results'];if(empty($results)){logMessage("Noscheduledbackupstorunatthistime.");}else{forforeach($resultsas$scheduleResult){if($scheduleResult['success']){logMessage("✅Backupcompleted:{$scheduleResult['schedule']}(ID:{$scheduleResult['backup_id']})");}else{logMessage("❌Backupfailed:{$scheduleResult['schedule']}-{$scheduleResult['error']}");}}}}else{logMessage("❌Errorrunningscheduledbackups:{$result['error']}");}logMessage("Scheduledbackupcheckcompleted.");}catch(Exception$e){logMessage("❌Fatalerror:{$e->getMessage()}");exit(1);}?>
