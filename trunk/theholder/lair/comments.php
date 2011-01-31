<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// COMMENTS ETC

require('header.php');

if ($verified == 0) {
	bounceout("I, comments.php, got an unauthorized request.");
} elseif ($administrator == 0) {
	// return empty comment set if not an admin
	print "<comments></comments>";
	exit;
}

print "<comments><info>";

if ($_REQUEST['action'] == "delete") {
	delComment($db);
} elseif ($_REQUEST['action'] == "edit") {
	editComment($db);	
} elseif ($_REQUEST['action'] == "getlast15") {
    getlast15($db);
} elseif ($_REQUEST['action'] == "search") {
    search($db);
} elseif ($_REQUEST['action'] == "betweentimes") {
    betweentimes($db);
} elseif ($_REQUEST['action'] == "getbody") {
    getbody($db);
} else {
    bounceout("I, comments.php, got a body, but it wasn't one I understood:");
}

print "</info></comments>";
$db->disconnect();

function delComment($db) {
	$cid = $_REQUEST['cid'];
	if (!is_numeric($cid)) {
		print "<errornum>1</errornum><cid>".$cid."</cid>";				
	} else {
		$result = $db->query("SELECT `id` FROM `".DB_PREFIX."comments` WHERE `cid`=$cid");
		list($id) = mysql_fetch_row($result);
		$db->query("UPDATE `".DB_PREFIX."news` SET `comments`=`comments`-1 WHERE `id`=$id LIMIT 1");
		$db->query("DELETE FROM `".DB_PREFIX."comments` WHERE `cid`=$cid");
		
		print "<errornum>0</errornum><cid>$cid</cid>";
	}
}

function editComment($db) {
	$errors = 0;
	$cid = $_REQUEST['cid'];
	$body = $_REQUEST['body'];
	$author = $_REQUEST['author'];
	if (!is_numeric($cid)) {
		exit;
	} else {
		$result = $db->query("SELECT `body`,`uname` FROM `".DB_PREFIX."comments` WHERE `cid`=$cid LIMIT 1", false);
		if (mysql_num_rows($result) == 0) {
			exit;	
		} else {
			list($origbody,$origauthor) = mysql_fetch_row($result);	
		}
		$db->free($result);
	}
	if (trim($body) == "") {
		$errors++;	
	}
	
	if (trim($author) == "") {
		$errors++;	
	}
	
	if ($errors != 0) {
	$origbody = shorten($origbody);
	print "<body>".xmlencode($origbody)."</body><author>".xmlencode($origauthor)."</author><cid>$cid</cid>";
	} else {
	$body = html_filter($body);
	$db->query("UPDATE `".DB_PREFIX."comments` SET `body`='".mysql_escape_string($body)."', `uname`='".mysql_escape_string($author)."' WHERE `cid`=$cid LIMIT 1");
	$body = shorten($body);
	print "<body>".xmlencode($body)."</body><author>".xmlencode($author)."</author><cid>$cid</cid>";
	
	}
}

function get_comments($query,$db) {
	
	print  "<comments>";
	if ($query != "") {
		$result = $db->query($query,0);
		while(list($cid,$date,$author,$body,$r_id,$r_sid,$r_subject) = mysql_fetch_row($result)) {
			$body = shorten($body);			
	        print "<comment>
			<cid>$cid</cid>
			<date>".compose_time($date)."</date> 
			<author>".xmlencode($author)."</author>
			<body>".xmlencode($body)."</body> 
			<r_id>$r_id</r_id>
			<r_sid>$r_sid</r_sid>
			<r_subject>$r_subject</r_subject>
			</comment>\n"; 
	     }
     }
     print "</comments>"; 
}

function search($db) {
    $query = mysql_escape_string($_POST['query']); 
    if (trim($query) == "") {
		get_last15comments($db);
    } else {
		get_comments("SELECT ".DB_PREFIX."comments.cid,".DB_PREFIX."comments.date,".DB_PREFIX."comments.uname,".DB_PREFIX."comments.body,".DB_PREFIX."news.id,".DB_PREFIX."news.sid,".DB_PREFIX."news.subject FROM `".DB_PREFIX."comments`,`".DB_PREFIX."news` WHERE ".DB_PREFIX."comments.id = ".DB_PREFIX."news.id AND (".DB_PREFIX."comments.uname LIKE '%$query%' OR ".DB_PREFIX."comments.body LIKE '%$query%') ORDER BY ".DB_PREFIX."comments.date DESC LIMIT 15",$db);
    } 
}

function getlast15($db) {
    	get_comments("SELECT ".DB_PREFIX."comments.cid,".DB_PREFIX."comments.date,".DB_PREFIX."comments.uname,".DB_PREFIX."comments.body,".DB_PREFIX."news.id,".DB_PREFIX."news.sid,".DB_PREFIX."news.subject FROM `".DB_PREFIX."comments`,`".DB_PREFIX."news` WHERE ".DB_PREFIX."comments.id = ".DB_PREFIX."news.id ORDER BY `date` DESC LIMIT 15",$db);
}

function betweentimes($db) {
	$time1 = $_REQUEST['time1'];
	$time2 = $_REQUEST['time2'];
	if (!is_numeric($time1) || !is_numeric($time2)) {
		get_last15comments($db);
	} else {
    	get_comments("SELECT ".DB_PREFIX."comments.cid,".DB_PREFIX."comments.date,".DB_PREFIX."comments.uname,".DB_PREFIX."comments.body,".DB_PREFIX."news.id,".DB_PREFIX."news.sid,".DB_PREFIX."news.subject FROM `".DB_PREFIX."comments`,`".DB_PREFIX."news` WHERE ".DB_PREFIX."comments.id = ".DB_PREFIX."news.id AND ".DB_PREFIX."comments.date BETWEEN $time1 AND $time2 ORDER BY ".DB_PREFIX."comments.date DESC",$db);
	}
}

function getbody($db) {

	if (!is_numeric($_POST['cid'])) {
		exit;
	} else {
		$result = $db->query("SELECT `body` FROM `".DB_PREFIX."comments` WHERE `cid`=".$_POST['cid']);
		list($body) = mysql_fetch_row($result);
		print "<comment><info><body>".xmlencode($body)."</body><cid>".$_POST['cid']."</cid></info></comment>";
	}
}
?>
