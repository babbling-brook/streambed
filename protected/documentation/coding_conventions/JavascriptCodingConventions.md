# JavaScript Coding conventions

Use [JSLint](http://jslint.com/) with the following options turned off.
* Tolerate white space.
* Tolerate many var statements  per function.
* Tolerate ++ and --.
* Tolerate misordered definitions.
* Assume a browser.
* Tolerate continue.
* In the predefined box, put BabblingBrook, jQuery, console, document, window, confirm
* Set line length to 120.


In addition to the JSLint specification, please observe the following:

### Quotes

Always use single quotes unless there is good reason not to.


### String concatenation over multiple lines.

'''
// If a long string is being assigned then indent the text 4 spaces.
var some_string = 'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ' +
    'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ' +
    'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ';


// If the string is part of a larger structure, E.G. an array or method call then indent the text 4 spaces.
var some_array = [
    'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ' +
        'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ' +
        'Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ',
    'less text'
];
var someMethod = function (param1, param2, long_text, param4) {
    // Method content
};
someMethod(
    null,
    null,
    "Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. " +
        "Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. " +
        "Lots of text. Lots of text. Lots of text. Lots of text. Lots of text. ",
    null
);
'''

### Where to put JavaScript

Styles are split into various files and then recombined and minified automatically.

All base js files are in /js and all theme JavaScript is in /theme/[your-theme]/js. The folder/file structure within the base js folder and the theme folder is mirrored.

/js/Public contains js that is specific to pages rendered for unathenticated users.

/js/Client contains css that is specific to pages that are rendered for authenticated users on client websites.

/js/Shared contains css that is shared between both authenticated and unauthenticated users on client websites.

There are also folders for each of the datastore subdomains such as Domus, Scientia, Filter etc.

There is also a Shared folder for code that is shared between all domains.

Within /js/Client there are four folders.

/js/Client/Admin contains JavaScript that is only used by admin users.
/js/Client/Core contains core JavaScript that is loaded on every page.
/js/Client/Component contains JavaScript that is specific to components and only loaded on the pages it is used on.
/js/Client/Page contains JavaScript that is specific to a page/view.

See the [framework overview](/protected/documentation/FrameworkOverview.md) for more details.

### HTML in strings

If a string contains HTML then always create a template for the HTML rather than inserting the HTML inline.

If the JavaScript is in a /js/Client/Page/ file the HTML should be in its respective /protected/views/Client/Page file. Alternatively, if the JavaScript is in a /js/Client/Component file then the HTML should be in its respective /protected/views/Client/Component file.

See the [framework overview](/protected/documentation/FrameworkOverview.md) for more details.
