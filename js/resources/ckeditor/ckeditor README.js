/*
 * The ckeditor folder is created from a compiled version of ckeditor-dev.
 *
 * If you want to create a new skin, you can just copy the cobalt skin from the ckeditor-dev/skins folder
 * to the a new folder in release/skins. (Don't copy the one from the release/skins folder as it is minified)
 * This will work fine, however the files will not be minified and combined. If you want you can rebuild the skin as
 * follows:
 *
 * Requirements: Linux and a github account.
 *
 * Linux neds Java installed:
 * sudo apt-get install openjdk-6-jdk
 *
 * Linux needs installed and setup
 * > sudo apt-get install git
 *  (see http://git-scm.com/download/linux)
 * > sudo git config --global user.name "YOUR GIT USERNAME"
 * > sudo git config --global user.email "YOUR GIT EMAIL ADDRESS"
 *
 * Fork the CKEditor github project from the Babbling Brook CKEDitor fork at https://github.com/babbling-brook/ckeditor-dev/
 *
 * (Make a new skin and commit it to your fork)
 *
 * cd to the directory location you are going to clone your fork.
 * Clone the project in linux with:
 * sudo git clone https://github.com/your_fork .
 * (The period at the end clones into the current directory)
 *
 * Switch to the correct branch:
 * > sudo git checkout release/4.4.x
 * Make sure that the branch is up to date.
 * > sudo git pull
 *
 * Goto the builder directory and run the build script (it takes a while.)
 * > cd dev/builder
 * >sudo bash build.sh
 * When it has finished there will be a new directory called releases with a zip and a tar.
 * Copy the one you want to the /js/resources/ckeditor/release folder of your babbling brook client website
 * and extract the skin to the skins directory.
 *
 * Make sure the correct skin is referenced in CKEditorAdapter.js
 *
 */


