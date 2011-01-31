// 
// 
// See index.php for full license.

function resetAll() {
loadPostsTable();
loadMonths();
}

function loadMonths() {
clearSelect("month");
           new Ajax.Request('formfunctions.php', {method:'post', 
			                          postBody:'action=getmonths',
						  onComplete: parseMonths,
						  onFailure: ohno,
						  asynchronous: true});
			
}

function gotoMonth() {
var sel = document.forms['monthForm'].month.options[document.forms['monthForm'].month.selectedIndex].value;
	if (sel != "none") {
		if (document.forms['monthForm'].month.options[0].value == "none") {
			document.forms['monthForm'].month.removeChild(document.forms['monthForm'].month.options[0]);
		}
		var name = document.forms['monthForm'].month.options[document.forms['monthForm'].month.selectedIndex].text;
		$('postTitle').innerHTML = "<b>Posts from "+name+":";
		var time1 = sel.substring(0,10);
		var time2 = sel.substring(11);
		getBetweenTimes(time1,time2);
		return false;
	}
}


function getBetweenTimes (time1,time2) {
showLoadingPosts();
          new Ajax.Request('news.php', {method:'post', 
			                          postBody:'action=betweentimes&time1='+time1+'&time2='+time2,
						  onComplete: parsePostsTable,
						  onFailure: ohno,
						  asynchronous: true});
}



function parseMonths(req) {
	document.forms['monthForm'].month.options.length = 0;
	document.forms['monthForm'].month.options[0] = new Option("--Choose Month--","none");
	var entries = req.responseXML.getElementsByTagName("entry");
	for (var i=0;i<entries.length;i++) {
		var name = getElementTextNS("name", entries[i], 0);
		var timestampstart = getElementTextNS("timestampstart", entries[i], 0);
		var timestampend = getElementTextNS("timestampend", entries[i], 0);
		document.forms['monthForm'].month.options[i+1] = new Option(name,timestampstart+"|"+timestampend);
	}
}



function SearchPosts() {
	showLoadingPosts();
	var query = document.forms.searchPostsForm.query.value;
	$('postTitle').innerHTML ="<b>Searching for '"+query+"':</b>";
	
	
	new Ajax.Request('news.php', {method:'post', 
				                          postBody:'action=search&query='+escape(query),
							  onComplete: parsePostsTable,
							  onFailure: ohno,
							  asynchronous: true});
	return false;
}



function loadPostsTable() {
showLoadingPosts();
document.forms.searchPostsForm.query.value='';
$('postTitle').innerHTML = "<b>Latest 15 Posts:</b>";
	   new Ajax.Request('news.php', {method:'post', postBody:'action=getlast15',
						onComplete: parsePostsTable,
						onFailure: ohno,
						asynchronous: true});
}

