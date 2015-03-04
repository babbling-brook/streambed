<?php
/**
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
 * Model to validate that an post scores object is valid.
 *
 * @package PHP_Model_Forms
 */
class PostScoresForm extends CFormModel
{
    /**
     * An object that holds the rhythm name data.
     *
     * @var array $posts
     * integer $posts.post_id The local id of the post that has been scored.
     * integer $posts.score The score of the the post.
     */
    public $posts;

    /**
     * @var integer If this is a tree sort then the id of the top parent post is required.
     */
    public $top_parent_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('posts', 'required'),
            array('posts', 'ruleArray'),
            array('posts', "ruleRows"),
            array('top_parent_id', "ruleTopParentId"),
        );
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleTopParentId() {
        if (isset($this->top_parent_id) === true) {
            $exists = Post::checkExists($this->top_parent_id);
            if ($exists === false) {
                $this->addError('top_parent_id', 'If the top parent id is defined, it must be a valid post id.');
            }
        }
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleArray() {
        if (is_array($this->posts) === false) {
            $this->addError('posts', 'The posts paramater is not an array');
        }
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleRows() {
        $post_ids = array();
        foreach ($this->posts as $key => $post) {
            if (isset($post['post_id']) === false) {
                $this->addError('posts', 'The post id is not valid for row: ' . $key);
            }
            if (isset($post['score']) === false) {
                $this->addError(
                    'posts',
                    'The score is not valid for post_id ' . $post['post_id'] . ' on row: ' . $key
                );
            }
            if (ctype_digit($post['post_id'] === false)) {
                 $this->addError('posts', 'The post id is not numeric: ' . $post['post_id']);
            }
            if (ctype_digit($post['score'] === false)) {
                 $this->addError(
                     'posts',
                     'The score is not numeric: ' . $post['score'] . ' for post_id: ' . $post['post_id']
                 );
            }
            $post_ids[$post['post_id']] = $post['post_id'];
        }

        $exists = Post::checkIfManyExist($post_ids);
        if ($exists !== true) {
             $this->addError('posts', 'An post does not exist for post_id: ' . $post['post_id']);
        }

    }
}

?>