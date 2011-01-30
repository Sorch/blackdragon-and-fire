<?

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// BlackDragon & Fire Installer script

// revsion sun-30-jan-2010 07:25am

// PLEASE DON'T ALTER THE REVSION DATE THANKS THEHOLDER

require("global.php");
if (get_magic_quotes_gpc()) {
$_POST = strip($_POST);
}
head();
if (!file_exists("../config.php")) {
fatal_error("You must create a config.php file in the directory below this one.<br/><br/>A sample config.php can be found in config-sample.php in the root installation directory");
} else {
require("../db.php");
}

check_installed();

if ($_POST['action'] == "steptwo") {
	step_two();	
} elseif ($_POST['action'] == "stepthree") {
	step_three();
} else{
	step_one();
}


foot();

function step_two() {

// verify I can connect to database.
$db2 = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, 1);
if (!$db2) {
    fatal_error("I seem to have had difficulty connecting to the mysql database you specified.<br>The error MySQL returned is: " . mysql_error());
} else {
@mysql_select_db(DB_NAME, $db2) or fatal_error("I seem to have had difficulty choosing the database name you specified.<br>The error MySQL returned is: " . mysql_error());
}


print <<<HTML
<h1 class="T"><b>Step Two:</b></h1><br/>
<div class="T">I'll need you to give me a little info about your blog before we continue:</div>
<div id="error"></div><form name="installF" action="install.php" method="POST"><h3 class="T">
<label for="blogname">Blog Name:</label><input type="text" name="blogname" value="" tabindex="1" style=\"width: 200px\" maxlength=\"150\"><br />
<label for="blogdescription">Blog Description:</label><input type="text" name="blogdescription" value="" tabindex="2" style=\"width: 200px\" maxlength="150">
<br/>Don't worry, you can always change these later.<br/>
<input type="hidden" name="action" value="stepthree">
<br /><input type="submit" value ="Step Three »">
</form>
HTML;
}

