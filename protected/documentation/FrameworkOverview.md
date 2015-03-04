# Framework overview

This file explains where the various parts of the project are stored.

### Data stores and client sites.

The final version of StreamBed will be able to be used to create both datastores and client sites just by changing some
config settings.  This is not quite working yet, however the files are already split into separate locations. See the file structure below for details.

### Libraries

StreamBed uses a php backend based on the [Yii framework](http://www.yiiframework.com/) (version 1). This mostly just used for authentication and data transportation. Most of the heavy lifting happens in JavaScript.

Javascript is using a homebrew framework that is essentially based on the classic JavaScript module pattern. [jQuery](http://jquery.com/) and [jQueryUI](http://jqueryui.com/), [Code Mirror]() and [CKEditor](http://codemirror.net/) are available.

### File structure

```
/assets                         // This is a yii folder which is not being used. Just leave it alone.

/css                            // All CSS is in here except for theme CSS (/themes/[them-name]/css).
/css/Client                     // All CSS that is used by authenticated users is in here.
                                // Authenticated users may also use CSS in the /css/Shared folder.
/css/Client/Admin               // CSS that is only used for admin users.
/css/Client/Component           // CSS that is specific to a JavaScript component. 
                                // See /js/Client/Component for  the JavaScript that uses this CSS. 
                                // The filenames will match.
/css/Client/Core                // CSS that is specific to a JavaScript core module. 
                                // See  /js/Client/Core for the JavaScript that uses this CSS. 
                                // The filenames will match.
/css/Client/Layouts/ClientType/[client-type-name]/SiteFurniture.CSS    
                                // CSS that is specific to the client type that is
                                // being used. See /index.php, CLIENT_TYPE variable.
/css/Client/Page                // CSS that is speific to a particular view. 
                                // See /protected/views/Client/Page for the view files that use 
                                // these styles.
/css/Client/Client.css          // All base client CSS.

/css/Libraries                  // CSS that is required by external libraries.

/css/Minified                   // Minified css is placed in here.

/css/Public                     // All CSS that is used by unauthenticated users is in here.
                                // Unauthenticated users may also use CSS in 
                                // the /css/Shared folder.
/css/Public/Layouts/ClientType/[client-type-name]/SiteFurniture.CSS    
                                // Public CSS that is specific to the client type that is
                                // being used. See /index.php, CLIENT_TYPE variable.
/css/Public/Page                // CSS that is speific to a particular view. 
                                // See /protected/views/Public/Page for the view files that use 
                                // these styles.
/css/Public/public.css          // All base public CSS.

/css/Shared                     // CSS that is used by both authenticated and unauthenticated users.
/css/Shared/Component           // CSS that is specific to a JavaScript component. 
                                // See /js/Client/Component for the JavaScript that uses this CSS. 
                                // The filenames will match.
/css/Shared/Core                // CSS that is specific to a JavaScript core module. 
                                // See  /js/Client/Core for
                                // the JavaScript that uses this CSS. The filenames will match.
/css/Shared/Layouts/ClientType/[client-type-name]/SiteFurniture.CSS    
                                // CSS that is specific to the client type that is being used. 
                                // See the /index.php, CLIENT_TYPE constant.
/css/Shared/Page                // CSS that is speific to a particular view. 
                                // See /protected/views/Client/Page 
                                // for the view files that use these styles.

/css/Shared/main.css            // This is the ultimate base CSS file included on all pages.
/css/Shared/reset.css           // A reset stylesheet, also included on all pages.
/css/Shared/theme.css           // This is an empty placeholder file that is overridden by themes 
                                // for general theme styles.

/css/Tests                      // Any CSS relating to testing goes here.


/js                             // All JavaScript is in here except for theme 
                                // JavaScript (/themes/[them-name]/js).
/js/Client                      // JavaScript used by authenticated users on client websites.
                                // Client websites are composed of pages(also called views) 
                                // which access `core` modules that are loaded when the site first 
                                // loads and `component` modules which have to be loaded
                                // by that pages view file (/protected/views/Client/Page).
/js/Client/Admin                // JavaScript that is only used by views accessed by admin users.
/js/Client/Component            // Component JavaScript (see /js/Client above).
/js/Client/Core                 // Core JavaScript (see /js/Client above).
/js/Client/Page                 // Page/View JavaScript (see /js/Client above).
/js/Client/ready.js             // Initialises the client websites JavaScript.

/js/Domus                       // JavaScript used by the `domus` subdomain of datastores.
/js/Domus/ready.js              // Initialises the domus domains JavaScript.

/js/Filter                      // JavaScript used by the `filter` subdomain of datastores. 
                                // Runs `filter` rhythms.

/js/Kindred                     // JavaScript used by the `kindred` subdomain of datastores. 
                                // Runs `kindred` rhythms.

/js/Minified                    // All minified JavaScript is kept here. It is automatically generated.

/js/Public                      // JavaScript used by views for unauthenticated users on client websites.

/js/Ring                        // JavaScript used by the `ring` subdomain of datastores. 
                                // Runs `ring` rhythms.

/js/Scientia                    // JavaScript used by the `scientia` subdomain of datastores.
/js/Scientia/ready.js           // Initialises the scientia domains JavaScript.

/js/Shared                      // JavaScript that is shared across all domains.
/js/Shared/Library.js           // A collection of generic functions for the Babbling Brook protocol.
/js/Shared/Models.js            // A collection of models for testing Babbling Brook objects.
/js/Shared/Test.js              // Used to test data types that are being passed between 
                                // Babbling Brook domains.

/js/Suggestion                  // JavaScript used by the `suggestion` subdomain of datastores.
                                // Runs `suggestion` rhythms.

/js/jquery_pluggins             // jQuery pluggins that are used by client websites.

/js/resources                   // Extreanl libraries that are used by Babbling Brook sites 
                                // such as jQuery and ckeditor.

/protected                      // All server side code other than theme code is found here.
                                // This folder is inaccessible from client computers, 
                                // all code is accessed via the root index.php file.

/protected/config               // All configuration options not found in index.php are in this folder.
/protected/type                 // Configuration options for different types of client website. 
                                // Only the config file for the type set in index.php is loaded.
/protected/client.php           // Config options that are specific to client websites.
/protected/domus.php            // Config options that are specific to the domus sub domain.
/protected/filter.php           // Config options that are specific to the filter sub domain.
/protected/kindred.php          // Config options that are specific to the kindred sub domain.
/protected/main.php             // The main config file, included by all domains.
                                // Includes path routing.
/protected/ring.php             // Config options that are specific to the ring sub domain.
/protected/scientia.php         // Config options that are specific to the scientia sub domain.
/protected/server.php           // Config options that are specific to the server instance 
                                // rather than a domain.
                                // Such as database connection details, if minification is turned on etc.
/protected/suggestion.php       // Config options that are specific to the suggestion sub domain.

/protected/controllers          // StreamBed uses an MVC pattern. All controllers are found here split 
                                // into folders for each sub domain.

/protected/cookiepath           // Session data is stored here.

/protected/data                 // Database schema is stored here for the installation script.

/protected/documentation        // Project documentation.

/protected/extendedyii          // Extended Yii classes.

/protected/extensions           // Yii pluggins.

/protected/filters              // Filters used by controllers for validation.

/protected/helpers              // General helper classes.

/protected/helptext             // The help text that is displayed in pop ups on client websites.

/protected/libraries            // Additional PHP libraries.

/protected/models               // All code dealing with database interactions and modeling of data.
                                // All files in this folder are models of db tables and static 
                                // functions that deal with only that table. 
                                // (This folder could really be refactored to place the models
                                // in a sub folder.)
/protected/models/babblingbrook // Form models that represent Babbling Brook data structures.
/protected/models/forms         // Forms used for validating data submitted to the server.
                                // (Usually instantiated by controllers.)
/protected/models/log           // Database models for tables in the log database.
/protected/models/multi         // All interaction that deals with multiple database tables/models.
                                // Anything that requires a transaction goes in here.
/protected/models/setup         // Database setup process. Only called when the site is initialised.
/protected/models/test          // All code that deals with the test database.
/protected/transactions         // This code is being refactored into /protected/models/multi

/protected/runtime              // Yii runtime code.

/protected/tests                // Server side tests. This code is currently being refactored.
/protected/tests/codecept       // All tests are currently being refactored to be run using codecept
                                // These are currently incomplete.


.............................


/protected/views                    // HTML views that are called by the controllers.
/protected/views/Client             // Views for authenticated users on client websites.
                                    // (also see /protected/views/Shared)
/protected/views/Client/Admin       // Views that are only used by admins.
/protected/views/Client/Component   // HTML templates for /js/Client/Component modules.
/protected/views/Client/Core        // HTML templates for /js/Client/Core modules.
/protected/views/Client/Layouts     // Site furniture views.
/protected/views/Client/Layouts/ClientType     
                                    // Site furniture views that are for a specific type of client.
/protected/views/Client/Page        // Views for specific pages.

/protected/views/Domus              // Views for the domus sub domain of datastores.
/protected/views/Filter             // Views for the filter sub domain of datastores.
/protected/views/Kindred            // Views for the kindred sub domain of datastores.
/protected/views/Public             // Views for for unauthenticated users on client websites.
                                    // (also see /protected/views/Shared)
/protected/views/Public/Layouts     // Site furniture views.
/protected/views/Public/Layouts/ClientType     
                                    // Site furniture views that are for a specific type of client.
/protected/views/Public/Page        // Views for specific pages.
/protected/views/Ring               // Views for the ring sub domain of datastores.
/protected/views/Scientia           // Views for the scientia sub domain of datastores.
/protected/views/Shared             // Views for the client websites that are shared between
                                    // authenticated and unauthenticated users.
/protected/views/Shared/Layouts     // Site furniture views.
/protected/views/Shared/Layouts/ClientType     
                                    // Site furniture views that are for a specific type of clien.
/protected/views/Shared/Page        // Views for specific pages.
/protected/views/Suggestion         // Views for the suggestion sub domain of datastores.

/protected/yii                      // The yii framework.

/themes/                            // Client site themes.
                                    // More than just a skin, can include additional modules to change 
                                    // funcitonality. Each folder is a theme name.
/themes/[them-name]/css             // Theme css. If the path and filename match a base css file then
                                    // the base file is not included.
/themes/[theme-name]/css/Shared/theme.css 
                                    // top level theme css without overriding the base main.css
/themes/[theme-name]/js             // Theme css. If the path and filename match a base js file
                                    // then the base file is not included.
/themes/[theme-name]/views          // Theme views. If the path and filename match a base view file 
                                    // then the base file is not included.

/index.php                          // Bootstrap script. The access point for all server side code.
```
