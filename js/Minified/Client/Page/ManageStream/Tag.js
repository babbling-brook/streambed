"object"!=typeof BabblingBrook.Client.Page.ManageStream&&(BabblingBrook.Client.Page.ManageStream={}),BabblingBrook.Client.Page.ManageStream.Tag=function(){"use strict";return{construct:function(){jQuery("input.tag").each(function(){if(null!==jQuery(this).attr("thing")&&null!==jQuery(this).attr("thing_id")){var e=jQuery(this).attr("thing"),r=jQuery(this).attr("thing_id"),t=jQuery(this).attr("id");jQuery(this).before('<div id="'+t+'_loading" class="ajax-loading inline-block"></div>'),jQuery("#"+t+"_loading").addClass("hide"),jQuery(this).after('<div id="'+t+'_error" class="error"></div>'),jQuery("#"+t+"_error").addClass("hide");var a=0;jQuery(this).tokenInput("/site/tag/getlist",{allowNewValues:!0,prePopulateFromInput:!0,searchingText:"",hintText:"",deleteFull:!0,onNewTag:function(i){jQuery("#"+t+"_loading").removeClass("hide"),jQuery("#"+t+"_error").addClass("hide"),a++,BabblingBrook.Library.post("/site/tag/insert","tag="+i+"&thing="+e+"&thing_id="+r,function(e){e=JSON.parse(e),e.success!==!0&&jQuery("#"+t+"_error").text(e.success).removeClass("hide"),a--,0===a&&jQuery("#"+t+"_loading").addClass("hide")})},onDeleteTag:function(i){jQuery("#"+t+"_loading").removeClass("hide"),jQuery("#"+t+"_error").addClass("hide"),a++,BabblingBrook.Library.post("/site/tag/removebyname","tag="+i+"&thing="+e+"&thing_id="+r,function(e){e=JSON.parse(e),e.success!==!0&&jQuery("#"+t+"_error").html(e.success).removeClass("hide"),a--,0===a&&jQuery("#"+t+"_loading").addClass("hide")})}})}})}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.ManageStream.Tag.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/ManageStream/Tag.js.map