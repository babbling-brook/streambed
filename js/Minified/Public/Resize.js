"object"!=typeof BabblingBrook&&(BabblingBrook={}),"object"!=typeof BabblingBrook.Public&&(BabblingBrook.Public={}),BabblingBrook.Public.Resize=function(){"use strict";var e,n=function(){},o=function(){document.getElementById("small_screen_menu").onclick=function(){document.getElementById("top_nav_list").classList.toggle("small-screen-menu");for(var e=document.getElementById("top_nav_list"),n=0;n<e.childNodes.length;n++)"LI"===e.childNodes[n].tagName&&"small_screen_menu"!==e.childNodes[n].id&&e.childNodes[n].classList.toggle("small-screen-menu");return!1}};return{construct:function(){o(),window.onresize=function(){"number"==typeof e&&clearTimeout(e),e=setTimeout(function(){n()},25)}}}}();
//# sourceMappingURL=/js/Minified/Public/Resize.js.map