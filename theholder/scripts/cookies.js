//
// 
// See index.php for full license.
function setCookie(name, value, session) {

if (session) {
    expires = "";
} else {
var expires = new Date();
expires.setTime(expires.getTime() + 365 * 24 * 60 * 60 * 1000);

}

var curCookie = name + "=" + escape(value) + 
((expires)? "; expires=" + expires.toGMTString() : "")
+ "; path=/";
document.cookie = curCookie;
}


function getCookie(name) {
  var dc = document.cookie;
  var prefix = name + "=";
  var begin = dc.indexOf("; " + prefix);
  if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0) return "";
  } else
    begin += 2;
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
    end = dc.length;
  return unescape(dc.substring(begin + prefix.length, end));
}



function deleteCookie(name) {
  if (getCookie(name)) {
    document.cookie = name + "=; path=/; expires=Thu, 01-Jan-70 00:00:01 GMT";
  }
}