/**
 * api/performance_metrics.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?phpdeclare(strict_types=1);require_once__DIR__.'/../core/config.php';require_once__DIR__.'/../core/SessionManager.php';require_once__DIR__.'/../core/auth_helper.php';/Setsecurityheadersheader('Content-Type:application/json');try{/Generatesampleperformancedata$labels=[];$response_times=[];/Generatelast10datapointsfor($i=9;$i>=0;$i--){$labels[]=date('H:i:s',strtotime("-$iminutes"));$response_times[]=rand(50,200);/Randomresponsetimesinms}/Getsystemresources$memory_usage=memory_get_usage()/1024/1024;/MB$cpu_usage=rand(10,80);/SimulatedCPUusage$performance_data=['labels'=>$labels,'response_times'=>$response_times,'memory_usage'=>round($memory_usage,2),'cpu_usage'=>$cpu_usage,'timestamp'=>date('Y-m-dH:i:s')];echojson_encode($performance_data);}catch(Exception$e){http_response_code(500);echojson_encode(['error'=>'Failedtogetperformancemetrics','message'=>$e->getMessage()]);}?>
