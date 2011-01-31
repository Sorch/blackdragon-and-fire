// 
// 
// See index.php for full license.

function importForm() {
	var d = $('content');
	var output = "<h1 id=\"importTitle\" class=\"T\"><b>Import from WordPress:</b></h1><br />You can find these values in your wp-config.php. Only attempt to import from 2.0 or higher.<br/><div id=\"error\"></div><form name=\"importF\" onSubmit=\"javascript:return Import();\"><h3 class=\"T\">";
	var items = ["DB_NAME","DB_USER","DB_PASSWORD","DB_HOST","DB_PREFIX"];
	
	for(var i=0;i<items.length;i++) {
	var curitem = items[i];
	if (curitem == "DB_PASSWORD") {
	var type = "password";
	} else {
	var type = "text";
	}
	if (curitem == "DB_HOST") {
	var value = "localhost";
	} else if (curitem == "DB_PREFIX") {
	var value = "wp_";
	} else {
	var value = "";
	}
	output += "<label for=\""+curitem+"\">"+curitem+":</label><input type=\""+type+"\" name=\""+curitem+"\" value=\""+value+"\" tabindex=\""+i+1+"\" style=\"width: 200px\" maxlength=\"150\"><br />";
	}
		d.innerHTML =  output + "<br /><input type=\"submit\" value=\"Import »\"><br /><br /></h3><input type=\"hidden\" name=\"action\" value=\"import\"></form>";
}


function gen_db_info(req) {
	var info = req.responseXML.getElementsByTagName("info");
	var DB_NAME = getElementTextNS("DB_NAME",info[0],0);
	var DB_USER = getElementTextNS("DB_USER",info[0],0);
	var DB_PASSWORD = getElementTextNS("DB_PASSWORD",info[0],0);
	var DB_HOST = getElementTextNS("DB_HOST",info[0],0);
	var DB_PREFIX = getElementTextNS("DB_PREFIX",info[0],0);
	return "<input type=\"hidden\" name=\"DB_NAME\" value=\""+DB_NAME+"\"><input type=\"hidden\" name=\"DB_USER\" value=\""+DB_USER+"\"><input type=\"hidden\" name=\"DB_PASSWORD\" value=\""+DB_PASSWORD+"\"><input type=\"hidden\" name=\"DB_HOST\" value=\""+DB_HOST+"\"><input type=\"hidden\" name=\"DB_PREFIX\" value=\""+DB_PREFIX+"\">";
}

function Import() {
	var f = document.forms['importF'];
	savingForm();
	new Ajax.Request('import.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseImport,
						onFailure: ohno,
						asynchronous: true});
	return false;
}



function parseImport(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		var d = $('content');
		d.innerHTML = "<h1 id=\"importTitle\" class=\"T\"><b>Import Users:</b></h1>Because of incompatabilities in encryption between WordPress and AJAXPress, you will need to redefine the passwords for the following users: <br /><div id=\"error\"></div><form name=\"importF\" onSubmit=\"javascript:return ImportUsers();\"><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Username</th><th scope=\"col\">New Password</th><th scope=\"col\">Display Name</th><th scope=\"col\">Type</th></tr></THEAD><TBODY id=\"usersTable\"></TBODY></table><input type=\"submit\" value=\"Continue »\"><br /><br /><input type=\"hidden\" name=\"action\" value=\"doimportusers\">"+gen_db_info(req)+"</form>";
		var users = req.responseXML.getElementsByTagName("user");
			for(var i=0;i<users.length;i++) {
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
										 Builder.node('td',{id: uid+'password'},''),
										 Builder.node('td',{id: uid+'name'},dname),
										 Builder.node('td',{id: uid+'administrator'},level)]);
				$('usersTable').appendChild(newRow);
				$(uid+'uid').innerHTML = uid + "<input type=\"hidden\" name=\"uids[]\" value=\""+uid+"\">";
				$(uid+'uname').innerHTML = "<input type=\"text\" size=\"10\" style=\"width: 100%\" name=\"unametext"+uid+"\" value=\""+$(uid+'uname').innerHTML+"\">";
				$(uid+'name').innerHTML = "<input type=\"text\" size=\"10\" style=\"width: 100%\" name=\"nametext"+uid+"\" value=\""+$(uid+'name').innerHTML+"\">";
				if ($(uid+'administrator').innerHTML == "Administrator") {
					var adminsel = "SELECTED";
					var regsel = "";
				} else {
					var adminsel = "";	
					var regsel = "SELECTED";
				}
				$(uid+'administrator').innerHTML = "<select name=\"administratortext"+uid+"\" size=\"1\"><option value=\"0\" "+regsel+">Regular User</option><option value=\"1\" "+adminsel+">Administrator</option></select>";
				$(uid+'password').innerHTML = "<input type=\"password\" style=\"width: 100%\" name=\"passwordtext"+uid+"\" value=\"\">";
			}

	}
}

function ImportUsers() {
	var f = document.forms['importF'];
	savingForm();
	new Ajax.Request('import.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseImportUsers,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function parseImportUsers(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		var imported = req.responseXML.getElementsByTagName("imported");
		var numimported = getElementTextNS("numimported",imported[0],0);
		var numtoimport = getElementTextNS("numtoimport",imported[0],0);
		var cat = getElementTextNS("cat",imported[0],0);
		$('content').innerHTML = "<h1>Imported " + numimported + " users and " + cat + " categories successfully!</h1><br />I will now attempt to import " + numtoimport + " posts, 100 at a time. <br /> <br /><div id=\"error\"></div><form name=\"importF\" onSubmit=\"javascript:return ImportPosts(0);\"><input type=\"submit\" name=\"sub\" value=\"Continue »\"><input type=\"hidden\" name=\"action\" value=\"importposts\">"+gen_db_info(req)+"</form>";
	}
}

function ImportPosts(start) {
	var f = document.forms['importF'];
	f.sub.disabled = true;
	savingForm();
	new Ajax.Request('import.php', {method:'post', postBody:Form.serialize(f)+"&start="+escape(start),
						onComplete: parseImportPosts,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function parseImportPosts(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		var imported = req.responseXML.getElementsByTagName("imported");
		var num = getElementTextNS("num",imported[0],0);
		var start = getElementTextNS("start",imported[0],0);
		var done = getElementTextNS("done",imported[0],0);
		if (done == 1) {
			$('content').innerHTML = "<h1>I imported everything successfully!</h1>";
		} else {
			$('error').className="success";
			$('error').innerHTML ="Imported 100 posts successfully! Importing 100 more in one second...";
			
			setTimeout("ImportPosts("+parseInt(parseInt(start)+100)+")",1000);
		}
	}
}