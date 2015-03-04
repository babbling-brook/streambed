# CSS Coding Conventions

Each style should use the following format

'''
selector {
    property: value;
    another-property: value;
    ...
}
'''

* The opening brace is on the same line as the selector and the closing brace is on a new line.
* Styles are indented with four spaces.
* There is no space before the separating colon, but there is one after it.

For nested styles indent with an additional four spaces. E.G:

'''
@media only screen {
    selector {
        property: value;
        another-property: value;
        ...
    }
}
'''

### Which file to put the styles in.

Styles are split into various files and then recombined and minified automatically.

All base css files are in /css and all theme css is in /theme/[your-theme]/css. The folder/file structure within the base css folder and the theme folder is mirrored.

/css/Public contains css that is specific to pages rendered for unathenticated users.

/css/Client contains css that is specific to pages that are rendered for authenticated users.

/css/Shared contains css that is shared between both authenticated and unauthenticated users.

/css/Shared/Main.css contains the base css shared by everything.

The same folder/files structure is mirrored within these three folders (Client, Public and Shared.)


/css/Client|Public|Shared/Layouts/ClientType/[your-client-type]/SiteFurniture.css contains css specific to the type of client that is being used.

/css/Client|Public|Shared/Page contains folders/files for CSS that is specific to the page/view that is being rendered. (See /protected/views/Client/Page for the matching views.)

/css/Client|Public|Shared/Component contains CSS that is specific to /js/Client/Component modules that are used on the currently rendered page.


Please see the [framework overview](/protected/documentation/FrameworkOverview.md) for further details.