<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the public stream nav works');
$I->amOnPage('/');
$window_width = $I->getWindowWidth($I);

// all present
$I->assertIdInCorrectLocation($I, 'body>div#page>*:nth-child(3)', 'streams_nav');
$I->assertLocation($I, SiteFurniturePage::$streams_nav, 0, 46);
$I->assertWidth($I, SiteFurniturePage::$streams_nav, $window_width);
$I->assertHeight($I, SiteFurniturePage::$streams_nav, 35);
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(1)>a');
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(2)>a');
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(3)>a');
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(4)>a');
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(5)>a');
$I->seeElement(SiteFurniturePage::$streams_nav . '>ul>li:nth-child(6)>a');

// links
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(1)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/news/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(2)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/chat/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(3)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/feedback/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(4)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/tasks/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(5)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/bugs/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(6)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/feature+requests/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(7)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/babbling+brook/latest/all/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(8)>a',
    'href',
    'http://' . GlobalPage::$domain . '/sky/stream/cobalt+meta/latest/all/latest'
);

// titles
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(1)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/news/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(2)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/chat/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(3)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/feedback/latest/latest/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(4)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/tasks/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(5)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/bugs/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(6)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/feature+requests/latest/all/all'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(7)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/babbling+brook/latest/all/latest'
);
$I->assertAttributeContent(
    $I,
    SiteFurniturePage::$streams_nav . '>ul>li:nth-child(8)>a',
    'title',
    GlobalPage::$domain . '/sky/stream/cobalt+meta/latest/all/latest'
);
