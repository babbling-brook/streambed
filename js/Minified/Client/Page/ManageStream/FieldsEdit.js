"object"!=typeof BabblingBrook.Client.Page.ManageStream&&(BabblingBrook.Client.Page.ManageStream={}),BabblingBrook.Client.Page.ManageStream.FieldUpdate=function(e,a){"use strict";var r=function(a){jQuery(".ajax-loading-container",e).removeClass("hide"),jQuery(".ajax-loading-container>div",e).attr("title",a).addClass("ajax-loading-error").removeClass("ajax-loading")},l=function(){jQuery(".add-list-item",e).click(function(){e.addClass("block-loading"),jQuery(".add-list-item-error",e).html("").addClass("hide");var l=jQuery("#field_url").val()+"addlistitem";BabblingBrook.Library.post(l,{field_id:a,new_list_item:jQuery(".new-list-item",e).val()},function(a){if("boolean"!=typeof a.success){var t="Data returned from "+l+" is invalid.";return console.error(t),void r(t)}if(a.success===!1)return jQuery.each(a.errors,function(a,r){jQuery(".add-list-item-error",e).append(r+"<br/>").removeClass("hide")}),void jQuery(".add-list-item-error",e).removeClass("hide");var i=jQuery(".list-items",e);i.append(jQuery("<option></option>").val(a.list_id).html(jQuery(".new-list-item",e).val())),i.append(i.find("option").sort(function(e,a){return e.text<a.text?-1:e.text===a.text?0:1})),jQuery(".new-list-item",e).val(""),e.removeClass("block-loading")})})},t=function(){jQuery(".remove-list-item",e).click(function(){var l=jQuery(".list-items option:selected",e).size();if(0!==l){var t=0,i=jQuery("#field_url").val()+"removelistitem";e.addClass("block-loading");var o=!1;jQuery(".list-items option:selected",e).each(function(){if(o===!0)return!1;var n=jQuery(this);BabblingBrook.Library.post(i,{field_id:a,list_item_to_delete:n.text()},function(a){if("boolean"!=typeof a.success){var u="Data returned from "+i+" is invalid.";return console.error(u),r(u),void(o=!0)}n.remove(),t++,t===l&&e.removeClass("block-loading")})})}})},i=function(a){var r;if(a>""){switch(a){case"13":r=jQuery(".value-updown",e).html();break;case"14":r=jQuery(".value-linear",e).html();break;case"15":r=jQuery(".value-log",e).html();break;case"16":r=jQuery(".value-text",e).html();break;case"24":r=jQuery(".value-stars",e).html();break;case"46":r=jQuery(".value-button",e).html();break;default:r="Select type of value:"}jQuery("select.value dt span",e).html(r)}},o=function(a){var l=jQuery("#field_url").val()+"update";e.addClass("block-loading"),BabblingBrook.Library.post(l,a,function(a){if(jQuery(".error",e).html("").addClass("hide"),"boolean"!=typeof a.success){var t="Data returned from "+l+" is invalid.";return console.error(t),void r(t)}a.success===!1&&jQuery.each(a.errors,function(a,r){a=a.replace(/_/g,"-"),jQuery("."+a+"-error",e).html(r).removeClass("hide")}),e.removeClass("block-loading")})},n=function(){"just_text"===jQuery(".text-type",e).val()?jQuery(".text-filter",e).prop("disabled",!1):jQuery(".text-filter",e).prop("disabled",!0)},u=function(){jQuery(".text-filter").change(function(a){"more"===a.target.value?jQuery(".regex-rows",e).removeClass("hide"):(jQuery(".regex-rows",e).addClass("hide"),jQuery(".text-regex-error",e).val(""),jQuery(".text-regex",e).val(""))}),n(),jQuery(".text-type",e).change(n),jQuery(".field-label, .text-max, .text-required, .text-filter, .text-regex, .text-regex-error, .text-type",e).change(function(){var r={type:"textbox",field_id:a,label:jQuery(".field-label",e).val(),text_type:jQuery(".text-type",e).val(),max_size:jQuery(".text-max",e).val(),required:jQuery(".text-required",e).is(":checked"),filter:jQuery(".text-filter",e).val(),regex:jQuery(".text-regex",e).val(),regex_error:jQuery(".text-regex-error",e).val()};o(r)})},s=function(){jQuery(".field-label, .link-required",e).change(function(){var r={type:"link",field_id:a,label:jQuery(".field-label",e).val(),required:jQuery(".link-required",e).val()};o(r)})},d=function(){jQuery(".field-label, .checkbox-default",e).change(function(){var r={type:"checkbox",field_id:a,label:jQuery(".field-label",e).val(),checkbox_default:jQuery(".checkbox-default",e).val()};o(r)})},v=function(){l(),t(),jQuery(".field-label, .list-select-qty-min, .list-select-qty-max",e).change(function(){var r={type:"list",field_id:a,label:jQuery(".field-label",e).val(),select_qty_min:jQuery(".list-select-qty-min",e).val(),select_qty_max:jQuery(".list-select-qty-max",e).val()};o(r)})},c=function(){jQuery(".field-label, .list-select-qty-min, .list-select-qty-max",e).change(function(){var r={type:"openlist",field_id:a,label:jQuery(".field-label",e).val(),select_qty_min:jQuery(".list-select-qty-min",e).val(),select_qty_max:jQuery(".list-select-qty-max",e).val()};o(r)})},y=function(){jQuery("select.type",e).change(function(){e.addClass("block-loading");var l=jQuery("#field_url").val()+"typechanged",t=jQuery("select.type",e).val();BabblingBrook.Library.post(l,{field_id:a,type:t},function(a){if("boolean"!=typeof a.success){var i="Data returned from "+l+" is invalid.";return console.error(i),void r(i)}jQuery(".inner-field-container",e).html(a.html),jQuery(".field-type",e).val(t),x(),e.removeClass("block-loading")})})},f=function(){jQuery(".remove-value-list-item",e).click(function(){var l=jQuery(".value-list-items option:selected",e).size();if(0!==l){var t=0,i=jQuery("#field_url").val()+"removevaluelistitem";e.addClass("block-loading");var o=!1;jQuery(".value-list-items option:selected",e).each(function(){if(o===!0)return!1;var n=jQuery(this);BabblingBrook.Library.post(i,{field_id:a,take_value_list_id:n.val()},function(a){if("boolean"!=typeof a.success){var u="Data returned from "+i+" is invalid.";return console.error(u),r(u),void(o=!0)}n.remove(),t++,t===l&&e.removeClass("block-loading")})})}})},m=function(){jQuery(".add-value-list-item",e).click(function(){if(""!==jQuery(".new-value-list-item",e).val()){e.addClass("block-loading"),jQuery(".add-value-list-item-error",e).html("").addClass("hide");var l=jQuery("#field_url").val()+"addvaluelistitem";BabblingBrook.Library.post(l,{field_id:a,new_value_list_item:jQuery(".new-value-list-item",e).val()},function(a){if("boolean"!=typeof a.success){var t="Data returned from "+l+" is invalid.";return console.error(t),void r(t)}if(a.success===!1)return jQuery.each(a.errors,function(a,r){jQuery(".add-value-list-item-error",e).append(r+"<br/>").removeClass("hide")}),void jQuery(".add-value-list-item-error",e).removeClass("hide");var i=jQuery(".value-list-items",e);i.append(jQuery("<option></option>").val(a.take_value_list_id).html(a.value+" "+jQuery(".new-value-list-item",e).val())),jQuery(".new-value-list-item",e).val(""),e.removeClass("block-loading")})}})},j=function(){jQuery(".value-min-row",e).addClass("hide"),jQuery(".value-max-row",e).addClass("hide"),jQuery(".value-rhythm-row",e).addClass("hide"),jQuery(".value-min",e).val(""),jQuery(".value-max",e).val(""),jQuery(".value-rhythm",e).val(""),jQuery(".value-list-remove-item-row",e).addClass("hide"),jQuery(".value-list-add-item-row",e).addClass("hide")},Q=function(){jQuery(".value-max-row",e).removeClass("hide");var a=jQuery("select.value",e).attr("data-value-id");"24"!==a&&"46"!==a?jQuery(".value-min-row",e).removeClass("hide"):(jQuery(".value-min-row",e).addClass("hide"),jQuery(".value-min-row input.value-min",e).val(0)),jQuery(".value-rhythm-row",e).addClass("hide"),jQuery(".value-rhythm",e).val(""),jQuery(".value-list-remove-item-row",e).addClass("hide"),jQuery(".value-list-add-item-row",e).addClass("hide")},h=function(){jQuery(".value-min-row",e).addClass("hide"),jQuery(".value-max-row",e).addClass("hide"),jQuery(".value-rhythm-row",e).removeClass("hide"),jQuery(".value-min",e).val(""),jQuery(".value-max",e).val(""),jQuery(".value-list-remove-item-row",e).addClass("hide"),jQuery(".value-list-add-item-row",e).addClass("hide")},b=function(){jQuery(".value-min-row",e).addClass("hide"),jQuery(".value-max-row",e).addClass("hide"),jQuery(".value-rhythm-row",e).addClass("hide"),jQuery(".value-list-remove-item-row",e).removeClass("hide"),jQuery(".value-list-add-item-row",e).removeClass("hide"),m(),f()},p=function(){if(jQuery(".field-row-value-options",e).hasClass("hide")===!0)return void b();var a=jQuery(".value-options option:selected",e).val();switch(a){case"17":j();break;case"18":Q();break;case"19":j();break;case"20":h();break;case"21":j()}},g=function(){jQuery(".value-options option",e).removeClass("hide");var a=jQuery("select.value option:selected",e).attr("data-value-id"),r=jQuery(".value-options option:selected",e).attr("value");if("148"===a)jQuery(".value-options option[value=18]",e).attr("selected","selected"),jQuery(".field-row-value-options",e).addClass("hide");else if("14"===a||"15"===a||"24"===a||"46"===a)jQuery(".field-row-value-options",e).removeClass("hide"),jQuery(".value-options option[value=17]",e).remove(),"17"===r&&jQuery(".value-options",e).val("18");else{jQuery(".field-row-value-options",e).removeClass("hide");var l=jQuery(".value-options option[value=17]",e);if(0===l.length){var t=jQuery("#stream_fileds_edit_any_value_template>option").clone();jQuery(".value-options",e).prepend(t)}}jQuery(".value-options",e).change(function(){p()})},_=function(){var l=e.index()+1;2===l&&jQuery(".value-who-can-edit-row",e).addClass("hide"),jQuery(".who-can-take",e).change(function(){e.addClass("block-loading");var l=jQuery("#field_url").val()+"whocantakechanged",t=this.value;BabblingBrook.Library.post(l,{field_id:a,who_can_take:t},function(a){if("boolean"!=typeof a.success){var t="Data returned from "+l+" is invalid.";return console.error(t),void r(t)}e.removeClass("block-loading")})})},k=function(){var l=jQuery("select.value option:selected",e).attr("data-value-id");l>""&&i(l),_(),jQuery("select.value",e).change(function(){e.addClass("block-loading");var l=jQuery("#field_url").val()+"valuetypechanged",t=jQuery("option:selected",this).attr("data-value-id");BabblingBrook.Library.post(l,{field_id:a,value_id:t},function(a){if("boolean"!=typeof a.success){var i="Data returned from "+l+" is invalid.";return console.error(i),void r(i)}jQuery("select.value",e).attr("data-value-id",t),g(),p(),e.removeClass("block-loading")})})},C=function(){k(),g(),p(),jQuery(".field-label, .value-options, .value-rhythm, .value-max, .value-min",e).change(function(){var r={type:"value",field_id:a,label:jQuery(".field-label",e).val(),value_type:jQuery("select.value",e).attr("data-value-id"),value_option:jQuery(".value-options",e).val(),value_min:jQuery(".value-min",e).val(),value_max:jQuery(".value-max",e).val(),value_rhythm:jQuery(".value-rhythm",e).val()};o(r)})},x=function(){if(""===jQuery(".field-label",e).val()){var r=jQuery("h4#stream_field_"+a).text().trim();jQuery(".field-label",e).val(r)}jQuery(".field-label",e).change(function(){var e=jQuery(this).val();if(""!==e){var r=jQuery("h4#stream_field_"+a),l=r.html(),t=l.substr(0,l.lastIndexOf("</span>")+7);r.html(t+e)}}),y();var l=jQuery(".field-type",e).val();switch(l){case"textbox":u();break;case"link":s();break;case"checkbox":d();break;case"list":v();break;case"openlist":c();break;case"value":C()}e.removeClass("block-loading")};x()},BabblingBrook.Client.Page.ManageStream.FieldUpdate.globalFieldEvents=function(){"use strict";jQuery(document).on("click",function(e){var a=jQuery(e.target);a.parents().hasClass("type")===!0&&a.is("li")===!1?jQuery(".type dd ul").toggleClass("hide"):jQuery(".type dd ul").addClass("hide"),a.parents().hasClass("value-dd")===!0&&a.is("li")===!1?jQuery("select.value dd ul").toggleClass("hide"):jQuery("select.value dd ul").addClass("hide")}),jQuery('.field-filter option[value="more"]').css("font-weight","bold")},BabblingBrook.Client.Page.ManageStream.FieldsEdit=function(){"use strict";var e=!1,a=function(e){"undefined"==typeof e&&(e=0);var a=!1;jQuery("#stream_fields h3").click(function(e){a&&(e.stopImmediatePropagation(),e.preventDefault(),a=!1)}),jQuery("#stream_fields").accordion({header:".stream_field_header",autoHeight:!1,heightStyle:"content"})},r=function(){jQuery("#stream_fields .sort-up img").removeClass("hide"),jQuery("#stream_fields .sort-down img").removeClass("hide"),jQuery("#stream_fields .sort-up:nth(2) img").addClass("hide"),jQuery("#stream_fields .sort-down:last img").addClass("hide")},l=function(e,a){var r=e.parentNode,l=e.nextSibling===a?e:e.nextSibling;a.parentNode.insertBefore(e,a),r.insertBefore(a,l)},t=function(a,t){var i=jQuery("#otf_container_"+t);i.addClass("block-loading"),BabblingBrook.Library.post(jQuery("#field_url").val()+a,{field_id:t},function(a){l(document.getElementById("otf_container_"+t),document.getElementById("otf_container_"+a.switch_id)),r(),jQuery("#otf_container_"+t+" .ajax-loading-container").addClass("hidden"),i.removeClass("block-loading"),e=!1})},i=function(){var a;jQuery(".sort-up img").click(function(){e!==!0&&(e=!0,jQuery(this).parent().parent().find(".ajax-loading").removeClass("hide"),a=jQuery(this).parent().find(".value").text(),t("moveup",a))}),jQuery(".sort-down img").click(function(){e!==!0&&(e=!0,jQuery(this).parent().parent().find(".ajax-loading").removeClass("hide"),a=jQuery(this).parent().find(".value").text(),t("movedown",a))}),jQuery(".delete img").click(function(){if(e!==!0){e=!0;var a=jQuery(this),l=a.parent().find(".value").text(),t=jQuery("#otf_container_"+l);t.addClass("block-loading"),BabblingBrook.Library.post(jQuery("#field_url").val()+"delete",{field_id:l},function(){r(),t.removeClass("block-loading"),e=!1,t.remove()})}})},o=function(){jQuery("#add_new_field").click(function(){BabblingBrook.Library.post(jQuery("#field_url").val()+"create",{stream_extra_id:jQuery("#StreamExtra_stream_extra_id").val()},function(e){if(null!==e&&"object"==typeof e){jQuery("#stream_fields").accordion("destroy"),jQuery("#stream_fields h3").unbind("click"),jQuery("#stream_fields").append(e.html);var l=jQuery("#stream_fields .field-container").length;a(l-1),r(),i();{var t=jQuery("#otf_container_"+e.id);new BabblingBrook.Client.Page.ManageStream.FieldUpdate(t,e.id)}}},function(e,a){console.error(a)})})},n=function(e){throw e},u=function(){var e=BabblingBrook.Library.changeUrlAction(window.location.pathname,"deleteallownerposts");BabblingBrook.Library.post(e,{},function(e){e.success===!1?n(e.error):BabblingBrook.Client.Core.Ajaxurl.redirect(window.location.pathname)},n.bind("There was a server error when requesting to delete all owner posts."))};return{construct:function(){a(),o(),i(),r(),jQuery("#delete_all_posts").click(u),jQuery(".field-container").each(function(){var e=jQuery(this),a=e.attr("id");a=a.substr(a.lastIndexOf("_")+1);new BabblingBrook.Client.Page.ManageStream.FieldUpdate(e,a)}),BabblingBrook.Client.Page.ManageStream.FieldUpdate.globalFieldEvents()}}}(),jQuery(function(){"use strict";BabblingBrook.Client.Page.ManageStream.FieldsEdit.construct()});
//# sourceMappingURL=/js/Minified/Client/Page/ManageStream/FieldsEdit.js.map