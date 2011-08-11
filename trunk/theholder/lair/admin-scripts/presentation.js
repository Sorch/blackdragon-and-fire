// 
// 
// See index.php for full license.


function presentationForm() {
	gen_side();
	$('content').innerHTML = "<h1 id=\"presentationTitle\" class=\"T\"><b>Presentation:</b></h1><br />";
	var tabs = ["Posts","Comments","Widgets","Loading Text","Errors Text"];
	for(var i=0;i<tabs.length;i++) {
		var id = tabs[i].replace(/ /g, '');
		$('content').innerHTML = $('content').innerHTML + "<span id=\""+id+"\" onmouseover=\"javascript:this.style.backgroundColor='#fffada';\" onmouseout=\"javascript:if(this.id!='"+id+"sel') this.style.backgroundColor='#f1f1f1';\" class=\"tab\" onClick=\"javascript:show('"+tabs[i]+"')\">"+tabs[i]+"</span>";

	}
	$('content').innerHTML = $('content').innerHTML + "<div id=\"tabbox\"></div><br /><div id=\"error\"></div>";
	show("Posts");
}



function show(what) {


var id = what.replace(/ /g, '');
if ($(id)) {
$(id).style.backgroundColor='#fffada';
$(id).id = id + 'sel';
}
var tabs = ["Posts","Comments","Widgets","Loading Text","Errors Text"];
for(var i=0;i<tabs.length;i++) {
	if (tabs[i].replace(/ /g, '') != id) {
		if ($(tabs[i].replace(/ /g, '')+"sel")) {
			$(tabs[i].replace(/ /g, '')+"sel").id = tabs[i].replace(/ /g, '');
		}
		$(tabs[i].replace(/ /g, '')).style.backgroundColor='#f1f1f1';
	}
}
	showPresentationLoading();
    new Ajax.Request('presentation.php', {method:'post', 
			                          postBody:'action=getoptions&what='+escape(what),
									  onComplete: parseShow,
									  onFailure: ohno,
									  asynchronous: true});

}

function parseShow(req) {

$('tabbox').innerHTML = "";
var options = req.responseXML.getElementsByTagName("option");

	for (var i=0;i<options.length;i++) {
		var pid = getElementTextNS("pid",options[i],0);
		var subtitle = getElementTextNS("subtitle",options[i],0);
		var available = getElementTextNS("available",options[i],0);
		var description = getElementTextNS("description",options[i],0);
        $('tabbox').innerHTML = $('tabbox').innerHTML + "<label for=\""+pid+"\">"+subtitle+":</label><div id=\"section"+pid+"\"><textarea rows=\"10\" style=\"width: 50%\" name=\""+subtitle+"\" tabindex=\""+i+"\" id=\"t"+pid+"\"></textarea></div><span style=\"padding-left: 190px; display: block\"><b>"+description+"</b> (<a href=\"javascript:;\" onclick=\"javascript:restoreDefault("+pid+")\">Restore Default?</a>)<br /><b>Available Variables:</b> "+available+"</span><br /><br />";
		$('t'+pid).appendChild(document.createTextNode(window[subtitle]));
	}

	$('tabbox').innerHTML = "<form name=\"presentationF\" onSubmit=\"javascript:return SavePresentation();\">" + $('tabbox').innerHTML  + "<input type=\"hidden\" name=\"action\" value=\"save\"><h3 class=\"T\"><input type=\"submit\" value=\"Save »\"></h3></form><br/><br/>";

}

function restoreDefault(pid,thedefault) {
    $('section'+pid).innerHTML = "<img src='images/loadingsmall.gif'> Restoring...";
    new Ajax.Request('presentation.php', {method:'post', 
			                          postBody:'action=getdefault&pid='+pid,
									  onComplete: parseRestoreDefault,
									  onFailure: ohno,
									  asynchronous: true});

}

function parseRestoreDefault(req) {

	var info = req.responseXML.getElementsByTagName("info");
	var pid = getElementTextNS("pid",info[0],0);
	var subtitle = getElementTextNS("subtitle",info[0],0);
	var tdefault = getElementTextNS("default",info[0],0);
	$('section'+pid).innerHTML = "<textarea rows=\"10\" style=\"width: 50%\" name=\""+subtitle+"\" id=\"t"+pid+"\"></textarea>";
	$('t'+pid).appendChild(document.createTextNode(tdefault));

}

function showPresentationLoading() {
	$('tabbox').innerHTML = "<img src='images/loadingsmall.gif'> Loading...";
}

function SavePresentation() {

	var f = document.forms['presentationF'];
	savingForm();
    new Ajax.Request('presentation.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseSavePresentation,
						onFailure: ohno,
						asynchronous: true});
	return false;

}

function parseSavePresentation(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		$('error').className="success";
		$('error').innerHTML ="Presentation updated successfully! You may view your blog by clicking <a href=\""+blogurl+"\">here</a>.";
	}
}