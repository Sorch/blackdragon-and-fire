<?php
// 
// See index.php for full license.

require('header.php');
if ($verified == 0) {
    bounceout("I, widgets.php, got an unauthorized request.");
    exit;
} elseif ($administrator == 0) {
	print "<widgets></widgets>";
	exit;
}
if ($_POST['action'] == "delete") {
	delete();
} elseif ($_REQUEST['action'] == "getwidgets") {
    get_widgets();	
} elseif ($_POST['action'] == "edit") {
	edit();
} elseif ($_POST['action'] == "create") {
	create();
} elseif ($_REQUEST['action'] == "getbody") {
    getbody();
} else {
	bounceout("I got a action, but it wasn't one I recognized.");
}

$db->disconnect();



function edit () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<widgets><errnum>1</errnum><errors><error>You do not have proper permissions to edit widgets!</error></errors></widgets>";
		exit;
	}
	print "<widgets>";
		$errors = Array();
		$widname = $_POST['widname'];
		$wid = $_POST['wid'];
		$contents = $_POST['contents'];
		if (!is_numeric($wid)) {
				bounceout("I, widgets.php, got an invalid widget ID");	
		}
		if (trim($widname) == "" || trim($contents) == "") {
			$errors[] = "You forgot to enter a widget name or contents!";	
		} else {
			$result = $db->query("SELECT `wid`,`widget_name` FROM `".DB_PREFIX."widgets` WHERE 1");
			while(list($check_wid,$check_widname) = mysql_fetch_row($result)) {
				if ($widname == $check_widname && $check_wid != $wid) {
					$errors[] = "A widget with that name already exists!";		
				}
			}

			$result = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."widgets` WHERE `wid`=$wid");
			$count = mysql_result($result,0);
			if ($count == 0) { 
				bounceout("I, widgets.php, got an invalid widget ID due to DB lookup");
			}
		}
		if (count($errors) == 0) { 
			$db->query('UPDATE `'.DB_PREFIX.'widgets` SET `widget_name`=\''.mysql_escape_string($widname).'\', `contents`=\''.mysql_escape_string($contents).'\' WHERE `wid`='.$wid.' LIMIT 1');
	          	print "<errnum>0</errnum><wid>$wid</wid><widname>" . xmlencode($widname) . "</widname>";
		} else {
	    		print "<errnum>".count($errors)."</errnum><errors>";
			foreach ($errors as $err) {
				print "<error>$err</error>";
			}
			print "</errors>";
	    	}
	print "</widgets>"; 
}

function create () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<widgets><errnum>1</errnum><errors><error>You do not have proper permissions to create widgets!</error></errors></widgets>";
		exit();
	}
	print "<widgets>";
		$errors = Array();
		$widname = $_POST['widname'];
		$contents  =$_POST['contents'];
		if (trim($widname) == "" || $contents == "") {
			$errors[] = "You forgot to enter a widget name or contents!";	
		}
		$result = $db->query("SELECT `widget_name` FROM `".DB_PREFIX."widgets` WHERE 1");
		while(list($check_widname) = mysql_fetch_row($result)) {
			if ($widname == $check_widname) {
				$errors[] = "A widget with that name already exists!";		
			}
		}
		if (count($errors) == 0) { 
			$db->query("INSERT INTO `".DB_PREFIX."widgets` (`wid`,`widget_name`,`contents`) VALUES (0,'" . mysql_escape_string($widname) . "','". mysql_escape_string($contents) ."')");
	          	print "<errnum>0</errnum>";
		} else {
	    		print "<errnum>".count($errors)."</errnum><errors>";
			foreach ($errors as $err) {
				print "<error>$err</error>";
			}
			print "</errors>";
	    	}
	print "</widgets>"; 
}

function delete () {
	global $db,$administrator;
	if ($administrator == 0) {
		print "<widgets><errnum>1</errnum><errors><error>You do not have proper permissions to delete widgets!</error></errors></widgets>";
		exit();
	}
	print "<widgets>";
		$wid = $_POST['wid'];
		if (!is_numeric($wid)) {
		bounceout("I, widgets.php, got an invalid WID while deleting");	
		}
		$result = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."widgets` WHERE `wid`=$wid");
		$count = mysql_result($result,0);
		if ($count == 0) { 
			exit();
		}
		$db->query('DELETE FROM `'.DB_PREFIX.'widgets` WHERE `wid`='.$wid.' LIMIT 1');
       	print "<errnum>0</errnum>";
	print "</widgets>"; 
}

function get_widgets() {
	global $db;
    $result = $db->query("SELECT `wid`,`widget_name`,`contents` FROM `".DB_PREFIX."widgets` WHERE 1 ORDER BY `wid` DESC");
     print "<widgets>";
    while ($array = mysql_fetch_array($result, MYSQL_ASSOC)) {
    	$contents = shorten($array['contents']);
 	  print '<widget><name>' . $array['widget_name'] . '</name><contents>' . xmlencode($contents) . '</contents>
			<wid>' . $array['wid'] . '</wid>';
      if ($_POST['selected'] == $array['wid']) {
		print '<selected>1</selected>';
      } else {
		print '<selected>0</selected>';	
      }
	  print '</widget>';
    } 
    print "</widgets>";
}
function getbody() {
	global $db;
	if (!is_numeric($_REQUEST['wid'])) {
		bounceout("I, widgets.php, received an invalid WID while trying to get a full body.");
	} else {
		$result = $db->query("SELECT `contents` FROM `".DB_PREFIX."widgets` WHERE `wid`=".$_REQUEST['wid']);
		list($contents) = mysql_fetch_row($result);
		print "<widgets><info><contents>".xmlencode($contents)."</contents><wid>".$_REQUEST['wid']."</wid></info></widgets>";
	}
}

?>
