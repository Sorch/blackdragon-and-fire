<?php
// 
// See index.php for full license.
require_once("db.php");
require_once("global.php");


if (!isset($noxml)) {
	$db = new db;
	$db->connect();
	$id = $_REQUEST['id'];
	if (!is_numeric($id)) {
	    print "<error>must be numeric</error>";
	    exit;
	}
	post_xml(get_post($db,$id),$db,$id);
	$db->disconnect();
}



// get the single post
function get_post($db,$id) {

return $db->query("SELECT  `id` ,  `".DB_PREFIX."news`.`uid` , `".DB_PREFIX."news`.`sid`, `display_name`,  `category_name` ,  `subject` ,  `body` ,  `date` ,  `allowcomments` ,  `comments`  FROM  `".DB_PREFIX."news`  INNER  JOIN  `".DB_PREFIX."categories`  ON  `".DB_PREFIX."news`.`sid`  =  `".DB_PREFIX."categories`.`sid`  INNER  JOIN  `".DB_PREFIX."users`  ON  `".DB_PREFIX."news`.`uid`  =  `".DB_PREFIX."users`.`uid` WHERE `id`=$id ORDER BY `date` DESC LIMIT 1", false);

}

// get links for next and prev
function get_nextprev($db,$id) {
	$nextid = $id+1;
	$previd = $id-1;
	$result = $db->query("SELECT `id`,`subject` FROM `".DB_PREFIX."news` WHERE `id` IN($previd,$nextid) ORDER BY `id` ASC", false);
	$prevlinks = mysql_fetch_array($result);
	
	
	if ($prevlinks["id"] != $previd) {
		$nextlinks = $prevlinks;
		$prevlinks["id"] = "";
		$prevlinks["subject"] = "";
	} else {
		$nextlinks = mysql_fetch_array($result);
		if ($nextlinks["id"] != $nextid) {
			$nextlinks["id"] = "";
			$nextlinks["subject"] = "";
		} 
	}
	$prevlinks["subject"] = ($prevlinks["subject"] == "")? "" : "&amp;laquo; ".$prevlinks["subject"];
	$nextlinks["subject"] = ($nextlinks["subject"] == "")? "" : $nextlinks["subject"]." &amp;raquo;";
	return Array($nextlinks,$prevlinks);
}

// generate xml for the post and then comments
function post_xml($result,$db,$id) {
	header("Content-type: text/xml");
	print "<viewpost>\n";
	if (mysql_num_rows($result) == 0) {
	print "<info><uid>1</uid><sid>0</sid><id>0</id></info><post><date>".date("F j, Y, g:i a",time())."</date><subject>No Such Post</subject><body>No such post exists. Perhaps it was deleted?</body><allowcomments>0</allowcomments><id>0</id></post>";
	print "<comments></comments>";
	} else {
		list($nextlinks,$prevlinks) = get_nextprev($db,$id);
		$array = mysql_fetch_array($result, MYSQL_ASSOC);
		print 	"<info>
		 <uid>" . $array["uid"] . "</uid>
		 <sid>" . $array["sid"] . "</sid>
		 <id>$id</id>
		 </info>";
		print 	"<post>
		 <nextid>".$nextlinks["id"]."</nextid>
		 <previd>".$prevlinks["id"]."</previd>
		 <nextsubj>".$nextlinks["subject"]."</nextsubj>
		 <prevsubj>".$prevlinks["subject"]."</prevsubj>
		 <date>" . date("F j, Y, g:i a", $array["date"]) . "</date>
		 <category>".$array["category_name"]."</category>
		 <subject>" . xmlencode($array["subject"]) . "</subject>
		 <name>" . xmlencode($array["display_name"]) . "</name>
		 <body>" . xmlencode(newsbody($array["body"], 1)) . "</body>
		 <allowcomments>" . $array["allowcomments"] . "</allowcomments>
		 </post>";
		comments_xml(get_comments($db,$id));
	}
	print "</viewpost>";
}

// get comments for $id
function get_comments($db,$id) {
	return $db->query("SELECT `cid`,`uname`,`url`,`body`,`date` FROM `".DB_PREFIX."comments` WHERE `id`=$id ORDER BY `date` ASC");
}

// return comments xml for $result
function comments_xml($result) {
	print "<comments>\n";
	
	while ($array = mysql_fetch_array($result, MYSQL_ASSOC)) {
	    print "<comment>
	           <cid>". $array["cid"] . "</cid>
	           <uname>" . $array["uname"] . "</uname>
	           <url>" . $array["url"] . "</url>
	           <date>" . date("F j, Y, g:i a", $array["date"]) . "</date>
	           <body>" . xmlencode(newsbody($array["body"],1)) . "</body>
	           </comment>";
	} 
	
	print "</comments>";
}
?>
