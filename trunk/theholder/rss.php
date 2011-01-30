<?php
header("Content-type: text/xml");
$sid = $_GET['sid'];
$id = $_GET['id'];
if (!is_numeric($sid)) {
$sid = 0;
}
if (!is_numeric($id)) {
$id = 0;
}

require("db.php");
$db = new db;
$db->connect();

    $result = $db->query("SELECT `BlogName`,`BlogDescription`,`BlogURL` FROM `".DB_PREFIX."options` WHERE 1 LIMIT 1");
    list($blogname,$blogdescription,$blogurl) = mysql_fetch_row($result);
    
    $content = <<<HTML
<?xml version="1.0"?>

<rdf:RDF
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns="http://my.netscape.com/rdf/simple/0.9/">

<channel>
<title>$blogname</title>
<link>$blogurl</link>
<description>$blogdescription</description>
</channel>

HTML;
if ($id == 0) {
	if ($sid == 0) {
		$what = "1";
	} else {
		$what = DB_PREFIX."news`.`sid`=$sid";
	}
    $result = $db->query("SELECT  `id`, `sid`,`subject`, `excerpt`, `body`  FROM  `".DB_PREFIX."news` WHERE $what ORDER BY `date` DESC LIMIT 0,10", false);
} else {
	$result = $db->query("SELECT `id`,'',`uname`,'',`body` FROM `".DB_PREFIX."comments` WHERE `id`=$id ORDER BY `date` ASC", false);
}

    
    while (list($id,$sid,$subject,$excerpt,$body) = mysql_fetch_row($result)) {
	    if ($excerpt) {
	    	$body = $excerpt;
	    }
	    if (!$sid) {
	    	$result2 = $db->query("SELECT `sid` FROM `".DB_PREFIX."news` WHERE `id`=$id LIMIT 1");
	    	list($sid) = mysql_fetch_row($result2);
	    }
        $subject = preg_replace("/&#44;/", ",", $subject);
        $subject = preg_replace("/&quot;/", '"', $subject);
        $subject = preg_replace("/[^A-Za-z0-9 '\"\(\(\!\.]/", "", $subject);
        $subject = htmlspecialchars($subject);

        $body = preg_replace("/<(.+?)>/", "", $body);
        $body = preg_replace("/[^A-Za-z0-9 \n]/", "", $body);
        if (strlen($body) > 150) {
            $body = substr($body, 0, 150);
            $body = $body . "...";
        } 
        $content .= <<<HTML

<item>
<title>$subject</title>
<link>$blogurl#$sid,$id</link>
HTML;
        if ($body != "") $content .= "<description>$body</description>\n";

        $content .= "</item>";
    } 
    $content .= <<<HTML
</rdf:RDF>
HTML;

print $content;
$db->disconnect();
?>