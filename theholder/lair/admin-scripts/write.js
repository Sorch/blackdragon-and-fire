// 
// 
// See index.php for full license.

function writeForm(error) {
	gen_side();
	if (error) {
		pretty_error(error);
	} else {
		var d = $('content');
		d.innerHTML = "<h1 id=\"categoriesTitle\" class=\"T\"><b>Write:</b></h1><form name=\"postF\" onSubmit=\"javascript:return Write();\"><h3 class=\"T\"><label for=\"subject\">Subject:</label><input type=\"text\" name=\"subject\" tabindex=\"1\" size=\"20\"><br /><label for=\"category\">Category:</label><select name=\"category\" tabindex=\"2\"><option value=\"0\">Loading...</option></select><br /><label for=\"body\">Body:</label><textarea rows=\"15\" style=\"width: 90%\" name=\"body\" tabindex=\"3\"></textarea><br /><br /><label for=\"excerpt\">Excerpt:</label><textarea rows=\"10\" style=\"width: 90%\" name=\"excerpt\" tabindex=\"4\"></textarea><br /><br /><input type=\"checkbox\" name=\"allowcomments\" class=\"checkBox\"value=\"1\" CHECKED><span class=\"checkText\">Allow Comments?</span><br /><br /><input type=\"submit\" value=\"Write »\"><br /><br /></h3><input type=\"hidden\" name=\"action\" value=\"write\"></form><div id=\"error\"></div>";
		getCategories(-1);
	}
}


function getCategories(sel) {
	   new Ajax.Request('categories.php', {method:'post', postBody:'action=get_write_categories&selected='+sel,
						onComplete: parsegetCategories,
						onFailure: ohno,
						asynchronous: true});
}

function parsegetCategories(req) {
document.forms['postF'].category.options.length = 0;
var categories = req.responseXML.getElementsByTagName("category");
	for (var i=0;i<categories.length;i++) {
	var name = getElementTextNS("name", categories[i], 0);
	var sid = getElementTextNS("sid", categories[i], 0);
	var selected = getElementTextNS("selected", categories[i], 0);
	document.forms['postF'].category.options[i] = new Option(name,sid);
		if (selected == 1) {
			document.forms['postF'].category.options[i].selected = true;
		}
	}
}

function parseWrite(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		writeForm(errors);
	} else {
		var id = getElementTextNS("id",req.responseXML,0);
		var sid = getElementTextNS("sid",req.responseXML,0);
		document.forms['postF'].subject.value = '';
		document.forms['postF'].body.value = '';
		document.forms['postF'].excerpt.value = '';
		$('error').className="success";
		$('error').innerHTML ="Post published successfully! You may view it by clicking <a target=\"_new\" href=\""+blogurl+"#"+sid+","+id+"\">here</a>.";
	}
}

function Write() {
	var f = document.forms['postF'];
	savingForm();
	   new Ajax.Request('news.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseWrite,
						onFailure: ohno,
						asynchronous: true});
	return false;
}

function savingForm() {
var E = $('error');
E.className="";
E.innerHTML = "<h2><img src=\"images/loading.gif\"> Saving...</h2>";
}
