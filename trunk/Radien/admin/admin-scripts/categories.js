// 
// 
// See index.php for full license.

function categoriesForm(errors) {
	$('content').innerHTML = "<h1 id=\"categoriesTitle\" class=\"T\"><b>Manage Categories:</b></h1><br><table width=\"100%\" cellpadding=\"3\" cellspacing=\"3\"><THEAD><tr><th scope=\"col\">ID</th><th scope=\"col\">Category Name</th><th scope=\"col\">Number of Posts</th><th scope=\"col\"></th><th scope=\"col\"></th></tr></THEAD><TBODY id=\"categoriesTable\"></TBODY></table><br><h1 id=\"categoriesCreateTitle\" class=\"T\"><b>Create Category:</b></h1><div id=\"error\"></div><form name=\"catF\" onSubmit=\"javascript:return CreateCategory();\"><h3 class=\"T\"><label for=\"uname\">Category Name:</label><input type=\"text\" name=\"catname\" tabindex=\"1\" size=\"20\"><br /><br /><input type=\"submit\" value=\"Create Category »\"></h3><input type=\"hidden\" name=\"action\" value=\"create\"></form>";
	loadCategoriesTable();
}

function loadCategoriesTable() {
	showLoadingCategories();
	new Ajax.Request('categories.php', {method:'post', postBody:'action=getcategories',
						onComplete: parseCategoriesTable,
						onFailure: ohno,
						asynchronous: true});

}

function parseCategoriesTable(req) {
	var categories = req.responseXML.getElementsByTagName("category");

	$("categoriesTable").removeChild($('LoadingRow'));
    if (categories.length == 0) {
    	categoryError("You need to be an administrator to modify or create categories.");
    }
	for (var i=0;i<categories.length;i++) {
		var sid = getElementTextNS("sid",categories[i],0);
		var catname = getElementTextNS("name",categories[i],0);
		var numposts = getElementTextNS("numposts",categories[i],0);
		var newRow = Builder.node('tr',{id: 'row'+sid},
				[Builder.node('td',{id: sid+'sid', className: 'id'},sid),
				 Builder.node('td',{id: sid+'catname'},catname),
				 Builder.node('td',{id: sid+'numposts'},numposts),
				 Builder.node('td',{id: sid+'edit'},'Edit'),
				 Builder.node('td',{id: sid+'del'},'Delete')]);
		$('categoriesTable').appendChild(newRow);
		$(sid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+sid+"\" onclick=\"javascript:editCategoryForm("+sid+")\">Edit</a>";
		// cannot delete the first category, uncategorized
		if (sid == 1) {
			$(sid+'del').style.backgroundColor = "#ffffff";
			$(sid+'del').innerHTML = "";
		} else {
			$(sid+'del').innerHTML = "<a href=\"javascript:;\" id=\"dellink"+sid+"\" onclick=\"javascript:deleteCategory("+sid+")\">Delete</a>";
		}
		if(window.addEventListener){ // Mozilla, Netscape, Firefox
			$('row'+sid).addEventListener('mouseover', highlight, false);
			$('row'+sid).addEventListener('mouseout', lowlight, false);
		} else { // IE
			$('row'+sid).attachEvent('onmouseover', highlight);
			$('row'+sid).attachEvent('onmouseout', lowlight);
		}
	}
}

function editCategoryForm(sid) {
	$(sid+'edit').innerHTML = "<a href=\"javascript:;\" id=\"editlink"+sid+"\" onclick=\"javascript:saveEditCategory("+sid+")\">Save</a>";
	$(sid+'catname').innerHTML = "<input type=\"text\" size=\"10\" id=\"catnametext"+sid+"\" value=\""+$(sid+'catname').innerHTML+"\">";	
}

function saveEditCategory(sid) {
	var catname = $('catnametext'+sid).value;
    smallCatLoad(sid);
    new Ajax.Request('categories.php', {method:'post', postBody:'action=edit&sid='+sid+'&catname='+catname,
						onComplete: parseEditCategory,
						onFailure: ohno,
						asynchronous: true});
    
	return false;
}

function parseEditCategory(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errors = req.responseXML.getElementsByTagName("errors")[0];
		for(var i=0;i<errnum;i++) {
			alert(getElementTextNS("error",errors,i));
		}
	}
	categoriesForm();
}


function CreateCategory() {
	var f = document.forms['catF'];
	savingForm();
	new Ajax.Request('categories.php', {method:'post', postBody:Form.serialize(f),
						onComplete: parseCreateCategory,
						onFailure: ohno,
						asynchronous: true});
	return false;
}


function parseCreateCategory(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum > 0) {
		var errs = req.responseXML.getElementsByTagName("errors")[0];
		var errors = [];
		for(var i=0;i<errnum;i++) {
			errors.push(getElementTextNS("error",errs,i));
		}
		pretty_error(errors);
	} else {
		categoriesForm();
	}
}


function deleteCategory(sid) {
var agree=confirm("Are you sure you wish to delete this category? The posts will not be deleted, just recategorized into 'Uncategorized'!\n\nThis action cannot be undone!");
if (agree) {
	doDeleteCategory(sid);
}
}

function doDeleteCategory(sid) {
	   new Ajax.Request('categories.php', {method:'post', postBody:'action=delete&sid='+sid,
						onComplete: parseDeleteCategory,
						onFailure: ohno,
						asynchronous: true});
}

function parseDeleteCategory(req) {
	var errnum = getElementTextNS("errnum",req.responseXML,0);
	if (errnum.length > 0) {
		var errors = req.responseXML.getElementsByTagName("errors")[0];
		for(var i=0;i<errnum;i++) {
			alert(getElementTextNS("error",errors,i));
		}
	}
	categoriesForm();
}

function categoryError (msg) {
	removeRows("categoriesTable");
	var newRow = Builder.node('tr',{},[Builder.node('td',{id: 'errorText'},'')]);
	$('categoriesTable').appendChild(newRow);
	$('errorText').colSpan = 5;
	$('errorText').innerHTML ="<strong>" + msg + "</strong>";
}

function showLoadingCategories() {
    removeRows("categoriesTable");
	var newRow = Builder.node('tr',{id: 'LoadingRow'},[Builder.node('td',{id: 'LoadingText'},'')]);
	$('categoriesTable').appendChild(newRow);
	$('LoadingText').colSpan = 9;
	$('LoadingText').style.backgroundColor = "#fff";
	$('LoadingText').innerHTML = '<h3><img src=\'images/loading.gif\'> Loading...</h3>';
}
function smallCatLoad(sid) {
	$(sid+'edit').innerHTML = "<img src=\"images/loadingsmall.gif\" border=\"0\"> Wait..";
}
