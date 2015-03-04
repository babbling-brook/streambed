<?php
namespace AcceptanceTester;

class PublicStreamSideBarSteps extends \AcceptanceTester
{
    /**
     * Tests a dropdown option for the rhythms on the side bar.
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use in these tests.
     * @param array $rhythm A rhtyhm name object for hte option to test.
     * @param int $rhythm_count The postion of the drop down option.
     * @param string $sort_bar_options_path.
     *
     * @return void
     */
    private function testDropDownOption ($I, $rhythm, $rhythm_count, $options_path, $stream) {
        $option_path = $options_path . '>*:nth-child(' . $rhythm_count . ')>a';
        $I->see($rhythm['name'], $option_path);
        $stream_rhyth_url = $I->makeStreamUrl($stream) . '/rhythm/' . $rhythm['domain'] . '/' . $rhythm['username'] . '/'
            . str_replace(' ', '%20', $rhythm['name']) . '/' . $rhythm['version']['major'] . '/' . $rhythm['version']['minor']
            . '/' . $rhythm['version']['patch'];
        $I->assertAttributeContent($I, $option_path, 'href', $stream_rhyth_url);
    }

    /**
     *
     * @param AcceptanceTester $I The current AccpetanceTester to use in these tests.
     * @param array $sidebar configuration object for the sidebar.
     * @param array $sidebar.stream A standard stream array plus the following elements.
     * @param array $sidebar.stream.partial_description The first part of the description for the stream.
     * @param array $sidebar.selected_rhythm The currently selected rhythm.
     * @param array $sidebar.rhythms The rest of the rhythms in the selector
     *
     * @returns void
     */
    public function testSideBar($I, $sidebar) {
        $side_bar_path = \SiteFurniturePage::$article_root . ' #sidebar_extra';

        // title
        $I->assertClassInCorrectLocation($I, $side_bar_path . '>*:nth-child(1)', 'title');
        $I->see($sidebar['stream']['name'], $side_bar_path . '>*:nth-child(1)>h3>a');
        $I->assertAttributeContent(
            $I,
            $side_bar_path . '>*:nth-child(1)>h3',
            'title',
            'Owned by : ' . $sidebar['stream']['domain'] . '/' . $sidebar['stream']['username']
        );
        $I->assertAttributeContent($I,
            $side_bar_path . '>*:nth-child(1)>h3>a',
            'href',
            ''
        );

        // sort bar
        $sort_bar_path = $side_bar_path . '>*:nth-child(2)';
        $I->assertIDInCorrectLocation($I, $sort_bar_path, 'sort_bar');
        $sort_bar_title_path = $sort_bar_path . '>*:nth-child(1)';
        $I->assertIDInCorrectLocation($I, $sort_bar_title_path, 'sort_bar_title');
        $I->assertAttributeContent($I,
            $sort_bar_title_path,
            'class',
            'sorted'
        );
        $I->see($sidebar['selected_rhythm']['name'], $sort_bar_title_path);
        $sort_bar_options_path = $sort_bar_path . '>*:nth-child(2)';
        $I->assertIDInCorrectLocation($I, $sort_bar_options_path, 'sort_bar_options');
        $I->dontSeeElement($sort_bar_options_path);
        $I->click($sort_bar_title_path);
        $I->seeElement($sort_bar_options_path);
        $I->click($sort_bar_title_path);
        $I->dontSeeElement($sort_bar_options_path);
        $I->click($sort_bar_title_path);
        $rhythm_count = 0;
        foreach($sidebar['rhythms'] as $rhythm) {
            $rhythm_count ++;
            $this->testDropDownOption($I, $rhythm, $rhythm_count, $sort_bar_options_path, $sidebar['stream']);
        }


        $I->assertClassInCorrectLocation($I, $side_bar_path . '>*:nth-child(3)', 'description');
        $I->see($sidebar['stream']['partial_description'], $side_bar_path . '>*:nth-child(3)');

        $I->assertClassInCorrectLocation($I, $side_bar_path . '>*:nth-child(4)', 'filter-details');

        // Close the side bar so that the page is left with it closed.
        $I->click($sort_bar_title_path);
    }
}