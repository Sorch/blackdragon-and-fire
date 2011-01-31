<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////


if (!file_exists("config.php")) {
print "A config.php file must exist! Read README.txt!";
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
