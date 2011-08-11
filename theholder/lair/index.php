<?php
//
require("../db.php");
$db = new db;
$db->connect();
$result = $db->query("SELECT `BlogName`,`BlogPath`,`BlogDescription`,`BlogURL`,`ThemePath`,`ActiveTheme`,`AbsolutePath`,`ScriptPath` FROM `".DB_PREFIX."options` LIMIT 1");
list($blogname,$blogpath,$blogdescription,$blogurl,$themepath,$activetheme,$absolutepath,$scriptpath) = mysql_fetch_row($result);

$index = file_get_contents("admin.html");
$index = str_replace("__ABSOLUTEPATH__",$absolutepath,$index);
$index = str_replace("__THEMEPATH__",$themepath . $activetheme . "/",$index);
$index = str_replace("__SCRIPTPATH__",$scriptpath,$index);
$index = str_replace("__BLOGURL__",$blogurl,$index);
$index = str_replace("__BLOGPATH__",$blogpath,$index);
$index = str_replace("__BLOGDESCRIPTION__",$blogdescription,$index);
$index = str_replace("__BLOGNAME__",$blogname,$index);
$index = str_replace("__ACTIVETHEME__",$activetheme,$index);


$db->disconnect();
print $index;
exit;
?>
