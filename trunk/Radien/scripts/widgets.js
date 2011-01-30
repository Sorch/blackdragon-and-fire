// Copyright (c) 2006 Chris McClelland (http://www.ajaxpress.org/)
// 
// See index.php for full license.

//doWidgets: calls ajax for widgets.php
function doWidgets() {
Loading = 1;

  showWidgetsLoading();
  new Ajax.Request('widgets.php', {method:'post',
				onComplete: parseWidgets,
				onFailure: ohno,
				asynchronous: true});

}

// makes any object, 'what', disappear. 
// used for closing widgets
function Disappear(what) {
new Effect.BlindUp(what,{queue:'front'});
	if (document.all) {
	// if IE
	        $('left').removeChild($(what));
		SaveSideOrder();			
	} else {
	window.setTimeout(function ( e ) {
	
				$('left').removeChild($(what));
				SaveSideOrder();
			}, 1000);
	}
}

function parseSingleWidget(what) {
	var main = $('sidebar');

	var title = getElementTextNS("title", what, 0);
	var id = getElementTextNS("id", what, 0);
	var text = getElementTextNS("text", what, 0);

	main.innerHTML = main.innerHTML + templateReplace(Widget,['__ID__','__TITLE__','__TEXT__'],[id,title,text]);

}

function parseWidgets(req) {
var main = $('sidebar');
main.innerHTML = "";
widgets = req.responseXML.getElementsByTagName("widget");

    for (i=0;i<widgets.length;i++) {
	parseSingleWidget(widgets[i]);
    }
Sortable.create('sidebar',{tag:'ul',ghosting:false,handle:'handle',constraint:'vertical',onChange: SaveSideOrder});
Loading = 0;
}

function ResetSideItems() {

	deleteCookie('left');
	doWidgets(['all'],Viewinguid);

}
function SaveSideOrder() {
var litems = $('sidebar').childNodes;
var sideCookie = "";
var Litem = "";
	for(i=0;i<litems.length;i++) {
		Litem = litems[i].id.substr(2);
		sideCookie += (i == 0)? Litem : ","+Litem;
	}

setCookie('left',sideCookie,0);
}

function showWidgetsLoading(xmlresp) {
$('sidebar').innerHTML = templateReplace(LoadingWidgets,["__THEMEPATH__"],[themepath + activetheme + "/"]);
}