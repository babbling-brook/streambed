"object"!=typeof BabblingBrook.Client.Page.Site&&(BabblingBrook.Client.Page.Site={}),BabblingBrook.Client.Page.Site.Logout=function(){"use strict";return{construct:function(){jQuery("#ajax_load").addClass("ajax-loading"),BabblingBrook.Client.Core.Loaded.onDomusLoaded(function(){BabblingBrook.Library.post("/site/locallogout",{},function(o){if(o.success===!0){jQuery("#logout_message").html("You have been logged out locally. Now logging you out of your data store...");var e=function(){var o="http://"+BabblingBrook.Client.User.domain+"/site/logoutall",e=jQuery("#logout_failed_template");jQuery("a",e).attr("href",o),jQuery("#logout_message").html(e.html()),jQuery("#logoutall").click(function(){return window.location=o,!1}),jQuery("#ajax_load").removeClass("ajax-loading")},t=function(o){o.success===!1&&e()};BabblingBrook.Client.Core.Interact.postAMessage({},"Logout",t,e)}else jQuery("#logout_message").append("Error. Failed to log out locally."),jQuery("#ajax_load").removeClass("ajax-loading")})})}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.Site.Logout.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/Site/Logout.js.map