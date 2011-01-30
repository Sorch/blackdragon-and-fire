<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// WORDPRESS IMPORTER (NO GOOD REALLY) PROB GONNA REMOVE

require('header.php');
if ($verified == 0) {
    bounceout("I, import.php, got an unauthorized request");
} elseif ($administrator == 0) {
		print "<import><errnum>1</errnum><errors><error>Only administrators can import posts!</error></errors></import>";
	exit;
}

$errors = Array();

$db2 = @mysql_connect($_POST['DB_HOST'], $_POST['DB_USER'], $_POST['DB_PASSWORD'], 1);
if (!$db2) {
    $errors[] = "I seem to have had difficulty connecting to the mysql database you specified.<br>The error MySQL returned is: " . mysql_error();
} else {
@mysql_select_db($_POST['DB_NAME'], $db2) or $errors[] = "I seem to have had difficulty choosing the database name you specified.<br>The error MySQL returned is: " . mysql_error();
}
check_errors($errors);

$p = $_POST['DB_PREFIX'];

if ($_POST['action'] == "import") {
import_users();	
} elseif ($_POST['action'] == "importposts") {
import_posts();
} elseif ($_POST['action'] == "doimportusers") { 
do_import_users();
}
mysql_close($db2);

function gen_db_info() {
return "<info>
		<DB_NAME>".xmlencode($_POST['DB_NAME'])."</DB_NAME>
		<DB_USER>".xmlencode($_POST['DB_USER'])."</DB_USER>
		<DB_PASSWORD>".xmlencode($_POST['DB_PASSWORD'])."</DB_PASSWORD>
		<DB_HOST>".xmlencode($_POST['DB_HOST'])."</DB_HOST>
		<DB_PREFIX>".xmlencode($_POST['DB_PREFIX'])."</DB_PREFIX>
		</info>";	
}

function import_users() {
	global $p,$db;
	$errors = Array();
	$result = query("SHOW  TABLES  LIKE '%${p}usermeta%'");
	if (mysql_num_rows($result) == 0) {
		// this is WP 1.5 or less.
		$wpv = "1.5";
	} else {
		$wpv = "2";
	}
	
	if ($wpv == 2) {
		$result = query("SELECT `${p}users`.`ID`,`${p}users`.`user_login`,`${p}usermeta`.`meta_value` FROM `${p}users`,`${p}usermeta` WHERE `${p}usermeta`.`user_id` = `${p}users`.`ID` AND `${p}usermeta`.`meta_key`='wp_user_level'");
	} else {
		$result = query("SELECT `ID`,`user_login`,`user_level` FROM `${p}users` WHERE `user_level` > 0");
	}
	print "<import>".gen_db_info()."<users>";
	while(list($uid,$uname,$admin) = mysql_fetch_row($result)) {
		$display_name = $uname;
		if (!$uid || trim($uname) == "") {
		next;
		} else {
			if ($admin == 10) {
			$administrator = 1;	
			} else {
			$administrator = 0;
			}
			print "<user>
					<uid>" . $uid . "</uid>
					<uname>" . xmlencode($uname) . "</uname>
					<name>" . xmlencode($display_name) . "</name>
					<administrator>" . $administrator . "</administrator>
				   </user>";
		}
	}
	print "</users></import>";
}

function do_import_users() {
	$errors = Array();
	global $uids, $p,$db;
	$numimported = 0;
	foreach ($uids as $uid) {
		// make sure we have all safe uids!
		if (!is_numeric($uid)) {
		exit;
		}
		if (trim($_POST['passwordtext'.$uid]) == "") {
		#$errors[] = "You forgot to enter a new password for ID#: " . $uid;	
		} else {
			$password = crypt($_POST['passwordtext'.$uid], "\$1\$ajaxpass");
			$result = $db->query("SELECT `uid` FROM `".DB_PREFIX."users` WHERE `uname`='".mysql_escape_string($_POST['unametext'.$uid])."'");
			if (mysql_num_rows($result) == 0) {
				$db->query("INSERT INTO `".DB_PREFIX."users` VALUES(0,'".mysql_escape_string($_POST['unametext'.$uid])."','".mysql_escape_string($password)."','".mysql_escape_string($_POST['nametext'.$uid])."','".mysql_escape_string($_POST['administratortext'.$uid])."')");
				$numimported++;
			}
		}
	}
	check_errors($errors);
	$result = query("SELECT COUNT(*) FROM `${p}posts` WHERE `post_status`='publish' OR `post_status`='static'");
	list($numtoimport) = mysql_fetch_row($result);
	mysql_free_result($result);
	// Import categories too!
	$result = query("SELECT `cat_ID`,`cat_name` FROM `${p}categories` WHERE 1");
	$cat = 0;
	while(list($cat_id,$cat_name) = mysql_fetch_row($result)) {
		if ($cat_id == 1) { 
			$db->query("UPDATE `".DB_PREFIX."categories` SET `category_name`='".mysql_escape_string($cat_name)."' WHERE `sid`=1");
		} else {
			$result2 = $db->query("SELECT `category_name` FROM `".DB_PREFIX."categories` WHERE `category_name`='".mysql_escape_string($cat_name)."'");
			if (mysql_num_rows($result2) == 0) {
				$cat++;
				$db->query("INSERT INTO `".DB_PREFIX."categories` VALUES(0,'".mysql_escape_string($cat_name)."')");	
			}
		}
	}
	@mysql_free_result($result);
	// Why not import blog description and name as well!
	$result = query("SELECT `option_value` FROM `${p}options` WHERE `option_name`='blogname'");
	list($blogname) = mysql_fetch_row($result);
	$result = query("SELECT `option_value` FROM `${p}options` WHERE `option_name`='blogdescription'");
	list($blogdescription) = mysql_fetch_row($result);
	
	$db->query("UPDATE `".DB_PREFIX."options` SET `BlogName`='".mysql_escape_string($blogname)."',`BlogDescription`='".mysql_escape_string($blogdescription)."' WHERE 1 LIMIT 1");
	
	print "<import>".gen_db_info()."<errnum>0</errnum><imported><cat>$cat</cat><numtoimport>$numtoimport</numtoimport><numimported>$numimported</numimported></imported></import>";
}


