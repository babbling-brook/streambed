<?php
namespace AcceptanceTester;

class PublicStreamSortChangeSteps extends \AcceptanceTester
{
    public function testChangeSortRhythm($I, $rhythm_name, $sort_child_number, $original_sort_order, $new_sort_order) {

        $child_count = 1;
        foreach ($original_sort_order as $post_id) {
            $selector = \SiteFurniturePage::$article_page
                . '>div:nth-child(1)>div#posts>div:nth-child(' . $child_count . ')';
            $I->assertAttributeContent($I, $selector, 'data-post-id', $post_id);
            $child_count++;
        }

        $side_bar_path = \SiteFurniturePage::$article_root . ' #sidebar_extra';
        $sort_bar_title_path = $side_bar_path . '>*:nth-child(2)' . '>*:nth-child(1)';
        $sort_bar_option_path = '#sort_bar_options>dd:nth-child(' . $sort_child_number . ')>a';
        $I->click($sort_bar_title_path);
        $I->see($rhythm_name, $sort_bar_option_path);
        $I->click($sort_bar_option_path);

        if (empty($new_sort_order) === true) {
            $I->seeElement(\SiteFurniturePage::$article_root . ' #no_posts');
        } else {
            $child_count = 1;
            foreach ($new_sort_order as $post_id) {
                $selector = \SiteFurniturePage::$article_page
                    . '>div:nth-child(1)>div#posts>div:nth-child(' . $child_count . ')';
                $I->assertAttributeContent($I, $selector, 'data-post-id', $post_id);
                $child_count++;
            }
        }
    }
}