/**
 * api/personil.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);require_once__DIR__.'/../core/config.php';require_once__DIR__.'/../core/SessionManager.php';require_once__DIR__.'/../core/auth_helper.php';/StartsessionusingSessionManagerSessionManager::start();/SetCORSheadersheader('Access-Control-Allow-Origin:*');header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers:Content-Type,Authorization');header('Content-Type:application/json');/Handlepreflightrequestsif($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(200);exit;}/Checkauthenticationfornon-GETrequestsif($_SERVER['REQUEST_METHOD']!=='GET'&&!AuthHelper::validateSession()){http_response_code(401);echojson_encode(['success'=>false,'message'=>'Unauthorized']);exit;}try{$pdo=newPDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';unix_socket='.DB_SOCKET,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);if($_SERVER['REQUEST_METHOD']==='GET'){/Getpersonillist$stmt=$pdo->query("SELECTp.*,b.nama_bagianFROMpersonilpLEFTJOINbagianbONp.id_bagian=b.idLIMIT10");$personil=$stmt->fetchAll(PDO::FETCH_ASSOC);echojson_encode(['success'=>true,'data'=>$personil,'count'=>count($personil)]);}}catch(PDOException$e){http_response_code(500);echojson_encode(['success'=>false,'message'=>'Databaseerror:'.$e->getMessage()]);}?>
