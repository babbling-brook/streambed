BabblingBrook.Client.Page.Mail.Compose=function(){"use strict";var o=function(o){jQuery("#recent_posts_container").removeClass("hide");var e=jQuery("#dummy_compose_template>div").clone();jQuery("#recent_posts").prepend(e);var t=jQuery("#post_inbox_template>.post").clone();BabblingBrook.Client.Component.Post(o,jQuery("#dummy_post"),t,void 0,void 0),jQuery(".make-post").empty(),n()},e=function(){jQuery("#compose_post>.make-post .private-post-check>input").attr("checked",!0),jQuery("#compose_post>.make-post").removeClass("block-loading")},t=function(){n()},n=function(){var n=new BabblingBrook.Client.Component.MakePost(o,t),r=BabblingBrook.Library.changeUrlAction(BabblingBrook.Client.User.Config.private_message_stream,"json");n.setupNewPost(r,jQuery("#compose_post>.make-post"),"open",void 0,void 0,void 0,"private",void 0,e)};return{construct:function(){n()}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Core.Loaded.onUserLoaded(function(){BabblingBrook.Client.Page.Mail.Compose.construct()})});
//# sourceMappingURL=/js/Minified/Client/Page/Mail/Compose.js.map