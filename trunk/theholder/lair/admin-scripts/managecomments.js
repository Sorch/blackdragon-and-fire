//
// 
// See index.php for full license.

function resetAllComments() {
loadCommentsTable();
loadCommentMonths();
}

function loadCommentMonths() {
clearSelect("commentmonth");
           new Ajax.Request('formfunctions.php', {method:'post', 
			                          postBody:'action=getmonths',
						  onComplete: parseCommentMonths,
						  onFailure: ohno,
						  asynchronous: true});
			
}

function gotoMonthComments() {
var sel = document.forms['monthFormComments'].month.options[document.forms['monthFormComments'].month.selectedIndex].value;
if (sel != "none") {
if (document.forms['monthFormComments'].month.options[0].value == "none") {
document.forms['monthFormComments'].month.removeChild(document.forms['monthFormComments'].month.options[0]);
}
var name = document.forms['monthFormComments'].month.options[document.forms['monthFormComments'].month.selectedIndex].text;
$('commentsTitle').innerHTML = "<b>Comments from "+name+":"; 
var time1 = sel.substring(0,10);
var time2 = sel.substring(11);
getBetweenTimesComments(time1,time2);
return false;
}
}

function getBetweenTimesComments (time1,time2) {
showLoadingComments();
          new Ajax.Request('comments.php', {method:'post', 
			                          postBody:'action=betweentimes&time1='+time1+'&time2='+time2,
						  onComplete: parseCommentsTable,
						  onFailure: ohno,
						  asynchronous: true});
}

function SearchComments() {
showLoadingComments();
var query = document.forms.searchCommentsForm.query.value;
$('commentsTitle').innerHTML ="<b>Searching for '"+query+"':</b>";
new Ajax.Request('comments.php', {method:'post', 
			                          postBody:'action=search&query='+escape(query),
						  onComplete: parseCommentsTable,
						  onFailure: ohno,
						  asynchronous: true});
return false;
}

function loadCommentsTable() {
showLoadingComments();
document.forms.searchCommentsForm.query.value='';
$('commentsTitle').innerHTML = "<b>Latest 15 Comments:</b>";
	   new Ajax.Request('comments.php', {method:'post', postBody:'action=getlast15',
						onComplete: parseCommentsTable,
						onFailure: ohno,
						asynchronous: true});
}

function parseCommentsTable (req) { 
	var comments = req.responseXML.getElementsByTagName("comment");
       if (comments.length == 0) { commentError("No comments found. (Are you an administrator?)"); }
	$("commentTable").removeChild($('commLoadingRow'));
	for (var i=0;i<comments.length;i++) {
		var cid = getElementTextNS("cid",comments[i],0);
		var date = getElementTextNS("date",comments[i],0);
		var author = getElementTextNS("author",comments[i],0);
		var body = getElementTextNS("body",comments[i],0);
		var r_id = getElementTextNS("r_id",comments[i],0);
		var r_sid = getElementTextNS("r_sid",comments[i],0);
		var r_subject = getElementTextNS("r_subject",comments[i],0);
		var newRow = Builder.node('tr',{id: 'rowcomm'+cid},
				[Builder.node('td',{id: cid+'commid', className: 'id'},cid),
				 Builder.node('td',{id: cid+'commdate'},date),
				 Builder.node('td',{id: cid+'commauthor'},author),
				 Builder.node('td',{id: cid+'commrsubject'},r_subject),
				 Builder.node('td',{id: cid+'commbody'},body),
				 Builder.node('td',{id: cid+'commedit'},'Edit'),
				 Builder.node('td',{id: cid+'commdel'},'Delete')]);
		$('commentTable').appendChild(newRow);
		$(cid+'commbody').innerHTML = body;
		$(cid+'commrsubject').innerHTML = "<a href=\"javascript:;\" id=\"csubjlink"+cid+"\" onclick=\"javascript:viewPost("+r_sid+","+r_id+")\">"+r_subject+"</a>";
		$(cid+'commedit').innerHTML = "<a href=\"javascript:;\" id=\"ceditlink"+cid+"\" onclick=\"javascript:editCommentForm("+cid+")\">Edit</a>";
		$(cid+'commdel').innerHTML = "<a href=\"javascript:;\" id=\"cdellink"+cid+"\" onclick=\"javascript:deleteComment("+cid+")\">Delete</a>";

		if(window.addEventListener){ // Mozilla, Netscape, Firefox
			$('rowcomm'+cid).addEventListener('mouseover', highlight, false);
			$('rowcomm'+cid).addEventListener('mouseout', lowlight, false);
		} else { // IE
			$('rowcomm'+cid).attachEvent('onmouseover', highlight);
			$('rowcomm'+cid).attachEvent('onmouseout', lowlight);
		}
	}
}

