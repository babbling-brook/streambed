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
 * A collection of static functions that affect multiple db tables to do with kindred relationships.
 *
 * @package PHP_Model_Forms
 */
class KindredMulti
{

    /**
     * Recalculates all the total scores for a user kindred rhythm. Run after the rhythm has generated new scores.
     *
     * @param type $user_id The id of the user whose kindred rhythm has run.
     * @param type $user_rhythm_id The id of the rhythm that generated the kindred results.
     *
     * @return void
     */
    public static function recalculateTotalsForUserRhythm($user_id, $user_rhythm_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            Kindred::deleteTotalsForUserRhythm($user_id, $user_rhythm_id);
            $connection = Yii::app()->db;
            $sql = "
                INSERT INTO kindred (user_id, kindred_user_id, score, user_rhythm_id)
                SELECT
                     user_id
                    ,scored_user_id
                    ,SUM(score) AS score
                    ,user_rhythm_id
                FROM take_kindred
                WHERE
                    user_id = :user_id
                    AND user_rhythm_id = :user_rhythm_id
                GROUP BY scored_user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
            $command->execute();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when collating kindred results for a users rhythm. ' . $e);
        }
    }


    /**
     * Fetches a users kindred tag data.
     *
     * THIS REQUEST IS GOING TO BE HEFTY WHEN DATA GETS LARGE.
     *
     * This equation counts the log of each kindred users total use of a tag
     * This means that whilst more use counts, it only counts a bit.
     * eg. a total of 10 uses counts as 3, but 100 only as 5.
     *
     * @param type $user_id The id of the user whose kindred tags are being fetched.
     *
     * @return void
     */
    public static function getKindredTags($user_id) {

        $rhythm_url = UserConfig::getConfigRow($user_id, 'kindred_rhythm_url');
        $rhythm_extra_id = Rhythm::getIDFromUrl($rhythm_url);
        $version = Version::getFromUrl($rhythm_url);
        $version_type_id = Version::getTypeId($version);
        $user_rhythm_id = UserRhythm::getUserRhythmId($rhythm_extra_id, $version_type_id, $user_id);

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 SUM(CEIL(LOG(user_stream_count.total * 1.0) )) AS score
                ,stream_site.domain AS stream_domain
                ,stream_user.username AS stream_username
                ,stream.name AS stream_name
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
            FROM kindred
                INNER JOIN user_stream_count ON kindred.kindred_user_id = user_stream_count.user_id
                INNER JOIN stream_extra ON user_stream_count.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
            WHERE
                kindred.user_id = :user_id
                AND kindred.user_rhythm_id = :user_rhythm_id
            GROUP BY user_stream_count.stream_extra_id
            ORDER BY score";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}