function import_posts() {
	global $p,$db;
	$start = $_POST['start'];
	$result = query("SELECT `ID`,`post_author`,unix_timestamp(`post_date`),`post_content`,`post_title`,`post_excerpt`,`comment_status` FROM `${p}posts` WHERE `post_status`='publish' OR `post_status`='static' LIMIT $start,100");
	$num = mysql_num_rows($result);
	if ($num != 100) {
		$done = 1;
	} else {
		$done = 0;
	}
	$errors = Array();
	while(list($id,$wpuid,$date,$body,$subject,$excerpt,$comment_status) = mysql_fetch_row($result)) {
		if ($comment_status == "open") {
			$allowcomments = 1;
		} else {
			$allowcomments = 0;	
		}
		$result2 = query("SELECT COUNT(*) FROM `${p}comments` WHERE `comment_post_ID`=$id");
		list($comments) = mysql_fetch_row($result2);
		mysql_free_result($result2);
		$result2 = query("SELECT `user_login` FROM `${p}users` WHERE `ID`=$wpuid");
		list($uname) = mysql_fetch_row($result2);
		mysql_free_result($result2);
		$result2 = $db->query("SELECT `uid` FROM `".DB_PREFIX."users` WHERE `uname`='".mysql_escape_string($uname)."'");
		list($uid) = mysql_fetch_row($result2);
		mysql_free_result($result2);
		if (!$uid) {
			$uid = 1;	
		}
		$result2 = query("SELECT `category_id` FROM `${p}post2cat` WHERE `post_id`=$id");
		// if multiple cats, we're just going for the first one
		// if no cats (i.e. static pages), set to uncategorized.
		if (mysql_num_rows($result2) == 0) {
			$sid = 1;
		} else {
			list($wpsid) = mysql_fetch_row($result2);
			mysql_free_result($result2);
			$result2 = query("SELECT `cat_name` FROM `${p}categories` WHERE `cat_ID`=$wpsid");
			list($cat_name) = mysql_fetch_row($result2);
			mysql_free_result($result2);
			$result2 = $db->query("SELECT `sid` FROM `".DB_PREFIX."categories` WHERE `category_name`='".mysql_escape_string($cat_name)."'");
			list($sid) = mysql_fetch_row($result2);
		}
		@mysql_free_result($result2);
		// try to evade duplicate posts.
		$result2 = $db->query("SELECT `id` FROM `".DB_PREFIX."news` WHERE `subject`='".mysql_escape_string($subject)."' AND `body`='".mysql_escape_string($body)."' AND `excerpt`='".mysql_escape_string($excerpt)."'");
		if (mysql_num_rows($result2) == 0) {
			mysql_free_result($result2); 
			$db->query("INSERT INTO `".DB_PREFIX."news` VALUES(0,$uid,$sid,'".mysql_escape_string($subject)."','".mysql_escape_string($excerpt)."','".mysql_escape_string($body)."','$date','',$allowcomments,$comments)");
			$new_id = mysql_insert_id($db->this());
			// import comments
			$result2 = query("SELECT `comment_author`,`comment_author_email`,`comment_author_url`,`comment_author_IP`,unix_timestamp(`comment_date`),`comment_content` FROM `${p}comments` WHERE `comment_approved`='1' AND `comment_post_ID`=$id");
			if (mysql_num_rows($result2) != 0) {
				while(list($uname,$email,$url,$ip,$date,$body) = mysql_fetch_row($result2)) {
					$db->query("INSERT INTO `".DB_PREFIX."comments` VALUES(0,$new_id,'".mysql_escape_string($uname)."','".mysql_escape_string($email)."','".mysql_escape_string($url)."','".mysql_escape_string($body)."','$date','$ip')");
				}
			}
	
		}
		// ..
	}
		print "<import>".gen_db_info()."<imported><done>$done</done><start>$start</start><num>$num</num></imported></import>";

}

function check_errors($errors) {
	if (count($errors) > 0) {
		print "<import>";
		print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
		foreach ($errors as $error) {
		     print "<error>".xmlencode($error)."</error>";
		} 
		print "</errors>";
		print "</import>";
		exit;
	} else {
		return 0;
	}
	
}

function query($query) {
  global $db2;
  $query_id = mysql_query($query,$db2); 
  if (!$query_id) {
     $errors = Array("mySQL query error: $query" . mysql_error());
	 check_errors($errors);
  } else {
    return $query_id; 
  }
}

?>
