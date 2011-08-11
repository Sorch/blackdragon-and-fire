// 
// 
// See index.php for full license.

function adminIndex() {
gen_side();
$('content').innerHTML = "<h3>You are logged in as: " + getCookie(dbprefix+"uname") + "</h3>";
}

function gen_side() {
var d = $('sidebar');
d.innerHTML = "<ul>";
var items = [	["View Blog","javascript:viewCategory(0)"],
		["Logout","javascript:logout();"],
		["Write","javascript:writeForm('');"],
		["Manage","javascript:manageForm();"],
		["Presentation","javascript:presentationForm();"],
		["Options","javascript:optionsForm();"],
		["Import","javascript:importForm();"]
	    ];

var alt = '';
for(var i=0;i<items.length;i++) {
	d.innerHTML = d.innerHTML + "<li id=\""+items[i][0]+"\" class=\"itemli "+alt+"\" onmouseover=\"javascript:this.className='itemli hover'\" onmouseout=\"javascript:this.className='itemli "+alt+"'\" onclick=\""+items[i][1]+"\">» <a href=\"javascript:;\" onclick=\""+items[i][1]+"\">"+items[i][0]+"</a></li>";
	if (alt == '') {
		alt = 'alt';
	} else {
		alt = '';
	}
}

d.innerHTML = d.innerHTML + "</ul>";
}