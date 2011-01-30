// 
// 
// See index.php for full license.

function usersForm(errors) {
	$('content').innerHTML = "<h1 id=\"usersTitle\" class=\"T\"><b>Manage Users:</b></h1><br><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Username</th><th scope=\"col\">New Password</th><th scope=\"col\">Display Name</th><th scope=\"col\">Type</th><th scope=\"col\"></th><th scope=\"col\"></th></tr></THEAD><TBODY id=\"usersTable\"></TBODY></table><br><h1 id=\"usersTitle\" class=\"T\"><b>Create User:</b></h1><div id=\"error\"></div><form name=\"userF\" onSubmit=\"javascript:return CreateUser();\"><h3 class=\"T\"><label for=\"uname\">Username:</label><input type=\"text\" name=\"uname\" tabindex=\"1\" size=\"20\"><br /><label for=\"name\">Real&nbsp;Name:</label><input type=\"text\" name=\"name\" tabindex=\"2\" size=\"20\"><br /><label for=\"password\">Password:</label><input type=\"password\" name=\"password\" tabindex=\"3\" size=\"20\"><br /><label for=\"password2\">Repeat:</label><input type=\"password\" name=\"password2\" tabindex=\"3\" size=\"20\"><br /><label for=\"administrator\">Access:</label><select name=\"administrator\" tabindex=\"4\"><option value=\"0\">Regular User</option><option value=\"1\">Administrator</option></select><br /><br /><br /><input type=\"submit\" value=\"Create User »\"><br /><br /></h3><input type=\"hidden\" name=\"action\" value=\"create\"></form>";
	loadUsersTable();
}

function loadUsersTable() {
	showLoadingUsers();
	new Ajax.Request('users.php', {method:'post', postBody:'action=getusers',
						onComplete: parseUsersTable,
						onFailure: ohno,
						asynchronous: true});

}



