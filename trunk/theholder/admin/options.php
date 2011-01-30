<?php
// 
// See index.php for full license.

require('header.php');

if ($verified == 0) {
    bounceout("I, options.php, got an unauthorized request");
}



if ($_POST['action'] == "get_themes") {
	get_themes();
} elseif ($_POST['action'] == "save") {
	save();
} else {
	bounceout("I, options.php, got a action, but it wasn't one I recognized");
}
$db->disconnect();

function save() {
global $db,$administrator;
	print "<options>";
	$blogname = $_POST['blogname'];
	$blogdescription = $_POST['blogdescription'];
	$blogurl = $_POST['blogurl'];
	$blogpath = $_POST['blogpath'];
	$scriptpath = $_POST['scriptpath'];
	$themepath = $_POST['themepath'];
	$absolutepath = $_POST['absolutepath'];
	$activetheme = $_POST['activetheme'];
	$defaultsid = $_POST['defaultsid'];
	$errors = Array();
	// basic checking
	if ($administrator == 0) {
	print "<errnum>1</errnum><errors><error>You must be an administrator to edit blog options!</error></errors></options>";
	exit;	
	}
	if (!is_numeric($defaultsid)) {
		$defaultsid = 0;
	}
	if (trim($blogname) == "") {
		$errors[] = "You must enter a Blog Name!";
	}
	if (trim($blogurl) == "") {
		$errors[] = "You must enter a Blog URL! This is the URL to your blog.";	
	}

	if (trim($blogpath) == "") {
		$errors[] = "You must enter a Blog Path! This is the relative path to your blog. '/' is the default.";
	}

	$maybeabsolutepath = getcwd();
	$maybeabsolutepath = preg_replace("/admin$/i","",$maybeabsolutepath);
	if (trim($absolutepath) == "") {
		$validabsolute = 0;
		$errors[] ="You must enter an Absolute Path! This is the absolute path to your blog. Perhaps it is '$maybeabsolutepath'?";
	} else {
	// advanced 
		if (!is_readable($absolutepath)) {
			$validabsolute = 0;	
			$errors[] = "I detected that the absolute path you entered is not readable! Does it exist? Are its permissions correct? Maybe you want '$maybeabsolutepath'?";	
		} else {
			$validabsolute = 1;	
		}
	}
	
	if (trim($themepath) == "") {
		$errors[] = "You must enter a Theme Path! The default is './themes'";	
	} else {
		if ($validabsolute == 1 && !is_readable($absolutepath . $themepath)) {
			$errors[] = "I detected that the Theme Path you entered is not readable! Does it exist? Are its permissions correct? The default is './themes'";
		}
	}
	if (trim($scriptpath) == "") {
		$errors[] = "You must enter a Script Path! The default is './scripts'";	
	} else {
		if ($validabsolute == 1 && !is_readable($absolutepath . $scriptpath)) {
			$errors[] = "I detected that the Script Path you entered is not readable! Does it exist? Are its permissions correct? The default is './scripts'";
		}
	}
	
		
	
	if (count($errors) != 0) {
	        print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
	        foreach ($errors as $error) {
	            print "<error>$error</error>";
	        } 
	        print "</errors>";			
	} else {
		$db->query("UPDATE `".DB_PREFIX."options` SET `BlogName`='".mysql_escape_string($blogname)."',`BlogDescription`='".mysql_escape_string($blogdescription)."',`BlogURL`='".mysql_escape_string($blogurl)."',`BlogPath`='".mysql_escape_string($blogpath)."',`ScriptPath`='".mysql_escape_string($scriptpath)."',`ThemePath`='".mysql_escape_string($themepath)."',`AbsolutePath`='".mysql_escape_string($absolutepath)."',`ActiveTheme`='".mysql_escape_string($activetheme)."',`DefaultSID`=$defaultsid WHERE 1 LIMIT 1");
		print "<errnum>0</errnum>";
	}
	
	
	print "</options>";
	
	
	
	
}

function get_themes() {
global $db;
$selected = $_POST['selected'];
$result = $db->query("SELECT `AbsolutePath`,`ThemePath` FROM `".DB_PREFIX."options` LIMIT 1");
list($absolutepath,$themepath) = mysql_fetch_row($result);

	if ($handle = opendir($absolutepath . $themepath)) {
	   	print "<options>";
	   	while (false !== ($file = readdir($handle))) {
	       if ($file != "." && $file != "..") {
	       	print "<theme>";
	       	print "<name>".xmlencode($file)."</name>";
	       	if ($selected == $file) {
	       		print "<selected>1</selected>";
	       	} else {
	       		print "<selected>0</selected>";
	       	}
			print "</theme>";
	       }
	   }
	   closedir($handle);
	print "</options>";
	} else {
		bounceout("I, options.php, tried to read a directory that didn't exist while trying to get the current themes");	
	}

	
}

?>
