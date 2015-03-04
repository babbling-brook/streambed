<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the about page works');
$I->amOnPage('/site/about');
$I->see('About Cobalt Cascade', SiteFurniturePage::$article_page . '>h2');
$I->see('Cobalt Cascade is an alpha implementation', SiteFurniturePage::$article_page . '>div.content-indent.blocktext');