function parseUsersTable(req) {
	var users = req.responseXML.getElementsByTagName("user");

	$("usersTable").removeChild($('LoadingRow'));
	for (var i=0;i<users.length;i++) {
		var uid = getElementTextNS("uid",users[i],0);
		var uname = getElementTextNS("uname",users[i],0);
		var dname = getElementTextNS("name",users[i],0);
		var administrator = getElementTextNS("administrator",users[i],0);
		if (administrator == 0) {
		var level = "Regular User";
		} else {
		var level = "Administrator";
		}
		var newRow = Builder.node('tr',{id: 'row'+uid},
				[Builder.node('td',{id: uid+'uid', className: 'id'},uid),
				 Builder.node('td',{id: uid+'uname'},uname),
				 Builder.node('td',{id: uid+'password'},'*'),
				 Builder.node('td',{id: uid+'name'},dname),
				 Builder.node('td',{id: uid+'administrator'},level),
				 Builder.node('td',{id: uid+'edit'},'Edit'),
				 Builder.node('td',{id: uid+'del'},'Delete')]);
		$('usersTable').appendChild(newRow);
		$(uid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+uid+"\" onclick=\"javascript:editUserForm("+uid+")\">Edit</a>";
		if (uname == getCookie(dbprefix+"uname")) {
			$(uid+'del').style.backgroundColor = "#ffffff";
			$(uid+'del').innerHTML = "";
		} else {
			$(uid+'del').innerHTML = "<a href=\"javascript:;\" id=\"dellink"+uid+"\" onclick=\"javascript:deleteUser("+uid+")\">Delete</a>";
		}
		
		if(window.addEventListener){ // Mozilla, Netscape, Firefox
			$('row'+uid).addEventListener('mouseover', highlight, false);
			$('row'+uid).addEventListener('mouseout', lowlight, false);
		} else { // IE
			$('row'+uid).attachEvent('onmouseover', highlight);
			$('row'+uid).attachEvent('onmouseout', lowlight);
		}
	}
}

function editUserForm(uid) {
	$(uid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+uid+"\" onclick=\"javascript:saveEditUser("+uid+")\">Save</a>";
	$(uid+'uname').innerHTML = "<input type=\"text\" size=\"10\" id=\"unametext"+uid+"\" value=\""+$(uid+'uname').innerHTML+"\">";
	$(uid+'name').innerHTML = "<input type=\"text\" size=\"10\" id=\"nametext"+uid+"\" value=\""+$(uid+'name').innerHTML+"\">";
	if ($(uid+'administrator').innerHTML == "Administrator") {
		var adminsel = "SELECTED";
		var regsel = "";
	} else {
		var adminsel = "";	
		var regsel = "SELECTED";
	}
	$(uid+'administrator').innerHTML = "<select id=\"administratortext"+uid+"\" size=\"1\"><option value=\"0\" "+regsel+">Regular User</option><option value=\"1\" "+adminsel+">Administrator</option></select>";
	$(uid+'password').innerHTML = "<input type=\"password\" id=\"passwordtext"+uid+"\" value=\"\">";
	
}

function saveEditUser(uid) {
	var uname = $('unametext'+uid).value;
	var name = $('nametext'+uid).value;
	var password = $('passwordtext'+uid).value;	
	var administrator = $('administratortext'+uid).options[$('administratortext'+uid).selectedIndex].value;
    smallUserLoad(uid);
    new Ajax.Request('users.php', {method:'post', postBody:'action=edit&uid='+escape(uid)+'&uname='+escape(uname)+'&name='+escape(name)+'&password='+escape(password)+'&administrator='+escape(administrator),
						onComplete: parseEditUser,
						onFailure: ohno,
						asynchronous: true});
    
	return false;
}

function parseEditUser(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var errnum = getElementTextNS("errornum",info[0],0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		var setcookies =getElementTextNS("setcookies",info[0],0);
		var uname =getElementTextNS("uname",info[0],0);
		var password = getElementTextNS("password",info[0],0);
                if (setcookies == "1") {
			if (getCookie(dbprefix+"uname") != uname) {
				setCookie(dbprefix+"uname",uname);
			}
			if (password != "") { // set the new pass cookie
				setCookie(dbprefix+"pass",password,0);
			}
		}
		usersForm();
	}
}


function CreateUser() {
	var f = document.forms['userF'];
	savingForm();
	new Ajax.Request('users.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseCreateUser,
						onFailure: ohno,
						asynchronous: true});
	return false;
}


function parseCreateUser(req) {
	var errnum = getElementTextNS("errornum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		// just refresh the user form and the new user should appear in it :)
		usersForm();
	}
}


function deleteUser(uid) {
var agree=confirm("Are you sure you wish to delete this user? All of his posts will be deleted as well!!\n\nThis action cannot be undone!");
if (agree)
	doDeleteUser(uid);
else
	return false;
}

function doDeleteUser(uid) {
	   new Ajax.Request('users.php', {method:'post', postBody:'action=delete&uid='+escape(uid),
						onComplete: parseDeleteUser,
						onFailure: ohno,
						asynchronous: true});
}

function parseDeleteUser(req) {
	var errnum = getElementTextNS("errornum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		// just refresh the user form and the new user should appear in it :)
		usersForm();
	}
}


function showLoadingUsers() {
    removeRows("usersTable");
	var newRow = Builder.node('tr',{id: 'LoadingRow'},[Builder.node('td',{id: 'LoadingText'},'')]);
	$('usersTable').appendChild(newRow);
	$('LoadingText').colSpan = 9;
	$('LoadingText').style.backgroundColor = "#fff";
	$('LoadingText').innerHTML = '<h3><img src=\'images/loading.gif\'> Loading...</h3>';
}
function smallUserLoad(uid) {
	$(uid+'edit').innerHTML = "<img src=\"images/loadingsmall.gif\" border=\"0\"> Wait..";
}
