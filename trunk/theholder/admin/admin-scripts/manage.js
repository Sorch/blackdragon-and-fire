//
// 
// See index.php for full license.

function manageForm() {
	newsAndCommentsForm();
	showSubManageLinks();
}

function showSubManageLinks() {
gen_side();
$("Manage").onclick=null;
$("Manage").appendChild(Builder.node('div',{onclick: 'javascript:;',className: 'smlinks',id: 'ManageSub'},''));
$("ManageSub").innerHTML = " &raquo; <a href=\"javascript:;\" onClick=\"javascript:newsAndCommentsForm()\">News/Comments</a><br /> &raquo; <a href=\"javascript:;\" onClick=\"javascript:usersForm()\">Users</a><br /> &raquo; <a href=\"javascript:;\" onClick=\"javascript:categoriesForm()\">Categories</a><br /> &raquo; <a href=\"javascript:;\" onClick=\"javascript:widgetsForm()\">Widgets</a>";
}

function newsAndCommentsForm() {
	$('content').innerHTML = "<h1 id=\"postTitle\" class=\"T\"><b>Manage Posts:</b></h1><div class=\"alignleft\">Search Posts: <form method=\"POST\" onSubmit=\"javascript:return SearchPosts();\" name=\"searchPostsForm\"><input type=\"text\" size=\"10\" name=\"query\"><input type=\"submit\" value=\"Search\" class=\"shortButton\"></form><input type=\"button\" onClick=\"javascript:resetAll()\" value=\"Reset\" class=\"shortButton\"></div><div class=\"alignright\">Browse Month: <form name=\"monthForm\" method=\"POST\" onSubmit=\"javascript:return gotoMonth();\"><select id=\"month\" name=\"month\" onChange=\"javascript:gotoMonth()\"><option value=\"0\">Loading...</option></select></form></div><br><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Date</th><th scope=\"col\">Subject</th><th scope=\"col\">Category</th><th scope=\"col\">Comments</th><th scope=\"col\">Author</th><th scope=\"col\"></th><th scope=\"col\"></th><th scope=\"col\"></th></tr></THEAD><TBODY id=\"postTable\"></TBODY></table><h3 id=\"commentsTitle\" class=\"T\"><b>Manage Comments:</b></h3><div class=\"alignleft\">Search Comments: <form method=\"POST\" onSubmit=\"javascript:return SearchComments();\" name=\"searchCommentsForm\"><input type=\"text\" size=\"10\" name=\"query\"><input type=\"submit\" value=\"Search\" class=\"shortButton\"></form><input type=\"button\" onClick=\"javascript:resetAllComments()\" value=\"Reset\" class=\"shortButton\"></div><div class=\"alignright\">Browse Month: <form name=\"monthFormComments\" method=\"POST\" onSubmit=\"javascript:return gotoMonthComments();\"><select id=\"commentmonth\" name=\"month\" onChange=\"javascript:gotoMonthComments()\"><option value=\"0\">Loading...</option></select></form></div><br><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Date</th><th scope=\"col\">Author</th><th scope=\"col\">Response To</th><th scope=\"col\">Body</th><th scope=\"col\"></th><th scope=\"col\"></th></tr></THEAD><TBODY id=\"commentTable\"></TBODY></table>";
	loadPostsTable();
	loadMonths();
	loadCommentsTable();
	loadCommentMonths();
}

function highlight(evt){
lightRow('#fffada',evt);
}

function lowlight(evt){
lightRow('#f1f1f1',evt);
}

function lightRow(color,evt) {
	var elem = (evt.target) ? evt.target : evt.srcElement;
	var id = elem.id;
	while(id.substring(0,3) != 'row') {
	id = $(id).parentNode.id;
	}
		$(id).style.backgroundColor=color;	
}

function redlight(what) {
$(what).style.backgroundColor='#FACED0';
}

function removeRows(what) {
	what = $(what);
	var num = what.rows.length;
	for (var i=0;i<num;i++) {
		what.removeChild(what.rows[0]);
	}
}

function removeRow(which,table) {
	$(table+'Table').removeChild($(which));
	if ($(table+'Table').rows.length == 0) {
		if (table == 'post') {
			postError("No Posts Found.");
		} else {
			commentError("No Comments Found.");
		}
	}
}

function clearSelect(what) {
	what = $(what);
	var num = what.options.length;
	for (var i=0;i<num;i++) {
		what.removeChild(what.options[0]);
	}
}