// 
// 
// See index.php for full license.

function showLoginVerify(xmlresp) {
try {
var E = $('error');
E.className="";
} catch (e) {
var E = $('content');
}

E.innerHTML = "<h2><img src=\"images/loading.gif\"> Verifying...</h2>";
}

function login(uname,pass) {
	pBody = "uname="+escape(uname)+"&pass="+escape(pass);
	showLoginVerify();
	   new Ajax.Request('login.php', {method:'post', postBody:pBody,
						onComplete: parseLogin,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function parseLogin(req) {
	var d = $('content');
	var info = req.responseXML.getElementsByTagName("info");
	var error = getElementTextNS("error", info[0], 0);
	if (error != "n/a") {
		gen_login_form();
		pretty_error([error]);
	} else {
		var uname = getElementTextNS("uname", info[0], 0);
		var pass = getElementTextNS("pass", info[0], 0);
		setCookie(dbprefix+'uname',uname,0);
		setCookie(dbprefix+'pass',pass,0);
		if (getCookie(dbprefix+'uname') == '') {
			gen_login_form();
			pretty_error(["Cookies must be enabled!"]);
		} else{ 
			adminIndex();
		}
	}
}



function gen_login_form() {
	var d = $('content');
	$('sidebar').innerHTML = "&raquo; <a href=\"javascript:;\">Login</a>";
	d.innerHTML = "<h1>Identification Required</h1><P>In order to continue, you must identify yourself using your username and password:<div id=\"error\"></div><form name=\"loginForm\" onSubmit=\"javascript:return login(document.loginForm.uname.value,document.loginForm.pass.value);\"><h3 class=\"T\"><label for=\"uname\">Username:</label><input type=\"text\" name=\"uname\" value=\"\" tabindex=\"1\" size=\"20\"><br /><label for=\"pass\">Password:</label><input type=\"password\" name=\"pass\" value=\"\" tabindex=\"2\" size=\"20\"><br /><input type=\"submit\" value=\"Login »\"></h3></form></P>";
}

function logout() {
deleteCookie(dbprefix+'uname');
deleteCookie(dbprefix+'pass');
gen_login_form();

}

function init_login() {
		var uname = getCookie(dbprefix+'uname');
		if (uname != '') {
			// we are possibly logged in
			login(uname,getCookie(dbprefix+'pass'));
		} else {
			gen_login_form();
		}
}
