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
 * A collection of static functions that affect multiple db tables to do with takes.
 */
class TakeMulti
{

    /**
     * Take up an post.
     *
     * @param integer $post_id The id of the post being taken.
     * @param integer $value The value to take the post for.
     * @param integer $user_id The ID of the user taking the post.
     * @param integer $field_id The id of field in the stream that the value is based in.
     * @param string $mode Valid values are 'new' or 'add'.
     *                     New creates a new value. Add, adds the passsed in value to the old one.
     *
     * @return integer|boolean current value of take or false.
     * @refactor Having an add mode just complicates things. Let the client work out what the current value is
     *      before sending it through.
     */
    public static function take($post_id, $value, $user_id, $field_id, $mode) {

        // Check if the user already has a take on this, if so update it.
        $old_value = Take::getTake($post_id, $user_id, $field_id);

        // The normal mode is "add", this works out the add value for a new passed in value
        if ($old_value !== false && $mode === "add") {
            $value = $old_value + $value;
        }

        // Check min/max have not been exceeded
        $minmax = PostMulti::getMinMax($post_id, $field_id);
        if ($minmax['value_min'] > $value && $minmax['value_min'] !== null) {
            // If both min and max are above zero then set value to zero if already at the lowest result.
            if ($minmax['value_min'] > 0 && $old_value !== 0) {
                $value = 0;
            } else {
                $value = $minmax['value_min'];
            }
        }
        if ($minmax['value_max'] < $value && $minmax['value_max'] !== null) {
            // If both min and max are above zero then set value to zero if already at the lowest result.
            if ($minmax['value_max'] < 0 && $old_value !== 0) {
                $value = 0;
            } else {
                $value = $minmax['value_max'];
            }
        }

        $take_id = 0;
        // Need to check if we have a zero value take and need to delete it.
        $post_row =  PostMulti::getPostRow($post_id);
        if ($value === 0) {
            $take_id = Take::getTakeID($post_id, $user_id, $field_id);
            TakeKindred::deleteByTakeId($take_id);
            TakeMulti::deleteTake($post_id, $user_id, $field_id);
        } else {
            if ($old_value !== false) {
                $value = Take::updateTake($post_id, $value, $user_id, $field_id);
            } else {
                $value = Take::insertTake($post_id, $value, $user_id, $field_id, $post_row["stream_extra_id"]);
            }
            $take_id = Take::getTakeID($post_id, $user_id, $field_id);
        }

        // takes against users are also stored againt the user.
        $kind_id = Stream::getKindFromPostID($post_id);
        if (LookupHelper::getValue($kind_id) === "user") {
            $post_user_id = PostUser::getUserId($post_id);    // The user_id of the person the post is for.
            $user_take_id = UserTake::getId(
                $post_user_id,
                $post_row['stream_extra_id'],
                $take_id,
                $post_id,
                $user_id
            );
            if ($value !== false) {
                if ($value !== 0 && $user_take_id === false) {
                    UserTake::insertTake(
                        $post_user_id,
                        $post_row['stream_extra_id'],
                        $take_id,
                        $post_id,
                        $user_id
                    );
                }
            }
        }
        return $value;
    }

    /**
     * Delete a take.
     *
     * WARNING this may error if TakeKindred::deleteByTakeId($take_id) is not called first.
     *
     * @param integer $post_id The id of the post that we are deleting a take from.
     * @param integer $user_id The id of the user who owns the take we are deleting.
     * @param integer $field_id The id of the post value field that we are deleting a take from.
     * @param boolean [$delete_remote=true] Should remote takes be deleted.
     *
     * @return void
     */
    public static function deleteTake($post_id, $user_id, $field_id, $delete_remote=false) {

        if ($delete_remote === true) {
            if (Post::isLocal($post_id) === false) {
                return;
            }
        }

        // Ensure any user take record is also deleted0
        $kind_id = Stream::getKindFromPostID($post_id);
        if (LookupHelper::getValue($kind_id) === "user") {
            $take_id = Take::getTakeID($post_id, $user_id, $field_id);
            UserTake::deleteByTakeIdAndTakeUserId($take_id, $user_id);
        }

        Take::model()->deleteAll(
            array(
                "condition" => "post_id = :post_id AND user_id = :user_id AND field_id = :field_id",
                "params" => array(
                    ":post_id" => $post_id,
                    ":user_id" => $user_id,
                    ':field_id' => $field_id,
                )
            )
        );
    }

