//
// 
// See index.php for full license.

function viewpost(id) {
Loading = 1;

pBody = "id="+id;
showLoading();
   new Ajax.Request('viewpost.php', {method:'post', postBody:pBody,
					onComplete: parseViewpost,
					onFailure: ohno,
					asynchronous: true});
}


	
function parseSingleComment(user,date,url,thebody,id) {
	if (alt == "alt") {
	 alt = "";
	} else {
	 alt = "alt";
	}

	return templateReplace(Comment,['__ALT__','__ID__','__UNAME__','__DATE__','__BODY__'],[alt,id,user,date,thebody]);
}

function parseViewpost(req) {
var d = $('content');
d.innerHTML = "";
alt = "";
post = req.responseXML.getElementsByTagName("post");
info = req.responseXML.getElementsByTagName("info");
var uid = getElementTextNS("uid", info[0], 0);
var sid = getElementTextNS("sid", info[0], 0);
Viewingsid = sid;
var id = getElementTextNS("id", info[0], 0);
Viewingid = id;
// update location
updateHash();

parseSinglePost(post[0],0,1,'content');

var subject = getElementTextNS("subject", post[0], 0);


var comments = req.responseXML.getElementsByTagName("comment");
var allowcomments = getElementTextNS("allowcomments", post[0], 0);
var Comm = "";

	for (var i=0;i<comments.length;i++) {
	var thebody = getElementTextNS("body", comments[i], 0);
	var name = getElementTextNS("uname", comments[i], 0);
	var date = getElementTextNS("date", comments[i], 0);
	var url = getElementTextNS("url", comments[i], 0);
	var cid = getElementTextNS("cid", comments[i], 0);
		Comm += parseSingleComment(name,date,url,thebody,cid);
	}
if (allowcomments == 1) {
d.innerHTML = d.innerHTML + templateReplace(CommentsStart,['__NUM__','__S__','__TITLE__','__COMMENTS__'],['<span id="commentnum">'+comments.length+'</span>',(comments.length == 1)? '' : 's',subject,Comm]);
MakeCommentForm([]);
}

Loading = 0;
}

function addComment() {
	var form = document.commentform;
	var user = form.author.value;
	var comment = form.comment.value;
	var email = form.email.value;
	var url = form.url.value;
	var pBody = "user="+user+"&url="+url+"&comment="+comment+"&id="+Viewingid+"&email="+email;
	form.submit.disabled = true;
	showCommentSaving();
	new Ajax.Request('addcomment.php', {method:'post', postBody:pBody, 
					onSuccess:parseAddComment,
					onFailure: ohno,
					asynchronous: true});
	return false;
}

function parseAddComment (req) {

var err = req.responseXML.getElementsByTagName("errors");
var info = req.responseXML.getElementsByTagName("info");
var errnum = getElementTextNS("errornum",info[0],0);
document.commentform.submit.disabled = false;
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
			MakeCommentForm(errors);
	} else {
			var user = getElementTextNS("user",info[0],0);
			var url = getElementTextNS("url",info[0],0);
			var body = getElementTextNS("body",info[0],0);
			var date = getElementTextNS("date",info[0],0);
			var num = getElementTextNS("num",info[0],0);
			var id = getElementTextNS("id",info[0],0);
			var form = document.commentform;
			form.author.value = "";
			form.comment.value = "";
			form.email.value = "";
			form.url.value = "";
			addPhysicalComment(user,date,url,body,num,id);
	}
}

function addPhysicalComment(user,date,url,thebody,num,id) {
	$('errorPlaceholder').innerHTML = "";
	var Comm = parseSingleComment(user,date,url,thebody,id);
	$('commentlist').innerHTML = $('commentlist').innerHTML + Comm;
	$('commentnum').innerHTML = num;

}

function prettyerr(err) {

var ErrItems = "";
for(var i=0;i<err.length;i++) {
ErrItems += templateReplace(ErrorsItem,['__ERROR__'],[err[i]]);
}
return templateReplace(ErrorsText,['__NUM__','__S__','__ERRORS__'],[err.length,(err.length == 1)? '' : 's',ErrItems]);
}


function MakeCommentForm(errors) {
if (errors.length > 0) {
$("errorPlaceholder").innerHTML = prettyerr(errors);
return false;
} else { 
var ErrTxt = "<span id=\"errorPlaceholder\"></span>";
}

commentAction = "return addComment();";

$('content').innerHTML = $('content').innerHTML + templateReplace(CommentForm,['__ERRORS__','__COMMENTACTION__'],[ErrTxt,commentAction]);
return true;
}

function showCommentSaving(xmlresp) {
var leftI = $('errorPlaceholder');
leftI.innerHTML = templateReplace(LoadingComment,["__THEMEPATH__"],[themepath + activetheme + "/"]);
}