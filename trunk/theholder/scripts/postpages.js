
// 
// See index.php for full license.


// also called by singlepost.js!
function parseSinglePost(what,showCommentLink,fullPost,appendto) {
	var d = $(appendto);
        var subject = getElementTextNS("subject", what, 0);
	var thebody = getElementTextNS("body", what, 0);
	var date = getElementTextNS("date", what, 0);
	var author = getElementTextNS("name", what, 0);
	var category = getElementTextNS("category",what, 0);
	if (fullPost == 1) {
		document.title = blogname + " » " + category + " » " + subject;
	} else {
		document.title = blogname;
	}
	var excerpt = getElementTextNS("excerpt",what, 0);
	var Numcomments = getElementTextNS("comments", what, 0);
	var id = getElementTextNS("id", what, 0);
	var sid = getElementTextNS("sid", what, 0);
	var allowcomments = getElementTextNS("allowcomments", what, 0);
	if (showCommentLink==1 && allowcomments==1) {
		if (Numcomments == 0){
			var commentsSt = NoComments;
		} else {
			var commentsSt = templateReplace(Comments,['__NUM__','__S__'],[Numcomments,(Numcomments == 1? '' : 's')]);
		}
	} else {
		var commentsSt = "";
	}
	if (excerpt != "" && fullPost != 1) {
		thebody = excerpt + "<a href=\"javascript:;\" onClick=\"javascript:viewpost("+id+")\">" + ReadMoreText + "</a>";
	}
	if (fullPost == 1) {
		var previd = getElementTextNS("previd", what, 0);
		var nextid = getElementTextNS("nextid", what, 0);
		var prevsubj = getElementTextNS("prevsubj", what, 0);
		var nextsubj = getElementTextNS("nextsubj", what, 0);
		d.innerHTML = d.innerHTML + templateReplace(SinglePost,['__ID__','__SID__','__TITLE__','__DATE__','__AUTHOR__','__BODY__','__CATEGORY__','__COMMENTS__','__PREVID__','__PREVPOST__','__NEXTID__','__NEXTPOST__','__RSS__'],[id,sid,subject,date,author,thebody,category,commentsSt,previd,prevsubj,nextid,nextsubj,'<a href="'+blogurl+'rss.php?id='+Viewingid+'&sid='+Viewingsid+'">RSS</a>']);
	} else {
		d.innerHTML = d.innerHTML + templateReplace(Post,['__ID__','__SID__','__TITLE__','__DATE__','__AUTHOR__','__BODY__','__CATEGORY__','__COMMENTS__'],[id,sid,subject,date,author,thebody,category,commentsSt]);
	}
}


function parsePosts(req) {
var d = $('content');
d.innerHTML = "";
var posts = "";
var info = "";
var posts = req.responseXML.getElementsByTagName("post");

var info = req.responseXML.getElementsByTagName("info");
var max = getElementTextNS("max_rows", info[0], 0);
var start = getElementTextNS("start", info[0], 0);
var uid = getElementTextNS("uid", info[0], 0);
var sid = getElementTextNS("sid", info[0], 0);
var search = getElementTextNS("search", info[0], 0);

Viewingsid = sid;
Viewingid = 0;

// update page location
updateHash();
  if (posts.length == 0) {
  if (search == 1) {
  d.innerHTML = "No posts could be found that matched your query.";
  } else {
  d.innerHTML = NoPosts;  
  }
  } else {
  	for (i=0;i<posts.length;i++) {
		parseSinglePost(posts[i],1,0,'content');
	}
  }
Loading = 0;
}

function gotoPostPage (start,sid) {
Loading = 1;
var pBody = "start="+start+"&sid="+sid;
showLoading();

   new Ajax.Request('posts.php', {method:'post', postBody:pBody,
					onComplete: parsePosts,
					onFailure: ohno,
					asynchronous: true});
}

function SearchPosts() {
	Loading = 1;
	var f = document.forms['searchF'];
	showLoading();

	new Ajax.Request('posts.php', {method:'post', postBody:Form.serialize(f),
					onComplete: parsePosts,
					onFailure: ohno,
					asynchronous: true});
	return false;
}