<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// GLOBAL CALL FUNCTIONS REALLY SHOULDNT NEED TO BE TOUCHED
// UNLESS SOMETHING BREAKS -_-

function xmlencode($string) {
    return str_replace (array ('“', '”', '…', '&', '"', "'", '<', '>', '‘'), array ('"', '"', '...', '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '\''), $string); 
} 


function strip($what) {
	foreach ($what as $param => $value) {
		$what[$param] = stripslashes($value);
	}
	return $what;
}

function verifylogin($user, $pass, $db) {
    $user = addslashes($user);
    $pass = addslashes($pass);
    if (trim($user) == "" && trim($pass) == "") {
        return 0;
    } 
    if (substr($pass, 0, 12) != "\$1\$ajaxpass$" && trim($pass) != '') {
        $pass = crypt($pass, "\$1\$ajaxpass");
    } 
    $result = $db->query("SELECT `uid`,`uname`,`pword`,`administrator` FROM `".DB_PREFIX."users` WHERE `uname`='$user'", false);
    if (mysql_num_rows($result) == 0) {
        return 0;
    } else {
        list($uid, $name, $pword, $administrator) = mysql_fetch_row($result);
        if ($pword != $pass) {
            return 0;
        } else {
            return Array($uid, $name, $pass, $administrator);
        } 
    } 
} 

function compose_time($time) {
    return date("F j, Y, g:i a", $time);
} 
function html_filter($a) {
    $a = stripslashes($a);
    $a = str_replace('&', '&amp;', $a);
    $a = str_replace('"', '&quot;', $a);
    $a = str_replace(',', '&#44;', $a);
    $a = str_replace('<', '&lt;', $a);
    $a = str_replace('>', '&gt;', $a);
    $a = str_replace('&lt;b&gt;', '<b>', $a);
    $a = str_replace('&lt;i&gt;', '<i>', $a);
    $a = str_replace('&lt;u&gt;', '<u>', $a);
    $a = str_replace('&lt;strike&gt;', '<strike>', $a);
    $a = str_replace('&lt;/b&gt;', '</b>', $a);
    $a = str_replace('&lt;/i&gt;', '</i>', $a);
    $a = str_replace('&lt;/u&gt;', '</u>', $a);
    $a = str_replace('&lt;/strike&gt;', '</strike>', $a);
    $a = str_replace('&lt;p&gt;', '<p>', $a);
    $a = str_replace('&lt;br /&gt;', '<br>', $a);

    return $a;
} 
function shorten($string) {
		if (strlen($string) > 20) {
			return substr($string,0,20)."...";
		} else {
			return $string;	
		}
}		

function bounceout($string) {
	header("HTTP/1.0 403 Forbidden");
    print $string;
    exit;	
}
	
?>