    /**
     * Fetch takes by this user.
     *
     * @param integer $user_id The id of the user we are fetchign takes for.
     * @param string $range The What range of time are we fetching takes for. Valid values are: day|month|year.
     * @param integer $timestamp The time from which the range starts.
     *
     * @return array
     */
    public static function getTakes($user_id, $range, $timestamp) {
        if ($range === 'day') {
            $start_date = date("Y-m-d", $timestamp);
            $end_date = date("Y-m-d", strtotime("+1 day", $timestamp));
        } else if ($range === 'month') {
            $start_date = date("Y-m-01", $timestamp);
            $end_date =date("Y-m-01", strtotime("+1 month", $timestamp));
        } else if ($range === 'year') {
            $start_date = date("Y-01-01", $timestamp);
            $end_date = date("Y-01-01", strtotime("+1 year", $timestamp));
        }

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 take.post_id
                ,take.value
                ,take.field_id
                ,site.domain
            FROM take
                INNER JOIN post ON post.post_id = take.post_id
                INNER JOIN site on post.site_id = site.site_id
            WHERE
                take.date_taken BETWEEN :start_date AND :end_date
                AND take.user_id = :user_id
            ORDER BY site.site_id, take.post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":start_date", $start_date, PDO::PARAM_STR);
        $command->bindValue(":end_date", $end_date, PDO::PARAM_STR);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        $takes = array();
        $domain = "";
        foreach ($rows as $row) {
            if ($domain !== $row['domain']) {
                $domain = $row['domain'];
                $takes[$domain] = array();
            }
            if (isset($takes[$domain][$row['post_id']]) === false) {
                $takes[$domain][$row['post_id']] = array();
            }
            $takes[$domain][$row['post_id']][$row['field_id']] = $row['value'];
        }
        return $takes;
    }

    /**
     * Fetch takes for the given rhythm_id that have not been processed.
     *
     * @param GetTakesForm $fmodel Validated takes data request form.
     *
     * @return array Results to be converted to JSON
     */
    public static function getNextKindredTakes($fmodel) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 take.take_id
                ,take.date_taken AS timestamp_taken
                ,take.value
                ,take.field_id
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,ot_site.domain AS stream_domain
                ,CONCAT(ot_version.major, '/', ot_version.minor, '/', ot_version.patch) AS stream_version
                ,o_user.user_id AS post_user_id
                ,o_user.username AS post_username
                ,o_site.domain AS post_domain
                ,post.post_id AS o_local_id
            FROM take
                INNER JOIN post ON take.post_id = post.post_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user AS ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN site AS ot_site ON ot_user.site_id = ot_site.site_id
                INNER JOIN version AS ot_version ON stream_extra.version_id = ot_version.version_id
                INNER JOIN user AS o_user ON post.user_id = o_user.user_id
                INNER JOIN site AS o_site ON o_user.site_id = o_site.site_id
                LEFT JOIN take_kindred
                     ON take.take_id = take_kindred.take_id
                     AND take_kindred.user_rhythm_id = :user_rhythm_id
                WHERE
                    take.user_id = :user_id
                    AND o_user.user_id != :user_id
                    AND take_kindred.score IS NULL
                    AND (take.field_id = 2 OR :all_values = 1)
                ORDER BY take.date_taken DESC
                LIMIT 0, " . Yii::app()->params['takes_to_process'];

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":user_rhythm_id", $fmodel->user_rhythm_id, PDO::PARAM_INT);
        if ($fmodel->all_values === true) {
            $all_values = 1;
        } else {
            $all_values = 0;
        }
        $command->bindValue(":all_values", $all_values, PDO::PARAM_INT);
        $rows = $command->queryAll();

        return $rows;
    }

    /**
     * Get a users kindred data.
     *
     * @param integer $user_id The id of the user we are fetching kindred data for.
     *
     * @return array
     */
    public static function getKindredData($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 SUM(score) AS score
                ,user.username
                ,site.domain
            FROM take_kindred
                INNER JOIN user ON take_kindred.scored_user_id = user.user_id
                INNER JOIN site on user.site_id = site.site_id
            WHERE
                take_kindred.user_id = :user_id
            GROUP BY scored_user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        $ary = array();
        foreach ($rows as $row) {
            $ary[$row['domain'] . "/" . $row['username']] = $row['score'];
        }

        return $ary;
    }

    /**
     * Fetch all the takes within a stream block.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch a block number for.
     * @param integer $block_number The stream block number to fetch takes for.
     * @param integer $field_id The id of the field to fetch takes for.
     *
     * @return object a nested array of data ready to be parsed to JSON for the protocol. See scientia.GetStreamTakes.
     */
    public static function getTakesForBlock($stream_extra_id, $block_number, $field_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 take.value
                ,take.post_id
                ,UNIX_TIMESTAMP(take.date_taken) AS timestamp
                ,take_user.username AS take_user_username
                ,take_site.domain AS take_user_domain
                ,post_user.username AS post_user_username
                ,post_site.domain AS post_user_domain
                ,stream_site.domain AS post_domain
            FROM take
                INNER JOIN post ON take.post_id = post.post_id
                INNER JOIN user AS take_user ON take.user_id = take_user.user_id
                INNER JOIN site AS take_site ON take_user.site_id = take_site.site_id
                INNER JOIN user AS post_user ON post.user_id = post_user.user_id
                INNER JOIN site AS post_site ON post_user.site_id = post_site.site_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
            WHERE
                take.stream_extra_id = :stream_extra_id
                AND post.block = :block_number
                AND take.field_id = :field_id
            ORDER BY take.post_id, take_site.domain";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":block_number", $block_number, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>