<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// FALL BACK & CMS FAILSAFE

require_once("db.php");
require_once("global.php");

$db = new db;
$db->connect();
// set noxml to one for all the phps
$noxml = 1;
	
$result = $db->query("SELECT `BlogName`,`BlogPath`,`BlogDescription`,`BlogURL`,`ThemePath`,`ActiveTheme`,`AbsolutePath`,`ScriptPath`,`DefaultSID` FROM `".DB_PREFIX."options` LIMIT 1");
list($blogname,$blogpath,$blogdescription,$blogurl,$themepath,$activetheme,$absolutepath,$scriptpath,$defaultsid) = mysql_fetch_row($result);

// set up initial index
$index = file_get_contents($absolutepath . $themepath . $activetheme . "/index.html");
$index = str_replace("__ABSOLUTEPATH__",$absolutepath,$index);
$index = str_replace("__THEMEPATH__",$themepath . $activetheme . "/",$index);
$index = str_replace("__SCRIPTPATH__",$scriptpath,$index);
$index = str_replace("__BLOGURL__",$blogurl,$index);
$index = str_replace("__BLOGDESCRIPTION__",$blogdescription,$index);
$index = str_replace("__BLOGNAME__",$blogname,$index);
$index = str_replace("__ACTIVETHEME__",$activetheme,$index);
$index = str_replace("Load();","",$index);
$index = preg_replace("/[\t\f]/","",$index);

// remove scripts
$index = preg_replace("/<script(.*?)script>/i","",$index);
$template = loadTemplate();
$viewingsid = $defaultsid;
if ($_POST['action'] == "search") {
$viewingsid = 0;
$viewingid = 0;
} else {
$viewingsid = $_GET['sid'];
$viewingid = $_GET['id'];
}
$content = getContent($viewingsid,$viewingid);
$sidebar = getSide();



$index = preg_replace("/<div(.+?)id=\"sidebar\"(.*?)>/is","<div\\1id=\"sidebar\"\\2>$sidebar",$index);
$index = preg_replace("/<div(.+?)id=\"content\"(.*?)>/is","<div\\1id=\"content\"\\2>$content",$index);
print $index;
$db->disconnect();


// decide what to get
function getContent($viewingsid,$viewingid) {
	if (!$viewingid) {
		return getPosts($viewingsid);	
	} else {
		return getPost($viewingid);
	}
}


// get one post, comments, and the lik
function getPost($viewingid) {
	global $db,$template,$noxml;
	$content = '';
	include("viewpost.php");
	$post = get_post($db,$viewingid);
	if (mysql_num_rows($post) == 0) {
		return parsesinglepost(Array("subject" => "Post not found",
				"body" => "The post you are looking for has not been found"),0,0);
	} else {
		list($nextlinks,$prevlinks) = get_nextprev($db,$viewingid);
		$post = mysql_fetch_array($post, MYSQL_ASSOC);
		$post = array_merge($post,Array("previd" => $prevlinks["id"], "prevsubj" => $prevlinks["subject"]), 
					Array("previd" => $nextlinks["id"], "prevsubj" => $nextlinks["subject"]));

		$allowcomments = $post["allowcomments"];
		$subject = $post["subject"];
		$content .= parsesinglepost($post,0,1);
		if ($allowcomments == 1) {
		$comments = get_comments($db,$viewingid);
		$comment = "";
		$num = 0;
			while ($array = mysql_fetch_array($comments, MYSQL_ASSOC)) {
				$num++;
				$comment .= parseSingleComment($array["uname"], date("F j, Y, g:i a",
									$array["date"]),$array["url"],newsbody($array["body"],1),$array["cid"]);
			} 
			$content .= templateReplace($template['CommentsStart'],Array('__NUM__','__S__','__TITLE__','__COMMENTS__'),Array('<span id="commentnum">'.$num.'</span>',($num == 1)? '' : 's',$subject,$comment));
			$commentForm = $template['CommentForm'];
			$commentForm = preg_replace('/<\/form>/i','<input type="hidden" name="noxml" value="1"><input type="hidden" name="id" value="'.$viewingid.'"></form>',$commentForm);
			$content .= templateReplace($commentForm,Array('__ERRORS__','__COMMENTACTION__'),Array("","\" action=\"addcomment.php"));
		}
		return $content;
	}

}

// get more than one post
function getPosts($viewingsid) {
	global $db,$noxml,$template;
	include("posts.php");
	if ($_POST['action'] == "search") {
		$result = search_posts($db,$_POST['query']);
	} else {
		$result = get_posts($db,$viewingsid,0);
	}
	$content = "";
	if (mysql_num_rows($result) == 0) {
	  if ($_POST['action'] == "search") {
		  return "No posts could be found that matched your query.";
	  } else {
		  return $template["NoPosts"];
	  }
	} else {
		while ($array = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$content .= parsesinglepost($array,1,0);
		}
	}
	return $content;
}

