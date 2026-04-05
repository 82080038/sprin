/**
 * api/activity_log.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?phpdeclare(strict_types=1);require_once__DIR__.'/../core/config.php';require_once__DIR__.'/../core/SessionManager.php';require_once__DIR__.'/../core/auth_helper.php';/Setsecurityheadersheader('Content-Type:application/json');try{/Generatesampleactivitylogdata$activities=[['type'=>'info-circle','action'=>'UserLogin','details'=>'Adminuserloggedinsuccessfully','timestamp'=>date('Y-m-dH:i:s',strtotime('-5minutes'))],['type'=>'database','action'=>'DatabaseQuery','details'=>'Personneldataretrievedfromdatabase','timestamp'=>date('Y-m-dH:i:s',strtotime('-10minutes'))],['type'=>'user','action'=>'PersonnelUpdate','details'=>'Personnelrecordupdatedsuccessfully','timestamp'=>date('Y-m-dH:i:s',strtotime('-15minutes'))],['type'=>'security','action'=>'SecurityCheck','details'=>'Securityvalidationcompleted','timestamp'=>date('Y-m-dH:i:s',strtotime('-20minutes'))],['type'=>'info-circle','action'=>'APIRequest','details'=>'APIendpointaccessedsuccessfully','timestamp'=>date('Y-m-dH:i:s',strtotime('-25minutes'))]];echojson_encode($activities);}catch(Exception$e){http_response_code(500);echojson_encode(['error'=>'Failedtogetactivitylog','message'=>$e->getMessage()]);}?>
