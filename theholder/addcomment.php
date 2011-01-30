<?php
// Copyright (c) 
// 
// See index.php for full license.

ini_set('magic_quotes_gpc', "0");

include("db.php");
include("global.php");

$db = new db;
$db->connect();

$user = $_POST['user'];
if (!$user) {
$user = $_POST['author'];
}
$email = $_POST['email'];
$url = $_POST['url'];

$thebody = $_POST['comment'];
$id = $_POST['id'];
$errors = Array();

if (!is_numeric($id)) {
$errors[] = "No ID!";
$id = 0;
} else {
$result = $db->query("SELECT `allowcomments`,`uid`,`sid` FROM `".DB_PREFIX."news` WHERE `id`=$id LIMIT 1", false);
             if (mysql_num_rows($result) != 1) { 
                 $errors[] = "No such ID! (Maybe the post was deleted?)"; 
             }
$post = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($post["allowcomments"] == 0) {
		$errors[] = "This post does not allow comments!";
	}
}

if (trim($user) == '') {
$errors[] = "You must enter your author name!";
} elseif (preg_match("/[^A-Za-z0-9 ]/",$user)) {
	$errors[] = "Your Name can only contain A-Z, a-z, 0-9, and spaces. ";
}
if (trim($thebody)=='') {
	$errors[] = "You must enter a comment!";
}

// sanitize!
$thebody = html_filter($thebody);
$user = html_filter($user);
$email = html_filter($email);
$url = html_filter($url);

$db->free($result);
$q = "SELECT `cid` FROM `".DB_PREFIX."comments` WHERE `id`=$id AND `uname`='".mysql_escape_string($user)."' AND `email`='".mysql_escape_string($email)."' AND `url`='".mysql_escape_string($url)."' AND `body`='".mysql_escape_string($thebody)."'";
$result = $db->query("$q", false);
if (mysql_num_rows($result) != 0) {
$errors[] = "This would be a duplicate post!";
}

if ($_POST['noxml'] == 1) {
	if (count($errors) == 0) {
		addcomment ($id,$user,$email,$url,$thebody);
	} else {
		header("Content-type: text/plain");
		print "Errors:\n" . implode("\n",	$errors) . "\nPlease go back to correct these errors.";
	}
} else {
	header("Content-type: text/xml");
	print "<addcomment>";
	if (count($errors) == 0) {
	addcomment ($id,$user,$email,$url,$thebody);
	} else {
		print "<info><errornum>".count($errors)."</errornum></info>";
		print "<errors>";
		foreach ($errors as $error) {
			print "<error>$error</error>";
		}
		print "</errors>";
	}
	print "</addcomment>";
}
$db->disconnect();
function addcomment ($id,$user,$email,$url,$thebody) {
global $db;

$time = time();
$ip = getenv('REMOTE_ADDR');
$db->query("INSERT INTO `".DB_PREFIX."comments` (`cid`,`id`,`uname`,`email`,`url`,`body`,`date`,`ip`) VALUES (0,$id,'".mysql_escape_string($user)."','".mysql_escape_string($email)."','".mysql_escape_string($url)."','".mysql_escape_string($thebody)."',$time,'$ip')");
$cid = mysql_insert_id();
$db->query("UPDATE `".DB_PREFIX."news` SET `comments`=`comments`+1 WHERE `id`=$id");
$result = $db->query("SELECT `comments` FROM `".DB_PREFIX."news` WHERE `id`=$id");
$num = mysql_result($result, 0);
$time = compose_time($time);
	if ($_POST['noxml'] == 1) {
		header("Location: noscript.php?id=$id");
	} else {
	print "<info><errornum>0</errornum><num>$num</num><id>$cid</id><date>$time</date><user>$user</user><url>$url</url><body>".xmlencode(newsbody($thebody,1))."</body></info>";
	}
}

?>
