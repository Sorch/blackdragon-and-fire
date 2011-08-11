// 
// 
// See index.php for full license.

function widgetsForm(errors) {
	$('content').innerHTML = "<h1 id=\"widgetsTitle\" class=\"T\"><b>Manage Widgets:</b></h1><br><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Widget Name</th><th scope=\"col\">Contents</th><th scope=\"col\"></th><th scope=\"col\"></th></tr></THEAD><TBODY id=\"widgetsTable\"></TBODY></table><br><h1 id=\"widgetsCreateTitle\" class=\"T\"><b>Create Widget:</b></h1><div id=\"error\"></div><form name=\"widF\" onSubmit=\"javascript:return CreateWidget();\"><h3 class=\"T\"><label for=\"widname\">Widget Name:</label><input type=\"text\" name=\"widname\" tabindex=\"1\" size=\"20\"><br /><label for=\"contents\">Contents:</label><textarea rows=\"7\" cols=\"70\" name=\"contents\"></textarea><br /><br /><input type=\"submit\" value=\"Create Widget »\"></h3><input type=\"hidden\" name=\"action\" value=\"create\"></form>";
	loadWidgetsTable();
}

function loadWidgetsTable() {
	showLoadingWidgets();
	new Ajax.Request('widgets.php', {method:'post', postBody:'action=getwidgets',
						onComplete: parseWidgetsTable,
						onFailure: ohno,
						asynchronous: true});

}

function parseWidgetsTable(req) {
	var widgets = req.responseXML.getElementsByTagName("widget");

	$("widgetsTable").removeChild($('LoadingRow'));
    if (widgets.length == 0) {
    	widgetError("You need to be an administrator to modify or create widgets.");
    }
	for (var i=0;i<widgets.length;i++) {
		var wid = getElementTextNS("wid",widgets[i],0);
		var widname = getElementTextNS("name",widgets[i],0);
		var contents = getElementTextNS("contents",widgets[i],0);
		var newRow = Builder.node('tr',{id: 'row'+wid},
				[Builder.node('td',{id: wid+'wid', className: 'id'},wid),
				 Builder.node('td',{id: wid+'widname'},widname),
				 Builder.node('td',{id: wid+'contents'},contents),
				 Builder.node('td',{id: wid+'edit'},'Edit'),
				 Builder.node('td',{id: wid+'del'},'Delete')]);
		$('widgetsTable').appendChild(newRow);
		$(wid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+wid+"\" onclick=\"javascript:editWidgetForm("+wid+")\">Edit</a>";
		$(wid+'del').innerHTML = "<a href=\"javascript:;\" id=\"dellink"+wid+"\" onclick=\"javascript:deleteWidget("+wid+")\">Delete</a>";

		if(window.addEventListener){ // Mozilla, Netscape, Firefox
			$('row'+wid).addEventListener('mouseover', highlight, false);
			$('row'+wid).addEventListener('mouseout', lowlight, false);
		} else { // IE
			$('row'+wid).attachEvent('onmouseover', highlight);
			$('row'+wid).attachEvent('onmouseout', lowlight);
		}
	}
}




function editWidgetForm(wid) {
	smallWidLoad(wid);
	new Ajax.Request('widgets.php', {method:'post', postBody:'action=getbody&wid='+wid,
						onComplete: parseEditWidgetForm,
						onFailure: ohno,
						asynchronous: true});

}

function parseEditWidgetForm(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var wid = getElementTextNS("wid",info[0],0);
	var contents = convertHTML(getElementTextNS("contents",info[0],0));
	var rows = textSize(contents);
	$(wid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+wid+"\" onclick=\"javascript:saveEditWidget("+wid+")\">Save</a>";
	$(wid+'contents').innerHTML = "<textarea rows=\""+rows+"\" cols=\"70\" id=\"contentstext"+wid+"\">"+contents+"</textarea>";
	$(wid+'widname').innerHTML = "<input type=\"text\" size=\"10\" id=\"widnametext"+wid+"\" value=\""+$(wid+'widname').innerHTML+"\">";
}

function saveEditWidget(wid) {
	var widname = $('widnametext'+wid).value;
	var contents = $('contentstext'+wid).value;
    smallWidLoad(wid);
    new Ajax.Request('widgets.php', {method:'post', postBody:'action=edit&wid='+escape(wid)+'&widname='+escape(widname)+'&contents='+escape(contents),
						onComplete: parseEditWidget,
						onFailure: ohno,
						asynchronous: true});
    
	return false;
}

function parseEditWidget(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errors = req.responseXML.getElementsByTagName("errors")[0];
		for(var i=0;i<errnum;i++) {
			alert(getElementTextNS("error",errors,i));
		}
	}
	widgetsForm();
}


function CreateWidget() {
	var f = document.forms['widF'];
	savingForm();
	new Ajax.Request('widgets.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseCreateWidget,
						onFailure: ohno,
						asynchronous: true});
	return false;
}


function parseCreateWidget(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		widgetsForm();
	}
}


function deleteWidget(wid) {
var agree=confirm("Are you sure you wish to delete this widget?");
if (agree) {
	doDeleteWidget(wid);
}
}

function doDeleteWidget(wid) {
	   new Ajax.Request('widgets.php', {method:'post', postBody:'action=delete&wid='+wid,
						onComplete: parseDeleteWidget,
						onFailure: ohno,
						asynchronous: true});
}

function parseDeleteWidget(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum.length > 0) {
		var errors = req.responseXML.getElementsByTagName("errors")[0];
		for(var i=0;i<errnum;i++) {
			alert(getElementTextNS("error",errors,i));
		}
	}
	widgetsForm();
}

function widgetError (msg) {
	removeRows("widgetsTable");
	var newRow = Builder.node('tr',{},[Builder.node('td',{id: 'errorText'},'')]);
	$('widgetsTable').appendChild(newRow);
	$('errorText').colSpan = 5;
	$('errorText').innerHTML ="<strong>" + msg + "</strong>";
}

function showLoadingWidgets() {
    removeRows("widgetsTable");
	var newRow = Builder.node('tr',{id: 'LoadingRow'},[Builder.node('td',{id: 'LoadingText'},'')]);
	$('widgetsTable').appendChild(newRow);
	$('LoadingText').colSpan = 9;
	$('LoadingText').style.backgroundColor = "#fff";
	$('LoadingText').innerHTML = '<h3><img src=\'images/loading.gif\'> Loading...</h3>';
}
function smallWidLoad(wid) {
	$(wid+'edit').innerHTML = "<img src=\"images/loadingsmall.gif\" border=\"0\"> Wait..";
}

