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
 * A collection of static functions that affect multiple db tables to do with streams.
 *
 * @package PHP_Model_Forms
 */
class StreamMulti
{

    /**
     * Duplicates a stream and al its dependencies.
     *
     * @param Stream $model A stream model with its dependencies.
     * @param String $new_name The name for the new stream.
     *
     * @return Stream|String The updated model or an error message.
     */
    public static function duplicateStream($model, $new_name) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            // Validate new name
            $model->name = $new_name;
            $model->setScenario('duplicate');
            if ($model->validate() === false) {
                $transaction->rollBack();
                return $model->getError('name');
            }

            $model->extra->version_id = Version::insertNew(LookupHelper::getID('version.type', 'stream'));
            $model->extra->version->version_id = $model->extra->version_id;
            $model->extra->version->major = 0;
            $model->extra->version->minor = 0;
            $model->extra->version->patch = 0;

            $old_id = $model->extra->stream_extra_id;
            $model->stream_id = null;
            $model->extra->stream_extra_id = null;
            $model->extra->status_id =  StatusHelper::getID('private');
            $model->isNewRecord = true;
            $model->extra->isNewRecord = true;
            $model->extra->date_created = null;

            $model->validate();
            $model->extra->validate();
            if ($model->hasErrors() === true || $model->extra->hasErrors() === true) {
                $transaction->rollBack();
                return $model->getError('name');
            }

            $model->save();
            $model->extra->stream_id = $model->stream_id;
            $model->extra->save();

            Version::updateType(
                $model->extra->version_id,
                LookupHelper::getid('version.type', 'stream'),
                $model->stream_id
            );

            // Copy attatched post fields
            StreamField::copyFields($old_id, $model->extra->stream_extra_id);

            // Copy attatched child streams
            StreamBedMulti::copyChildren($old_id, $model->extra->stream_extra_id);

            StreamDefaultRing::duplicateForNewStream($old_id, $model->extra->stream_extra_id);

            StreamDefaultRhythm::defaultForNewRhythm($old_id, $model->extra->stream_extra_id);

