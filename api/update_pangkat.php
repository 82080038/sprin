/**
 * api/update_pangkat.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);/***SimplepangkatupdateAPI*/require_once'core/config.php';/Enableerrorreportingfordebuggingerror_reporting(E_ALL);ini_set('display_errors',1);header('Content-Type:application/json');/GetPOSTdata$action=filter_input(INPUT_POST,\'\1\',FILTER_DEFAULT)??\2,'nrp',FILTER_DEFAULT)??'';$id_pangkat=filter_input(INPUT_POST,\'\1\',FILTER_DEFAULT)??\2{/Updatepangkat$sql="UPDATEpersonilSETid_pangkat=?WHEREnrp=?";$stmt=mysqli_prepare($koneksi,$sql);mysqli_stmt_bind_param($stmt,"is",$id_pangkat,$nrp);if(mysqli_stmt_execute($stmt)){$affected=mysqli_stmt_affected_rows($stmt);if($affected>0){echojson_encode('\1\'=>\2);}else{echojson_encode('\1\'=>\2);}}else{echojson_encode('\1\'=>\2);}mysqli_stmt_close($stmt);}else{echojson_encode('\1\'=>\2);}mysqli_close($koneksi);?>
