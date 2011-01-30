<?php

// 
// See index.php for full license.

header("Content-type: text/xml");
require('global.php');
require('../db.php');
$db = new db;
$db->connect();
print "<info>";
$user = $_POST['uname'];
$pass = $_POST['pass'];
$verified = verifylogin($user, $pass, $db);
if ($verified == 0) {
    print '<login><info><error>Incorrect User/Pass!</error></info></login>';
} else {
    print '<uname>' . $user . '</uname><pass>' . $verified[2] . '</pass>';
} 
print "</info>";
$db->disconnect();

?>
