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
 * restores the test data from the test db.
 *
 * @package PHP_Model_Forms
 */
class RestoreTestData
{
    /**
     * @var array A lis of user_ids to delete data for.
     */
    private $test_user_ids;

    /**
     * @var array The SQL delete statments used in deleting test data.
     */
    private $delete_stale_test_data_statements;

    private function deleteStaleData() {
        foreach ($this->test_user_ids as $row) {
            $test_user_id = $row['user_id'];

        }

    }


    private function fetchTestUsers() {
        $sql = "SELECT user_id FROM user WHERE test_user = 1";
        $command = Yii::app()->db->createCommand($sql);
        $this->test_user_ids = $command->queryAll();
    }

    /**
     * Flags all the test users in preperation for their data being deleted.
     *
     * @return void
     */
    private function markTestUsers() {
        $sql = "UPDATE user SET test_user = 1 WHERE username like 'test%'";
        $command = Yii::app()->db->createCommand($sql);
        $command->execute();
    }

    public function __construct() {

        $this->delete_stale_test_data_statements = array(
            "UPDATE user SET test_user = 1 WHERE username like 'test%'",
            "DELETE take_value_list FROM take_value_list "
                . "INNER JOIN stream_field ON take_value_list.stream_field_id = stream_field.stream_field_id  "
                . "INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content INNER JOIN post ON post_content.post_id = post.post_id "
                . "INNER JOIN  stream_extra ON post.post_id = stream_extra.meta_post_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN user ON user_take.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN stream_extra ON user_take.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS  post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN take ON user_take.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN user ON user_take.take_user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_take FROM user_take "
                . "INNER JOIN post ON user_take.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_private_recipient FROM post_private_recipient "
                . "INNER JOIN post ON  post_private_recipient.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN  stream_extra ON post.post_id = stream_extra.meta_post_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_rhythm FROM user_rhythm  "
                . "INNER JOIN user ON  user_rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",

            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN take ON take_kindred.take_id = take.take_id "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take_kindred FROM take_kindred "
                . "INNER JOIN user ON take_kindred.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE kindred FROM kindred "
                . "INNER JOIN user ON kindred.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE kindred FROM kindred "
                . "INNER JOIN user ON kindred.kindred_user_id = user.user_id "
                . "WHERE user.test_user = 1",

            "DELETE post_private_recipient FROM post_private_recipient "
                . "INNER JOIN user ON post_private_recipient.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN user ON take.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_content FROM post_content "
                . "INNER JOIN post ON post_content.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_popular FROM post_popular "
                . "INNER JOIN post ON post_popular.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block_tree FROM stream_block_tree "
                . "INNER JOIN post ON stream_block_tree.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN post ON stream_public.post_id = post.post_id "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN stream_extra ON take.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id  "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE take FROM take "
                . "INNER JOIN post ON take.post_id = post.post_id  "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN post AS post2 ON post.parent = post2.post_id "
                . "INNER JOIN  stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post_user FROM post_user "
                . "INNER JOIN user ON post_user.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN  stream_extra ON post2.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN post AS post2 ON post.top_parent = post2.post_id "
                . "INNER JOIN user ON post2.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring "
                . "INNER JOIN ring ON user_stream_subscription_ring.ring_id = ring.ring_id "
                . "INNER JOIN user ON ring.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN  stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE signup_code FROM signup_code "
                . "INNER JOIN user ON signup_code.used_user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE post FROM post "
                . "INNER JOIN user ON post.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_list FROM stream_list "
                . "INNER JOIN stream_field ON stream_list.stream_field_id = stream_field.stream_field_id "
                . "INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_filter FROM user_stream_subscription_filter "
                . "INNER JOIN user_stream_subscription "
                . "ON user_stream_subscription_filter.user_stream_subscription_id = "
                . "user_stream_subscription.user_stream_subscription_id "
                . "INNER JOIN user ON user_stream_subscription.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_filter FROM user_stream_subscription_filter "
                . "INNER JOIN user_stream_subscription "
                . "ON user_stream_subscription_filter.user_stream_subscription_id = "
                . "user_stream_subscription.user_stream_subscription_id "
                . "INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_filter FROM user_stream_subscription_filter "
                . "INNER JOIN rhythm_extra ON rhythm_extra.rhythm_extra_id = "
                . "user_stream_subscription_filter.rhythm_extra_id "
                . "INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id "
                . "INNER JOIN user ON rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring "
                . "INNER JOIN user_stream_subscription ON user_stream_subscription_ring.user_stream_subscription_id = "
                . "user_stream_subscription.user_stream_subscription_id "
                . "INNER JOIN user ON user_stream_subscription.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring "
                . "INNER JOIN user_stream_subscription ON user_stream_subscription_ring.user_stream_subscription_id = "
                . "user_stream_subscription.user_stream_subscription_id "
                . "INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE version FROM version "
                . "INNER JOIN stream_extra ON version.version_id = stream_extra.version_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE ring_user_take FROM ring_user_take  "
                . "INNER JOIN user ON ring_user_take.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE waiting_post_time FROM waiting_post_time "
                . "INNER JOIN user ON waiting_post_time.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_level FROM user_level "
                . "INNER JOIN user ON user_level.user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE version FROM version "
                . "INNER JOIN rhythm_extra ON version.version_id = rhythm_extra.version_id "
                . "INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id "
                . "INNER JOIN user ON rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_block FROM stream_block "
                . "INNER JOIN stream_extra ON stream_extra.stream_extra_id = stream_block.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_child FROM stream_child "
                . "INNER JOIN stream_extra ON stream_child.parent_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_child FROM stream_child "
                . "INNER JOIN stream_extra ON stream_child.child_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_default_ring FROM stream_default_ring "
                . "INNER JOIN stream_extra ON stream_default_ring.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_field FROM stream_field "
                . "INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_public FROM stream_public "
                . "INNER JOIN stream_extra ON stream_public.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE ring_user_take FROM ring_user_take "
                . "INNER JOIN ring_take_name ON ring_user_take.ring_take_name_id = ring_take_name.ring_take_name_id "
                . "INNER JOIN ring ON ring_take_name.ring_id = ring.ring_id  "
                . "INNER JOIN user ON ring.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription FROM user_stream_subscription "
                . "INNER JOIN user ON user_stream_subscription.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_subscription FROM user_stream_subscription "
                . "INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_count FROM user_stream_count "
                . "INNER JOIN stream_extra ON user_stream_count.stream_extra_id = stream_extra.stream_extra_id "
                . "INNER JOIN stream ON stream_extra.stream_id = stream.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_stream_count FROM user_stream_count "
                . "INNER JOIN user ON user_stream_count.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_ring FROM user_ring "
                . "INNER JOIN user ON user_ring.user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_ring FROM user_ring "
                . "INNER JOIN ring ON ring.ring_id = user_ring.ring_id "
                . "INNER JOIN user ON ring.user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_ring_password FROM user_ring_password "
                . "INNER JOIN user ON user_ring_password.user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_ring_password FROM user_ring_password "
                . "INNER JOIN user ON user_ring_password.ring_user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE rhythm_user_data FROM rhythm_user_data "
                . "INNER JOIN user ON rhythm_user_data.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE rhythm_extra FROM rhythm_extra "
                . "INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id "
                . "INNER JOIN user ON rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream_extra FROM stream_extra "
                . "INNER JOIN stream ON stream.stream_id = stream_extra.stream_id "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE ring_take_name  FROM ring_take_name  "
                . "INNER JOIN ring ON ring.ring_id = ring_take_name.ring_id "
                . "INNER JOIN user ON ring.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE rhythm FROM rhythm "
                . "INNER JOIN user ON rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE ring FROM ring  "
                . "INNER JOIN user ON ring.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE stream FROM stream "
                . "INNER JOIN user ON stream.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE invitation FROM invitation "
                . "INNER JOIN user ON invitation.from_user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE invitation FROM invitation "
                . "INNER JOIN user ON invitation.to_user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE invitation FROM invitation "
                . "INNER JOIN ring ON ring.ring_id = invitation.ring_id "
                . "INNER JOIN user ON ring.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_rhythm FROM user_rhythm "
                . "INNER JOIN user ON user_rhythm.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_config FROM user_config "
                . "INNER JOIN user ON user_config.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_feature_usage FROM user_feature_usage "
                . "INNER JOIN user ON user_feature_usage.user_id = user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE user_profile FROM user_profile "
                . "INNER JOIN user ON user_profile.user_id= user.user_id "
                . "WHERE user.test_user = 1",
            "DELETE FROM user "
                . "WHERE test_user = 1",
        );

        $this->markTestUsers();
        $this->fetchTestUsers();
        $this->deleteStaleData();
        $this->restoreTestData();
    }

}