<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the content page is in the correct place');
$I->amOnPage('/');

$I->assertIDInCorrectLocation($I, SiteFurniturePage::$article_root . '>*:nth-child(1)', 'sidebar_container');
$side_bar_path = SiteFurniturePage::$article_root . '>*:nth-child(1)>*:nth-child(1)';
$I->assertIDInCorrectLocation($I, $side_bar_path, 'sidebar');
$I->assertIDInCorrectLocation($I, $side_bar_path . '>*:nth-child(1)', 'sidebar_open');
$I->dontSeeElement($side_bar_path . '>*:nth-child(1)');
$I->assertIDInCorrectLocation($I, $side_bar_path . '>*:nth-child(2)', 'sidebar_extra');
$I->seeElement($side_bar_path . '>*:nth-child(2)');

$I->assertIDInCorrectLocation($I, SiteFurniturePage::$article_root . '>*:nth-child(2)', 'content_page');
