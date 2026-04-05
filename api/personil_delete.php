/**
 * api/personil_delete.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);session_start();require_once__DIR__.'/../core/config.php';/Checkauthenticationif(!isset($_SESSION['logged_in'])||$_SESSION['logged_in']!==true){header('Content-Type:application/json');echojson_encode(['success'=>false,'message'=>'Unauthorized']);exit;}/OnlyacceptPOSTrequestsif($_SERVER['REQUEST_METHOD']!=='POST'){header('Content-Type:application/json');echojson_encode(['success'=>false,'message'=>'Invalidrequestmethod']);exit;}/GetJSONinput$json_input=file_get_contents('php://input');$data=json_decode($json_input,true);if(!isset($data['id'])||empty($data['id'])){header('Content-Type:application/json');echojson_encode(['success'=>false,'message'=>'IDpersoniltidakvalid']);exit;}try{/Databaseconnectionusingconfigconstants$dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME.";unix_socket=/opt/lampp/var/mysql/mysql.sock";$pdo=newPDO($dsn,DB_USER,DB_PASS);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);/Softdeletepersonil(setis_deleted=true)$stmt=$pdo->prepare("UPDATEpersonilSETis_deleted=true,updated_at=NOW()WHEREid=?");$stmt->execute([$data['id']]);if($stmt->rowCount()>0){header('Content-Type:application/json');echojson_encode(['success'=>true,'message'=>'Datapersonilberhasildihapus']);}else{header('Content-Type:application/json');echojson_encode(['success'=>false,'message'=>'Datapersoniltidakditemukan']);}}catch(Exception$e){header('Content-Type:application/json');echojson_encode(['success'=>false,'message'=>'Databaseerror:'.$e->getMessage()]);}?>
