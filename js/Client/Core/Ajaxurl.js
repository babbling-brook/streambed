/*
 * A lightweight ajax url pluggin for navigating a site entirely using ajax
 * @version 0.0.1
 * @date August 08/11/2010
 * @author Sky Wickenden
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Instructions:
 * Include in the page head
 * Call it using :
     jQuery(document).ready(function() {
        jQuery('body').ajaxURL({
            host: 'cobaltcascade.localhost'
        });
    });
 * You can also include any other custom options. Details below.
 *
 * All page requests are sent via POST and will include ajaxurl = true as a value so that you can
 * differrentiate between requests on the server. (This can be changed in the options)
 * The response must be in the form of a JSON object formated as follows: (this is slightly
 * odd due to Yii's automatic Javascript and CSS objects)
    {
        'url' = 'relative/url/of/page',
        'title' : 'The Page Title,
        'css' : {
            'path/to/css/file1' : '',
            'path/to/css/file2' : '',
            ...
        }
        'script' : [
            'path/to/javascript/file1' : 'path/to/javascript/file1',
            'path/to/javascript/file2' : 'path/to/javascript/file2',
                ...
        ],
        'inlineScript' : '// Some inline javascript to be attatched to the page',
        'elements' : {
            'aJQuerySelector'=>'New Content',
            'anotherJQuerySelector'=>'New Content',
            ...
        }
    )
 *
 * All parts of the JSON are optional except for url
 *
 * NOTES:
 * Does not work with multipart forms. Suggest using another ajax plugin to upload forms
 * Does not work with SSL links
 */

// This needs to be here when the QqFileuploader is uploaded via ajax because Ajaxurl has strict mode enabled, making
// it impossible to create the document level variable QqFileuploader without a var statment.
if (typeof QqFileuploader !== "object") {
    QqFileuploader = {};
}
if (typeof window.CodeMirror === "undefined") {
    window.CodeMirror = {};
}


/**
 * @namespace Create a closure for the Jquery pluggin to prevent any conflicts
 * @package JS_Client
 */
