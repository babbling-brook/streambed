<?php
require __DIR__ . '/../../fixtures.php';

$I = new AcceptanceTester\PublicStreamSteps($scenario);
$I->wantTo('ensure that the home page works');
$I->amOnPage('/');
$window_width = $I->getWindowWidth($I);

$I->testStreamPage($I);
$I = new AcceptanceTester\PublicPostSteps($scenario);

$posts_path = SiteFurniturePage::$article_page . '>div:nth-child(1)>div#posts';
$I->testPost($I, $posts_path . '>div:nth-child(1)', $fixtures['posts'][10225]);
$I->testPost($I, $posts_path . '>div:nth-child(2)', $fixtures['posts'][10226]);

$I = new AcceptanceTester\PublicStreamSideBarSteps($scenario);
$sidebar = array(
    'stream' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'news',
        'version' => array(
            'major' => 'latest',
            'minor' => 'latest',
            'patch' => 'latest',
        ),
        'partial_description' => 'News about',
    ),
    'selected_rhythm' => array (
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'skys priority',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'rhythms' => array (
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'popular recently',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'popular in last week',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'popular in last day',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'popular in last hour',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'skys priority',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
        array (
            'domain' => GlobalPage::$domain,
            'username' => 'sky',
            'name' => 'newest',
            'version' => array(
                'major' => '0',
                'minor' => '0',
                'patch' => '0',
            ),
        ),
    ),
);
$I->testSideBar($I, $sidebar);

$I = new AcceptanceTester\PublicStreamSortChangeSteps($scenario);
$skys_priority_sort = [
    '10225',
    '10226',
];
$popular_recently_sort = [
    '10225',
    '10226',
];
$popular_in_last_hour_sort = [
];
$popular_in_last_day_sort = [
];
$popular_in_last_week_sort = [
];
$newest_sort = [
    '10226',
    '10225',
];
$I->testChangeSortRhythm($I, 'popular recently', 1, $skys_priority_sort, $popular_recently_sort);
$I->testChangeSortRhythm($I, 'popular in last week', 2, $popular_recently_sort, $popular_in_last_week_sort);
$I->testChangeSortRhythm($I, 'popular in last day', 3, $popular_in_last_week_sort, $popular_in_last_day_sort);
$I->testChangeSortRhythm($I, 'popular in last hour', 4, $popular_in_last_day_sort, $popular_in_last_hour_sort);
$I->testChangeSortRhythm($I, 'skys priority', 5, $popular_in_last_hour_sort, $skys_priority_sort);
$I->testChangeSortRhythm($I, 'newest', 6, $skys_priority_sort, $newest_sort);