
// 
// See index.php for full license.
function Load() {
get_options();
}
function initialize() {
    // check for an initial hash
    if (window.location.hash!="") {
      pollHash();
    } else {
      gotoPostPage(0,Viewingsid);
    }
    doWidgets();
    setInterval(pollHash, 1000);
}



// retrieve text of an XML document element, including
// elements using namespaces
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
    alert('Failure!!!! ' + t.status + ' -- ' + t.statusText);
}

function showLoading(xmlresp) {
var d = $('content');
d.innerHTML = templateReplace(LoadingContent,["__THEMEPATH__"],[themepath + activetheme + "/"]);
}

// adapted from ajaxpatterns
function pollHash() {

  if (Loading == 1) {
    return; // don't want to do anything if something is loading
  }
  if (window.location.hash==recentHash) {
    return; // Nothing's changed since last polled.
  }

  recentHash = window.location.hash;
  var figuresRE = /#([0-9]+),([0-9]+)/;
  var figuresSpec = window.location.hash;
  if (!figuresRE.test(figuresSpec)) {
     return; // ignore url if invalid
  }
  Viewingsid = figuresSpec.replace(figuresRE, "$1");
  Viewingid = figuresSpec.replace(figuresRE, "$2");
  whereTo();
}

function whereTo() {
  if (Viewingid == 0) {
    gotoPostPage(0,Viewingsid);
  } else {
    viewpost(Viewingid);
  }
}

function get_options() {
          new Ajax.Request('options.php', {method:'post', 
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

Viewingsid = defaultsid;
initialize();
}


function updateHash() {
hash =   "#" + Viewingsid + "," + Viewingid;
window.location.hash = hash;
recentHash = hash;
}

function templateReplace(what,variables,to) {
	for(var i=0;i<variables.length;i++) {
	         var re = new RegExp(variables[i],'gi');
		 what = what.replace(re, to[i]);
	}
	return what;
}