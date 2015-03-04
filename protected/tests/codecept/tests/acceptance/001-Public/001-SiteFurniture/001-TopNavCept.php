<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the public header works');
$I->amOnPage('/');
$window_width = $I->getWindowWidth($I);

// Check the header exists and is the correct size.
$I->assertIdInCorrectLocation($I, 'body>div#page>*:first-child', 'top_nav');
$I->seeElement(SiteFurniturePage::$top_nav);
$I->assertHeight($I, SiteFurniturePage::$top_nav, 35);
$I->assertWidth($I, SiteFurniturePage::$top_nav, $window_width);

// @todo x,y coords
$I->assertLocation($I, SiteFurniturePage::$top_nav, 0, 0);

// Logo
$header_image_path = SiteFurniturePage::$top_nav . '>header#header>a>img';
$I->seeElement($header_image_path);
$I->assertAttributeContent(
    $I,
    $header_image_path,
    'src',
    'http://' . GlobalPage::$domain . '/images/ui/fade-logo-small.png'
);
$I->assertHeight($I, $header_image_path, 25);
$I->assertWidth($I, $header_image_path, 259);

// Links
// link locations
$header_links_path = SiteFurniturePage::$top_nav . '>#top_nav_list';
$I->seeElement($header_links_path);
$I->assertIdInCorrectLocation($I, $header_links_path . '>*:first-child', 'small_screen_menu');
$I->assertIdInCorrectLocation($I, $header_links_path . '>*:nth-child(2)', 'about_top_nav');
$I->assertIdInCorrectLocation($I, $header_links_path . '>*:nth-child(3)', 'login');
$I->assertAttributeContent(
    $I,
    $header_links_path . '>*:nth-child(4)>a',
    'href',
    'https://' . GlobalPage::$domain . '/site/signup'
);
// link visibility
$I->dontSeeElement($header_links_path . '>*:nth-child(1)');
$I->seeElement($header_links_path . '>*:nth-child(2)');
$I->seeElement($header_links_path . '>*:nth-child(3)');
$I->seeElement($header_links_path . '>*:nth-child(4)');
// link contents
$I->assertContent($I, $header_links_path . '>*:nth-child(1)>a', '');
$I->see('About', $header_links_path . '>*:nth-child(2)>a');
$I->see('Login', $header_links_path . '>*:nth-child(3)>a');
$I->see('Signup', $header_links_path . '>*:nth-child(4)>a');
// link urls
$I->assertAttributeContent(
    $I,
    $header_links_path . '>*:nth-child(2)>a',
    'href',
    'http://' . GlobalPage::$domain . '/site/about'
);
$I->assertAttributeContent(
    $I,
    $header_links_path . '>*:nth-child(3)>a',
    'href',
    'https://' . GlobalPage::$domain . '/site/login'
);
$I->assertAttributeContent(
    $I,
    $header_links_path . '>*:nth-child(4)>a',
    'href',
    'https://' . GlobalPage::$domain . '/site/signup'
);
// Ensure they are inline
$I->assertCSSValue($I, $header_links_path, 'position', 'static');
$I->assertAttributeContent($I, $header_links_path, 'class', '');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(1)', 'position', 'static');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(2)', 'display', 'inline-block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(3)', 'display', 'inline-block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(4)', 'display', 'inline-block');

// Mobile version
$I->resizeWindow(800, 600);
// links now not visible.
$I->seeElement($header_links_path . '>*:nth-child(1)');
$I->dontSeeElement($header_links_path . '>*:nth-child(2)');
$I->dontSeeElement($header_links_path . '>*:nth-child(3)');
$I->dontSeeElement($header_links_path . '>*:nth-child(4)');
$I->assertCSSValue($I, $header_links_path, 'position', 'absolute');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(1)', 'position', 'absolute');
$I->assertAttributeContent($I, $header_links_path, 'class', '');
// Click to open the menu
$I->click($header_links_path . '>*:nth-child(1)');
$I->assertCSSValue($I, $header_links_path, 'position', 'absolute');
$I->assertAttributeContent($I, $header_links_path, 'class', 'small-screen-menu');
$I->seeElement($header_links_path . '>*:nth-child(1)');
$I->seeElement($header_links_path . '>*:nth-child(2)');
$I->seeElement($header_links_path . '>*:nth-child(3)');
$I->seeElement($header_links_path . '>*:nth-child(4)');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(2)', 'display', 'block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(3)', 'display', 'block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(4)', 'display', 'block');
// close menu
$I->click($header_links_path . '>*:nth-child(1)');
$I->assertAttributeContent($I, $header_links_path, 'class', '');
$I->seeElement($header_links_path . '>*:nth-child(1)');
$I->dontSeeElement($header_links_path . '>*:nth-child(2)');
$I->dontSeeElement($header_links_path . '>*:nth-child(3)');
$I->dontSeeElement($header_links_path . '>*:nth-child(4)');
// Resize back
$I->resizeWindow(GlobalPage::$large_site_width, GlobalPage::$large_site_height);
$I->assertCSSValue($I, $header_links_path, 'position', 'static');
$I->assertAttributeContent($I, $header_links_path, 'class', '');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(1)', 'position', 'static');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(2)', 'display', 'inline-block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(3)', 'display', 'inline-block');
$I->assertCSSValue($I, $header_links_path . '>*:nth-child(4)', 'display', 'inline-block');

// Yellow bar
$I->seeElement(SiteFurniturePage::$top_bar);
$I->assertClassInCorrectLocation($I, 'body>div#page>*:nth-child(2)', 'yellow_orange_bar');
$I->assertLocation($I, SiteFurniturePage::$top_bar, 0, 35);
$I->assertWidth($I, SiteFurniturePage::$top_bar, $window_width);