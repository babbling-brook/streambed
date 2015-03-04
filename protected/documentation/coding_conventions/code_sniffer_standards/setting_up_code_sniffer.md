# Setting up PHP_CodeSniffer

This is a command line tool that will automatically scan all your files to ensure that they are adhering to the coding standards.

[PHP CodeSniffer](https://www.squizlabs.com/php-codesniffer) on the web.
[GitHub](https://github.com/squizlabs/PHP_CodeSniffer) with [documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki).
[Pear](http://pear.php.net/package/PHP_CodeSniffer/).

### Installation

These instructions show how to install with Pear. There are also alternative composer and phar instructions(https://github.com/squizlabs/PHP_CodeSniffer/blob/master/README.md).

Windows users should use a command prompt with administrative rights:
* Start>All Programs>Accessories
* Right click *Command Prompt* and select *Run as Administrator*.

##### Pear
[Pear installation instructions](http://pear.php.net/manual/en/installation.getting.php)

##### Code Sniffer

CD into the root of your php folder. (This is also the root of pear).

'''
cd /path/to/php
'''

Install code sniffer.

'''
pear install PHP_CodeSniffer
'''

### Setup
Windows users need to make sure that [php is in their path](http://php.net/manual/en/faq.installation.php#faq.installation.addtopath).

Next, set the standard to be the BabblingBrook Standard

'''
phpcs --config-set default_standard */full/path/to/project/root*/protected/documentation/coding_conventions/code_sniffer_standards/BabblingBrook
'''

### Usage
To test the whole project

'''
phpcs /full/path/to/project/root
'''

To test a single file

'''
phpcs /full/path/to/file
'''

For more options see the [PHP CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki).