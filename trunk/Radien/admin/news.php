<?php

// 
// See index.php for full license.

require('header.php');

if ($verified == 0) {
    bounceout("I, news.php, got an unauthorized request");
}

if ($_POST['action'] == "getlast15") {
	getlast15();
} elseif ($_POST['action'] == "search") {
	search();
} elseif ($_POST['action'] == "betweentimes") {
	betweentimes();
} elseif ($_POST['action'] == "delete") {
	delete();
} elseif ($_POST['action'] == "getpost") {
	get_post();
} elseif ($_POST['action'] == "edit") {
	edit();
} elseif ($_POST['action'] == "write") {
	write();
} else {
	bounceout("I, news.php, got a action, but it wasn't one I recognized: " . $_POST['action']);
}

$db->disconnect();


function edit() {
	global $db,$uid,$administrator;
	print "<news>";
	    $subject = $_POST['subject'];
	    $category = $_POST['category'];
	    $body = $_POST['body'];
	    $excerpt = $_POST['excerpt'];
	    $allowcomments = $_POST['allowcomments'];
	    $errors = Array();
           $id = $_POST['id'];	
           if (!is_numeric($id)) {
		exit();
	    }
	    if ($allowcomments != 1) {
	        $allowcomments = 0;
	    } 
	    if (!$subject) {
	        $errors[] = "You must enter a subject for your post!";
	    } 
	    if (!is_numeric($category)) {
	        $errors[] = "You must enter a category for your post!";
	    } 
	    if (!$body) {
	        $errors[] = "You must enter a body for your post!";
	    } 
	    $result = $db->query("SELECT `uid` FROM `".DB_PREFIX."news` WHERE `id`=$id LIMIT 1");
	    $realuid = mysql_result($result,0);
	    if ($realuid != $uid && $administrator == 0) {
			bounceout("I, news.php, got a request to edit a post that didn't belong to them (and they weren't an administrator)!");
	    }

	    if (count($errors) == 0) {
		$db->query('UPDATE `'.DB_PREFIX.'news` SET `sid`=\''.$category.'\',`subject`=\''.mysql_escape_string($subject).'\',`excerpt`=\''.mysql_escape_string($excerpt).'\',`body`=\''.mysql_escape_string($body).'\',`ip`=\''.getenv("REMOTE_ADDR").'\',`allowcomments`=\''.$allowcomments.'\' WHERE `id`='.$id.' LIMIT 1');
		print "<errnum>0</errnum><sid>$category</sid><id>$id</id>";
	    } else {
	        print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
	        foreach ($errors as $error) {
	            print "<error>$error</error>";
	        } 
	        print "</errors>";
	    } 
	    print "</news>";
}	


function write() {
	global $db,$administrator,$uid;
	print "<news>";
	    $subject = $_POST['subject'];
	    $category = $_POST['category'];
	    $body = $_POST['body'];
	    $excerpt = $_POST['excerpt'];
	    $allowcomments = $_POST['allowcomments'];
	    $errors = Array();
	    if ($allowcomments != 1) {
	        $allowcomments = 0;
	    } 
	    if (!$subject) {
	        $errors[] = "You must enter a subject for your post!";
	    } 
	    if (!is_numeric($category)) {
	        $errors[] = "You must enter a category for your post!";
	    } 
	    if (!$body) {
	        $errors[] = "You must enter a body for your post!";
	    }

	    if (count($errors) == 0) {
		$db->query("INSERT INTO `".DB_PREFIX."news` VALUES (0,$uid,$category,'" . mysql_escape_string($subject) . "','" . mysql_escape_string($excerpt) . "','" . mysql_escape_string($body) . "'," . time() . ",'" . getenv("REMOTE_ADDR") . "'," . $allowcomments . ",0)");
		$id = mysql_insert_id();
		print "<errnum>0</errnum><sid>$category</sid><id>$id</id>";
	    } else {
	        print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
	        foreach ($errors as $error) {
	            print "<error>$error</error>";
	        } 
	        print "</errors>";
	    } 
	    print "</news>";
}    

function delete() {
	global $db,$uid,$administrator;
	print "<news>";
       $id = $_POST['id'];	
       if (!is_numeric($id)) {
	    exit();
	}
	$result = $db->query("SELECT `uid` FROM `".DB_PREFIX."news` WHERE `id`=$id LIMIT 1");
	$realuid = mysql_result($result,0);
	if ($realuid != $uid && $administrator == 0) {
	    $errors[] = "You do not have proper permission to edit this post!";	
	}
	
	if (count($errors) != 0) {
	        print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
	        foreach ($errors as $error) {
	            print "<error>$error</error>";
	        } 
	        print "</errors>";			
	} else {
		$db->query("DELETE FROM `".DB_PREFIX."news` WHERE `id`=".$id);
		$db->query("DELETE FROM `".DB_PREFIX."comments` WHERE `id`=".$id);
		print "<errnum>0</errnum><id>".$id."</id>";
	}
	print "</news>";
}

