/*
 * This file creates a fake firebug console object to prevent errors if firebug is not open
 * For live, all firebug commands should be removed via the minify script
 * 
 */
//if(typeof console == "undefined"){
    console = {
        log : function(){
        },

        debug : function(){
        },
        
        error : function(){
        },

        dir : function(){
        },

        time : function(){
        },

        timeEnd : function(){
        },

        info : function(){
        },

        warn : function(){
        },

        profile : function(){
        },

        profileEnd : function(){
        },

        trace : function(){
        },

        group : function(){
        },

        groupEnd : function(){
        },

        dirxml : function(){
        }

    };
//}