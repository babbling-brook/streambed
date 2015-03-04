<?php

/**
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 */
define('TEST_BASE_URL', 'http://cobaltcascade.localhost/');

Yii::import('ext.webdriver-bindings.CWebDriverTestCase');
Yii::import('application.tests.restore.Restore');

/**
 * The base class for functional test cases.
 * In this class, we set the base URL for the test application.
 * We also provide some common methods to be used by concrete test classes.
 */
class WebTestCase extends PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Sets up before each test method runs.
     * This mainly sets the base URL for the test application.
     */
    public function setUp()
    {
        //$this->expectOutputString('');    // Uncomment this line to echo to the cmd window.
        new Restore;
        $this->setBrowser('firefox');
        $this->setBrowserUrl(TEST_BASE_URL);
    }



}