BabblingBrook.Client.Core.Ajaxurl = (function () {
    'use strict';

    /**
     * Default options. Override these by passsing them into ajaxURL
     */
    var options = {
        // Print Debug information to Firebug console
        debug : false,
        // Host of the site containing url redirection
        host : 'example.com',
        // The jQuery selector of the page element that is displayed whilst a page is loading (It should default to off)
        loadingElement : '#ajax_load',
        // The class to add to the page loading element to make it visible
        loadingClass : 'ajax-loading',
        // ID of the page element that contains inline JavaScript, this should be a div, not the <script> tag
        inlineScript : 'inlineScript',
        // This value will be appended to the POST data of all submissions so that the server
        // can identify it as a request for JSON data and not a normal page.
        postIndicator : 'ajaxurl',
        // The default protocol
        protocol : 'http://'
    };
    // Update page using url hashes or html5 - detected in ajaxURL function
    var html5 = false;

    var ignore_hash_change = false;                // A flag that prevents extra reloads

    /**
     * @type {boolean} Chrome fires popstate on page load, we only want it if there is history to navigate
     * or we end up fetching the data twice which can result in unwanted behavior.
     */
    var enable_pop_state = false;

    // Define functions here to prevent circular references in jsLint.
    var displayError, updateURLHtml5, updateURLHash,
        updateContent, fetchContent, clickEvent, updateScript,
        bindEvents, convertUrlToObject, javascriptLoaded;

    /**
     * Incremented each time the page is redirected.
     *
     * This value can be used via getRedirectCount to detect if the page has been redirected since a request was
     * made. Preventing the page from reverting after a redirect.
     *
     * @type {number}
     */
    var redirect_count = 0;

    /**
     * Display an error message on screen for the user
     */
    displayError = function (message) {
        if(typeof message === 'undefined') {
            message = 'There has been a problem loading a page.';
        }
        console.error(message);
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });
        BabblingBrook.Client.Core.Ajaxurl.hidePageLoading();
        showErrorLoadingBorder();
    };

    var showErrorLoadingBorder = function () {
        jQuery('#content').addClass('error-loading-page');
    };

    /**
     * Updates the url and history object using html5.
     *
     * @param {string} url The new url.
     *
     * @return void
     */
    updateURLHtml5 = function (url) {
        ignore_hash_change = true;
        window.history.pushState('state', document.title, url);
    };

    /**
     * Updates the url and history object using a hash
     */
    updateURLHash = function (url) {
        ignore_hash_change = true;
        window.location.hash  = '!' + url;
    };


    /**
     * Update content received back from an ajax POST.
     *
     * @param {string} response OBJECT JSON response data.
     * @param {string} updateUrl BOOLEAN If the url needs updating (Sometimes not, eg on back button press).
     *
     * @return void
     */
    updateContent = function (response, updateUrl) {
        // check that the response is an object
        if (typeof response !== 'object') {
            displayError();
            console.error(response);
            return;
        }

        // Check url is present, everything else is optional
        if (typeof response.url === 'undefined') {
            displayError('No url present the response content.');
        }

        // Update the url
        if (html5 && updateUrl) {
            updateURLHtml5(response.url);
        } else if(updateUrl) {
            updateURLHash(response.url);
        }

        // if we have a new title use it
        if (typeof response.title === 'string') {
            document.title = response.title;
        }

        //Remove any scroll events on the previous page.
        jQuery(window).unbind('scroll');

        // Replace Elements
        // remove all jquery event handlers
        if (typeof response.elements === 'undefined') {
            return;
        }
        jQuery.each(response.elements, function(index, html) {
            //jQuery(index).unbind();
            // Display new content
            jQuery(index).html(html);
            // Rebind ajaxurl events
            bindEvents(jQuery(index));
        });
    };

    /**
     * Fetches new content.
     *
     * @param {string} url The relative url to fetch content for.
     * @param {string} postData to submit with the request as a serialized string.
     * @param {boolean} [updateUrl=true] If the url needs updating (Sometimes not, eg on back button press).
     * @param {function} [onDone] Function to call when the content has loaded.
     *
     * @return void
     */
    fetchContent = function (url, postData, updateUrl, onDone) {
        redirect_count++;
        console.log('Redirecting page from ' + window.location.pathname + ' to ' + url);
        if (typeof updateUrl === 'undefined') {
            updateUrl = true;
        }
        BabblingBrook.Client.Core.Ajaxurl.showPageLoading();
        // Fetch the results via jQuery
//        if (postData) {
//            postData += '&' + options.postIndicator + '=true';
//        } else {
//            postData = options.postIndicator + '=true';
//        }
        var post_data = {};
        post_data[options.postIndicator] = true;
        BabblingBrook.Library.post(
            url,
            post_data,
            function(response) {
                updateContent(response, updateUrl);
                updateScript(response);
                BabblingBrook.Client.Component.Resize.retest();
                BabblingBrook.Client.Component.Messages.fixHeight();
                BabblingBrook.Client.Component.Messages.minimizeMessage();
                BabblingBrook.Client.Component.Messages.showNext();
                BabblingBrook.Client.Core.Ajaxurl.hidePageLoading();
                if (typeof onDone === 'function') {
                    onDone();
                }
            },
            function(error_code, error_data) {
                var error_message = 'Url ( ' + url + ' ) did not load. HTTP Error : ' + error_code;
                displayError(error_message);
                BabblingBrook.Client.Core.Ajaxurl.hidePageLoading();
            }
        );
    };

    /**
     * Click event for urls that need to be captured and fetched via ajax
     * Sends the url via ajax and processes the results
     */
    clickEvent = function (event) {
        if (event.which === 3) {
            return;
        }

        // Ajaxurl interfers with jquery_ui buttons set code.
        if (jQuery(event.target).hasClass('ui-button-text') === true) {
            return;
        }

        var url = jQuery(event.target).attr('href');
        if (event.which === 2) {
            // Don't open new tabs with middle click if the link goes nowhere.
            if (url === '' || url === '#') {
                event.preventDefault();
            }
            return;
        }

        if (url === '' || url === '#') {
            return;
        }

        // Only click events on links and form submissions are processed.
        if(event.target.nodeName !== 'A' && event.target.tagName !== 'INPUT') {
            return true;
        }

        // Only submit button clicks are processed.
        if (event.target.tagName === 'INPUT' && jQuery(event.target).attr('type') !== 'submit') {
            return;
        }

        // If the user is not logged on then it needs to go through normally.
        if (typeof BabblingBrook.Client.User.username === 'undefined') {
            return true;
        }

        // Is the path a valid url for ajaxlink?
        var path = this.href;   // Default for forms
        if(event.target.nodeName === 'A') {
            path = event.target.href;
            // If path is not local then do furhter checks.
            if (/^\//.test(path) === false) {
                // If path is absolute and does not include the local domain then return.
                var regex = new RegExp('^http(s|)://' + options.host);
                if(regex.test(path) === false) {
                    return;
                }
            }
        }

        // Now a click event has fired we can enable html5 popState actions.
        enable_pop_state = true;
        event.preventDefault();
        if (options.debug) {
            if(event.target.tagName === 'A') {
                console.log('Click event for ' + event.currentTarget.href);
            } else {
                console.log('Click event for form submission');
            }
        }

        // If this is a form then prepare the data
        var postData;
        if(event.target.tagName === 'INPUT') {
            // Is this a valid form type
            var enctype = jQuery(event.target).attr('enctype');
            if(enctype === 'multipart/form-data') {
                console.error(
                    'Error: multipart forms not supported use something like the Ajax Upload plugin instead.'
                );
            }
            var jq_form = jQuery(event.target).closest("form")
            postData = jq_form.serialize();
            path = jq_form.attr('action');
            if(typeof path === 'undefined') {
                path = window.location;
            }
        }

        fetchContent(path, postData);

        //prevent the window from going to the new url
        return false;
    };

    /**
     * Bind ajaxurl to the jquery elments passed in.
     *
     * @param {object} An array of jQuery elements.
     *
     * @return void
     */
    bindEvents = function (jq_elements) {
        jQuery.each(jq_elements, function(i, jq_element) {
            var jq_this = jQuery(jq_element);
            //jq_this.bind('click', clickEvent); // now handled in a global .on event.
            jq_this.find('form').bind('submit', clickEvent);
        });
    };

    /**
     * Update scripts received back from an ajax POST
     * @param response OBJECT JSON response data
     */
    updateScript = function (response) {

        // Include any css files
        var url, possition;
        if (response.css && !jQuery.isEmptyObject(response.css)) {
            jQuery.each(response.css, function(pos, url) {
                if(jQuery('link[href*="' + url + '"]').length === 0) {
                    jQuery('link:last')
                        .after('<link rel="stylesheet" type="text/css" href="' + url + '" media="screen" />');
                }
            });
        }

        // Register any new inline script
        var inline = '';
        if(response.inlineScript) {
            jQuery.each(response.inlineScript, function(possition, script) {
                jQuery.each(script, function(j, code) {
                    inline += code + '\n';
                });
            });
        }
        // This will be added to the page after all script files have loaded.
        if (inline) {
            inline = '<script id=' + options.inlineScript + '"'
                + ' type="text/javascript">\n//<![CDATA[\n' + inline + '\n//]]>\n</script>\n';
        }

        // Include any javascript files
        // Once all files are loaded any inline script needs appending... hence the counting
        if (response.script && !jQuery.isEmptyObject(response.script)) {
            var loaded_count = 0;
            var count = response.script.length;
            jQuery.each(response.script, function(pos, url) {
                var js_object = convertUrlToObject(url);
                // If the script is not already on the page and the main object in the file does not
                // exist then load the relevant javascript file.
                // objects in the namespace BabblingBrook.Client are only loaded once. Subsequently their 'construct'
                // function is rerun. All other scripts are re-loaded with each request.
                if (js_object === false) {
                    jQuery.ajax(
                        {
                            url : url,
                            dataType : 'script',
                            async : false,  // Firefox does not always fire the construct method
                            success : function () {
                                loaded_count++;
                                javascriptLoaded(count, loaded_count, inline);
                            }
                        }
                    );
                } else {

                    // The constructor function may be nested in a container module.
                    // Recurisvely iterate down until one is found.
                    var runConstructor = function (js_object) {
                        if (typeof js_object.construct === 'function') {
                            js_object.construct();
                        } else {
                            jQuery.each(js_object, function (i, module) {
                                runConstructor(module);
                            });
                        }
                    };
                    runConstructor(js_object);

                    loaded_count ++;
                    javascriptLoaded(count, loaded_count, inline);
                }
            });
        } else if (inline) {
            jQuery('html').append(inline);
        }
    };

    /**
     * Converts a javascript url into the object represented by that url.
     *
     * This works because file paths are the same as object paths.
     *
     * @param {string} url The url to convert.
     *
     * @return object|boolean The object referenced in the url of false.
     */
    convertUrlToObject = function (url) {
        try {
            // remove any query string.
            if (url.indexOf('?') > 0) {
                url = url.substring(0, url.indexOf('?'));
            }
            url = url.substring(0, url.length - 3);   // remove the .js
            var obj_array = url.split('/');
            // Only client files are loaded. Other files must be loaded on first page load.
            if(obj_array[2] !== 'Client') {
                return false;
            }
            var obj = BabblingBrook.Client;
            var len = obj_array.length;
            for (var i=3; i<len; i++) {
                if(typeof obj_array[i] === 'string') {
                    if(typeof obj[obj_array[i]] === 'undefined') {
                        return false;
                    }
                    obj = obj[obj_array[i]];
                }
            }
            return obj;

        } catch(exception) {
            console.log(exception);
            console.error('failed to parse javascript url to object');
        }
    };

    /**
     * Called after a javascript file has loaded
     *
     * Checks if all files have loaded and inline script should be inserted.
     *
     * @param {number} The total number of scripts that are being loaded.
     * @param {number} The number of scripts that have been loaded so far.
     * @param {string} The inline code to run when the scripts have all been loaded.
     *
     * @return void
     */
    javascriptLoaded = function (total_scripts, loaded_count, inline) {
        if (total_scripts === loaded_count) {
            jQuery('html').append(inline);
            jQuery.ajaxSetup({ cache: false });
        }
    };

    /**
     * Callback for after the content has been reloaded in a pop event.
     *
     * @returns {undefined}
     */
    var onAfterPop = function() {
        if (history.state !== null && typeof history.state.on_load_function_name === 'string') {
            BabblingBrook.Library.executeFunctionByName(
                history.state.on_load_function_name,
                window,
                history.state.args
            );
        }
    };

    /**
     * Event handler for HTML5 url changes
     */
    var onPopEvent = function (event) {
        // Chrome fires pushState on page load, which we don't want.
        // Escape until at least one url change has happened.
        if(enable_pop_state === false) {
            return;
        }

        var url = window.location.href;
        fetchContent(url, null, false, onAfterPop);

    };

    return {

        /**
         * An Ajax URL loading pluggin.
         *
         * Overrides all local urls so that they are processed via AJAX.
         *
         * @param {object} my_options Overrides for the default options.
         * @param {object} apply_to_elements jQuery selector of objects to apply ajax url to.
         */
        setup : function (my_options, apply_to_elements) {
            BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
                // Override defaults with passed in options
                jQuery.extend(options, my_options);

                // Is htm5 pushstate available?
                if (window.history.pushState) {
                    html5 = true;
                    window.onpopstate = onPopEvent;
                } else {
                    console.error('Babbling Brook requires a browser with pushState functionality.');
                }

                // Add a click event to all selections that have been passed in
                bindEvents(apply_to_elements);
            });
        },

        /**
         * Redirect to the url provided.
         *
         * If the url is local then ajax url is used to redirect.
         * If the url is remote then the page is redirected.
         *
         * @param {string} url The url to redirect to. Must be absolute, but does not  require protocol.
         * @param {function} onLoaded An optional callback to call once the page has loaded.
         *      (Only fires if a local redirect)
         *
         * @returns {void}
         */
        redirect : function (url, onLoaded) {
            BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
                // This enables url formats:
                // http://domain/path
                // domain/path
                // /path
                if (url.substring(0, 1) === '/') {
                    url = window.location.hostname + url;
                }
                if (url.substring(0, 4) !== 'http') {
                    url = window.location.protocol + '//' + url;
                }

                var local = false;
                var host_length = window.location.host.length;
                if (url.substring(0, 7 + host_length) === 'http://' + window.location.host) {
                    local = true;
                    url = url.substring(7 + host_length);
                } else if (url.substring(0, 8 + host_length) === 'https://' + window.location.host) {
                    local = true;
                    url = url.substring(8 + host_length);
                } else if (url.substring(0, 1) === '/') {
                    local = true;
                } else if (url.length === host_length) {
                    local = true;
                    url = '/';
                }

                if (local === true) {
                    // Open using ajax.
                    fetchContent(url, '', true, function () {
                        window.scrollTo(0, 0);
                        if (typeof onLoaded === 'function') {
                            onLoaded();
                        }
                    });
                } else {
                    // Open in a new window/tab.
                    window.open('page.html');

                }
            });
        },

        /**
         * Getter for the redirect count
         *
         * @return {integer}
         */
        getRedirectCount : function () {
            return redirect_count;
        },

        /**
         * Change the url and add a history state without reloading the page.
         *
         * @param {string} url The url to change the page to.
         *      Can have the protocol or not.
         * @param {string} on_load_function_name The name of a function to call to redisplay the stored state.
         *      This will usually be the construct function for the current page.
         *      should include the full namespace and be in global scope.
         * @param {string} [title] New page title for the page.
         * @param {array} args An array of paramaters that will be sent to the function.
         *
         * @returns void
         */
        changeUrl : function (url, on_load_function_name, title, args) {
            if (typeof title !== 'string') {
                title = '';
            }
            var state = {
                on_load_function_name : on_load_function_name,
                args : args

            };

            url = BabblingBrook.Library.removeProtocol(url);
            url = window.location.protocol + '//' + url;
            window.history.pushState(state, title, url);

            if (title !== '') {
                document.title = title;
            }
        },

        /**
         * Show the ajax page loading indicator.
         *
         * This is a generic class so that it can be used when loading page elements.
         *
         * @param {string} [title] The title tag for the loading element.
         *
         * @return void
         */
        showPageLoading : function(title) {

            if(typeof title === 'undefined') {
                title = 'Loading';
            }

            jQuery('#content').addClass('page-loading').removeClass('error-loading-page');
        },

        /**
         * Show the ajax page loading indicator.
         *
         * This is a generic class so that it can be used when loading page elements.
         *
         * @return void
         */
        hidePageLoading : function() {
            jQuery('#content').removeClass('page-loading error-loading-page');;
        },

        /**
         * Register the global click event as quickly as possible after page load.
         *
         * @returns {undefined}
         */
        registerGlobalClickEvent : function () {
            // Using jQuery(document) and jquery('a') enables the detection of middle clicks using event.which
            jQuery(document).on('click', jQuery('a'), clickEvent);
        }
    };

}());
