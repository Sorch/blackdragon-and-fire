<?php
// 
// See index.php for full license.

require('header.php');

if ($verified == 0) {
	bounceout("I got an unauthorized request.");
}

if ($_POST['action'] == "delete") {
	delete();
} elseif ($_POST['action'] == "getusers") {
    get_users();
} elseif ($_POST['action'] == "edit") {
	edit();
} elseif ($_POST['action'] == "create") {
	create();
} else {
bounceout("I, users.php, got an action, but it wasn't one i recognized: " . $_POST['action']);
}

   $db->disconnect();

function edit () {
	global $db, $administrator, $uid, $user;
	print "<users>";
	$errors = Array();
	$theuid = $_POST['uid'];
	if (!is_numeric($theuid)) {
		bounceout("I, users.php, got a uid that wasn't numeric.");
	}
	$uname = $_POST['uname'];
	if (trim($uname) == "") {
		$errors[] = "You forgot to enter a username!";	
	}
	if ($theuid != $uid && $administrator == 0) {
		$errors[] = "You can't edit that user!";
	}
	$name = $_POST['name'];
	if (trim($name) == "") {
		$errors[] = "You forgot to enter a display name!";	
	}
	$isadministrator = $_POST['administrator'];
	if ($isadministrator != 1) {
		$isadministrator = 0;	
	}
	$password = $_POST['password'];

	if (count($errors) == 0) {
		if ($password) {
	    	$password = crypt($password, "\$1\$ajaxpass");
	    } 
          	doedit($uname,$name,$password,$isadministrator,$theuid);       	
    		print "<info><errornum>0</errornum><setcookies>".($uid == $theuid? "1" : "0")."</setcookies><uname>".$uname."</uname><password>".$password."</password></info>";
   } else {
    		$result = $db->query("SELECT `uname`,`display_name`,`administrator` FROM `".DB_PREFIX."users` WHERE `uid`=$uid");
    		list($uname,$name,$administrator) = mysql_fetch_row($result);
    		print "<info><errornum>" . count($errors) . "</errornum></info><errors>";
		foreach ($errors as $err) {
			print "<error>".xmlencode($err)."</error>";
		}
		print "</errors><uid>$uid</uid>";
	}
	
   print "</users>";	 
}


function doedit($uname,$name,$password,$administrator,$uid) {
	global $db;
	$db->query('UPDATE `'.DB_PREFIX.'users` SET `uname`=\''.mysql_escape_string($uname).'\',`display_name`=\''.mysql_escape_string($name).'\',`administrator`=\''.mysql_escape_string($administrator).'\' WHERE `uid`='.$uid.' LIMIT 1');
	if ($password) {
	$db->query('UPDATE `'.DB_PREFIX.'users` SET `pword`=\''.mysql_escape_string($password).'\' WHERE `uid`='.$uid.' LIMIT 1');
	}
}

function create () {
	global $db, $administrator, $uid;
	print "<users>";
	$errors = Array();
	$uname = $_POST['uname'];
	if (trim($uname) == "") {
		$errors[] = "You forgot to enter a username!";	
	}
	if ($uname != $_COOKIE[DB_PREFIX.'uname'] && $administrator == 0) {
		$errors[] = "You do not have proper permissions to create users! (Are you an administrator?)";
	}
	$name = $_POST['name'];
	if (trim($name) == "") {
		$errors[] = "You forgot to enter a real name!";	
	}
	$isadministrator = $_POST['administrator'];
	if ($isadministrator != 1) {
		$isadministrator = 0;	
	}
	$result = $db->query("SELECT `uname` FROM `".DB_PREFIX."users` WHERE 1");
	while(list($check_uname) = mysql_fetch_row($result)) {
		if ($uname == $check_uname) {
			$errors[] = "That username, $uname, already exists!";		
		}
	}
	$password = $_POST['password'];
	if (trim($password) == "") {
		$errors[] = "You forgot to enter a password!";
	}
	$password2 = $_POST['password2'];
	if ($password != $password2) {
		$errors[] = "Those passwords do not match!";
	}
	if (count($errors) == 0) {
		if ($password) {
	    	$password = crypt($password, "\$1\$ajaxpass");
	    	}
          	docreate($uname,$name,$password,$isadministrator);
    		print "<errornum>0</errornum><uid>$uid</uid>";
      } else {
    		$result = $db->query("SELECT `uname`,`display_name`,`administrator` FROM `".DB_PREFIX."users` WHERE `uid`=$uid");
    		list($uname,$name,$administrator) = mysql_fetch_row($result);
    		print "<errornum>" . count($errors) . "</errornum><errors>";
		foreach ($errors as $err) {
			print "<error>$err</error>";
		}
		print "</errors><uid>$uid</uid>";
	}
   print "</users>";	 
}


function docreate($uname,$name,$password,$administrator) {
    global $db;
    $db->query("INSERT INTO `".DB_PREFIX."users` VALUES (0,'"
         . mysql_escape_string($uname) . "','"
         . mysql_escape_string($password) . "','"
         . mysql_escape_string($name) . "',"
         . $administrator.")");
} 

function delete() {
	global $db,$administrator,$uid;
	print "<users><info>";
	if (!is_numeric($_POST['uid'])) {
		bounceout("I got a uid in POST that wasn't numeric.");
	} else {
		if ($administrator == 0) {
			bounceout("I, users.php, got someone who isn't an administrator who was trying to delete a user.");
		} else { 
			$db->query("DELETE FROM `".DB_PREFIX."news` WHERE `uid`=".$_POST['uid']);
			$db->query("DELETE FROM `".DB_PREFIX."users` WHERE `uid`=".$_POST['uid']);
			print "<errornum>0</errornum><uid>".$_POST['uid']."</uid>";
		}
	}
	print "</info></users>";
}

function get_users() {
	global $db,$administrator,$uid;
	if ($administrator == 0) {
		$terms = "`uid`=$uid";	
	} else {
		$terms = "1";	
	}
	$result = $db->query("SELECT `uid`,`uname`,`display_name`,`administrator` FROM `".DB_PREFIX."users` WHERE $terms ORDER BY `uid` DESC");
	print "<users>";
	while(list($uid,$uname,$name,$administrator) = mysql_fetch_row($result)) {
		print "<user>
				<uid>$uid</uid>
				<uname>".xmlencode($uname)."</uname>
				<name>".xmlencode($name)."</name>
				<administrator>$administrator</administrator>
			   </user>";
	}
	print "</users>";
}


?>
