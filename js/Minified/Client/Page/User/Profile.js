"object"!=typeof BabblingBrook.Client.Page.User&&(BabblingBrook.Client.Page.User={}),BabblingBrook.Client.Page.User.Profile=function(){"use strict";var e,r,a,n,t,s,o,i,l=20,u=1e3,i=[],m=[],d=[],g={},_=jQuery.Deferred(),c=function(e,r){console.trace(),console.error("info request error : "+e),console.error(r)},p=function(a){var n="data returned from profile call to /profilejson data does not validate.";if(BabblingBrook.Test.isA([[a.about,"string|null"],[a.real_name,"string|null"]],n),BabblingBrook.Models.ringMembershipType(a.ring_membership_type,n),"undefined"!=typeof a.real_name){null===a.real_name&&(a.real_name="This user has elected to remain anonymous."),null===a.about&&(a.about="This user has not entered any details."),jQuery("#name_content").html(a.real_name).removeClass("block-loading"),jQuery("#about_content").text(a.about).removeClass("block-loading");var t="http://"+a.meta_url;jQuery("#conversation").attr("href",t).removeClass("text-loading");var s=!1,o=!1;jQuery.each(BabblingBrook.Client.User.Rings,function(a,n){n.domain===e&&n.name===r&&"1"===n.member&&(s=!0),n.domain===e&&n.name===r&&"1"===n.admin&&(o=!0)}),o===!0&&window.location.host===e&&jQuery("#edit_ring_profile").removeClass("hide"),s===!1&&"public"===a.ring_membership_type&&window.location.host===e&&(jQuery("#join_ring").removeClass("hide"),jQuery("#join_ring a").click(function(){return BabblingBrook.Client.Core.Interact.postAMessage({domain:window.location.hostname,username:r},"RingJoin",function(e){e.success===!0?(BabblingBrook.Client.Page.User.Profile.onJoindRingHook(r),BabblingBrook.Client.User.Rings.push(e.ring_client_data),BabblingBrook.Client.Core.Ajaxurl.redirect("/"+r+"/ring/members")):jQuery("#join_ring").html(e.error)}),!1})),s===!1&&"request"===a.ring_membership_type&&(jQuery("#request_ring_membership").removeClass("hide"),jQuery("#request_ring_membership a").click(function(){return jQuery("#request_ring_membership a").addClass("text-loading"),BabblingBrook.Client.Core.Interact.postAMessage({domain:window.location.hostname,username:r},"RequestRingMembership",function(a){if(a.success===!0){jQuery("#request_ring_membership a").removeClass("text-loading");var n=jQuery("#on_request_ring_membership_success_template").clone();jQuery(".ring-name",n).text(r+"@"+e),BabblingBrook.Client.Component.Messages.addMessage({type:"notice",message:n.text()})}else f()},f),!1}))}},f=function(){jQuery("#request_ring_membership a").addClass("error").removeClass("text-loading");var a=jQuery("#on_request_ring_membership_error_template").clone();jQuery(".ring-name",a).text(r+"@"+e),BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:a.text()})},y=function(){jQuery("#profile_image").attr("src","/images/user/"+BabblingBrook.Client.User.domain+"/"+BabblingBrook.Client.User.username+"/profile/small/profile.jpg"),jQuery("#profile_image").error(function(){jQuery("#profile_image").attr("src","/images/default_user_large.png")}).attr("src",a)},b=function(){var e={url:s,data:{},https:!1};BabblingBrook.Client.Core.Interact.postAMessage(e,"InfoRequest",p,c)},v=function(){var a=jQuery("#kindred_score");BabblingBrook.Client.User.domain===e&&BabblingBrook.Client.User.username===r?a.html("&#8734;").attr("title","Hopefully you know this person quite well.").removeClass("block-loading something"):BabblingBrook.Client.Core.Loaded.onKindredLoaded(function(){"undefined"==typeof BabblingBrook.Client.User.kindred[e+"/"+r]?a.attr("title","You do not have a relationship with this user").html("0").removeClass("block-loading something"):a.html(BabblingBrook.Client.User.kindred[e+"/"+r]).removeClass("block-loading something")})},h=function(){BabblingBrook.Client.User.username===r&&BabblingBrook.Client.User.domain===e&&jQuery("#editprofile").removeClass("hide"),jQuery.each(BabblingBrook.Client.User.Rings,function(a,n){n.name===r&&n.domain===e&&"1"===n.admin&&jQuery("#editprofile").removeClass("hide")})},j=function(){jQuery.each(BabblingBrook.Client.User.Rings,function(a,n){n.domain===e&&n.name===r&&"1"===n.member&&jQuery("#ring_members").removeClass("hide")})},Q=function(){var e=jQuery("#users_tags_for_profile .user-tag");0===e.length?jQuery("#users_tags_for_profile_none").removeClass("hide"):jQuery("#users_tags_for_profile_none").addClass("hide")},k=function(e,a,n,t,s){var o=jQuery("#tag_template>div").clone(),i=e.domain+"/"+e.username+"/stream/"+e.name+"/"+e.version;if(o.attr("data-tag-stream-url",i),"undefined"==typeof n||"undefined"==typeof n.post_id?jQuery(".tag-name-no-link",o).attr("title",i).text(e.name):(jQuery(".tag-name",o).attr("title",i+"\nClick to view comments on this user tag.").text(e.name).removeClass("hide").attr("href","/postwithtree/"+n.domain+"/"+n.post_id),jQuery(".tag-name-no-link",o).remove()),"undefined"!=typeof t){var l=jQuery(".tag-score",o).attr("title").replace("!username!",r).replace("!qty!",t);if(jQuery(".tag-score",o).removeClass("hide").attr("title",l).text(t),"string"==typeof s&&"kindred"===s){var u=jQuery("#kindred_tag_extra_title_template").text().trim();jQuery(".tag-score",o).attr("title",l+u)}}a.append(o);var m=jQuery(".tag-icon",o).attr("title").replace("this user",r);jQuery(".tag-icon",o).attr("title",m),m=jQuery(".untag-icon",o).attr("title").replace("this user",r),jQuery(".untag-icon",o).attr("title",m),"undefined"==typeof g[i]?jQuery(".tag-icon",o).removeClass("hide").click(R.bind(null,e)):jQuery(".untag-icon",o).removeClass("hide").click(I.bind(null,e,n)),Q()},C=function(e){console.error("info request error "+e)},B=function(e,r){var a=jQuery("#users_tags_for_profile"),n={post_id:r.post_id,take_value:1};k(e,a,n);var t=e.domain+"/"+e.username+"/stream/"+e.name+"/"+e.version,s=jQuery(".selector-action-tag img[data-tag-stream-url='"+t+"']");s.addClass("hide").parent().addClass("hide").parent().parent().removeClass("block-loading");var o=jQuery(".selector-action-untag img[data-tag-stream-url='"+t+"']");o.removeClass("hide").parent().removeClass("hide").parent().parent().removeClass("block-loading");var i=jQuery(".user-tag[data-tag-stream-url='"+t+"']");jQuery(".tag-icon",i).addClass("hide").unbind("click"),jQuery(".untag-icon",i).removeClass("hide").click(I.bind(null,e,r)),("undefined"==typeof r||"undefined"==typeof r.post_id)&&(jQuery(".tag-name",i).attr("title",t+" Click to view comments on this user tag.").text(e.name).removeClass("hide").attr("href","/postwithtree/"+r.domain+"/"+r.post_id),jQuery(".tag-name-no-link",i).remove()),Q(),BabblingBrook.Client.Page.User.Profile.onTaggedHook(e)},w=function(e,r,a,n){BabblingBrook.Client.Core.Interact.postAMessage({post_id:r.post_id,field_id:2,stream_domain:e.domain,stream_username:e.username,stream_name:e.name,stream_version:e.version,value:n,value_type:"updown",mode:"new"},"Take",a,function(){console.error("Error taking a new tag post."),console.log(e),console.log(r)})},U=function(e,r){w(e,r,B.bind(null,e,r),1)},q=function(e,r){var a={post_id:r.post_id,domain:r.domain,take_value:1};U(e,a)},x=function(e,r){if(r.post===!1){var a=new BabblingBrook.Client.Component.MakePost(q.bind(null,e));a.setupHiddenPost(e,n)}else{var t={post_id:r.post.post_id,domain:r.post.domain,take_value:1};U(e,t)}},M=function(a,n){var t=BabblingBrook.Library.makeStreamUrl(a,"postuser"),s={url:t,data:{username:r,domain:e},https:!1};BabblingBrook.Client.Core.Interact.postAMessage(s,"InfoRequest",n.bind(null,a),C)},R=function(e){var r={stream_domain:e.domain,stream_username:e.username,stream_name:e.name,stream_version:e.version};o.push(r),M(e,x)},A=function(e){var r=e.domain+"/"+e.username+"/stream/"+e.name+"/"+e.version,a=jQuery(".selector-action-tag img[data-tag-stream-url='"+r+"']");a.removeClass("hide").parent().removeClass("hide").parent().parent().removeClass("block-loading");var n=jQuery(".selector-action-untag img[data-tag-stream-url='"+r+"']");n.addClass("hide").parent().addClass("hide").parent().parent().removeClass("block-loading");var t=jQuery(".user-tag[data-tag-stream-url='"+r+"']");jQuery(".tag-icon",t).removeClass("hide").click(R.bind(null,e)),jQuery(".untag-icon",t).addClass("hide").unbind("click");var s=jQuery("#users_tags_for_profile .user-tag");jQuery.each(s,function(){var e=jQuery(this).attr("data-tag-stream-url");e===r&&jQuery(this).remove()});for(var i=0;i<o.length;i++){var l=o[i].stream_domain+"/"+o[i].stream_username+"/stream/"+o[i].stream_name+"/"+o[i].stream_version;l===r&&o.splice(i,1)}jQuery.each(g,function(e){return r===e?(delete g[e],!1):void 0}),Q()},T=function(e,r){if(r.post===!1)throw console.log(e),console.log(post),"A user take has failed to untag a user. Could not retrieve the take.";var a={post_id:r.post.post_id,domain:r.post.domain,take_value:0};w(e,a,A.bind(null,e,a),0)},I=function(e,r){var a={post_id:r.post_id,take_value:0};w(e,a,A.bind(null,e,a),0)},P=function(e,r,a,n){r.preventDefault(),a.addClass("block-loading");var t={name:n.name,domain:n.domain,username:n.username,version:n.version},s=t.domain+"/"+t.username+"/stream/"+t.name+"/"+t.version;jQuery(".action img",a).attr("data-tag-stream-url",s),"tag"===e?R(t):M(t,T)},L=function(){jQuery("#search_tags").removeClass("block-loading")},H=function(e,r,a){var n=!1;jQuery.each(o,function(e,r){return a.domain===r.stream_domain&&a.username===r.stream_username&&a.name===r.stream_name&&a.version===r.stream_version?(n=!0,!1):!0}),"tag"===e&&n===!1?(jQuery(".tag-icon",r).removeClass("hide"),jQuery(".untag-icon",r).parent().addClass("hide")):"untag"===e&&n===!0&&(jQuery(".untag-icon",r).removeClass("hide"),jQuery(".tag-icon",r).parent().addClass("hide"))},D=function(e,r,a){_.done(H.bind(null,e,r,a))},S=function(){jQuery("#search_tags_on, #search_tags_hint").click(function(){jQuery("#search_tags_list").html(""),jQuery("#search_tags_off").removeClass("hide"),jQuery("#search_tags_on").addClass("hide"),jQuery("#search_tags").addClass("content-block-3 block-loading");{var e=jQuery("<div>").append(jQuery("#tag_template .tag-icon").clone()).html().replace("this user",r),a=jQuery("<div>").append(jQuery("#tag_template .untag-icon").clone()).html().replace("this user",r),n=[{name:e,"class":"tag",onClick:P.bind(null,"tag"),onReady:D.bind(null,"tag")},{name:a,"class":"untag",onClick:P.bind(null,"untag"),onReady:D.bind(null,"untag")}],t=jQuery("#search_tags_list");new BabblingBrook.Client.Component.Selector("stream","search_rate",t,n,{show_fields:{version:!1,stream_kind:!1},initial_values:{stream_kind:"user"},onReady:L,loading_selector:"#search_tags",additional_selector_class:"selector-2"})}}),jQuery("#search_tags_off").click(function(){jQuery("#search_tags_list").slideUp(250,function(){jQuery("#search_tags_off").addClass("hide"),jQuery("#search_tags_on").removeClass("hide"),jQuery("#search_tags_list").empty().show(),jQuery("#search_tags").removeClass("content-block-3 block-loading")})})},J=function(e){var r=jQuery.grep(e,function(e){var r=e.stream_domain+"/"+e.stream_username+"/stream/"+e.stream_name+"/"+e.stream_version,a=!1;return jQuery.each(o,function(e,n){var t=n.stream_domain+"/"+n.stream_username+"/stream/"+n.stream_name+"/"+n.stream_version;return r===t?(a=!0,!1):!0}),a===!0?!1:!0});return r},K=function(e){_.done(function(){jQuery("#users_popular_tags").removeClass("block-loading"),jQuery.each(e,function(e,r){BabblingBrook.Test.isA([[r.stream_domain,"domain"],[r.stream_name,"resource-name"],[r.stream_username,"username"],[r.stream_version,"version"]],"data returned from profile call to /popularuserstreams popular_tags does not validate.")});var r="users_popular_tags_list";jQuery("#"+r).html("");var a=J(e);a.length<1?0===jQuery("#users_tags_for_profile .user-tag").length?jQuery("#users_popular_tags_none").removeClass("hide"):jQuery("#users_popular_tags_used").removeClass("hide"):(jQuery("#users_popular_tags_none").addClass("hide"),jQuery("#users_popular_tags_used").addClass("hide"),F(a,r))})},E=function(){console.error("info request error")},G=function(){jQuery("#users_popular_tags_off").click(function(){jQuery(this).addClass("hide").parent().addClass("content-block-3 block-loading"),jQuery("#users_popular_tags_on").removeClass("hide");var e=jQuery("#users_popular_tags_list");e.empty(),V(e,1,20)}),jQuery("#users_popular_tags_on").click(function(){jQuery("#users_popular_tags_list").slideUp(250,function(){jQuery(this).empty().show().parent().removeClass("content-block-3 block-loading"),jQuery("#users_popular_tags_off").removeClass("hide"),jQuery("#users_popular_tags_on").addClass("hide"),jQuery("#users_popular_tags_none").addClass("hide"),jQuery("#users_popular_tags_used").addClass("hide")})})},V=function(e,r,a){var n=BabblingBrook.Client.User.domain+"/"+BabblingBrook.Client.User.username+"/popularuserstreams",t={page:r,qty:a},s={url:n,data:t,https:!1};BabblingBrook.Client.Core.Interact.postAMessage(s,"InfoRequest",K,E,void 0,{jq_popular:e,page:r,qty:a})},Y=function(e){var r={};jQuery.extend(r,e),_.done(function(){jQuery("#suggest_user_tags").removeClass("block-loading"),jQuery.each(r,function(e,r){BabblingBrook.Test.isA([[r.domain,"domain"],[r.name,"resource-name"],[r.username,"username"],[r.version,"version-object|version"]],"data returned from profile call to generatetag suggestions does not validate.")});var e=[];jQuery.each(r,function(r,a){e.push({stream_domain:a.domain,stream_username:a.username,stream_name:a.name,stream_version:BabblingBrook.Library.makeVersionString(a.version)})}),r=e;var a="suggest_user_tags_list";jQuery("#"+a).html("");var n=J(r);n.length<1?jQuery("#suggest_user_tags_none").removeClass("hide"):(jQuery("#suggest_user_tags_none").addClass("hide"),F(n,a))})},z=function(){jQuery("#suggest_user_tags_on").click(function(){jQuery("#suggest_user_tags").addClass("content-block-3 block-loading"),jQuery("#suggest_user_tags_off").removeClass("hide"),jQuery("#suggest_user_tags_on").addClass("hide"),BabblingBrook.Client.Core.Suggestion.fetch("user_stream_suggestion",Y,{})}),jQuery("#suggest_user_tags_off").click(function(){jQuery("#suggest_user_tags_list").slideUp(250,function(){jQuery("#suggest_user_tags_off").addClass("hide"),jQuery("#suggest_user_tags_on").removeClass("hide"),jQuery("#suggest_user_tags_none").addClass("hide"),jQuery("#suggest_user_tags_list").empty().show(),jQuery("#suggest_user_tags").removeClass("content-block-3 block-loading")})})},F=function(e,r){var a=jQuery("#"+r);e.length>0&&a.html(""),jQuery.each(e,function(e,r){var n={domain:r.stream_domain,username:r.stream_username,name:r.stream_name,version:r.stream_version},t={post_id:r.post_id,domain:r.domain,take_value:r.take_value};k(n,a,t,r.score,r.type)})},N=function(e,r){var a="The "+r+" callback function data object is incorrect.";BabblingBrook.Test.isA([[e,"array"]],a),jQuery.each(e,function(e,r){BabblingBrook.Test.isA([[r.stream_domain,"domain"],[r.stream_username,"username"],[r.stream_name,"resource-name"],[r.stream_version,"version"],[r.post_id,"string"],[r.domain,"domain"],[r.parent_id,"null|undefined|string"],[r.top_parent_id,"null|undefined|string"],[r.timestamp,"uint"],[r.take_value,"int|undefined"],[r.date_taken,"uint"]],a)})},O=function(e){N(e,"loadUserTagsByUser"),o=e;for(var r=0;r<o.length;r++){var a=o[r].stream_domain+"/"+o[r].stream_username+"/stream/"+o[r].stream_name+"/"+o[r].stream_version;g[a]=!0}if(jQuery("#users_tags_for_profile").parent().removeClass("block-loading"),0!==e.length){var n="users_tags_for_profile";F(e,n)}_.resolve()},W=function(e){console.error("info request error : "+e)},X=function(){var a=BabblingBrook.Client.User.domain+"/"+BabblingBrook.Client.User.username+"/usertagsbyuser",n={url:a,data:{profile_domain:e,profile_username:r,start:0,qty:l},https:!1};BabblingBrook.Client.Core.Interact.postAMessage(n,"InfoRequest",O,W)},Z=function(){if(jQuery("#kindred_tag_list").parent().removeClass("block-loading"),0!==m.length){var e="kindred_tag_list";F(m,e)}else jQuery("#kindred_tag_list_none").removeClass("hide")},$=function(){if(jQuery("#global_tag_list").parent().removeClass("block-loading"),0!==d.length){var e="global_tag_list";F(d,e)}else jQuery("#global_tag_list_none").removeClass("hide")},er=function(e){BabblingBrook.Client.Core.Loaded.onKindredLoaded(function(){N(e,"loadGlobalUserTags"),i=i=e,m=[],d=[];var r,a=0,n=0,t="",s=i.length;jQuery.each(i,function(e,o){var i=o.user_domain+"/"+o.user_username,l=!1;"undefined"!=typeof BabblingBrook.Client.User.kindred[i]&&(l=!0);var u=o.stream_domain+"/"+o.stream_username+"/stream/"+o.stream_name+"/"+o.stream_version;if(l===!0&&n++,0!==e&&t!==u||s===e-1){if(r.score=a,d.push(r),n>0){var g=jQuery.extend({},r);g.score=n,g.type="kindred",m.push(g)}n=0,a=0}a++,r={stream_domain:o.stream_domain,stream_username:o.stream_username,stream_name:o.stream_name,stream_version:o.stream_version,post_id:o.post_id,domain:o.domain},t=u});var o=function(e,r){return r.score-e.score};d=d.sort(o),d=d.sort(o),_.done($),_.done(BabblingBrook.Client.Core.Loaded.onKindredLoaded(Z))})},rr=function(){console.error("info request error")},ar=function(){var a=t+"/usertagsglobal",n={start:0,qty:u,full_username:e+"/"+r},s={url:a,data:n,https:!1};BabblingBrook.Client.Core.Interact.postAMessage(s,"InfoRequest",er,rr)},nr=function(e){jQuery("#create_stream").removeClass("button-loading");for(var r in e){for(var a=e[r].length,n="",t=0;a>t;t++)n+=e[r][t]+"<br/>";jQuery("#stream_"+r+"_error").removeClass("hide").html(n)}},tr=function(e,r){jQuery("#create_tag_stream").removeClass("button-loading");var a="/"+BabblingBrook.Client.User.username+"/stream/"+e+"/0/0/0/changestatus";if(BabblingBrook.Library.post(a,{action:"publish"},function(){},sr),"boolean"!=typeof r.success&&sr(),r.success===!0){var n={domain:BabblingBrook.Client.User.domain,username:BabblingBrook.Client.User.username,name:e,version:"0/0/0"};jQuery("#new_tag_name").val(""),jQuery("#new_tag_description").val(""),jQuery("#the_new_tag").html(""),k(n,jQuery("#the_new_tag")),jQuery("#make_new_tag_success").slideDown()}else nr(r.errors)},sr=function(){jQuery("#create_stream").removeClass("button-loading"),BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:"An unknown error occured whilst trying to save a stream."})},or=function(){jQuery("#make_new_tag>a").click(function(){jQuery("#make_new_tag_form").slideDown(),jQuery("#make_new_tag").slideUp()}),jQuery("#make_new_tag_form_off").click(function(){jQuery("#make_new_tag_form").slideUp(),jQuery("#make_new_tag").slideDown()}),jQuery("#create_tag_stream").click(function(){jQuery("#create_tag_stream").addClass("button-loading"),jQuery("#make_new_tag_form .error").html("").addClass("hide");var e=jQuery("#new_tag_name").val(),r=jQuery("#new_tag_description").val();0===r.length&&(r=jQuery("#new_tag_default_description_template").text());var a="user";BabblingBrook.Library.post("/"+BabblingBrook.Client.User.username+"/streams/make",{name:e,description:r,kind:a,post_mode:"anyone"},tr.bind(null,e),sr,"make_stream_error")})},ir=function(){G(),z(),S(),or()},lr=function(){var a=!1;jQuery.each(BabblingBrook.Client.User.Rings,function(n,t){if("1"===t.member&&"invitation"===t.member_type){a=!0;var s=jQuery("#make_ring_invite_line_template>div").clone(),o=jQuery("a",s).text().replace("*ring_name*",t.name);jQuery("a",s).attr("href","/"+BabblingBrook.Client.User.username+"/ring/index?to="+e+"/"+r).text(o),jQuery("#make_ring_invite>div").append(s)}}),a===!0&&jQuery("#make_ring_invite").removeClass("hide")};return{construct:function(){e=jQuery.trim(jQuery("#domain").text()),r=jQuery.trim(jQuery("#username").text()),a="http://"+e+"/images/user/"+e+"/"+r+"/profile/large/profile.jpg",t=e+"/"+r,s=t+"/profile",n=[{display_order:"1",link_title:e+"/"+r,link:"http://"+e+"/"+r+"/profile"},{display_order:"2"}],y(),b(),v(),h(),j(),ir(),lr(),X(),ar(),BabblingBrook.Client.Core.Loaded.setProfileLoaded()},onTaggedHook:function(){},onJoindRingHook:function(){}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.User.Profile.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/User/Profile.js.map