function step_three() {

if (!$_POST['blogname']) {
	$blogname = "BlackDragon & Fire CMS";
} else {
	$blogname = $_POST['blogname'];
}
if (!$_POST['blogdescription']) {
	$blogdescription = "Just another BlackDragon & Fire Blog.";
} else {
	$blogdescription = $_POST['blogdescription'];
}
	$absolutepath = getcwd();
	$absolutepath = preg_replace("/admin$/i","",$absolutepath);
	$scriptpath = "./scripts/";
	$themepath = "./themes/";
	$activetheme = "default";
	$blogpath = $_SERVER['REQUEST_URI'];
	$blogpath = preg_replace("/admin\/install.php$/i","",$blogpath);
	$blogurl = "http://" . $_SERVER['HTTP_HOST'] . $blogpath;

print <<<HTML
<h1 class="T"><b>Step Three:</b></h1><br/>
<div class="T">First I'll be setting up a bunch of database information...</div>

HTML;


$db = new db;
$db->connect();
// first, lets set up the databases
create_dbs($db);


// default options
$db->query("INSERT INTO `".DB_PREFIX."options` VALUES('".mysql_escape_string($blogname)."','".mysql_escape_string($blogdescription)."','".mysql_escape_string($blogurl)."','".mysql_escape_string($blogpath)."','".mysql_escape_string($absolutepath)."','$themepath','$scriptpath','$activetheme',0)");

// first category
$db->query("INSERT INTO `".DB_PREFIX."categories` VALUES(1,'Uncategorized')");

// first user
$random_password = substr(md5(uniqid(microtime())), 0, 6);

$db->query("INSERT INTO `".DB_PREFIX."users` VALUES(1,'admin','".crypt($random_password,"\$1\$ajaxpass")."','Administrator',1)");

// first news post
$db->query("INSERT INTO `".DB_PREFIX."news` VALUES(0,1,1,'My First BlackDragon & Fire CMS Post','','Just a quick hello and thanks for installing BlackDragon & Fire CMS - From your development team. You may delete or modify this as you please!',UNIX_TIMESTAMP(),'',1,1)");

// first comment
$db->query("INSERT INTO `".DB_PREFIX."comments` VALUES(0,1,'Mr BlackDragon & Fire CMS','','http://bdf.sorch.info','This is the first BlackDragon & Fire CMS comment ^_^',UNIX_TIMESTAMP(),'')");

// first widgets
$db->query("INSERT INTO `".DB_PREFIX."widgets` VALUES(0,'Search','[search]')");
//$db->query("INSERT INTO `".DB_PREFIX."widgets` VALUES(0,'Latest 10 posts','[rss,&bull;&nbsp;,${blogurl}rss.php]')");
$db->query("INSERT INTO `".DB_PREFIX."widgets` VALUES(0,'About Me','Insert some information about yourself here!')");
$db->query("INSERT INTO `".DB_PREFIX."widgets` VALUES(0,'Links','&bull; <a href=\"http://code.google.com/p/blackdragon-and-fire\">Source Code</a><br />&bull; <a href=\"${blogurl}admin/\">Admin Interface</a>')");



// presentation options
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (1, 'Comments', 'NoComments', '', 'Add a comment to this post!', 'The text to display if there haven\'t been any comments on the post.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (2, 'Comments', 'Comments', '__NUM__, __S__', '__NUM__ comment__S__', 'The text to display if there are comments on the post.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (3, 'Comments', 'CommentsStart', '__NUM__, __S__, __TITLE__, __COMMENTS__', '<h3 id=\"comments\">__NUM__ Response__S__ to &#8220;__TITLE__&#8221;</h3><ol id=\"commentlist\" class=\"commentlist\">__COMMENTS__</ol>', 'The outline for the display of comments.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (4, 'Comments', 'Comment', '__ALT__, __ID__, __UNAME__, __DATE__, __BODY__', '<li class=\"__ALT__\" id=\"comment-__ID__\"><cite>__UNAME__</cite> Says:<br /><small class=\"commentmetadata\">__DATE__</small>__BODY__</li>', 'A single comment.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (5, 'Comments', 'CommentForm', '__ERRORS__, __COMMENTACTION__', '__ERRORS__<form onSubmit=\"__COMMENTACTION__\" method=\"post\" name=\"commentform\" id=\"commentform\"><p><input type=\"text\" name=\"author\" id=\"author\" value=\"\" size=\"22\" tabindex=\"1\" /><label for=\"author\"><small>Name</small></label></p><p><input type=\"text\" name=\"email\" id=\"email\" value=\"\" size=\"22\" tabindex=\"2\" /><label for=\"email\"><small>Mail (will not be published)</small></label></p><p><input type=\"text\" name=\"url\" id=\"url\" value=\"\" size=\"22\" tabindex=\"3\" /><label for=\"url\"><small>Website</small></label></p><p><textarea name=\"comment\" id=\"comment\" cols=\"100%\" rows=\"10\" tabindex=\"4\"></textarea></p><p><input name=\"submit\" type=\"submit\" id=\"submit\" tabindex=\"5\" value=\"Submit Comment\" /></p></form>', 'The Comment Form.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (6, 'Posts', 'Post', '__ID__, __SID__, __TITLE__, __DATE__, __AUTHOR__, __BODY__, __CATEGORY__, __COMMENTS__', '<div class=\"post\"><h2 id=\"post-__ID__\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__ID__)\" rel=\"bookmark\" title=\"Permanent Link to __TITLE__\">__TITLE__</a></h2><small>__DATE__ by __AUTHOR__</small><div class=\"entry\">__BODY__</div><p class=\"postmetadata\">Posted in <a href=\"javascript:;\" onClick=\"javascript:gotoPostPage(0,__SID__)\" rel=\"bookmark\" title=\"View posts from __CATEGORY__\">__CATEGORY__</a> &nbsp; &nbsp; <a href=\"javascript:;\" onClick=\"javascript:viewpost(__ID__)\" id=\"commentsLink\" rel=\"bookmark\" title=\"Comments in __TITLE__\">__COMMENTS__</a></p></div>', 'The template for a list of posts.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (7, 'Posts', 'SinglePost', '__ID__, __SID__, __TITLE__, __DATE__, __AUTHOR__, __BODY__, __CATEGORY__, __COMMENTS__, __PREVID__, __PREVPOST__, __NEXTID__, __NEXTPOST__', '<div class=\"navigation\"><div class=\"alignleft\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__PREVID__)\">__PREVPOST__</a></div><div class=\"alignright\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__NEXTID__)\">__NEXTPOST__</a></div></div><div class=\"post\"><h2 id=\"post-__ID__\"><a href=\"javascript:;\" rel=\"bookmark\" title=\"Permanent Link: __TITLE__\">__TITLE__</a></h2><div class=\"entrytext\"><br />__BODY__<br /><br /><br /><p class=\"postmetadata alt\"><small>This entry was posted at __DATE__ by __AUTHOR__ and is filed under __CATEGORY__.You can follow any responses to this entry through the __RSS__ feed. </small></p></div></div>', 'The template for viewing a single post.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (8, 'Posts', 'NoPosts', '', '<p>No posts have been added yet! Stay tuned!</p>', 'The text displayed when there are no posts.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (9, 'Posts', 'ReadMoreText', '', '<br /><br />Read the rest of this entry &raquo;', 'The \"Read More\" text.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (10, 'Widgets', 'Widget', '__ID__, __TITLE__, __TEXT__', '<ul id=\"w-__ID__\"><li><h2 class=\"handle\">__TITLE__</h2><p>__TEXT__</p></li></ul>', 'The template for a single widget.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (11, 'Loading Text', 'LoadingComment', '__THEMEPATH__', 'Saving.. <img src=\"__THEMEPATH__images/loading.gif\">', 'The text displayed while saving a comment.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (12, 'Loading Text', 'LoadingWidgets', '__THEMEPATH__', 'Loading.. <img src=\"__THEMEPATH__images/loading.gif\">', 'The text displayed while loading the widgets.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (13, 'Loading Text', 'LoadingContent', '__THEMEPATH__', 'Loading.. <img src=\"__THEMEPATH__images/loading.gif\">', 'The text displayed while loading any body content.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (14, 'Errors Text', 'ErrorsText', '__NUM__, __S__, __ERRORS__', '__NUM__ Error__S__ occurred: <ul>__ERRORS__</ul>', 'The template for one or more errors.');");
$db->query("INSERT INTO `".DB_PREFIX."presentation` (`pid`, `MainCat`, `SubTitle`, `Available`, `Default`, `Description`) VALUES (15, 'Errors Text', 'ErrorsItem', '__ERROR__', '<li>__ERROR__</li>', 'The template for a single error.');");
$db->disconnect();

print <<<HTML
<div class="T"><br /><br /><i>...Finished!</i>
<br/><br />
Your BlackDragon & Fire CMS installation is complete. You may <a href="${blogurl}admin/">Login</a> to the admin interface with the following credentials:
<br/>
<strong>Username:</strong> admin<br/>
<strong>Password:</strong> $random_password<br/><br/>
Do not forget this password!<br /><br />
<a href="$blogurl">View your blog now!</a> or <a href="${blogurl}admin/">View the admin interface now!</a>
<br /><br /><br />
You're all done!
</div>
HTML;


}

function create_dbs($db) {
$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."categories`;");
$db->query("CREATE TABLE `".DB_PREFIX."categories` (
  `sid` mediumint(8) unsigned NOT NULL auto_increment,
  `category_name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`sid`)
) TYPE=MyISAM;");

$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."comments`;");
$db->query("CREATE TABLE `".DB_PREFIX."comments` (
  `cid` mediumint(8) unsigned NOT NULL auto_increment,
  `id` mediumint(8) unsigned NOT NULL default '0',
  `uname` varchar(15) NOT NULL default '',
  `email` varchar(128) NOT NULL default '',
  `url` varchar(128) NOT NULL default '',
  `body` text NOT NULL,
  `date` int(10) default NULL,
  `ip` varchar(15) default NULL,
  PRIMARY KEY  (`cid`),
  KEY `news_id` (`id`),
  KEY `date` (`date`)
) TYPE=MyISAM;");

$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."news`;");
$db->query("CREATE TABLE `".DB_PREFIX."news` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `sid` mediumint(8) unsigned NOT NULL default '0',
  `subject` varchar(150) NOT NULL default '',
  `excerpt` text NOT NULL,
  `body` text NOT NULL,
  `date` int(10) default NULL,
  `ip` varchar(15) default NULL,
  `allowcomments` tinyint(1) NOT NULL default '0',
  `comments` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`),
  KEY `cat` (`uid`,`sid`),
  KEY `uid` (`uid`,`sid`,`id`)
) TYPE=MyISAM;");


$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."options`;");
$db->query("CREATE TABLE `".DB_PREFIX."options` (
  `BlogName` varchar(150) NOT NULL default '',
  `BlogDescription` varchar(150) NOT NULL default '',
  `BlogURL` varchar(150) NOT NULL default '',
  `BlogPath` varchar(150) NOT NULL default '',
  `AbsolutePath` varchar(150) NOT NULL default '',
  `ThemePath` varchar(150) NOT NULL default '',
  `ScriptPath` varchar(150) NOT NULL default '',
  `ActiveTheme` varchar(150) NOT NULL default '',
  `DefaultSID` mediumint(8) NOT NULL default '0'
) TYPE=MyISAM;");

$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."presentation`;");
$db->query("CREATE TABLE `".DB_PREFIX."presentation` (
  `pid` mediumint(8) NOT NULL auto_increment,
  `MainCat` varchar(150) NOT NULL default '',
  `SubTitle` varchar(150) NOT NULL default '',
  `Available` text NOT NULL,
  `Default` text NOT NULL,
  `Description` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`pid`)
) TYPE=MyISAM;");

