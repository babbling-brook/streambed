"object"!=typeof BabblingBrook.Client.Page.ManageRhythm&&(BabblingBrook.Client.Page.ManageRhythm={}),BabblingBrook.Client.Page.ManageRhythm.Update=function(){"use strict";var e=function(){jQuery("#rhythm_status_actions .publish").addClass("hide"),jQuery("#rhythm_status_actions .deprecate").removeClass("hide"),jQuery("#rhythm_status_actions .delete").addClass("hide"),jQuery("#rhythm_status").text("Public"),jQuery("#RhythmExtra_description").attr("disabled","disabled"),jQuery("#rhythm_cats").attr("disabled","disabled"),jQuery(".cm-s-default").removeClass("cm-s-default").addClass("cm-s-disabled"),v()},r=function(){jQuery("#rhythm_status_actions .publish").removeClass("hide"),jQuery("#rhythm_status_actions .deprecate").addClass("hide"),jQuery("#rhythm_status_actions .edit").addClass("hide"),jQuery("#rhythm_status").text("Deprecated"),v()},a=function(e){var r=jQuery("#rhythm_deleted_template").clone();jQuery(".deleted-rhythm-name",r).text(e.name),jQuery("#sidebar #versions").prev().remove(),jQuery("#sidebar #versions").remove(),jQuery("#sidebar_update_rhythm").remove(),jQuery("#sidebar_view_rhythm").remove(),jQuery("#rhythm_form").html(r.html())},t=function(t,n){jQuery("#ajax_load").addClass("ajax-loading");var i=jQuery("#RhythmExtra_rhythm_extra_id").val();BabblingBrook.Library.post(jQuery("#ajax_url").val(),{rhythm_extra_id:i,action:n},function(t){if(jQuery("#ajax_load").removeClass("ajax-loading"),null===t||"object"!=typeof t)return void console.error("Error publishing Rhythm: No data returned");if("undefined"!=typeof t.error)return void console.error("Error "+n+" Rhythm: "+t.error);switch(n){case"publish":e(t);break;case"deprecate":r(t);break;case"delete":a(t)}})},n=function(){jQuery("#rhythm_status_actions .publish").on("click",function(){t(this,"publish")})},i=function(){jQuery("#rhythm_status_actions .deprecate").on("click",function(){t(this,"deprecate")})},o=function(){jQuery("#rhythm_status_actions .delete").on("click",function(){confirm("Are you sure? Deleted Rhythms are not recoverable.")&&t(this,"delete")})},s=function(){jQuery("#duplicate").click(function(){jQuery("#duplicate_loading").css({visibility:"visible"}),BabblingBrook.Library.post(window.location.pathname,{duplicate:!0,duplicate_name:jQuery("#duplicate_name").val()},function(e){if("undefined"!=typeof e.error)return jQuery("#duplicate_error").html(e.error).removeClass("hide"),void jQuery("#duplicate_success").addClass("hide");if(null!==e.url){var r=jQuery("#rhythm_duplicated_template").clone();jQuery(".duplicated-rhythm-url",r).attr("href",e.url),jQuery("#duplicate_success").html(r.html()).removeClass("hide"),jQuery("#duplicate_error").addClass("hide")}jQuery("#duplicate_loading").css({visibility:"hidden"})},"json")})},u=function(){jQuery("#new_version").click(function(){return jQuery("#new_version_loading").css({visibility:"visible"}),BabblingBrook.Library.post(window.location.pathname,{new_version:jQuery("#version option:selected").val()},function(e){if("undefined"!=typeof e.error&&(jQuery("#new_version_error").html(e.error).removeClass("hide"),jQuery("#new_version_success").addClass("hide")),"undefined"!=typeof e.url){var r=jQuery("#new_rhythm_version_template").clone();jQuery(".new-rhythm-version-url",r).attr("href",e.url),jQuery("#new_version_success").html(r.html()).removeClass("hide"),jQuery("#new_version_error").addClass("hide")}jQuery("#new_version_loading").css({visibility:"hidden"})},"json"),!1})},d=function(e,r,a){if("undefined"!=typeof a.errors)jQuery("#add_parameter_error>td").empty(),jQuery.each(a.errors,function(e,r){jQuery("#add_parameter_error>td").append("<div>"+r+"</div>")}),jQuery("#add_parameter_error").removeClass("hide");else{jQuery("#add_parameter_error").addClass("hide");var t=jQuery("#parameter_row_template>tbody>tr").clone();jQuery(".rhythm-param-name>input",t).val(e),jQuery(".rhythm-param-hint>textarea",t).val(r),jQuery("#rhythm_parameters>tbody").append(t),jQuery("#new_param_name").val(""),jQuery("#new_param_hint").val(""),t.find(".rhythm-param-original-name>input").val(e)}jQuery("#add_new_parameter").removeClass("block-loading")},l=function(){var e=BabblingBrook.Library.changeUrlAction(window.location.href,"addparameter");e=BabblingBrook.Library.extractPath(e),jQuery("#add_new_parameter").addClass("block-loading"),BabblingBrook.Library.post(e,{name:jQuery("#new_param_name").val(),hint:jQuery("#new_param_hint").val()},d.bind(null,jQuery("#new_param_name").val(),jQuery("#new_param_hint").val()))},h=function(){jQuery("#no_rhythm_parameters").addClass("hide"),jQuery("#rhythm_parameters").removeClass("hide"),jQuery("#open_new_parameter").addClass("hide")},y=function(e,r){"undefined"!=typeof r.errors?c(e,r):(jQuery("td",e).fadeOut(250),e.next().hasClass("parameter-row-error")===!0&&e.next().fadeOut(250)),jQuery(this).removeClass("block-loading")},m=function(){var e=BabblingBrook.Library.changeUrlAction(window.location.href,"removeparameter");e=BabblingBrook.Library.extractPath(e),jQuery(this).addClass("block-loading");var r=jQuery(this).parent().parent();BabblingBrook.Library.post(e,{name:r.find(".rhythm-param-name>input").val()},y.bind(null,r))},c=function(e,r){e.next().hasClass("parameter-row-error")===!0&&e.next().remove();var a=jQuery("#parameter_row_error_template>tbody>tr").clone();jQuery.each(r.errors,function(e,r){jQuery("td",a).append("<div>"+r+"</div>")}),e.after(a)},p=function(e,r,a){"undefined"!=typeof a.errors?c(e,a):(e.next().hasClass("parameter-row-error")===!0&&e.next().remove(),e.find(".rhythm-param-original-name>input").val(r)),jQuery(".rhythm-param-name>input, .rhythm-param-hint>textarea",e).removeClass("block-loading")},_=function(){var e=jQuery(this).parent().parent(),r=BabblingBrook.Library.changeUrlAction(window.location.href,"updateparameter");r=BabblingBrook.Library.extractPath(r),jQuery(".rhythm-param-name>input, .rhythm-param-hint>textarea",e).addClass("block-loading"),BabblingBrook.Library.post(r,{original_name:e.find(".rhythm-param-original-name>input").val(),name:e.find(".rhythm-param-name>input").val(),hint:e.find(".rhythm-param-hint>textarea").val()},p.bind(null,e,e.find(".rhythm-param-name>input").val()))},j=function(){jQuery("#open_new_parameter").click(h),jQuery("#add_new_parameter").click(l),jQuery("#rhythm_parameters tbody").on("click",".remove-parameter",m),jQuery("#rhythm_parameters tbody").on("change",".rhythm-param-name>input",_),jQuery("#rhythm_parameters tbody").on("change",".rhythm-param-hint>textarea",_)},b=function(){jQuery(".error").addClass("hide"),jQuery("#save_rhythm").addClass("button-loading");var e=BabblingBrook.Library.changeUrlAction(window.location.pathname,"updatejson");BabblingBrook.Library.post(e,{description:jQuery("#rhythm_description").val(),category:jQuery("#rhythm_category").val(),javascript:BabblingBrook.Client.Component.CodeMirror.getValue()},function(e){e.success!==!0&&jQuery.each(e.errors,function(e,r){jQuery("#rhythm_"+e+"_error").text(r).removeClass("hide")}),jQuery("#save_rhythm").removeClass("button-loading")})},v=function(){"Private"!==jQuery("#rhythm_status").text()&&(jQuery(".only-private").addClass("hide"),jQuery("#no_editing").removeClass("hide"))};return{construct:function(){n(),i(),o(),s(),u(),j(),v(),jQuery("#save_rhythm").click(b),BabblingBrook.Client.Component.CodeMirror.create()}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.ManageRhythm.Update.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/ManageRhythm/Update.js.map