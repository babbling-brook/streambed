<?php
require __DIR__ . '/../../fixtures.php';

$I = new AcceptanceTester\PublicPostSteps($scenario);
$I->wantTo('ensure that the public test stream page works');
$I->amOnPage('/test/stream/test stream/latest/latest/latest');

$post_path = SiteFurniturePage::$article_page . '>div:nth-child(1)>div#posts';
$I->testPost($I, $post_path .'>div:nth-child(1)', $fixtures['posts'][20]);
$I->testPost($I, $post_path .'>div:nth-child(2)', $fixtures['posts'][19]);
$I->testPost($I, $post_path .'>div:nth-child(3)', $fixtures['posts'][7]);
$I->testPost($I, $post_path .'>div:nth-child(4)', $fixtures['posts'][6]);
$I->testPost($I, $post_path .'>div:nth-child(5)', $fixtures['posts'][4]);