$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."users`;");
$db->query("CREATE TABLE `".DB_PREFIX."users` (
  `uid` mediumint(8) unsigned NOT NULL auto_increment,
  `uname` varchar(15) NOT NULL default '',
  `pword` varchar(34) NOT NULL default '',
  `display_name` varchar(150) NOT NULL default '',
  `administrator` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `uname` (`uname`)
) TYPE=MyISAM;");

$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."widgets`;");
$db->query("CREATE TABLE `".DB_PREFIX."widgets` (
  `wid` mediumint(8) unsigned NOT NULL auto_increment,
  `widget_name` varchar(50) NOT NULL default '',
  `contents` text NOT NULL,
  PRIMARY KEY  (`wid`)
) TYPE=MyISAM;");

}

function step_one() {
print "<h1 class=\"T\"><b>FireBolt CMS Installation:</b></h1><br/><div class=\"T\">Welcome to the BlackDragon & Fire CMS installer! BlackDragon & Fire is beta and should <b>not</b> be used on production servers.</div><br/><form name=\"installF\" action=\"install.php\" method=\"POST\"><h3 class=\"T\">
<input type=\"hidden\" name=\"action\" value=\"steptwo\">
<br /><input type=\"submit\" value =\"Step Two »\">
</form>";
}

function check_installed() {
	$db2 = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, 1);
	@mysql_select_db(DB_NAME, $db2);
	$result = @mysql_query("SELECT COUNT(*) FROM `".DB_PREFIX."users` WHERE 1",$db2);
	if (@mysql_num_rows($result) != 0){ 
		fatal_error("It appears FireBolt CMS is already installed!");	
	}
}

function head() {

print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>BlackDragon & Fire CMS install Wizard</title>
	<link rel="stylesheet" href="adminstyle.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="rounded.css" type="text/css" media="screen" />
<script src="admin-scripts/rounded.js" type="text/javascript" ></script>

</head>
<body>

<div class="round" id="header"><img src="images/header.gif"></div>
 <div class="round" id="content">';

}

function fatal_error($error) {
	print "<h1 class=\"T\"><b>An Error has occurred: <br/><br/><font color=\"red\">$error</font></h1>";
	foot();
	exit;
}

function foot() {
print '</div><p>&copy; 2010 - 2011 BlackDragon & Fire CMS Development Team</p>
</body>
</html>';
}

?>
