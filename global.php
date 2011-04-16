<?php
// Globals
// 
// This will be cleaned up and patched to run cleaner 
function xmlencode($string)
{
    return str_replace (array ('“', '”', '…', '&', '"', "'", '<', '>', '‘'), array ('"', '"', '...', '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '\''), $string); 
    // return str_replace (array ('<','>'), array('&lt;','&gt;'),$string);
} 

function newsbody($body, $html, $user = "")
{
	$body = stripslashes($body);
    if ($html == 1) {
        $body = " $body";
        $body = str_replace('’', '\'', $body);
        $body = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3" target="_blank">\2://\3</a>', $body);
        $body = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" target="_blank">\2.\3</a>', $body);
        $body = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $body);
        $body = preg_replace("/[\r\t\f]/", '', $body);
        // $body = str_replace("	","",$body);
        $body = str_replace("\n", '<br />', $body); 
        // $body = preg_replace("/(  +)/e", "str_repeat('&nbsp;', strlen('\\1'))", $body);
        $body = preg_replace("/^ /", "", $body);
    } 

return $body;
}

function compose_time($time) {
	return date("F j, Y, g:i a", $time);
}
function html_filter($a)
{
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
?>
