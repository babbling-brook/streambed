<?php
/**
 *
 * Copyright 2015 Sky Wickenden
 *
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 *
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 *
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 * Post controller
 *
 * @package PHP_Controllers
 */
class PostController extends Controller
{
    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl',
        );
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules.
     */
    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array('post', 'PostWithTree'),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(''),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Display an post.
     *
     * @return void
     */
    public function actionPost($g_post_id, $g_post_domain) {
        if (Yii::app()->user->isGuest === true) {
            $this->publicPost($g_post_id);
        } else {
            $this->userPost();
        }
    }

    /**
     * Display an post with a tree of child posts.
     *
     * @return void
     */
    public function actionPostWithTree($g_post_id, $g_post_domain) {
        if (Yii::app()->user->isGuest === true) {
            $this->publicPostWithTree($g_post_id);
        } else {
            $this->userPostWithTree();
        }
    }

    /**
     * Display an post for a user/bot who is not logged in.
     *
     * @param string $post_id The id of the post that is being generated.
     *
     * @return void
     */
    protected function publicPostWithTree($post_id) {
        $child_posts = StreamPublic::getTreePostsForPost($post_id);
        if (count($child_posts) === 0) {
            StreamPublic::makePopularTree($post_id);
            $child_posts = streamPublic::getTreePostsForPost($post_id);
        } else if (time() - Yii::app()->params['public_post_cache_time'] > $child_posts[0]['time_cached']) {
            StreamPublic::makePopularTree($post_id);
            $child_posts = streamPublic::getTreePostsForPost($post_id);
        }

        $this->render(
            '/Public/Page/Post/PostWithTree',
            array(
                'post_id' => $post_id,
                'child_posts' => $child_posts,
            )
        );
    }


    /**
     * Display an post for a user/bot who is not logged in.
     *
     * @param string $post_id The id of the post that is being generated.
     *
     * @return void
     */
    protected function publicPost($post_id) {
        // Child posts will not be displayed as they are not passed in.
        $this->render(
            '/Public/Page/Post/PostWithTree',
            array(
                'post_id' => $post_id,
                'child_posts' => array(),
            ));
    }

    /**
     * Display an post for a logged in user.
     *
     * @return void
     */
    protected function userPostWithTree() {
        $this->render('/Client/Page/Post/PostWithTree');
    }

}

?>