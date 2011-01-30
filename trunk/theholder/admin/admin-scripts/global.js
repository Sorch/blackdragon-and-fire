//
// 
// See index.php for full license.

// retrieve text of an XML document element, including
// elements using namespaces
function get_options() {
          new Ajax.Request('../options.php', {method:'post', 
			                          postBody:'',
						  onComplete: parsegetOptions,
						  onFailure: ohno,
						  asynchronous: true});
}

function parsegetOptions(req) {
var info = req.responseXML.getElementsByTagName("info");
blogpath = getElementTextNS("blogpath",info[0],0);
absolutepath = getElementTextNS("absolutepath",info[0],0);
blogurl = getElementTextNS("blogurl",info[0],0);
blogname = getElementTextNS("blogname",info[0],0);
activetheme = getElementTextNS("activetheme",info[0],0);
scriptpath = getElementTextNS("scriptpath",info[0],0);
themepath = getElementTextNS("themepath",info[0],0);
blogdescription = getElementTextNS("blogdescription",info[0],0);
defaultsid = getElementTextNS("defaultsid",info[0],0);
dbprefix = getElementTextNS("dbprefix",info[0],0);
init_login();
}

function getElementTextNS(local, parentElem, index) {
    var result = parentElem.getElementsByTagName(local)[index];
    if (result) {
        // get text, accounting for possible
        // whitespace (carriage return) text nodes 
        if (result.childNodes.length > 1) {
            return result.childNodes[1].nodeValue;
        } else if(result.firstChild) {
            return result.firstChild.nodeValue;
        } else {
	return "";
	}
    } else {
        return "n/a";
    }
}

function ohno(t) {
    alert('Failure: ' + t.status + ' (' + t.statusText + ")\n\n" + t.responseText);
}


function convertSimpleHTML(string) {
	return string.replace(/&amp;/gi,"&").replace(/&lt;/gi,"<").replace(/&gt;/gi,">");
}


// ripped from scriptaculous controls.js
function convertHTML(string) {
	return string.replace(/<br>/gi, "\n").replace(/<br\/>/gi, "\n").replace(/<\/p>/gi, "\n").replace(/<p>/gi, "").replace(/&amp;/gi,"&").replace(/&lt;/gi,"<").replace(/&gt;/gi,">");
}

function textSize (text) {

    var textRows = text.split('\n');
    var newRowAmount = textRows.length;

    for ( var i=0; i<textRows.length; i++ ) {
      if (textRows[i].length > 30) {
        newRowAmount += Math.floor(textRows[i].length/30);

      }
    }
    return newRowAmount;
}

function pretty_error(errors) {
	$('error').className="rederror";
	if (errors.length == 1) {
		var s = '';
	} else {
		var s = 's';
	}
	$('error').innerHTML = "<h3>"+errors.length+" Error"+s+":</h3><ul>";
	for(i=0;i<errors.length;i++) {
		$('error').innerHTML = $('error').innerHTML + "<li>"+errors[i]+"</li>";
	}
	$('error').innerHTML = $('error').innerHTML + "</ul>";

}

function showLoadingBody() {
	$('content').innerHTML =  "<h2><img src=\"images/loading.gif\"> Loading...</h2>";
}