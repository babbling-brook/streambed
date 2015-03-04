<?php
namespace AcceptanceTester;

class PublicPostSteps extends \AcceptanceTester
{
    public function testPost($I, $path, array $post) {
        $I->wantTo('ensure that a post with name "' . $post['title'] . '" is valid');
        $I->assertAttributeContent($I, $path, 'class', 'post stream-post');
        $post_url = 'http://' . $post['user']['domain'] . '/post/' . $post['user']['domain'] . '/' . $post['post_id'];

        // top value
        $top_value_path = $path . '>*:nth-child(1)';
        $I->assertAttributeContent($I, $top_value_path, 'class', 'top-value');
        $I->seeElement($top_value_path . '>*:nth-child(1)');
        $take_path = $top_value_path . '>*:first-child';
        $I->assertAttributeContent($I, $take_path, 'class', 'field-2 field updown take');
        $I->assertAttributeContent($I, $take_path . '>*:nth-child(1)', 'class', 'up-arrow up-untaken');
        $I->assertAttributeContent($I, $take_path . '>*:nth-child(2)', 'class', 'down-arrow down-untaken');

        // title row
        $I->assertAttributeContent($I, $path . '>*:nth-child(2)', 'class', 'title');
        $I->seeElement($path . '>*:nth-child(2)');
        $title_path = $path . '>*:nth-child(2)>:nth-child(1)';
        $I->assertAttributeContent($I, $title_path, 'class', 'field-1 fiels textbox-field');
        $I->assertAttributeContent($I, $title_path, 'href', $post_url);
        $I->see($post['title'], $title_path);

        // info row
        $info_path = $path . '>*:nth-child(3)';
        $I->assertAttributeContent($I, $info_path, 'class', 'info');
        $I->seeElement($info_path);
        $I->see('Made by ' . $post['user']['username'], $info_path);
        $I->see('ago', $info_path);
        $I->seeElement($info_path . '>a.username');
        $made_by_title = 'Made by :' . $post['user']['domain'] . '/' . $post['user']['username'];
        $I->assertAttributeContent($I, $info_path . '>a.username', 'title', $made_by_title);
        $made_by_link = 'http://' . $post['user']['domain'] . '/' . $post['user']['username'];
        $I->assertAttributeContent($I, $info_path . '>a.username', 'href', $made_by_link);
        $I->seeElement($info_path . '>time.time-ago');
        $I->assertAttributeContent($I, $info_path . '>time.time-ago', 'title', $post['time']);

        // actions row
        $actions_path = $path . '>*:nth-child(4)';
        $I->assertAttributeContent($I, $actions_path, 'class', 'actions');
        $I->seeElement($actions_path);
        $actions_post_post = $actions_path . '>a.link-to-post';
        if ($post['comment_count'] !== -1) {
            $I->see('comments (' . $post['comment_count'] . ')', $actions_post_post);
        }
        $I->assertAttributeContent($I, $actions_post_post, 'href', $post_url);
        $I->dontSeeElement($actions_path . '>*:nth-child(2)');

        $I->dontSeeElement($path . '>*:nth-child(5)');

    }
}