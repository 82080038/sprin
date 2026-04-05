/**
 * security/create_default_user.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php/DevelopmentErrorReportingif(!defined('DEVELOPMENT_MODE')){error_reporting(E_ALL);ini_set('display_errors',1);ini_set('display_startup_errors',1);}declare(strict_types=1);/Insertdefaultuserbagopsrequire_once'core/config.php';try{$pdo=newPDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASS);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);/Checkifuseralreadyexists$stmt=$pdo->prepare("SELECTidFROMusersWHEREusername=?");$stmt->execute(['bagops']);$existing=$stmt->fetch();if(!$existing){/InsertdefaultuserwithArgon2IDhash$passwordHash='$argon2id$v=19$m=65536,t=4,p=3$OHNlTGE0MC90cU5VMFZwdw$iMJrqO/ojh490yMDtHWtsVpImxVprmx9u8VetcjT0Ww';$stmt=$pdo->prepare("INSERTINTOusers(username,password_hash,email,full_name,role,is_active)VALUES(?,?,?,?,?,?)");$stmt->execute(['bagops',$passwordHash,'bagops@polres-samosir.polri.go.id','AdministratorBAGOPS','admin',1]);echo"Defaultuser'bagops'createdsuccessfully!\n";}else{echo"User'bagops'alreadyexists.\n";}/Testlogin$stmt=$pdo->prepare("SELECTid,username,password_hash,role,is_activeFROMusersWHEREusername=?ANDis_active=1");$stmt->execute(['bagops']);$user=$stmt->fetch();if($user){echo"Userfoundindatabase:\n";echo"-ID:".$user['id']."\n";echo"-Username:".$user['username']."\n";echo"-Role:".$user['role']."\n";echo"-Active:".$user['is_active']."\n";}}catch(PDOException$e){echo"Error:".$e->getMessage()."\n";}?>
