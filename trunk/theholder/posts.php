<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////


require_once("db.php");
require_once("global.php");

if (!isset($noxml)) {
	$db = new db;
	$db->connect();
	if ($_POST['action'] == "search") {
		$query = $_POST['query'];
		$sid = 0;
		posts_xml(search_posts($db,$query),$db);
	} else {
		$sid = $_REQUEST['sid'];
		$start = $_POST['start'];
		if (!$start) $start = 0;
		if (!is_numeric($sid) || !is_numeric($start)) {
		    print "<error>must be numeric</error>";
		    exit;
		}
		posts_xml(get_posts($db,$sid,$start),$db);
	}
	$db->disconnect();
}

function get_posts($db,$sid,$start) {
	if ($sid == 0) {
		$what = 1;
	} else {
		$what = "`".DB_PREFIX."news`.`sid`=" . $sid;
	}
	return $db->query("SELECT  `id` ,  `".DB_PREFIX."news`.`uid` , `".DB_PREFIX."news`.`sid`, `display_name`,  `category_name` ,  `subject` ,  `excerpt` , `body` , `date` ,  `allowcomments` ,  `comments`  FROM  `".DB_PREFIX."news`  INNER  JOIN  `".DB_PREFIX."categories`  ON  `".DB_PREFIX."news`.`sid`  =  `".DB_PREFIX."categories`.`sid`  INNER  JOIN  `".DB_PREFIX."users`  ON  `".DB_PREFIX."news`.`uid`  =  `".DB_PREFIX."users`.`uid` WHERE $what ORDER BY `date` DESC LIMIT $start,10", false);
}

function posts_xml($result,$db) {
	global $sid;
	header("Content-type: text/xml");
	print "<posts>\n";
	$result2 = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."news` WHERE `sid`=" . $sid, false);
	$max_rows = mysql_fetch_row($result2);
	$max_rows = $max_rows[0];
	
	print "<info><uid>$uid</uid>\n<sid>$sid</sid>\n<max_rows>$max_rows</max_rows>\n<start>$start</start>";
	if ($_POST['action'] == "search") {
		print "<search>1</search>";
	} else {
		print "<search>0</search>";
	}
		   print "</info>";
	while ($array = mysql_fetch_array($result, MYSQL_ASSOC)) {
	    print "<post>
		   <name>".$array["display_name"]."</name>
		   <date>" . date("F j, Y, g:i a", $array["date"]) . "</date>
		   <excerpt>".$array["excerpt"]."</excerpt>
		   <category>".$array["category_name"]."</category>
		   <sid>".$array["sid"]."</sid>
		   <subject>" . xmlencode(newsbody($array["subject"], 1)) . "</subject>
		   <body>" . xmlencode(newsbody($array["body"], 1)) . "</body>
		   <comments>" . $array["comments"] . "</comments>
		   <allowcomments>" . $array["allowcomments"] . "</allowcomments>
		   <id>" . $array["id"] . "</id>";
		   print "</post>";
	
	}
	print "\n</posts>";
}

function search_posts($db,$query) {
	if (strlen($query) > 3) {
		return $db->query("SELECT `id` ,  `".DB_PREFIX."news`.`uid` , `".DB_PREFIX."news`.`sid`, `display_name`,  `category_name` ,  `subject` ,  `excerpt` , `body` , `date` ,  `allowcomments` ,  `comments`  FROM  `".DB_PREFIX."news`  INNER  JOIN  `".DB_PREFIX."categories`  ON  `".DB_PREFIX."news`.`sid`  =  `".DB_PREFIX."categories`.`sid`  INNER  JOIN  `".DB_PREFIX."users`  ON  `".DB_PREFIX."news`.`uid`  =  `".DB_PREFIX."users`.`uid` WHERE (`subject` LIKE '%$query%' OR `body` LIKE '%$query%' OR `excerpt` LIKE '%$query%') ORDER BY `".DB_PREFIX."news`.`date` DESC"); 
	} else {
		return get_posts($db,0,0);
	}
}

?>
