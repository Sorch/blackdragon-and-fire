<?
// 
// See index.php for full license.
require('db.php');
require('global.php');

header("Content-type: text/xml");
$db = new db;
$db->connect();
$result = $db->query("SELECT `BlogName`,`BlogPath`,`BlogDescription`,`BlogURL`,`ThemePath`,`ActiveTheme`,`AbsolutePath`,`ScriptPath`,`DefaultSID` FROM `".DB_PREFIX."options` LIMIT 1");
list($blogname,$blogpath,$blogdescription,$blogurl,$themepath,$activetheme,$absolutepath,$scriptpath,$defaultsid) = mysql_fetch_row($result);


print "<options>
	<info>
	<defaultsid>$defaultsid</defaultsid>
	<blogname>".xmlencode($blogname)."</blogname>
	<blogpath>".xmlencode($blogpath)."</blogpath>
	<blogurl>".xmlencode($blogurl)."</blogurl>
	<blogdescription>".xmlencode($blogdescription)."</blogdescription>
	<scriptpath>".xmlencode($scriptpath)."</scriptpath>
	<absolutepath>".xmlencode($absolutepath)."</absolutepath>
	<themepath>".xmlencode($themepath)."</themepath>
	<activetheme>".xmlencode($activetheme)."</activetheme>
	<dbprefix>".DB_PREFIX."</dbprefix>
	</info>
</options>";
$db->disconnect();
?>
