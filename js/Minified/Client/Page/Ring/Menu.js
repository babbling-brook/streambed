"object"!=typeof BabblingBrook.Client.Page.Ring&&(BabblingBrook.Client.Page.Ring={}),BabblingBrook.Client.Page.Ring.Menu=function(){"use strict";var e,n=null,r=function(){var e,n,r=[],i=window.location.href.slice(window.location.href.indexOf("?")+1).split("&");for(n=0;n<i.length;n++)e=i[n].split("="),r.push(e[0]),r[e[0]]=e[1];return r},i=function(e,n,r,i){for(var t=0;t<e.length;t++)e[t].name===r&&e[t].domain===n&&(e[t][i]="1",e[t].super_ring=!0)},t=function(e){for(var n=[],r=0;r<e.length;r++){for(var t=!1,a=0;r>=a;a++)r!==a&&e[r].name===e[a].name&&e[r].domain===e[a].domain&&(t=!0,("0"===e[r].member&&"1"===e[a].member||"1"===e[r].member&&"0"===e[a].member)&&i(n,e[r].domain,e[r].name,"member"),("0"===e[r].admin&&"1"===e[a].admin||"1"===e[r].admin&&"0"===e[a].admin)&&i(n,e[r].domain,e[r].name,"admin"));t===!1&&n.push(e[r])}var o=function(e,n){return e.domain<n.domain?-1:e.domain>n.domain?1:e.name<n.name?-1:1};return n.sort(o),n},a=function(){var e=BabblingBrook.Client.User.Rings.slice(0);jQuery.merge(e,n.member_super_rings),jQuery.merge(e,n.admin_super_rings),e=t(e),jQuery("#member_rings_loading").addClass("hide"),jQuery("#admin_rings_loading").addClass("hide"),jQuery.each(e,function(e,n){"1"===n.admin&&u(n),"1"===n.member&&d(n)})},o=function(){jQuery("#invitations_loading").addClass("hide");var e=jQuery("#on_fetching_invitations_error_template").text();BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:e})},s=function(e,n,r){if(r.success===!1)return void m(e,n);var i="http://"+n.domain+"/"+n.name+"/ring/vetmembershiprequests";jQuery(".vet-users",e).attr("href",i),jQuery(".vet-users-content",e).removeClass("text-loading").find(".vet-users-qty").text(r.qty),jQuery(".vet-users",e).text(jQuery(".vet-users",e).text())},m=function(e,n){var r=jQuery("#on_fetching_ring_users_waiting_to_be_vetted_error_template").clone();jQuery(".waiting-to-be-vetted-ring",r).text(n.name+"@"+n.domain),jQuery(".vet-users-content",e).removeClass("text-loading").addClass("error"),BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:r.text()})},u=function(n){var r=jQuery("#admin_rings>table>tbody");jQuery("#admin_rings table").removeClass("hide"),jQuery("#no_admin_rings").addClass("hide");var i=jQuery("#admin_row_template>tbody>tr").clone(),t="http://"+n.domain+"/"+n.name+"/";jQuery(".edit-profile",i).attr("href",t+"editprofile"),jQuery(".admin-page",i).attr("href",t+"ring/update"),jQuery(".admin-invitation",i).attr("href",t+"ring/invite?menu_type=admin&type=admin&to="+e.to),"request"===n.member_type&&(jQuery(".vet-users-content",i).removeClass("hide"),BabblingBrook.Client.Core.Interact.postAMessage({domain:n.domain,username:n.name},"FetchRingUsersWaitingToBeVetted",s.bind(null,i,n),m.bind(null,i,n))),jQuery(".ring-name",i).attr("title",n.domain+"/"+n.name).text(n.name),"undefined"!=typeof n.super_ring&&jQuery(".ring-name",i).text(n.name+" (super ring grants access)"),jQuery(".profile-page",i).attr("href",t),jQuery(".member-invitation",i).attr("href",t+"ring/invite?menu_type=admin&type=member&to="+e.to),"admin_invitation"===n.member_type&&jQuery(".member-invitation",i).removeClass("hide"),"invitation"===n.admin_type&&jQuery(".admin-invitation",i).removeClass("hide"),jQuery(r).append(i),jQuery(".admin-invitation",i).is(":visible")===!0&&jQuery(".member-invitation",i).is(":visible")===!0&&jQuery(".member-invitation",i).after("<br />")},d=function(n){var r=jQuery("#member_rings>table>tbody");jQuery("#member_rings table").removeClass("hide"),jQuery("#no_membership_rings").addClass("hide");var i="http://"+n.domain+"/"+n.name+"/",t=jQuery("#member_row_template>tbody>tr").clone();jQuery(".members-area",t).attr("href",i+"ring/members"+e.to),jQuery(".ring-name",t).attr("title",n.domain+"/"+n.name).text(n.name),jQuery(".profile-page",t).attr("href",i),jQuery(".member-invitation",t).attr("href",i+"ring/invite?menu_type=member&type=member&to="+e.to),"invitation"===n.member_type&&jQuery(".member-invitation",t).removeClass("hide"),jQuery(r).append(t)},l=function(e){return jQuery("#invitations_loading").addClass("hide"),0===e.invitations.length?void jQuery("#invitations_none").removeClass("hide"):(jQuery.each(e.invitations,function(e,n){var r,i=jQuery("#invites_row_template>tbody>tr").clone();r="member"===n.type?jQuery("#join_as_member_invitation_template>div").clone():jQuery("#join_as_admin_invitation_template>div").clone();var t=jQuery(r);jQuery(".ring-name",t).text(n.ring_username).attr("title",n.ring_domain+"/"+n.ring_username),jQuery(".from-user",t).text(n.from_username).attr("title",n.from_domain+"/"+n.from_username),jQuery(".invite-details",i).append(t),jQuery(".invite-join",i).attr("data-ring-username",n.ring_username),jQuery(".invite-join",i).attr("data-ring-domain",n.ring_domain),jQuery(".invite-join",i).attr("data-type",n.type),jQuery("#invitations_table>tbody").append(i),jQuery(".invite-join",i).click(_)}),void jQuery("#invitations_table").removeClass("hide"))},g=function(){BabblingBrook.Library.get("/"+BabblingBrook.Client.User.username+"/ring/invitations",{},l,o)},y=function(e){"string"!=typeof e&&(e=jQuery("#on_invite_accepted_generic_error_template").text()),BabblingBrook.Client.Component.Messages.addMessage({type:"error",message:e})},b=function(e,n,r){return"string"==typeof r.error?void y(r.error):(BabblingBrook.Client.User.Rings.push(r.ring),"member"===e?d(r.ring):u(r.ring),void n.slideUp())},_=function(){var e=jQuery(this),n=e.parent().parent(),r=e.attr("data-ring-username"),i=e.attr("data-ring-domain");e.addClass("text-loading");var t=e.attr("data-type");return BabblingBrook.Library.post("/"+r+"/ring/acceptinvitation",{ring_domain:i,ring_username:r,type:t},b.bind(null,t,n),y),!1},v=function(e,n){if(jQuery("#search_rings").hasClass("hide")===!1)return jQuery("#selector_member_ring_user").slideUp(250,function(){jQuery("#join_rings").text("Search for rings to join"),jQuery("#search_rings").empty().addClass("hide")}),!1;{var r=jQuery("#search_rings"),i=[{name:"View",onReady:function(e,n){var r;r=n.domain!==window.location.host?"http://"+n.domain+"/"+n.username+"/profile":"/"+n.username+"/profile",jQuery(".selector-action-view",e).attr("href",r)}}];new BabblingBrook.Client.Component.Selector("user","member_ring_user",r,i,{onReady:function(e){n===!0?BabblingBrook.Client.Core.Ajaxurl.changeUrl(window.location.href,"BabblingBrook.Client.Page.Ring.Menu.reconstructRingSearch",document.title,[e]):n=!0},user_type:"ring",only_joinable_rings:!0},e)}return r.slideDown(250,function(){r.removeClass("hide"),jQuery("#join_rings").text("Close ring search")}),!1};return{reconstructRingSearch:function(e){v(e,!1)},construct:function(){e=r(),"undefined"==typeof e.to&&(e.to=""),BabblingBrook.Library.post("superinvitors",{},function(e){n=e,a()}),g(),jQuery("#join_rings").click(v.bind(null,1,!0))}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.Ring.Menu.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/Ring/Menu.js.map