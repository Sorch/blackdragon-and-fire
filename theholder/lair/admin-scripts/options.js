// 
// 
// See index.php for full license.

function optionsForm() {
	gen_side();
	var d = $('content');
	var output = "<h1 id=\"optionsTitle\" class=\"T\"><b>Options:</b></h1><div id=\"error\"></div><form name=\"optionsF\" onSubmit=\"javascript:return saveOptions();\"><h3 class=\"T\">";
	var items = ["BlogName","BlogDescription","BlogURL","BlogPath","ScriptPath","ThemePath","AbsolutePath","DefaultSID"];
	
	for(var i=0;i<items.length;i++) {
	var curitem = items[i];
	var lccuritem = curitem.toLowerCase();
	output += "<label for=\""+lccuritem+"\">"+curitem+":</label><input type=\"text\" name=\""+lccuritem+"\" value=\""+eval(lccuritem)+"\" tabindex=\""+i+"\" style=\"width: 200px\" maxlength=\"150\"><br />";
	}
	
	// activetheme information
	output += "<label for=\"activetheme\">ActiveTheme:</label><select name=\"activetheme\" size=\"1\"><option value=\"-1\">Loading...</option></select><br />";
	
	d.innerHTML =  output + "<br /><input type=\"submit\" value=\"Save Options »\"><br /><br /></h3><input type=\"hidden\" name=\"action\" value=\"save\"></form>";
	getThemes(activetheme);
}

function getThemes(sel) {
	   new Ajax.Request('options.php', {method:'post', postBody:'action=get_themes&selected='+sel,
						onComplete: parsegetThemes,
						onFailure: ohno,
						asynchronous: true});
}

function parsegetThemes(req) {
document.forms['optionsF'].activetheme.options.length = 0;
var themes = req.responseXML.getElementsByTagName("theme");
	for (var i=0;i<themes.length;i++) {
	var name = getElementTextNS("name", themes[i], 0);
	var selected = getElementTextNS("selected", themes[i], 0);
	
	document.forms['optionsF'].activetheme.options[i] = new Option(name);
		if (selected == 1) {
			document.forms['optionsF'].activetheme.options[i].selected = true;
		}
	}
}

function saveOptions() {
	var f = document.forms['optionsF'];
	savingForm();
	new Ajax.Request('options.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseSaveOptions,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function parseSaveOptions(req) {
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
		$('error').innerHTML ="Options saved successfully!";
	}
}