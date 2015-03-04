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
 * A collction of static functions that affect multiple db tables to do with rhythms.
 *
 * @package PHP_Model_Forms
 */
class RhythmMulti
{

    /**
     * Generate search results for rhtyhms.
     *
     * @param RhythmSearchForm $fmodel Contains the search paramaters.
     *
     * @return array An array of search result rows.
     */
    public static function searchForRhythms($fmodel) {
        // Split the version filter into major, minor and patch
        $versions = Version::splitPartialVersionString($fmodel->version_filter);

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 site.domain AS domain
                ,user.username
                ,rhythm.name
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS version
                ,rhythm_extra.date_created
                ,status.value AS status
                ,rhythm_cat.name AS cat_type
                ,rhythm_extra.meta_post_id AS meta_post_id
            FROM
                rhythm
                INNER JOIN rhythm_extra ON rhythm.rhythm_id = rhythm_extra.rhythm_id
                INNER JOIN version ON rhythm_extra.version_id = version.version_id
                INNER JOIN user ON rhythm.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN status ON rhythm_extra.status_id = status.status_id
                INNER JOIN rhythm_cat ON rhythm_extra.rhythm_cat_id = rhythm_cat.rhythm_cat_id
            WHERE
                0 = 0";

        if (strlen($fmodel->domain_filter) > 0) {
            if ($fmodel->exact_match['domain'] === true) {
                $sql .= " AND site.domain = :domain_filter";
            } else {
                $sql .= " AND site.domain LIKE CONCAT('%', :domain_filter, '%')";
            }
        }
        if (strlen($fmodel->username_filter) > 0) {
            if ($fmodel->exact_match['username'] === true) {
                $sql .= " AND user.username = :username_filter";
            } else {
                $sql .= " AND user.username LIKE CONCAT('%', :username_filter, '%')";
            }
        }
        if (strlen($fmodel->name_filter) > 0) {
            if ($fmodel->exact_match['name'] === true) {
                $sql .= " AND rhythm.name = :name_filter";
            } else {
                $sql .= " AND rhythm.name LIKE CONCAT('%', :name_filter, '%')";
            }
        }
        if (ctype_digit($versions['major']) === true) {
            $sql .= " AND version.major = :major";
        }
        if (ctype_digit($versions['minor']) === true) {
            $sql .= " AND version.minor = :minor";
        }
        if (ctype_digit($versions['patch']) === true) {
            $sql .= " AND version.patch = :patch";
        }
        if (strlen($fmodel->cat_type) > 0) {
            $sql .= " AND rhythm_cat.name = :cat_type";
        }
        if (strlen($fmodel->status) > 0) {
            $sql .= " AND rhythm_extra.status_id = :status_id
                AND (rhythm_extra.status_id != 1 OR rhythm.user_id = :user_id)";
        } else {
            $sql .= " AND (rhythm_extra.status_id != 1 OR rhythm.user_id = :user_id) ";
        }
        if ($fmodel->include_test_users === false) {
            $sql .= " AND user.test_user = false";
        }
        if ($fmodel->show_version === false) {
            $sql .= " GROUP BY site.domain, user.username, rhythm.name ";
        }


        $sql .= " ORDER BY ";
        $order_by_comma = '';
        foreach ($fmodel->sort_priority as $sort) {
            if ($fmodel->sort_order[$sort] === 'ascending') {
                $sort_direction = ' ';
            } else {
                $sort_direction = ' DESC ';
            }
            switch ($sort) {
                case 'domain':
                    $sql .= $order_by_comma . 'site.domain ' . $sort_direction;
                    break;
                case 'username':
                    $sql .= $order_by_comma . 'user.username ' . $sort_direction;
                    break;
                case 'name':
                    $sql .= $order_by_comma . 'rhythm.name ' . $sort_direction;
                    break;
                case 'status':
                    $sql .= $order_by_comma . 'status.value ' . $sort_direction;
                    break;
                case 'version':
                    $sql .= $order_by_comma . 'version.major ' . $sort_direction;
                    $sql .= ', version.minor ' . $sort_direction;
                    $sql .= ', version.patch ' . $sort_direction;
                    break;
            }
            $order_by_comma = ', ';
        }

        $sql .= " LIMIT :start, :row_qty";

        $command = $connection->createCommand($sql);

        if (strlen($fmodel->domain_filter) > 0) {
            $command->bindValue(":domain_filter", $fmodel->domain_filter, PDO::PARAM_STR);
        }
        if (strlen($fmodel->username_filter) > 0) {
            $command->bindValue(":username_filter", $fmodel->username_filter, PDO::PARAM_STR);
        }
        if (strlen($fmodel->name_filter) > 0) {
            $command->bindValue(":name_filter", $fmodel->name_filter, PDO::PARAM_STR);
        }
        if (ctype_digit($versions['major']) === true) {
            $command->bindValue(":major", $versions['major'], PDO::PARAM_INT);
        }
        if (ctype_digit($versions['minor']) === true) {
            $command->bindValue(":minor", $versions['minor'], PDO::PARAM_INT);
        }
        if (ctype_digit($versions['patch']) === true) {
            $command->bindValue(":patch", $versions['patch'], PDO::PARAM_INT);
        }
        if (strlen($fmodel->cat_type) > 0) {
            $command->bindValue(":cat_type", $fmodel->cat_type, PDO::PARAM_STR);
        }
        if (strlen($fmodel->status) > 0) {
            $command->bindValue(
                ":status_id",
                StatusHelper::getID($fmodel->status),
                PDO::PARAM_INT
            );
        }
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":start", intval(($fmodel->page - 1) * 10), PDO::PARAM_INT);
        $command->bindValue(":row_qty", intval($fmodel->row_qty), PDO::PARAM_INT);
        $rhythms = $command->queryAll();

        return $rhythms;
    }

    /**
     * Delete a Rhythm by its rhythm extra id.
     *
     * Note: only Rhythms with a private status can be deleted.
     * Public and deprecated Rhythms must not be deleted.
     *
     * @param integer $rhythm_extra_id The Rhythm to delete.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $rhythm_id = RhythmExtra::getRhythmIdFromRhythmExtraId($rhythm_extra_id);

        $delete_multi = new DeleteMulti;
        $delete_multi->deleteRhythmByRhythmExtraId($rhythm_extra_id);

        // Remove the primary refference if all sub rows have been deleted
        $any_left = RhythmExtra::doAnyExistForRhythmId($rhythm_id);

        if ($any_left === false) {
            $delete_multi->deleteRhythm($rhythm_id);
        }
    }

    /**
     * Gets a full rhythm name array for a rhythm.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to fetch a name array for.
     *
     * @return array
     */
    public static function getRhythmNameArray($rhythm_extra_id) {
        $rhythm_model = Rhythm::getByIDWithSite($rhythm_extra_id);
        $rhythm_name = array(
            'domain' => $rhythm_model->rhythm->user->site->domain,
            'username' => $rhythm_model->rhythm->user->username,
            'name' => $rhythm_model->rhythm->name,
            'version' => array(
                'major' => $rhythm_model->version->major,
                'minor' => $rhythm_model->version->minor,
                'patch' => $rhythm_model->version->patch,
            ),
        );
        return $rhythm_name;
    }
}

?>
