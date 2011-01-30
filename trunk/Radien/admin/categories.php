<?php
// 
// See index.php for full license.

require('header.php');
if ($verified == 0) {
    bounceout("I got an unauthorized request.");
    exit;
} elseif ($administrator == 0 && $_POST['action'] != "get_write_categories") {
	print "<categories></categories>";
	exit;
}
if ($_POST['action'] == "delete") {
	delete();
} elseif ($_POST['action'] == "getcategories" || $_POST['action'] == "get_write_categories") {
    get_categories();	
} elseif ($_POST['action'] == "edit") {
	edit();
} elseif ($_POST['action'] == "create") {
	create();
} else {
	bounceout("I got a action, but it wasn't one I recognized.");
}

$db->disconnect();



function edit () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<categories><errnum>1</errnum><errors><error>You do not have proper permissions to edit categories!</error></errors><sid>".$_POST['sid']."</sid><catname>".$_POST['catname']."</catname></categories>";
		exit;
	}
	print "<categories>";
		$errors = Array();
		$catname = $_POST['catname'];
		$sid = $_POST['sid'];
		if (trim($catname) == "") {
			$errors[] = "You forgot to enter a category name!";	
		} else {
			$result = $db->query("SELECT `sid`,`category_name` FROM `".DB_PREFIX."categories` WHERE 1");
			while(list($check_sid,$check_catname) = mysql_fetch_row($result)) {
				if ($catname == $check_catname && $check_sid != $sid) {
					$errors[] = "A category with that name already exists!";		
				}
			}
			
			$result = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."categories` WHERE `sid`=$sid");
			$count = mysql_result($result,0);
			if (!is_numeric($sid) || $count == 0) { 
				exit();
			}
		}
		if (count($errors) == 0) { 
			$db->query('UPDATE `'.DB_PREFIX.'categories` SET `category_name`=\''.mysql_escape_string($catname).'\' WHERE `sid`='.$sid.' LIMIT 1');
	          	print "<errnum>0</errnum><sid>$sid</sid><catname>" . xmlencode($catname) . "</catname>";
		} else {
	    		print "<errnum>".count($errors)."</errnum><errors>";
			foreach ($errors as $err) {
				print "<error>$err</error>";
			}
			print "</errors>";
	    	}
	print "</categories>"; 
}

function create () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<categories><errnum>1</errnum><errors><error>You do not have proper permissions to create categories!</error></errors></categories>";
		exit();
	}
	print "<categories>";
		$errors = Array();
		$catname = $_POST['catname'];
		if (trim($catname) == "") {
			$errors[] = "You forgot to enter a category name!";	
		}
		$result = $db->query("SELECT `category_name` FROM `".DB_PREFIX."categories` WHERE 1");
		while(list($check_catname) = mysql_fetch_row($result)) {
			if ($catname == $check_catname) {
				$errors[] = "A category with that name already exists!";		
			}
		}
		if (count($errors) == 0) { 
			$db->query("INSERT INTO `".DB_PREFIX."categories` (`sid`,`category_name`) VALUES (0,'" . mysql_escape_string($catname) . "')");
	          	print "<errnum>0</errnum><sid>$sid</sid><catname>" . xmlencode($catname) . "</catname>";
		} else {
	    		print "<errnum>".count($errors)."</errnum><errors>";
			foreach ($errors as $err) {
				print "<error>$err</error>";
			}
			print "</errors>";
	    	}
	print "</categories>"; 
}

function delete () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<categories><errnum>1</errnum><errors><error>You do not have proper permissions to delete categories!</error></errors><sid>".$_POST['sid']."</sid><catname>".$_POST['catname']."</catname></categories>";
		exit();
	}
	print "<categories>";
		$sid = $_POST['sid'];
		$result = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."categories` WHERE `sid`=$sid");
		$count = mysql_result($result,0);
		if (!is_numeric($sid) || $count == 0) { 
			exit();
		}
		$db->query('DELETE FROM `'.DB_PREFIX.'categories` WHERE `sid`='.$sid.' LIMIT 1');
       	print "<errnum>0</errnum>";
	print "</categories>"; 
}

function get_categories() {
	global $db;
    $result = $db->query("SELECT `sid`,`category_name` FROM `".DB_PREFIX."categories` WHERE 1 ORDER BY `sid` DESC");
     print "<categories>";
    while ($array = mysql_fetch_array($result, MYSQL_ASSOC)) {
 	  $result2 = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."news` WHERE `sid`=".$array['sid']);
 	  list($numposts) = mysql_fetch_row($result2);
 	  $db->free($result2);   	
      print '<category><name>' . $array['category_name'] . '</name><sid>' . $array['sid'] . '</sid><numposts>'.$numposts.'</numposts>';
      if ($_POST['selected'] == $array['sid']) {
		print '<selected>1</selected>';
      } else {
		print '<selected>0</selected>';	
      }
	  print '</category>';
    } 
    print "</categories>";
}


?>
