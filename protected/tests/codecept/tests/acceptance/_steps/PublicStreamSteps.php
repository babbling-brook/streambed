<?php
namespace AcceptanceTester;

class PublicStreamSteps extends \AcceptanceTester
{
    private function makePost ($I) {
        $I->assertIDInCorrectLocation(
            $I,
            \SiteFurniturePage::$article_page . '>div:first-child>*:nth-child(1)',
            'make_post'
        );
        $I->assertAttributeContent(
            $I,
            \SiteFurniturePage::$article_page . '>div:first-child>*:nth-child(1)',
            'class',
            'content-block-2'
        );
        $input_path = \SiteFurniturePage::$article_page . '>div:first-child>*:nth-child(1)>input';
        $I->seeElement($input_path);
        $I->assertAttributeContent($I, $input_path, 'value', 'Write something...');
        $I->assertAttributeContent($I, $input_path, 'type', 'text');
        $I->assertAttributeContent($I, $input_path, 'class', 'make-post');

        //@todo click on text box, check that log in opens up.

    }

    private function posts ($I) {
        $I->assertIDInCorrectLocation(
            $I,
            \SiteFurniturePage::$article_page . '>div:first-child>*:nth-child(2)',
            'posts'
        );
    }

    public function testStreamPage($I) {
        $I->assertClassInCorrectLocation($I, \SiteFurniturePage::$article_page . '>div:first-child', 'content-indent');
        $this->makePost($I);
        $this->posts($I);
    }
}