function parsePostsTable (req) { 
	var posts = req.responseXML.getElementsByTagName("post");
        if (posts.length == 0) { postError("No posts found."); }
	var subject, body, date, sid, category, id, comments, uname, uid;
	$("postTable").removeChild($('LoadingRow'));
	for (var i=0;i<posts.length;i++) {
		subject = getElementTextNS("subject",posts[i],0);
		date = getElementTextNS("date",posts[i],0);
		category = getElementTextNS("category",posts[i],0);
		sid = getElementTextNS("sid",posts[i],0);
		id = getElementTextNS("id",posts[i],0);
		uid = getElementTextNS("uid",posts[i],0);
		name = getElementTextNS("name",posts[i],0);
		comments = getElementTextNS("comments",posts[i],0);
		var newRow = Builder.node('tr',{id: 'row'+id},
				[Builder.node('td',{id: id+'id', className: 'id'},id),
				 Builder.node('td',{id: id+'date'},date),
				 Builder.node('td',{id: id+'subj'},subject),
				 Builder.node('td',{id: id+'cat'},category),
				 Builder.node('td',{id: id+'comm'},comments),
				 Builder.node('td',{id: id+'name'},name),
				 Builder.node('td',{id: id+'view'},'View'),
				 Builder.node('td',{id: id+'edit'},'Edit'),
				 Builder.node('td',{id: id+'del'},'Delete')]);
		$('postTable').appendChild(newRow);

		$(id+'cat').innerHTML = "<a href=\"javascript:;\" id=\"catlink"+id+"\" onclick=\"javascript:viewCategory("+sid+");\">"+category+"</a>";
		$(id+'view').innerHTML = "<a href=\"javascript:;\" id=\"viewlink"+id+"\" onclick=\"javascript:viewPost("+sid+","+id+")\">View</a>";
		$(id+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+id+"\" onclick=\"javascript:PostEditForm("+id+")\">Edit</a>";
		$(id+'del').innerHTML = "<a href=\"javascript:;\" id=\"dellink"+id+"\" onclick=\"javascript:deletePost("+uid+","+id+")\">Delete</a>";
		if(window.addEventListener){ // Mozilla, Netscape, Firefox
			$('row'+id).addEventListener('mouseover', highlight, false);
			$('row'+id).addEventListener('mouseout', lowlight, false);
		} else { // IE
			$('row'+id).attachEvent('onmouseover', highlight);
			$('row'+id).attachEvent('onmouseout', lowlight);
		}

	}
} 

function viewPost(sid,id) {
	window.open(blogurl+"#"+sid+","+id,"popup");
}

function viewCategory(sid) {
	window.open(blogurl+"#"+sid+",0","popup");
}

function showLoadingPosts() {
       removeRows("postTable");
	var newRow = Builder.node('tr',{id: 'LoadingRow'},[Builder.node('td',{id: 'LoadingText'},'')]);
	$('postTable').appendChild(newRow);
	$('LoadingText').colSpan = 9;
	$('LoadingText').style.backgroundColor = "#fff";
	$('LoadingText').innerHTML = '<h3><img src=\'images/loading.gif\'> Loading...</h3>';
}

function postError (msg) {
	removeRows("postTable");
	var newRow = Builder.node('tr',{},[Builder.node('td',{id: 'errorText'},'')]);
	$('postTable').appendChild(newRow);
	$('errorText').colSpan = 9;
	$('errorText').innerHTML ="<strong>" + msg + "</strong>";

}

function PostEditForm(id) {
	showLoadingBody();
	new Ajax.Request('news.php', {method:'post', postBody:'action=getpost&id='+id,
						onComplete: parsePostEditForm,
						onFailure: ohno,
						asynchronous: true});
}

function parsePostEditForm(req) {
	var post = req.responseXML.getElementsByTagName("post")[0];
	var subject = getElementTextNS("subject",post,0);
	var date = getElementTextNS("date",post,0);
	var category = getElementTextNS("category",post,0);
	var sid = getElementTextNS("sid",post,0);
	var id = getElementTextNS("id",post,0);
	var uid = getElementTextNS("uid",post,0);
	var uname = getElementTextNS("uname",post,0);
	var body = getElementTextNS("body",post,0);
	var allowcomments = getElementTextNS("allowcomments",post,0);
	var excerpt = getElementTextNS("excerpt",post,0);
	if (allowcomments == 1) {
		var allow ="CHECKED";
	} else {
		var allow = "";
	}
	var d = $('content');
	d.innerHTML = "<a href=\"javascript:;\" onclick=\"javascript:manageForm()\">&laquo; Back to Management</a><br /><br /><form name=\"postF\" onSubmit=\"javascript:return EditPost();\"><h3 class=\"T\"><label for=\"subject\">Subject:</label><input type=\"text\" name=\"subject\" tabindex=\"1\" size=\"20\" value=\""+subject+"\"><br /><label for=\"category\">Category:</label><select name=\"category\" tabindex=\"2\"><option value=\"0\">Loading...</option></select><br /><label for=\"body\">Body:</label><textarea rows=\"15\" style=\"width: 90%\" name=\"body\" tabindex=\"3\">"+body+"</textarea><br /><br /><label for=\"excerpt\">Excerpt:</label><textarea rows=\"10\" style=\"width: 90%\" name=\"excerpt\" tabindex=\"4\">"+excerpt+"</textarea><br /><br /><input type=\"checkbox\" name=\"allowcomments\" class=\"checkBox\" value=\"1\""+allow+"><span class=\"checkText\">Allow Comments?</span><br /><br /><input type=\"submit\" value=\"Edit »\"><br /><br /></h3><input type=\"hidden\" name=\"uid\" value=\""+uid+"\"><input type=\"hidden\" name=\"id\" value=\""+id+"\"><input type=\"hidden\" name=\"action\" value=\"edit\"></form><div id=\"error\"></div>";
	getCategories(sid);
}

function parsePostEdit(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		var id = getElementTextNS("id",req.responseXML,0);
		var sid = getElementTextNS("sid",req.responseXML,0);
		$('error').className="success";
		$('error').innerHTML ="Post published successfully! You may view it by clicking <a href=\""+blogurl+"#"+sid+","+id+"\" target=\"_new\">here</a>.";
	}
}


function EditPost() {
	var f = document.forms['postF'];
	savingForm();
	   new Ajax.Request('news.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parsePostEdit,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function deletePost(uid,id) {
var agree=confirm("Are you sure you wish to delete this post? All of its comments will be deleted as well!\n\nThis action cannot be undone.");
if (agree)
	doDeletePost(uid,id);
else
	return false;
}

function doDeletePost(uid,id) {
	   new Ajax.Request('news.php', {method:'post', postBody:'action=delete&id='+escape(id)+'&uid='+escape(uid),
						onComplete: parseDeletePost,
						onFailure: ohno,
						asynchronous: true});
}

function parseDeletePost(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	var id = getElementTextNS("id",req.responseXML,0);
	newsAndCommentsForm();
}
