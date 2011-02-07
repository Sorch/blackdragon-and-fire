var NoComments = "Add a comment to this post!";
var Comments = "__NUM__ comment__S__";
var CommentsStart = "<h3 id=\"comments\">__NUM__ Response__S__ to &#8220;__TITLE__&#8221;</h3><ol id=\"commentlist\" class=\"commentlist\">__COMMENTS__</ol>";
var Comment = "<li class=\"__ALT__\" id=\"comment-__ID__\"><cite>__UNAME__</cite> Says:<br /><small class=\"commentmetadata\">__DATE__</small>__BODY__</li>";
var CommentForm = "__ERRORS__<form onSubmit=\"__COMMENTACTION__\" method=\"post\" name=\"commentform\" id=\"commentform\"><p><input type=\"text\" name=\"author\" id=\"author\" value=\"\" size=\"22\" tabindex=\"1\" /><label for=\"author\"><small>Name</small></label></p><p><input type=\"text\" name=\"email\" id=\"email\" value=\"\" size=\"22\" tabindex=\"2\" /><label for=\"email\"><small>Mail (will not be published)</small></label></p><p><input type=\"text\" name=\"url\" id=\"url\" value=\"\" size=\"22\" tabindex=\"3\" /><label for=\"url\"><small>Website</small></label></p><p><textarea name=\"comment\" id=\"comment\" cols=\"100%\" rows=\"10\" tabindex=\"4\"></textarea></p><p><input name=\"submit\" type=\"submit\" id=\"submit\" tabindex=\"5\" value=\"Submit Comment\" /></p></form>";

var Post = "<div class=\"post\"><h2 id=\"post-__ID__\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__ID__)\" rel=\"bookmark\" title=\"Permanent Link to __TITLE__\">__TITLE__</a></h2><small>__DATE__ by __AUTHOR__</small><div class=\"entry\">__BODY__</div><p class=\"postmetadata\">Posted in <a href=\"javascript:;\" onClick=\"javascript:gotoPostPage(0,__SID__)\" rel=\"bookmark\" title=\"View posts from __CATEGORY__\">__CATEGORY__</a> &nbsp; &nbsp; <a href=\"javascript:;\" onClick=\"javascript:viewpost(__ID__)\" id=\"commentsLink\" rel=\"bookmark\" title=\"Comments in __TITLE__\">__COMMENTS__</a></p></div>";
var SinglePost = "<div class=\"navigation\"><div class=\"alignleft\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__PREVID__)\">__PREVPOST__</a></div><div class=\"alignright\"><a href=\"javascript:;\" onClick=\"javascript:viewpost(__NEXTID__)\">__NEXTPOST__</a></div></div><div class=\"post\"><h2 id=\"post-__ID__\"><a href=\"javascript:;\" rel=\"bookmark\" title=\"Permanent Link: __TITLE__\">__TITLE__</a></h2><div class=\"entrytext\"><br />__BODY__<br /><br /><br /><p class=\"postmetadata alt\"><small>This entry was posted at __DATE__ by __AUTHOR__ and is filed under __CATEGORY__.You can follow any responses to this entry through the __RSS__ feed. </small></p></div></div>";
var NoPosts = "<p>No posts have been added yet! Stay tuned!</p>";
var ReadMoreText = "<br /><br />Read the rest of this entry &raquo;";

var Widget = "<ul id=\"w-__ID__\"><li><h2 class=\"handle\">__TITLE__</h2><p>__TEXT__</p></li></ul>";

var LoadingComment = "Saving.. <img src=\"__THEMEPATH__images/loading.gif\">";
var LoadingWidgets = "Loading.. <img src=\"__THEMEPATH__images/loading.gif\">";
var LoadingContent = "Loading.. <img src=\"__THEMEPATH__images/loading.gif\">";

var ErrorsText = "__NUM__ Error__S__ occurred: <ul>__ERRORS__</ul>";
var ErrorsItem = "<li>__ERROR__</li>";
