<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// WIDGETS



require_once("db.php");
require_once("global.php");

if (!isset($noxml)) {
	$db = new db;
	$db->connect();
	widget_xml(get_widgets($db));
	$db->disconnect();
}
function widget_xml($result) {
header("Content-type: text/xml");
print "<widgets>\n";
if (mysql_num_rows($result) == 0) {
	print "<widget><title>None Yet!</title><id>0</id><text>No Widgets have been created yet!</text></widget>";
} else {
	while (list($wid, $name,$contents) = mysql_fetch_row($result)) {
		if ($contents{0} == '[' && $contents{strlen($contents)-1} == ']') {
			$contents = advanced_widgets(substr($contents,1,-1));
		}
		print "<widget><title>".xmlencode($name)."</title><id>$wid</id><text>".xmlencode($contents)."</text></widget>";
	}
}

print "</widgets>";
}

function get_widgets($db) {
	$result = $db->query("SELECT COUNT(*) FROM `".DB_PREFIX."widgets` WHERE 1");
	list($count) = mysql_fetch_row($result);
	if ($_COOKIE['left']) {
		$left = preg_replace("[^0-9,]","",$_COOKIE['left']);
		$items = count(explode(",",$left));
		if ($items == $count) {
			$select = "`wid` IN ($left) ORDER BY FIELD(wid,$left)";
		} else {
			$select = "1";
		}
	} else {
		$select = 1;	
	}
	
	return $db->query("SELECT `wid`,`widget_name`,`contents` FROM `".DB_PREFIX."widgets` WHERE $select");
}
// handler for advanced widgets
// i.e. rss feeds, thusfar.
// make sure to check/update noscript as well.
function advanced_widgets($what) {
	if (substr($what,0,3) == "rss") {
		$array = explode(',',$what);
		$separator = $array[1];
		$url = $array[2];
		include_once('./rss_parser.php');
		$rss = new lastRSS; 
		// no cache, no thanks.
		$rss->cache_dir = ''; 
		$rss->cache_time = 0;
		// load some RSS file
		if ($rs = $rss->get($url)) {
			$content = '';
			foreach ($rs["items"] as $item) {
				$content .= $separator . '<a href="'.$item['link'].'" target="_new">'.$item['title'].'</a><br />';
			}
			return $content;
		} else {
			return "[rss error: could not load $url]";
		}
	} elseif ($what == "search") {
		return "<form name=\"searchF\" onSubmit=\"javascript:return SearchPosts();\" method=\"POST\"><input type=\"hidden\" name=\"action\" value=\"search\"><input type=\"text\" name=\"query\" size=\"10\" maxlength=\"150\"><input type=\"submit\" value=\"&raquo;\"></form>";
	}
	// just return what if we don't know what to do with it
	return $what;
}

?>
