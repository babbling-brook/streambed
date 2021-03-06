IMPORTANT:
Remember to start the selenium server before starting selenium tests. (eg selenium-server-standalone-2.37.0.jar in wamp/bin/php folder)

Notes on installing PhPUnit

Tutorial http://net.tutsplus.com/tutorials/php/how-to-use-selenium-2-with-phpunit/

(
Also, see this netbeans guide : http://www.yiiframework.com/wiki/83/netbeans-ide-and-yii-projects/

Install this netbeans plugin:
http://plugins.netbeans.org/plugin/37753/selenium-module-for-php

Set a new test case run configuration to use path\to\php\phpunit-debug.bat as the php file,
which should contain :

SET XDEBUG_CONFIG=idekey=netbeans-xdebug
phpunit %*

Once setup in netbeans, select the test file to run and press CTR + SHIFT + F5. (Debug file)
)

Install pear
Install phpunit
Download PHP_Invoker-1.1.3 from http://pear.phpunit.de/ and copy the invoker folder and file into php-dir/pear/php folder

install phpunit db module
pear install phpunit/DbUnit

install phpunit story module
pear install phpunit/PHPUnit_Story

install phpunit selenium2 extension
pear install phpunit/PHPUnit_Selenium

download latest selenium 2.0 server (jar file. Needs to be running before tests are run.)
(Put it in the PHP folder)

setting up netbeans to debug phpunit tests with xdebug
http://blog.doh.ms/2011/05/13/debugging-phpunit-tests-in-netbeans-with-xdebug/


Commands.
These commands have been derived from https://github.com/sebastianbergmann/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
<?php
// Standard PHP unit functions.
$this->assertEquals('text 1', 'text 2');
$this->assertContains('needle','haystack');
$this->assertFalse(false);
$this->assertRegEx('/regex/', 'text');
$this->assertStringStartsWith('needle', 'haystack');
$this->assertStringEndsWith('needle', 'haystack');
$this->markTestIncomplete(); // Indicates a test is not fully functional.
$this->fail();
$this->assertSame(); // Asserts two elements are the same.

// Change the page url
$this->url('the url');

// Elements
$element = $this->byName('namefield');	// returns an element object.
$element = $this->byCssSelector('css');  // returns an element object.
$element = $this->byId('the id');				// returns an element object.
$element = $this->byLinkText('a string');	    // returns an element object.
$element = $this->byPartialLinkText('a string');	// returns an element object.
$element = $this->byClassName('a string');	// returns an element object.
$element = $this->byTag('a string');	// returns an element object.
$element = $this->byXPath('a string')	// returns an element object.
	->submit()  // submits a form.
	->text()    // Gets text from a retrieved element.
	->enabled() // Tests if a form element is enabled.
	->click()	// Clicks on an element.
	->doubleclick()	// double clicks on an element.
	->attribute('attribute name')  // returns an element object.
	->name()	// Returns the tag name of the element.
	->equals($element) // Checks if two elements are the same.
	->location()	// Returns an x and y location of an element in an array.
	->size()	// Returns width and height values in an array.
	->css('css')	// Returns the value of a css element.
	->displayed(); // Returns a boolean. Is an element visible.

// Multiple elements
$elements = $this->elements('elements'); // Objectifies a bunch of elements.
$elements = $this->elements($this->using('css selector|id')->value('tag type'));
// Get elements using javascript.
$elements = $this->execute(array(
	'script' => 'return document.body;',
	'args'	=> array(),
));
$element = $this->elementFromResponseValue($elements); // ???
// use an element in an elements array
$elements[0];

// Fetching child of an already fetched element.
$child = $parent->element($this->using('css selector|id')->value('the css'));

// Forms
// N.B using ->click() on checkboxes does not work in all browsers.
// select list
$this->select('element for the select tag')
	->selectOptionByValue('the value')
	->selectedLabels()	// Returns an array of strings.
	->selectOptionByLabel('label name')
	->clearSelectedOptions()	// Clears the selections.
	->selectOptionByCriteria($this->using('id|css selector')->value('string')) // ??
		// Option element
		->selected(); // Is an option selected.
$form_element
	->enabled()  // Is a form element enabled or not.
	->value() // Get a value.
	->value('a string') // Set a value.
	->clear() // Clears a value.
	->submit(); // Click a submit button (If element is the form).

// Key strokes.
$this->keys('key strokes'); // Sends key strokes to the current active element.
	// Also see form section -> value() and clear()
$this->keys(Keys::F2);	// Used for special keys such as modifiers.	Line 1082


// time
$this->timeouts()
	->implicitWait(milliseconds)

	// Async timeout ... line 520
	->asyncScript(milliseconds);	// ?? Think the time is for a fail timeout.
		// Followed by
	$script = 'var callback = arguments[0];
		window.setTimeout(function() {
			callback("string to send back");
		}, 1000);
	';
	$result = $this->executeAsync(array(
		'script' => $script,
		'args' => array()
	));		// $result = callback.


// Javascript
// If the result is returned directly.
$script = 'return "a string";';
$result = $this->execute(array(
	'script' => $script,
	'args' => array(),
));
// If the result is in a callback rather than returned directly.
$script = 'var callback = arguments[0]; callback(document.title);';
$result = $this->executeAsync(array(
	'script' => $script,
	'args' => array(),
));

// Windows tabs and frames.
$this->frame('iframe_id');	// Places the focus on an iframe.
$this->window('window name');	// ??? I think it opens a new window.
$this->closeWindow();	// Closes the currently in focus window.
$this->currentWindow();	// Returns a window object for the in focus window.
$window = $this->windowHandle();	// ??? Presume for identifying different tabs/windows
$windows = $this->windowHandles();	// ??? Presume for identifying different tabs/windows

// cookies
$cookie = $this->cookie()	// Fetches the cookie object.
	->get('name');	// If cookie 'name' does not exist an exception will be raised. Needs to be in a try.
$cookies->add('id', 'id_value');
$cookies->add('name', 'name_value')
	->path('/html')
	->domain('127.0.0.1')
	->expiry(time()+60*60*24)
	->secure(FALSE)
	->set();
$cookies->clear();
$this->assertThereIsNoCookieNamed('id');
$this->assertThereIsNoCookieNamed('name');

// Mouse.  ! Does not work in Firefox.
$this->moveto(array(
	'element' => $this->byId('id name'),	// If this is missing then the move will be from top left.
	'xoffset' => 10,
	'yoffset' => 10,
));
$this->buttondown();
$this->buttonup();
$this->clickOnElement('string');	// ??? Not sure what selector is used in string.

// More...
$this->source(); // Source HTML for the page.
$this->acceptAlert(); // Accepts an alert popup.
$this->dismissAlert(); // Dissmiss an alert popup.
$this->back(); 	// The browsers back button.
$this->forward(); // The browsers forward button.
$this->refresh(); // The browsers refresh button.
$screenshot = $this->currentScreenshot();	// Fetches a screenshot.
$this->orientation('LANDSCAPE|PORTRAIT');
?>
