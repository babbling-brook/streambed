<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{

    /**
     * Fetch an element form selenium web driver with a css path.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the eleement.
     * @param type $path The path to fetch the element with.
     * @return object The websdriver element.
     */
    private function getWebDriverElementByCSS($I, $path) {
        $element = $I->executeInSelenium(function(\WebDriver $webdriver) use ($path) {
            $element = $webdriver->findElement(\WebDriverBy::cssSelector($path));
            return $element;
        });
        return $element;
    }

    public function getWindowWidth($I) {
        $element = $this->getWebDriverElementByCSS($I, 'body');
        $size = $element->getSize();
        $width = $size->getWidth();
        return $width;
    }

    /**
     * Asserts that a given path contains a css ID.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the attribute.
     * @param string $path The path to check.
     * @param string $id The id to assert is found at the end of the path.
     */
    public function assertAttributeContent($I, $path, $attribute, $content) {
        $found_content = $I->grabAttributeFrom($path, $attribute);
        \PHPUnit_Framework_Assert::assertSame($found_content, $content);
    }

    /**
     * Asserts that a given path contains a css ID.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the attribute.
     * @param string $path The path to check.
     * @param string $id The id to assert is found at the end of the path.
     */
    public function assertIdInCorrectLocation($I, $path, $id) {
        $found_id = $I->grabAttributeFrom($path, 'id');
        \PHPUnit_Framework_Assert::assertSame($found_id, $id);

    }

    /**
     * Asserts that a given path contains a css ID.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the attribute.
     * @param string $path The path to check.
     * @param string $id The id to assert is found at the end of the path.
     */
    public function assertClassInCorrectLocation($I, $path, $class) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $classes = $element->getAttribute('class');
        $classes_array = split(' ', $classes);
        \PHPUnit_Framework_Assert::assertTrue(in_array($class, $classes_array));
    }

    /**
     * Asserts that the height of the element in the path is the same as the height given.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the element.
     * @param type $path The css path to the element.
     * @param type $height The expected height of the element.
     */
    public function assertHeight($I, $path, $height) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $size = $element->getSize();
        $actual_height = $size->getHeight();
        \PHPUnit_Framework_Assert::assertSame($actual_height, (int)$height);
    }

    /**
     * Asserts that the width of the element in the path is the same as the width given.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the element.
     * @param type $path The css path to the element.
     * @param type $width The expected width of the element.
     */
    public function assertWidth($I, $path, $width) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $size = $element->getSize();
        $actual_width = $size->getWidth();
        \PHPUnit_Framework_Assert::assertSame($actual_width, (int)$width);
    }

    /**
     * Asserts the content of an element even if it is not visible.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the element.
     * @param type $path The css path to the element.
     * @param type $content The expected content of the element.
     */
    public function assertContent($I, $path, $content) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $actual_content = $element->getAttribute('innerHTML');
        \PHPUnit_Framework_Assert::assertSame(trim($actual_content), trim($content));
    }

    /**
     * Asserts that an element is at the given coordinates relative to the top left of the browser window.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the element.
     * @param type $path The css path to the element.
     * @param type $content The expected content of the element.
     */
    public function assertLocation($I, $path, $x, $y) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $location = $element->getLocation();
        \PHPUnit_Framework_Assert::assertSame($location->getX(), $x);
        \PHPUnit_Framework_Assert::assertSame($location->getY(), $y);
    }

    /**
     * Asserts that an elements css contains a property with a given value.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use to fetch the element.
     * @param type $path The css path to the element.
     * @param type $css_property The css property to check.
     * @param type $value The expected value of the element.
     */
    public function assertCSSValue($I, $path, $css_property, $value) {
        $element = $this->getWebDriverElementByCSS($I, $path);
        $actual_value = $element->getCSSValue($css_property);
        \PHPUnit_Framework_Assert::assertSame($actual_value, $value);
    }

    /**
     * Returns a stream url when given a standard stream name array.
     *
     * @param array $stream A standard stream name array.
     *
     * @return string An absolute url to the stream.
     */
    public function makeStreamURL($stream) {
        $stream_url = 'http://' . $stream['domain'] . '/' . $stream['username'] . '/stream/' .
                $stream['name'] . '/' . $stream['version']['major'] . '/' .
                $stream['version']['minor'] . '/' .$stream['version']['patch'];
        return $stream_url;
    }
}
