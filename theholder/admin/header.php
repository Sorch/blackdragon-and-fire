<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

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