function showLoadingComments() {
       removeRows("commentTable");
	var newRow = Builder.node('tr',{id: 'commLoadingRow'},[Builder.node('td',{id: 'commLoadingText'},'')]);
	$('commentTable').appendChild(newRow);
	$('commLoadingText').colSpan = 5;
	$('commLoadingText').style.backgroundColor = "#fff";
	$('commLoadingText').innerHTML = '<h3><img src=\'images/loading.gif\'> Loading...</h3>';
}

function commentError (msg) {
	removeRows("commentTable");
	var newRow = Builder.node('tr',{},[Builder.node('td',{id: 'commerrorText'},'')]);
	$('commentTable').appendChild(newRow);
	$('commerrorText').colSpan = 7;
	$('commerrorText').innerHTML ="<strong>" + msg + "</strong>";

}

function parseCommentMonths (req) {
	document.forms['monthFormComments'].month.options.length = 0;
	document.forms['monthFormComments'].month.options[0] = new Option("--Choose Month--","none");
	var entries = req.responseXML.getElementsByTagName("entry");
	for (var i=0;i<entries.length;i++) {
		var name = getElementTextNS("name", entries[i], 0);
		var timestampstart = getElementTextNS("timestampstart", entries[i], 0);
		var timestampend = getElementTextNS("timestampend", entries[i], 0);
		document.forms['monthFormComments'].month.options[i+1] = new Option(name,timestampstart+"|"+timestampend);
	}
}

function editCommentForm(cid) {
	smallLoad(cid);
	new Ajax.Request('comments.php', {method:'post', postBody:'action=getbody&cid='+escape(cid),
						onComplete: parseEditCommentForm,
						onFailure: ohno,
						asynchronous: true});

}

function parseEditCommentForm(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var cid = getElementTextNS("cid",info[0],0);
	var body = convertHTML(getElementTextNS("body",info[0],0));
	var rows = textSize(body);
	$(cid+'commedit').innerHTML = "<a href=\"javascript:;\" id=\"ceditlink"+cid+"\" onclick=\"javascript:saveComment("+cid+")\">Save</a>";
	$(cid+'commbody').innerHTML = "<textarea rows=\""+rows+"\" cols=\"30\" id=\"commbodytext"+cid+"\">"+body+"</textarea>";
	$(cid+'commauthor').innerHTML = "<input type=\"text\" size=\"10\" id=\"commauthortext"+cid+"\" value=\""+$(cid+'commauthor').innerHTML+"\">";
}

function saveComment(cid) {

	var body = $('commbodytext'+cid).value;
	var author = $('commauthortext'+cid).value;
	smallLoad(cid);
	new Ajax.Request('comments.php', {method:'post', postBody:'action=edit&body='+escape(body)+'&cid='+escape(cid)+'&author='+escape(author),
						onComplete: parseSaveComment,
						onFailure: ohno,
						asynchronous: true});
}


function parseSaveComment(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var body = getElementTextNS("body",info[0],0);
	var author = getElementTextNS("author",info[0],0);
	var cid = getElementTextNS("cid",info[0],0);
	$(cid+'commbody').innerHTML = body;
	$(cid+'commauthor').innerHTML = author;
	$(cid+'commedit').innerHTML = "<a href=\"javascript:;\" id=\"ceditlink"+cid+"\" onclick=\"javascript:editCommentForm("+cid+")\">Edit</a>";
}

function deleteComment(cid) {
var agree=confirm("Are you sure you wish to delete this comment?");
if (agree)
	doDeleteComment(cid);
else
	return false;
}

function doDeleteComment(cid) {
	   new Ajax.Request('comments.php', {method:'post', postBody:'action=delete&cid='+escape(cid),
						onComplete: parseDeleteComment,
						onFailure: ohno,
						asynchronous: true});
}

function parseDeleteComment(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var errnum = getElementTextNS("errornum",info[0],0);
	var cid = getElementTextNS("cid",info[0],0);
	newsAndCommentsForm();
}

function smallLoad(cid) {
	$(cid+'commedit').innerHTML = "<img src=\"images/loadingsmall.gif\" border=\"0\"> Wait..";
}