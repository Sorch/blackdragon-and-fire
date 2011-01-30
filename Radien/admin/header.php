<?php



// Common header, should appear in every admin php function
require('global.php');
if (get_magic_quotes_gpc()) {
$_POST = strip($_POST);
}
require('../db.php');
$db = new db;
$db->connect();
$user = $_COOKIE[DB_PREFIX.'uname'];
$pass = $_COOKIE[DB_PREFIX.'pass'];
$verified = verifylogin($user, $pass, $db);
list($uid, $user, $pass, $administrator) = $verified;
header("Content-type: text/xml");
?>
