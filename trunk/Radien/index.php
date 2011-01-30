<?php
// 
// 
//

if (!file_exists("config.php")) {
print "A config.php file must exist! Read README.txt!";
exit;
}

// browser detection and redirection
// only ie7 and firefox 1.5 should make it.
// browsers like opera sneak on by, and thats a-okay with me!

$agent = $_SERVER['HTTP_USER_AGENT'];
if (!preg_match("/MSIE 7.0/i",$agent) && !preg_match("/Firefox\/3.6.12/i",$agent)) {
	header("Location: noscript.php");
	exit;
}


require("config.php");
require("db.php");
$db = new db;
$db->connect();
$result = $db->query("SELECT `BlogName`,`BlogPath`,`BlogDescription`,`BlogURL`,`ThemePath`,`ActiveTheme`,`AbsolutePath`,`ScriptPath` FROM `".DB_PREFIX."options` LIMIT 1");
list($blogname,$blogpath,$blogdescription,$blogurl,$themepath,$activetheme,$absolutepath,$scriptpath) = mysql_fetch_row($result);

$index = file_get_contents($absolutepath . $themepath . $activetheme . "/index.html");
$index = str_replace("__ABSOLUTEPATH__",$absolutepath,$index);
$index = str_replace("__THEMEPATH__",$themepath . $activetheme . "/",$index);
$index = str_replace("__SCRIPTPATH__",$scriptpath,$index);
$index = str_replace("__BLOGURL__",$blogurl,$index);
$index = str_replace("__BLOGDESCRIPTION__",$blogdescription,$index);
$index = str_replace("__BLOGNAME__",$blogname,$index);
$index = str_replace("__ACTIVETHEME__",$activetheme,$index);


$db->disconnect();
print $index;
exit;
?>