function getlast15() {
	global $db,$administrator,$uid;
	if ($administrator == 0) {
		$terms = "`".DB_PREFIX."news`.`uid`=$uid";
	} else {
		$terms = "1";
	}
	get_posts("SELECT `".DB_PREFIX."news`.`id`,`".DB_PREFIX."news`.`uid`,`".DB_PREFIX."news`.`sid`,`".DB_PREFIX."categories`.`category_name` AS `catname`, `".DB_PREFIX."users`.`display_name` AS `name`,`".DB_PREFIX."news`.`subject`,`".DB_PREFIX."news`.`date`,`".DB_PREFIX."news`.`comments` FROM `".DB_PREFIX."news`,`".DB_PREFIX."categories`,`".DB_PREFIX."users` WHERE $terms AND `".DB_PREFIX."news`.`sid`=`".DB_PREFIX."categories`.`sid` AND `".DB_PREFIX."news`.`uid`=`".DB_PREFIX."users`.`uid` ORDER BY date DESC LIMIT 15",$db);
}

function get_posts($query,$db) {
	$result = $db->query($query,0);
	print  "<posts>";
	while(list($id,$uid,$sid,$category,$name,$subject,$date,$comments) = mysql_fetch_row($result)) {
        print "<post>
		<subject>".xmlencode($subject)."</subject> 
		<sid>$sid</sid>
		<id>$id</id>
		<name>".xmlencode($name)."</name>
		<uid>$uid</uid>
		<category>".xmlencode($category)."</category>  
		<date>".compose_time($date)."</date> 
		<comments>$comments</comments>
		</post>\n"; 
     } 
     print "</posts>"; 

}

function betweentimes() {
	global $db,$administrator,$uid;
	$time1 = $_POST['time1'];
	$time2 = $_POST['time2'];
	if (!is_numeric($time1) || !is_numeric($time2)) {
		get_last15posts($db,$administrator,$uid);
	} else {
		if ($administrator == 0) {
			$terms = "AND `uid`=$uid";
		} else {
			$terms = "";
		}
		get_posts("SELECT `".DB_PREFIX."news`.`id`,`".DB_PREFIX."news`.`uid`,`".DB_PREFIX."news`.`sid`,`".DB_PREFIX."categories`.`category_name` AS `catname`, `".DB_PREFIX."users`.`display_name` AS `name`,`".DB_PREFIX."news`.`subject`,`".DB_PREFIX."news`.`date`,`".DB_PREFIX."news`.`comments` FROM `".DB_PREFIX."news`,`".DB_PREFIX."categories`,`".DB_PREFIX."users` WHERE (`".DB_PREFIX."news`.`date` BETWEEN $time1 AND $time2) $terms AND `".DB_PREFIX."news`.`sid`=`".DB_PREFIX."categories`.`sid` AND `".DB_PREFIX."news`.`uid`=`".DB_PREFIX."users`.`uid` ORDER BY `".DB_PREFIX."news`.`date` DESC",$db); 
	}
}

function search() {
	global $db,$administrator,$uid;
     $query = mysql_escape_string($_POST['query']); 
     if (trim($query) == "") {
		get_last15posts($db,$administrator,$uid);
     }
     if ($administrator == 0) {
     	$terms = "AND `uid`=$uid";	
     } else {
     	$terms = "";
     }
     get_posts("SELECT `".DB_PREFIX."news`.`id`,`".DB_PREFIX."news`.`uid`,`".DB_PREFIX."news`.`sid`,`".DB_PREFIX."categories`.`category_name` AS `catname`, `".DB_PREFIX."users`.`display_name` AS `name`,`".DB_PREFIX."news`.`subject`,`".DB_PREFIX."news`.`date`,`".DB_PREFIX."news`.`comments` FROM `".DB_PREFIX."news`,`".DB_PREFIX."categories`,`".DB_PREFIX."users` WHERE (`subject` LIKE '%$query%' OR `body` LIKE '%$query%' OR `excerpt` LIKE '%$query%') $terms AND `".DB_PREFIX."news`.`sid`=`".DB_PREFIX."categories`.`sid` AND `".DB_PREFIX."news`.`uid`=`".DB_PREFIX."users`.`uid` ORDER BY `".DB_PREFIX."news`.`date` DESC",$db); 
}

function get_post() {
	global $db;
	$id = $_POST['id'];
	if (!is_numeric($id)) {
		print "<error>Id invalid!</error>";
	} else {
		$result = $db->query("SELECT `id`,`uid`,`sid`,`subject`,`body`,`excerpt`,`date`,`allowcomments` FROM `".DB_PREFIX."news` WHERE `id`=$id",$db);
		list($id,$uid,$sid,$subject,$body,$excerpt,$date,$allow) = mysql_fetch_row($result); 
        print "<posts><post>
		<subject>".xmlencode($subject)."</subject> 
		<sid>$sid</sid>
		<id>$id</id>
		<uid>$uid</uid>
		<body>".xmlencode($body)."</body> 
		<excerpt>".xmlencode($excerpt)."</excerpt>
		<date>".compose_time($date)."</date> 
		<allowcomments>".$allow."</allowcomments>
		</post></posts>\n"; 
	}
}


?>
