BabblingBrook.Client.Page.Mail=function(){"use strict";var e,t,o,n,i=0,a=jQuery.Deferred(),r=function(){return i++,i},l=function(){BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:"Unable to retrieve some of your posts."})},s=function(e,t){var o=r(e);if(0===o)return void jQuery("#load_more").addClass("hide");jQuery("#load_more").removeClass("hide");var n={url:BabblingBrook.Library.changeUrlAction(BabblingBrook.Client.User.Config.default_private_filter,"json"),name:BabblingBrook.Library.extractName(BabblingBrook.Client.User.Config.default_private_filter),priority:BabblingBrook.Client.User.Config.default_private_filter_priority};BabblingBrook.Client.Core.Interact.postAMessage({sort_request:{type:e,filter:n,moderation_rings:[],posts_to_timestamp:null,private_page:o,user:{username:BabblingBrook.Client.User.username,domain:BabblingBrook.Client.User.domain}}},"SortRequest",p.bind(null,e,o,t),l,BabblingBrook.Client.User.Config.action_timeout+5e3)},p=function(i,r,l,s){t===i&&(1===r&&(n=jQuery.extend({},BabblingBrook.Client.Component.PostsWaiting.getWaitingData()),a.resolve(),BabblingBrook.Client.Component.PostsWaiting.onInboxViewed(i)),s.sort_request.update===!1?l(s.posts):o.update(s.posts),e.removeClass("block-loading"))},u=function(e,o){a.done(function(){var i;"local_private"===t||"local_public"===t||"local_all"===t?"private"===o.status?i=n.private_client.timestamp:"public"===o.status&&(i=n.public_client.timestamp):"private"===o.status?i=n.private_global.timestamp:"public"===o.status&&(i=n.public_global.timestamp),parseInt(o.timestamp)>parseInt(i)&&e.addClass("new-post")})};return{construct:function(){},changeType:function(n){BabblingBrook.Client.Core.Loaded.onUserLoaded(function(){var a="#post_inbox_template>.post";("local_sent_private"===n||"global_sent_private"===n)&&(a="#post_sent_template>.post"),e=jQuery("#post_list"),t=n,e.empty(),i=0,o=new BabblingBrook.Client.Component.Cascade(e,a,s.bind(null,n),".post-replies",jQuery("#post_sent_template>.post"),u,void 0,void 0,s.bind(null,n))})}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Core.Loaded.onUserLoaded(function(){BabblingBrook.Client.Page.Mail.construct()})});
//# sourceMappingURL=/js/Minified/Client/Page/Mail.js.map