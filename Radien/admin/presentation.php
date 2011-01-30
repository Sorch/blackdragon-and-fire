<?php
// 
// See index.php for full license.

require('header.php');

if ($verified == 0) {
    bounceout("I, presentation.php, got an unauthorized request.");
}

if ($_POST['action'] == "getoptions") {
	getoptions();	
} elseif ($_POST['action'] == "save") {
	save();
} elseif ($_POST['action'] == "getdefault") {
	getDefault();
} else {
	bounceout("I, presentation.php, got an action, but it wasn't one I recognized.");
}
$db->disconnect();
exit;

function getoptions() {
	global $db;
	$what = $_POST['what'];
		if (!in_array($what, Array("Posts","Comments","Widgets","Loading Text","Errors Text"))) {
			bounceout("I, presentation.php, got an unrecognized option request.");	
		}
	$result = $db->query("SELECT `pid`,`SubTitle`,`Available`,`Description` FROM `".DB_PREFIX."presentation` WHERE `MainCat`='".$what."'");
	print "<presentation><options>";
	while(list($pid,$subtitle,$available,$description) = mysql_fetch_row($result)) {
			print "<option>
					<pid>$pid</pid>
					<subtitle>".xmlencode($subtitle)."</subtitle>
					<available>".xmlencode($available)."</available>
					<description>".xmlencode($description)."</description>
				   </option>";
	}
	print "</options></presentation>";
	
}

function getDefault() {
	global $db;
	$pid = $_POST['pid'];
	if (!is_numeric($pid)) {
		bounceout("I, presentation.php, got an invalid pid while trying to fetch defaults.");
	}
	$result = $db->query("SELECT `Default`,`SubTitle` FROM `".DB_PREFIX."presentation` WHERE `pid`=".mysql_escape_string($pid)." LIMIT 1");
	if (mysql_num_rows($result) != 1) {
		bounceout("I, presentation.php, got an invalid resultset while trying to fetch defaults.");	
	}
	list($default,$subtitle) = mysql_fetch_row($result);
	print "<presentation><info><subtitle>".xmlencode($subtitle)."</subtitle><pid>$pid</pid><default>".xmlencode($default)."</default></info></presentation>";
}

function save() {
	global $db,$administrator;
	print "<presentation>";
	$errors = Array();
	if ($administrator == 0) {
		$errors[] = "You must be an administrator to edit presentations!";	
	}
	$result = $db->query("SELECT `ThemePath`,`ActiveTheme`,`AbsolutePath` FROM `".DB_PREFIX."options` LIMIT 1");
	list($themepath,$activetheme,$absolutepath) = mysql_fetch_row($result);
	$template = $absolutepath . $themepath . $activetheme . "/templates.js";
	if (!is_writable($template)) {
		$errors[] = "I cannot seem to write the file: $template. Check its permissions (0666) and that the file exists.";
	}

	
	if (count($errors) == 0) {
			print "<errnum>0</errnum>";

			$newlines = Array();
			$lines = file($template);
			
			foreach ($lines as $linenum => $line) {
   				$subtitle = trim(preg_replace("/var (.+?)=.*/i","\\1",$line));
   				if ($_POST[$subtitle] && $subtitle != '') {
   					$newlines[] = prepare($subtitle,$_POST[$subtitle]);
   				} else {
   					$newlines[] = $line;
   				}
   				
			}
			$handle = fopen($template, 'w');
			fwrite($handle, join($newlines,''));
			fclose($handle);
	} else {
	        print "<errnum>" . count($errors) . "</errnum>";
	        print "<errors>";
	        foreach ($errors as $error) {
	            print "<error>$error</error>";
	        } 
	        print "</errors>";
	}
	print "</presentation>";
}

function prepare($subtitle,$text) {
$text = str_replace(array("\r", "\n"), '',$text);
$text = stripslashes($text);
$text = str_replace('"','\"',$text);
return 'var '.$subtitle.' = "'.$text.'"'.";\n"; 
}
?>