            StreamMulti::createMetaPost($model);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when creating a new stream. ' . $e);
        }
        return $model;
    }

    /**
     * Create a new version of a stream.
     *
     * @param  $model A stream model with its dependencies.
     * @param String $new_version A new version string in the 'major/minor/patch' format.
     *
     * @return Stream|String The updated model or an error message.
     */
    public static function newVersion($model, $new_version) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $family_id = $model->extra->version->family_id;
            $valid_next = Version::checkValidNext(
                $family_id, LookupHelper::getID('version.type', 'stream'),
                $new_version
            );
            if ($valid_next === true) {
                $model->extra->version_id = Version::insertNewFromString(
                    $family_id,
                    LookupHelper::getID('version.type', 'stream'),
                    $new_version
                );
            } else {
                $transaction->rollBack();
                return 'The selected version is invalid.';
            }

            $old_id = $model->extra->stream_extra_id;
            $model->extra->stream_extra_id = null;
            $model->extra->status_id = StatusHelper::getID('private');
            $model->extra->isNewRecord = true;
            $model->extra->date_created = null;

            $model->extra->validate();
            if ($model->extra->hasErrors() === true) {
                $transaction->rollBack();
                return 'The new version did not save.';
            }
            $model->extra->save();

            Version::updateType(
                $model->extra->version_id,
                LookupHelper::getid('version.type', 'stream'),
                $model->stream_id
            );

            // Copy attatched post fields
            StreamField::copyFields($old_id, $model->extra->stream_extra_id);

            // Copy attatched child streams
            StreamBedMulti::copyChildren($old_id, $model->extra->stream_extra_id);

            StreamDefaultRing::duplicateForNewStream($old_id, $model->extra->stream_extra_id);

            StreamDefaultRhythm::defaultForNewRhythm($old_id, $model->extra->stream_extra_id);

            $version_parts = explode('/', $new_version);
            $model->extra->version->major = $version_parts[0];
            $model->extra->version->minor = $version_parts[1];
            $model->extra->version->patch = $version_parts[2];
            StreamMulti::createMetaPost($model);

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when creating a new stream. ' . $e);
        }
        return $model;
    }


    /**
     * Generate search results for a stream.
     *
     * @param StreamSearchForm $fmodel Contains the search paramaters.
     *
     * @return array An array of search result rows.
     */
    public static function searchForStreams($fmodel) {
        // Split the version filter into major, minor and patch
        $versions = Version::splitPartialVersionString($fmodel->version_filter);

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 site.domain AS domain
                ,user.username
                ,stream.name
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS version
                ,stream_extra.date_created
                ,status.value AS status
                ,lookup_post_mode.value AS post_mode
                ,lookup_edit_mode.value AS edit_mode
                ,stream_extra.meta_post_id AS meta_post_id
                ,lookup_group_period.value AS group_period
                ,lookup_kind.value AS stream_kind
            FROM
                stream
                INNER JOIN stream_extra ON stream.stream_id = stream_extra.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN status ON stream_extra.status_id = status.status_id
                INNER JOIN lookup AS lookup_post_mode ON stream_extra.post_mode = lookup_post_mode.lookup_id
                INNER JOIN lookup AS lookup_edit_mode ON stream_extra.edit_mode = lookup_edit_mode.lookup_id
                INNER JOIN lookup AS lookup_kind ON stream.kind = lookup_kind.lookup_id
                LEFT JOIN lookup AS lookup_group_period ON stream_extra.group_period = lookup_group_period.lookup_id
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
                $sql .= " AND stream.name = :name_filter";
            } else {
                $sql .= " AND stream.name LIKE CONCAT('%', :name_filter, '%')";
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
        if (strlen($fmodel->kind) > 0) {
            $sql .= " AND stream.kind = :kind_id";
        }
        if (strlen($fmodel->status) > 0) {
            $sql .= " AND stream_extra.status_id = :status_id
                AND (stream_extra.status_id != 1 OR stream.user_id = :user_id)";
        } else {
            $sql .= " AND (stream_extra.status_id != 1 OR stream.user_id = :user_id) ";
        }
        if ($fmodel->include_test_users === false) {
            $sql .= " AND user.test_user = false";
        }
        if ($fmodel->show_version === false) {
            $sql .= " GROUP BY site.domain, user.username, stream.name ";
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
                    $sql .= $order_by_comma . 'stream.name ' . $sort_direction;
                    break;
                case 'status':
                    $sql .= $order_by_comma . 'status.value ' . $sort_direction;
                    break;
                case 'version':
                    $sql .= $order_by_comma . 'version.major ' . $sort_direction;
                    $sql .= ', version.minor ' . $sort_direction;
                    $sql .= ', version.patch ' . $sort_direction;
                    break;
                case 'stream_kind':
                    $sql .= $order_by_comma . 'lookup_kind.value ' . $sort_direction;
                    break;
            }
            $order_by_comma = ', ';
        }

        $sql .= " LIMIT :start, :row_qty";

        $command = $connection->createCommand($sql);

        if (strlen($fmodel->domain_filter) > 0) {
            $command->bindValue(':domain_filter', $fmodel->domain_filter, PDO::PARAM_STR);
        }
        if (strlen($fmodel->username_filter) > 0) {
            $command->bindValue(':username_filter', $fmodel->username_filter, PDO::PARAM_STR);
        }
        if (strlen($fmodel->name_filter) > 0) {
            $command->bindValue(':name_filter', $fmodel->name_filter, PDO::PARAM_STR);
        }
        if (ctype_digit($versions['major']) === true) {
            $command->bindValue(':major', $versions['major'], PDO::PARAM_INT);
        }
        if (ctype_digit($versions['minor']) === true) {
            $command->bindValue(':minor', $versions['minor'], PDO::PARAM_INT);
        }
        if (ctype_digit($versions['patch']) === true) {
            $command->bindValue(':patch', $versions['patch'], PDO::PARAM_INT);
        }
        if (strlen($fmodel->kind) > 0) {
            $command->bindValue(
                ':kind_id',
                LookupHelper::getID('stream.kind', $fmodel->kind, false),
                PDO::PARAM_INT
            );
        }
        if (strlen($fmodel->status) > 0) {
            $command->bindValue(
                ':status_id',
                StatusHelper::getID($fmodel->status),
                PDO::PARAM_INT
            );
        }
        $command->bindValue(':user_id', Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(':start', intval(($fmodel->page - 1) * 10), PDO::PARAM_INT);
        $command->bindValue(':row_qty', intval($fmodel->row_qty), PDO::PARAM_INT);
        $streams = $command->queryAll();

        return $streams;
    }

    /**
     * Delete an extra stream version and all that depends on it.
     *
     * If this is the only version of the stream then also delete the stream row.
     *
     * @param Integer $stream_extra_id The extra id of the stream to delete.
     *
     * @return True|String  True or an error message.
     */
    public static function deleteStream($stream_extra_id) {

        try {
            $stream_id = StreamExtra::getStreamID($stream_extra_id);

            $delete_multi = new DeleteMulti;
            $delete_multi->deleteStreamExtraByStreamExtraId($stream_extra_id);

            $version_count = StreamExtra::getVersionCount($stream_id);
            if ($version_count === 0) {
                Stream::deleteByStreamId($stream_id);
            }
        } catch (Exception $ex) {
            return 'Stream failed to delete. ' . $ex->getMessage();
        }
        return true;
    }

    /**
     * Check if a stream is deletable.
     *
     * True if its status is private or it has no posts by users that are not the owner.
     *
     * @param type $stream_extra_id
     *
     * @return boolean
     */
    public static function isDeletable($stream_extra_id, $user_id) {
        $status = StreamExtra::getStatus($stream_extra_id);
        if ($status === 'public' || $status === 'deprecated' ) {
            $posts_by_others = Post::areTherePostsNotByOwner($stream_extra_id, $user_id);
            if ($posts_by_others === true) {
                return false;
            } else {
                return true;
            }
        } else if ($status === 'private') {
                return true;
        } else {
            return false;
        }
    }

    /**
     * Get all the extra ids for a stream. Will normally return just one result, but if one of the version numbsers
     *      is set to 'all' Then it will return all the extra ids within that.
     *
     * @param intger $stream_user_id The id of the user that owns the stream.
     * @param string $name The name of the stream.
     * @param string $major The major version of the stream. Or 'latest', or 'all'.
     * @param string $minor The minor version of the stream. Or 'latest', or 'all'.
     * @param string $patch The patch version of the stream. Or 'latest', or 'all'.
     * @param boolean $all_private Should all private versions be fetched even if they are not owned by the
     *      current logged on user.
     */
    public static function getAllStreamExtraIds($stream_user_id, $name, $major, $minor, $patch, $all_private) {

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 stream_extra.stream_extra_id
                ,version.major
                ,version.minor
                ,version.patch
            FROM
                stream
                INNER JOIN stream_extra ON stream.stream_id = stream_extra.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user ON stream.user_id = user.user_id
            WHERE
                stream.name = :name
                AND stream.user_id = :stream_user_id
                AND (version.major = :major OR :major = 'latest' OR :major = 'all')
                AND (version.minor = :minor OR :minor = 'latest' OR :minor = 'all')
                AND (version.patch = :patch OR :patch = 'latest' OR :patch = 'all')
                AND (stream_extra.status_id != :private_status_id
                    OR user.user_id = :current_user_id
                    OR :all_private = 'true')
            ORDER BY version.major DESC, version.minor DESC, version.patch DESC";
        $command = $connection->createCommand($sql);
        $command->bindValue(':name', $name, PDO::PARAM_STR);
        $command->bindValue(':stream_user_id', $stream_user_id, PDO::PARAM_INT);
        $command->bindValue(':major', $major, PDO::PARAM_STR);
        $command->bindValue(':minor', $minor, PDO::PARAM_STR);
        $command->bindValue(':patch', $patch, PDO::PARAM_STR);
        $command->bindValue(':private_status_id', StatusHelper::getID('private'), PDO::PARAM_STR);
        $command->bindValue(':current_user_id', Yii::app()->user->getId(), PDO::PARAM_INT);
        $all_private_string = $all_private === true ? 'true' : 'false';
        $command->bindValue(':all_private', $all_private_string, PDO::PARAM_STR);
        $original_rows = $command->queryAll();
        if (count($original_rows) === 0) {
            return array();
        }

        // remove any rows that are not 'latest' when 'latest' is set.
        if ($major === 'latest') {
            $rows = array();
            $current_major = $original_rows[0]['major'];
            foreach ($original_rows as $row) {
                if ($row['major'] === $current_major) {
                    $rows[] = $row;
                }
            }
            $original_rows = $rows;
        }
        if ($minor === 'latest') {
            $rows = array();
            $current_minor = $original_rows[0]['minor'];
            $current_major = $original_rows[0]['major'];
            foreach ($original_rows as $row) {
                if ($row['major'] !== $current_major) {
                    $current_major = $row['major'];
                    $current_minor = $row['minor'];
                }
                if ($current_minor === $row['minor']) {
                    $rows[] = $row;
                }
            }
            $original_rows = $rows;
        }
        if ($patch === 'latest') {
            $rows = array();
            $current_minor = $original_rows[0]['minor'];
            $current_major = $original_rows[0]['major'];
            $current_patch = $original_rows[0]['patch'];
            foreach ($original_rows as $row) {
                if ($row['major'] !== $current_major) {
                    $current_major = $row['major'];
                    $current_minor = $row['minor'];
                    $current_patch = $row['patch'];
                }
                if ($row['minor'] !== $current_minor) {
                    $current_minor = $row['minor'];
                    $current_patch = $row['patch'];
                }
                if ($current_patch === $row['patch']) {
                    $rows[] = $row;
                }
            }
            $original_rows = $rows;
        }

        $stream_extra_ids = array();
        foreach ($original_rows as $row) {
            $stream_extra_ids[] = $row['stream_extra_id'];
        }
        return $stream_extra_ids;
    }


    /**
     * Get the last + one display order of streams for this user. Used for inserting a new stream at the end.
     *
     * @param integer $user_id The id of the user to fetch stream display order for.
     *
     * @return integer
     */
    public static function getNextDisplayOrder($user_id) {
        $model = UserStreamSubscription::model()->find(
            array(
                'select' => 'display_order',
                'order' => 'display_order DESC',
                'condition' => 'user_id = :user_id',
                'params' => array(
                    ":user_id" => $user_id,
                )
            )
        );

        // Return 1 if no entries are found for this user
        if (isset($model) === false) {
            return 1;
        }

        return $model->display_order + 1;
    }

    /**
     * Get the full and partial versions for an stream that is linked via $user_stream_subscription_id.
     *
     * @param integer $user_stream_subscription_id The id of the user post stream to fetch version information for.
     *
     * @return array Ready for encoding to JSON.
     */
    public static function getVersions($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 user_stream_subscription.version_type
                ,stream_version.family_id AS type_family
            FROM user_stream_subscription
                LEFT JOIN stream_extra
                    ON stream_extra.stream_extra_id = user_stream_subscription.stream_extra_id
                LEFT JOIN version as stream_version
                    ON stream_extra.version_id = stream_version.version_id
            WHERE user_stream_subscription.user_stream_subscription_id = :user_stream_subscription_id";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $row = $command->queryRow();

        if (isset($row) === false) {
            throw new Exception("Stream id not found");
        }

        if ($row['type_family'] !== null) {
            $family_id = $row['type_family'];
            $version_type = "stream";
        }

        $full_versions = Version::getPublicVersions($family_id, $version_type);
        $partial_versions = Version::getPartialVersions($full_versions);

        return $partial_versions;
    }

    /**
     * Update the version of a stream that is subscribed to.
     *
     * @param integer $user_stream_subscription_id The id of the user post stream whose version is being updated.
     * @param integer $user_id The id of the  user who is supposed to own this stream - used to check.
     * @param string $partial_version A full or partial version.
     *
     * @return boolean Success
     */
    public static function updateVersion($user_stream_subscription_id, $user_id, $partial_version) {
        if (StreamMulti::checkOwner($user_stream_subscription_id, $user_id) === false) {
            throw new Exception("User does not own this stream");
        }

        $full_version = Version::getBaseVersion($partial_version);
        $version_array = explode("/", $full_version);
        $version_type = Version::getTypeId($partial_version);

        // Fetch the stream_id or post_extra_id from the stream_extra_id and then
        // select the new stream_extra_id for this version
        $stream = StreamMulti::getStream($user_stream_subscription_id);
        $stream_extra_id = null;
        if ($stream->stream_extra_id !== null) {
            $stream_extra_id = StreamBedMulti::getNewTypeVersion($stream->stream_extra_id, $version_array);
            // !!! if not found and not local then check site of origin and then error if still not present.
        }


        $count = $model = UserStreamSubscription::model()->updateByPk(
            $user_stream_subscription_id,
            array(
                'stream_extra_id' => $stream_extra_id,
                'version_type' => $version_type,
            ),
            "",
            array()
        );

        if ($count > 0) {
            return true;
        }

        return false;
    }


    /**
     * Fetch the model for this primary key.
     *
     * @param integer $user_stream_subscription_id The priamrty key of the stream that is being fetched.
     *
     * @return UserStreamSubscription
     */
    public static function getStream($user_stream_subscription_id) {
        return UserStreamSubscription::model()->findbyPk($user_stream_subscription_id);
    }

    /**
     * Check that the user owns this stream.
     *
     * @param integer $user_stream_subscription_id The primary key of the stream we are checking.
     * @param integer $user_id The id of the suer we are checking owns this stream.
     *
     * @return type
     */
    public static function checkOwner($user_stream_subscription_id, $user_id) {
        $model = UserStreamSubscription::model()->find(
            array(
                'select' => 'user_id',
                'condition' => 'user_stream_subscription_id = :user_stream_subscription_id',
                'params' => array(
                    ":user_stream_subscription_id" => $user_stream_subscription_id,
                )
            )
        );

        if (isset($model) === false) {
            throw new Exception("user_stream_subscription row does not exist: " . $user_stream_subscription_id);
        }

        if (intval($model->user_id) === $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Insert a filter for a user post stream.
     *
     * @param StreamFilterSubscription $fmodel Contains details of the subscription.
     * @param integer $user_id The id of the user who owns the stream we are subscribing a fiter to.
     *
     * @return integer|string ID of the subscription or an error message.
     */
    public static function subscribeStreamFilter($fmodel, $user_id) {
        // Check user owns this stream
        $exists = UserStreamSubscription::model()->findByPk(
            $fmodel->user_stream_subscription_id,
            "user_id=:user_id",
            array(
                ":user_id" => $user_id,
            )
        );
        if (isset($exists) === false) {
            throw new Exception(
                "Attempting to insert a filter for the wrong user, user_id:"
                    . $user_id . " , user_stream_subscription_id:" . $fmodel->user_stream_subscription_id
            );
        }

        // Get the user id
        $filter_site_id = SiteMulti::getSiteID($fmodel->domain);
        $user_multi = new UserMulti($filter_site_id);
        $filter_user_id = $user_multi->getIDFromUsername($fmodel->username);
        $rhythm_id = Rhythm::getVersionFamily($filter_user_id, $fmodel->name);
        $version_type = LookupHelper::getID('version.type', 'rhythm');
        $version_array = Version::getLatestVersionFromString($fmodel->version, $rhythm_id, $version_type);

        $rhythm_extra_id = Rhythm::getIDByName(
            $filter_user_id,
            $fmodel->name,
            $version_array['major'],
            $version_array['minor'],
            $version_array['patch']
        );
        // @ !!! if not found and not local then fetch from remote source, if still not found throw an error.
        $filter = new UserStreamSubscriptionFilter;
        $filter->user_stream_subscription_id = $fmodel->user_stream_subscription_id;
        $filter->rhythm_extra_id = $rhythm_extra_id;
        $filter->version_type = LookupHelper::getID("version_type", 'latest/latest/latest');
        $filter->display_order = StreamMulti::getNextFilterDisplayOrder($fmodel->user_stream_subscription_id);

        if ($filter->validate() === false) {
            return "Stream filter does not validate: " . ErrorHelper::model($filter->getErrors());
        }

        $filter->Save();
        return $filter->user_filter_id;
    }

    /**
     * Get the last + one display order of streams for this user.
     *
     * Used for inserting a new stream at the end
     *
     * @param integer $user_stream_subscription_id The id of the users stream subscription we
     *                                      are getting the display order for.
     *
     * @return integer
     */
    public static function getNextFilterDisplayOrder($user_stream_subscription_id) {
        $model = UserStreamSubscriptionFilter::model()->find(
            array(
                'select' => 'display_order',
                'order' => 'display_order DESC',
                'condition' => 'user_stream_subscription_id = :user_stream_subscription_id',
                'params' => array(
                    ':user_stream_subscription_id' => $user_stream_subscription_id,
                )
            )
        );

        // Return 1 if no entries are found for this user
        if (isset($model) === false) {
            return 1;
        }

        return $model->display_order + 1;
    }

    /**
     * Creates an post in the stream meta stream to enable conversation about a stream.
     *
     * @param Stream $model A model of the stream that a meta post is being created for
     *                         Contains extra, extra->version and user sub models.
     * @param boolean $update_stream If this is true then the stream_extra row in the model is updated.
     *
     * @return integer The id of the newly inserted post.
     */
    public static function createMetaPost($model, $update_stream=true) {
        $stream_title = 'http://' . $model->user->site->domain .'/' . $model->user->username . '/stream' . '/posts/'
            . $model->name . '/'. $model->extra->version->major . '/'. $model->extra->version->minor
            . '/'. $model->extra->version->patch;
        $stream_link = 'http://' . $model->user->site->domain .'/' . rawurlencode($model->user->username)
            . '/stream' . '/posts/' . rawurlencode($model->name) . '/'. $model->extra->version->major
            . '/'. $model->extra->version->minor . '/'. $model->extra->version->patch;

        $view_title = 'http://' . $model->user->site->domain . '/' . $model->user->username . '/stream' . '/view/'
            . $model->name . '/'. $model->extra->version->major . '/'. $model->extra->version->minor
            . '/'. $model->extra->version->patch;
        $view_link = 'http://' . $model->user->site->domain .'/' . rawurlencode($model->user->username)
            . '/stream' . '/view/' . rawurlencode($model->name) . '/'. $model->extra->version->major
            . '/'. $model->extra->version->minor . '/'. $model->extra->version->patch;

        $result = PostMulti::insertPost(
            Yii::app()->params['meta_stream_extra_id'],
            array(
                array(
                    'display_order' => '1',
                    'text' => 'Meta conversation for the ' . $model->name . ' stream',
                ),
                array(
                    'display_order' => '2',
                ),
                array(
                    'display_order' => '3',
                    'link_title' => $stream_title,
                    'link' => $stream_link,
                ),
                array(
                    'display_order' => '4',
                    'link_title' => $view_title,
                    'link' => $view_link,
                ),
                array(
                    'display_order' => '5',
                    'text' => $model->extra->description,
                ),
            ),
            Yii::app()->params['system_user_id']
        );
        if (is_array($result) === true) {
            throw new Exception("Meta post for stream not submitting. " . ErrorHelper::ary($result));
        } else if ($result === false) {
            throw new Exception("Remote site not accepting new post.  Should never happen as it should be local.");
        } else {
            if ($update_stream === true) {
                StreamExtra::updateMetaPostId($model->extra->stream_extra_id, $result->post_id);
            }
        }

        return $result->post_id;
    }
}

?>