function parseSingleComment($user,$date,$url,$thebody,$id) {
	global $template;
	if ($alt == "alt") {
	 $alt = "";
	} else {
	 $alt = "alt";
	}
	return templateReplace($template['Comment'],Array('__ALT__','__ID__','__UNAME__','__DATE__','__BODY__'),Array($alt,$id,$user,$date,$thebody));
}

function parsesinglepost($post,$showCommentLink,$fullPost) {
	global $template,$blogurl,$viewingid,$viewingsid;
        $subject = $post["subject"];
	$thebody = $post["body"];
	$date =  date("F j, Y, g:i a",$post["date"]);
	$author = $post["display_name"];
	$category = $post["category_name"];

	if ($fullpost == 1) {
	// update title		document.title = blogname + " » " + category + " » " + subject;
	} else {
	// dont 		document.title = blogname;
	}
	
	$excerpt = $post["excerpt"];
	$numcomments = $post["comments"];
	$id = $post["id"];
	$sid = $post["sid"];
	$allowcomments = $post["allowcomments"];
	if ($showCommentLink==1 && $allowcomments==1) {
		if ($numcomments == 0){
			$commentsSt = $template["NoComments"];
		} else {
			$commentsSt = templateReplace($template["Comments"],Array('__NUM__','__S__'),Array($numcomments,($numcomments == 1? '' : 's')));
		}
	} else {
		$commentsSt = "";
	}
	if ($excerpt != "" && $fullPost != 1) {
		$thebody = $excerpt + "<a href=\"noscript.php?id=$id\">" + $template["ReadMoreText"] + "</a>";
	}
	
	if ($fullPost == 1) {
		$previd = $post["previd"];
		$nextid = $post["nextid"];
		$prevsubj = $post["prevsubj"];
		$nextsubj = $post["nextsubj"];
		return templateReplace($template["SinglePost"],Array('__ID__','__SID__','__TITLE__','__DATE__','__AUTHOR__','__BODY__','__CATEGORY__','__COMMENTS__','__PREVID__','__PREVPOST__','__NEXTID__','__NEXTPOST__','__RSS__'),Array($id,$sid,$subject,$date,$author,$thebody,$category,$commentsSt,$previd,$prevsubj,$nextid,$nextsubj,'<a href="'.$blogurl.'rss.php?id='.$viewingid.'&sid='.$viewingsid.'">RSS</a>'));
	} else {
		return templateReplace($template["Post"],Array('__ID__','__SID__','__TITLE__','__DATE__','__AUTHOR__','__BODY__','__CATEGORY__','__COMMENTS__'),Array($id,$sid,$subject,$date,$author,$thebody,$category,$commentsSt));
	}

}

function getSide() {
	global $db,$template,$noxml;
	include("widgets.php");
	$result = get_widgets($db);
	$side = "";
	while (list($wid, $name,$contents) = mysql_fetch_row($result)) {
		if ($contents{0} == '[' && $contents{strlen($contents)-1} == ']') {
			if (substr($contents,1,-1) == "search") {
				$contents = "<form name=\"searchF\" action=\"noscript.php\" method=\"POST\"><input type=\"hidden\" name=\"action\" value=\"search\"><input type=\"text\" name=\"query\" size=\"10\" maxlength=\"150\"><input type=\"submit\" value=\"&raquo;\"></form>";
			} else {
				$contents = advanced_widgets(substr($contents,1,-1));
			}
		}
		$side = $side . templateReplace($template['Widget'],Array('__ID__','__TITLE__','__TEXT__'),Array($wid,$name,$contents));
	}

	return $side;
}



function templateReplace($what,$variables,$to) {
	for($i=0;$i<count($variables);$i++) {
		 $what = preg_replace("/".$variables[$i]."/i",$to[$i],$what);
	}
	return $what;
}



// loads the javascript template and converts it to a php array
function loadTemplate() {
global $absolutepath,$themepath,$activetheme;
$template = file_get_contents($absolutepath . $themepath . $activetheme . "/templates.js");
	$array = Array();
	foreach (explode("\n",$template) as $line) {
		$line = trim($line);
		if (!preg_match("/^var/i",$line)) {
			continue;
		}
		$variable = trim(preg_replace("/^var (.*?) = (.*)/i","\\1",$line));
		$contents = str_replace('\"','"',preg_replace("/^(.*?) = \"(.*)\";$/i","\\2",$line));
		if ($variable == "Post") {
		$contents = preg_replace("/href=\"javascript:;\" onClick=\"javascript:viewpost\(__ID__\)\"/i","href=\"noscript.php?id=__ID__\"",$contents);
		$contents = preg_replace("/href=\"javascript:;\" onClick=\"javascript:gotoPostPage\(0,__SID__\)\"/i","href=\"noscript.php?sid=__SID__&id=0\"",$contents);
		}
		$array[$variable] = $contents;
	}
return $array;
}

?>
