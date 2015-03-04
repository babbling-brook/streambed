<?php

require_once 'Testing/Selenium.php';

class Example extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    $this = new Testing_Selenium("*chrome", "http://cobaltcascade.localhost/");
    $this->open("/tests/restore");
    $this->open("/offer/cobaltcascade.localhost/1");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ((bool)preg_match('/^[\s\S]*test offer 13 - comment 5[\s\S]*$/', $this->getText("css=#sub_offers>#suboffer_13>div.content>div.inner-content>div.field-count-0"))) {
                break;
            }
        } catch (Exception $e) {}
        sleep(1);
    }

    // test ring
    try {
        $this->assertEquals("rings", $this->getText("css=#suboffer_13>div.content>div.foot>div.offer-rings>span.ring_title"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("css=#suboffer_13>div.content>div.foot>div.offer-rings>span.ring_title");
    try {
        $this->assertEquals("ring-waiting", $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ("" == $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertEquals("take comment test", $this->getText("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a:nth(0) li"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertFalse($this->isElementPresent("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a:nth(1) li"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a:nth(0) li");
    try {
        $this->assertEquals("ring-waiting", $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ("ring-taken" == $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->click("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a:nth(0) li");
    try {
        $this->assertEquals("ring-waiting", $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ("" == $this->getAttribute("css=#suboffer_13>div.content>div.foot>div.offer-rings>ul a li@class")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

  }
}
?>