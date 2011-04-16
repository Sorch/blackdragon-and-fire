<?php

///////////////////////////////////////////////////////////////
// (c) 2010 - 2011 BlackDragon & Fire CMS  Development Team   /
//  THIS SOFTWARE IS BETA                                     /
//                                                            /
// http://code.google.com/p/blackdragon-and-fire              /
///////////////////////////////////////////////////////////////

// FORMS 

require('header.php');
if ($verified == 0) {
	bounceout("I, formfunctions.php, got an unauthorized request.");
} else {
	list($uid, $name, $pass, $administrator) = $verified;
    switch ($_REQUEST['action']) {
	case "getmonths":
		getmonths();
	break;
	default:
		bounceout("I, formfunctions.php, got an action, but it wasn't one I recognized:");
	break;
    }
}

function getmonths() {

$curmonth = date("n");
$curyear = date("Y");
print "<months>";
$months = Array(1 => 'January',
		2 => 'Feburary',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December');
	for($i=0;$i<12;$i++ ){
	$timestampstart = mktime(0, 0, 0, $curmonth, 1, $curyear);
	if ($curmonth == 12) {
	$timestampend = mktime(0,0,0,1,1,$curyear+1);
	} else {
	$timestampend = mktime(0,0,0,$curmonth+1,1,$curyear);
	}
	print "<entry><timestampstart>$timestampstart</timestampstart><timestampend>$timestampend</timestampend><name>".$months[$curmonth]." $curyear</name></entry>";

	$curmonth = $curmonth - 1;
		if ($curmonth == 0) {
			$curyear--;
			$curmonth = 12;
		} 
	}
print "</months>";
}
?>
