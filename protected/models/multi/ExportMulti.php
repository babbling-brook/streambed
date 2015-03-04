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
 * A class for exporting all of a users data and bundling it up into a nested array structure
 * ready for conversion to JSON
 *
 * @package PHP_Model_Multi
 */
class ExportMulti
{
    /**
     * Gets this users takes.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getTakes($user_id) {
        $rows = Take::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'time_taken' => time($row['date_taken']),
                'post' => ExportMulti::getPost($row['post_id']),
                'value' => $row['value'],
                'stream_field_id' => $row['field_id'],
                'user_block' => $row['block_id'],
                'stream_block' => $row['stream_block_id'],
                'stream_block' => $row['tree_block_id'],
                'user_takes' => ExportMulti::getUserTakesForTake($row['take_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets ring data for this user that is also a ring.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getRing($user_id) {
        $rows = Ring::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $membership_rhythm = RhythmMulti::getRhythmNameArray($row['membership_rhythm_id']);
            $membership_rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['membership_rhythm_version_type'],
                $membership_rhythm['version']
            );
            $ring_rhythm = RhythmMulti::getRhythmNameArray($row['ring_rhythm_id']);
            $ring_rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['ring_rhythm_version_type'],
                $ring_rhythm['version']
            );
            $item = array(
                'membership_type' => LookupHelper::getValue($row['membership_type']),
                'membership_rhythm' => $membership_rhythm,
                'membership_super_ring' => UserMulti::getUserNameArray($row['membership_super_ring_user_id']),
                'admin_type' => LookupHelper::getValue($row['admin_type']),
                'admin_super_ring' => UserMulti::getUserNameArray($row['admin_super_ring_user_id']),
                'ring_rhythm' => $ring_rhythm,
                'ring_rhythm_data' => ExportMulti::getRingRhythmDataForRing($row['ring_id']),
                'ring_user_takes' => ExportMulti::getRingUserTakesForRingUserId($user_id),
                // @fixme The table nane is reversed. ring_users is correct, the code needs refactoring.
                // This should be named ring_user as it is owned by the ring domain not the user domain.
                'ring_users' => ExportMulti::getUserRingsForRingId($row['ring_id']),
                // @fixme that the table name is different. ring_invitations is correct, the code needs refactoring.
                'ring_invitations' => ExportMulti::getInvitationsForRingId($row['ring_id']),
                'ring_applications' => ExportMulti::getRingApplicationsForRingId($row['ring_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets ring_application data for this ring.
     *
     * @param integer $ring_id The id of the ring to get data for.
     *
     * @return array
     */
    private static function getRingApplicationsForRingId($ring_id) {
        $rows = RingApplication::getRowsForRingId($ring_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets invitation data for this ring.
     *
     * @param integer $ring_id The id of the ring to get data for.
     *
     * @return array
     */
    private static function getInvitationsForRingId($ring_id) {
        $rows = Invitation::getRowsForRingId($ring_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'from_user' => UserMulti::getUserNameArray($row['from_user']),
                'to_user' => UserMulti::getUserNameArray($row['to_user']),
                'type' => LookupHelper::getValue($row['tyoe']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_ring data for this ring.
     *
     * @param integer $ring_id The id of the ring to get data for.
     *
     * @return array
     */
    private static function getUserRingsForRingId($ring_id) {
        $rows = UserRing::getRowsForRingId($ring_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
                'password' => $row['password'],
                'admin' => boolval($row['admin']),
                'member' => boolval($row['member']),
                'ban' => boolval($row['ban']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets ring_user_take data for this ring.
     *
     * @param integer $user_id The id of the user that represents the ring to get data for.
     *
     * @return array
     */
    private static function getRingUserTakesForRingUserId($user_id) {
        $rows = RingUserTake::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'ring_take_name' => $row['ring_take_name'],
                'post' => ExportMulti::getPostName($row['post_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets ring_rhythm_data for this ring.
     *
     * @param integer $ring_id The id of the ring to get data for.
     *
     * @return array
     */
    private static function getRingRhythmDataForRing($ring_id) {
        $rows = RingRhythmData::getRowsForRingId($ring_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
                'time_created' => $row['date_created'],
                'type' => LookupHelper::getValue($row['type_id']),
                'data' => $row['data'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_ring_password data for this user.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getUserRingPasswords($user_id) {
        $rows = UserRingPassword::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'ring' => UserMulti::getUserNameArray($row['ring_user_id']),
                'password' => $row['password'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets post_private_recipient data for posts sent to this user.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getPostPrivateRecipientsRecieved($user_id) {
        $rows = PostPrivateRecipient::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'post' => ExportMulti::getPostName($row['post_id']),
                'deleted' => boolval($row['deleted']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets post data for this user.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getPostsForUser($user_id) {
        $rows = Post::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'post' => ExportMulti::getPost($row['post_id']),
                // other users that this post was sent to.
                'private_recipients' => ExportMulti::getPostPrivateRecipientsForPost($row['post_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets post_private_recipient data for this user.
     *
     * @param integer $post_id The id of the post to get data for.
     *
     * @return array
     */
    private static function getPostPrivateRecipientsForPost($post_id) {
        $rows = PostPrivateRecipient::getRowsForPostId($post_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
                'deleted' => boolval($row['deleted']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream data for a user.
     *
     * @param integer $user_id The id of the user to fetch stream data for.
     *
     * @return array
     */
    private static function getStreams($user_id) {
        $rows = Stream::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'name' => $row['name'],
                'kind' => LookupHelper::getValue($row['kind']),
                'versions' => ExportMulti::getStreamExtras($row['stream_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_extra data for a stream.
     *
     * @param integer $stream_id The id of the stream to fetch stream_extra data for.
     *
     * @return array
     */
    private static function getStreamExtras($stream_id) {
        $rows = StreamExtra::getRowsForStreamId($stream_id);
        $final = array();
        foreach ($rows as $row) {
            $version_model = Version::getByVersionId($row['version_id']);
            $version = array(
                'major' => $version_model['major'],
                'minor' => $version_model['minor'],
                'patch' => $version_model['patch'],
            );
            $group_period = '';
            if (isset($row['group_period']) === true) {
                $group_period = LookupHelper::getValue($row['group_period']);
            }
            $item = array(
                'time_created' => time($row['date_created']),
                'description' => $row['description'],
                'status' => StatusHelper::getValue($row['status_id']),
                'group_period' => $group_period,
                'post_mode' => LookupHelper::getValue($row['post_mode']),
                'edit_mode' => LookupHelper::getValue($row['edit_mode']),
                'version' => $version,
                'meta_post' => ExportMulti::getPost($row['meta_post_id']),
                'fields' => ExportMulti::getStreamFields($row['stream_extra_id']),
                'children' => ExportMulti::getStreamChildren($row['stream_extra_id']),
                'blocks' => ExportMulti::getStreamBlocks($row['stream_extra_id']),
                'default_rings' => ExportMulti::getDefaultRings($row['stream_extra_id']),
                'default_rhythms' => ExportMulti::getDefaultRhythms($row['stream_extra_id']),
                'stream_public' => ExportMulti::getStreamPublic($row['stream_extra_id']),
                'posts' => ExportMulti::getPostsForStream($row['stream_extra_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets post data for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch post data for.
     *
     * @return array
     */
    private static function getPostsForStream($stream_extra_id) {
        $rows = Post::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'post' => ExportMulti::getPost($row['post_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_public data for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch stream_default_rhythm data for.
     *
     * @return array
     */
    private static function getStreamPublic($stream_extra_id) {
        $rows = StreamPublic::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $rhythm = RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']);
            $top_parent_post = '';
            if (isset($row['post_id']) === true) {
                $top_parent_post = ExportMulti::getPostName($row['post_id']);
            }
            $item = array(
                'time_cached' => time($row['time_cached']),
                'post' => ExportMulti::getPostName($row['post_id']),
                'rhythm' => $rhythm,
                'score' => $row['score'],
                'top_parent_post' => $top_parent_post,
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_default_rhythm data for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch stream_default_rhythm data for.
     *
     * @return array
     */
    private static function getDefaultRhythms($stream_extra_id) {
        $rows = StreamDefaultRhythm::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $rhythm = RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']);
            $rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $rhythm['version']
            );
            $item = array(
                'rhythm' => $rhythm,
                'display_order' => time($row['sort_order']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets default_ring data for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch default_ring data for.
     *
     * @return array
     */
    private static function getDefaultRings($stream_extra_id) {
        $rows = StreamDefaultRing::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'ring' => UserMulti::getUserNameArray($row['ring_user_id']),
                'display_order' => time($row['sort_order']),
            );
            array_push($final, $item);
        }
        return $final;
    }


    /**
     * Gets stream_block data for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch stream_block data for.
     *
     * @return array
     */
    private static function getStreamBlocks($stream_extra_id) {
        $rows = StreamBlock::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'start_time' => time($row['start_time']),
                'end_time' => time($row['end_time']),
                'block_number' => $row['block_number'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_child data for a stream_field_id.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch stream_child data for.
     *
     * @return array
     */
    private static function getStreamChildren($stream_extra_id) {
        $rows = StreamChild::getRowsForParentId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $stream = StreamBedMulti::getStreamNameArray($row['child_id']);
            $stream['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $stream['version']
            );
            $item = array(
                'stream' => $stream,
                'display_order' => $row['sort_order'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_field data for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch stream_field data for.
     *
     * @return array
     */
    private static function getStreamFields($stream_extra_id) {
        $rows = StreamField::getRowsForStreamExtraId($stream_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $text_type_id = '';
            if (isset($row['text_type_id']) === true) {
                $text_type_id = LookupHelper::getValue($row['text_type_id']);
            }
            $who_can_take = '';
            if (isset($row['who_can_take']) === true) {
                $who_can_take = LookupHelper::getValue($row['who_can_take']);
            }
            $value_options = '';
            if (isset($row['value_options']) === true) {
                $value_options = LookupHelper::getValue($row['value_options']);
            }

            $item = array(
                'type' => LookupHelper::getValue($row['field_type']),
                'label' => $row['label'],
                'max_size' => $row['max_size'],
                'required' => boolval($row['required']),
                'regex' => $row['regex'],
                'regex_error' => $row['regex_error'],
                'checkbox_default' => boolval($row['checkbox_default']),
                'taken_records' => boolval($row['taken_records']),
                'display_order' => $row['display_order'],
                'value_min' => $row['value_min'],
                'value_max' => $row['value_max'],
                'value_type' => isset($row['value_type']) === true ? LookupHelper::getValue($row['value_type']) : '',
                'value_options' => $value_options,
                'select_qty_max' => $row['select_qty_max'],
                'select_qty_min' => $row['select_qty_min'],
                'rhythm_check_url' => $row['rhythm_check_url'],
                'who_can_take' => $who_can_take,
                'text_type_id' => $text_type_id,
                'take_value_list_items' => ExportMulti::getTakeValueList($row['stream_field_id']),
                'list_items' => ExportMulti::getStreamList($row['stream_field_id']),
                'open_list_items' => ExportMulti::getStreamOpenListItems($row['stream_field_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_open_list_item data for a stream_field_id.
     *
     * @param integer $stream_field_id The id of the stream field to fetch stream_open_list_item data for.
     *
     * @return array
     */
    private static function getStreamOpenListItems($stream_field_id) {
        $rows = StreamOpenListItem::getRowsForStreamFieldId($stream_field_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'item' => $row['item'],
                'count' => $row['count'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets stream_list data for a stream_field_id.
     *
     * @param integer $stream_field_id The id of the stream field to fetch stream_list data for.
     *
     * @return array
     */
    private static function getStreamList($stream_field_id) {
        $rows = StreamList::getRowsForStreamFieldId($stream_field_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'name' => $row['name'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets take_value_list data for a stream_field_id.
     *
     * @param integer $stream_field_id The id of the stream field to fetch take_value_list data for.
     *
     * @return array
     */
    private static function getTakeValueList($stream_field_id) {
        $rows = TakeValueList::getRowsForStreamFieldId($stream_field_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'name' => $row['name'],
                'value' => $row['value'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets rhythm data for a user.
     *
     * @param integer $user_id The id of the user to fetch rhythm data for.
     *
     * @return array
     */
    private static function getRhythms($user_id) {
        $rows = Rhythm::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'name' => $row['name'],
                'versions' => ExportMulti::getRhythmExtras($row['rhythm_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets rhythm_extra data for a rhythm.
     *
     * @param integer $rhythm_id The id of the rhythm to fetch rhythm_extra data for.
     *
     * @return array
     */
    private static function getRhythmExtras($rhythm_id) {
        $rows = RhythmExtra::getRowsForRhythmId($rhythm_id);
        $final = array();
        foreach ($rows as $row) {
            $version_model = Version::getByVersionId($row['version_id']);
            $version = array(
                'major' => $version_model['major'],
                'minor' => $version_model['minor'],
                'patch' => $version_model['patch'],
            );
            $item = array(
                'time_created' => time($row['date_created']),
                'description' => $row['description'],
                'mini' => $row['mini'],
                'full' => $row['full'],
                'status' => StatusHelper::getValue($row['status_id']),
                'rhythm_cat' => RhythmCat::getCategoryFromID($row['rhythm_cat_id']),
                'version' => $version,
                'meta_post' => ExportMulti::getPost($row['meta_post_id']),
                'rhythm_param' => ExportMulti::getRhythmParams($row['rhythm_extra_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets rhythm_param data for a rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to fetch rhythm_param data for.
     *
     * @return array
     */
    private static function getRhythmParams($rhythm_extra_id) {
        $rows = RhythmParam::getRowsForUserRhythmExtraId($rhythm_extra_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'name' => $row['name'],
                'hint' => $row['hint'],
                'display_order' => $row['display_order'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets rhythm_user_data data for a user.
     *
     * @param integer $user_id The id of the user to fetch rhythm_user_data data for.
     *
     * @return array
     */
    private static function getRhythmUserData($user_id) {
        $rows = RhythmUserData::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'rhythm' => RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']),
                'data' => $row['data'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_rhythm data for a user.
     *
     * @param integer $user_id The id of the user to fetch user_rhythm data for.
     *
     * @return array
     */
    private static function getUserRhythms($user_id) {
        $rows = UserRhythm::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $rhythm = RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']);
            $rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $rhythm['version']
            );
            $item = array(
                'rhythm' => $rhythm,
                'order' => $row['order'],
                'take_kindred' => ExportMulti::getTakeKindredForUserRhythm($row['user_rhythm_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets take_kindred data for a user_rhythm_id.
     *
     * @param integer $user_rhythm_id The id of the user_rhtyhm to fetch take_kindred data for.
     *
     * @return array
     */
    private static function getTakeKindredForUserRhythm($user_rhythm_id) {
        $rows = TakeKindred::getRowsForUserRhythmId($user_rhythm_id);
        $final = array();
        foreach ($rows as $row) {
            $post_id = Take::getPostIdFromTakeId($row['take_id']);
            $item = array(
                'time_processed' => time($row['date_processed']),
                'scored_user' => UserMulti::getUserNameArray($row['scored_user_id']),
                'score' => $row['score'],
                'post' => ExportMulti::getPostName($post_id),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_stream_subscription data for a user.
     *
     * @param integer $user_id The id of the user to fetch user_stream_subscription data for.
     *
     * @return array
     */
    private static function getUserStreamSubscriptions($user_id) {
        $rows = UserStreamSubscription::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $stream = StreamBedMulti::getStreamNameArray($row['stream_extra_id']);
            $stream['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $stream['version']
            );
            $item = array(
                'domain' => Site::getDomain($row['site_id']),
                'stream' => $stream,
                'display_order' => $row['display_order'],
                'locked' => $row['locked'],
                'filters' => ExportMulti::getUserStreamSubscriptionFilters($row['user_stream_subscription_id']),
                'rings' => ExportMulti::getUserStreamSubsctiptionRings($row['user_stream_subscription_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_stream_subscription_ring data for a user.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscrtiption to fetch ring data for.
     *
     * @return array
     */
    private static function getUserStreamSubsctiptionRings($user_stream_subscription_id) {
        $rows = UserStreamSubscriptionRing::getRowsForUserStreamSubscriptionId($user_stream_subscription_id);
        $final = array();
        foreach ($rows as $row) {
            $ring_user_id = Ring::getRingUserId($user_stream_subscription_id);
            $item = array(
                'ring' => UserMulti::getUserNameArray($ring_user_id),
                'display_order' => $row['display_order'],
                'locked' => $row['locked'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_stream_subscription_filter data for a user.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscrtiption to fetch filter data for.
     *
     * @return array
     */
    private static function getUserStreamSubscriptionFilters($user_stream_subscription_id) {
        $rows = UserStreamSubscriptionFilter::getRowsForUserStreamSubscriptionId($user_stream_subscription_id);
        $final = array();
        foreach ($rows as $row) {
            $rhythm = RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']);
            $rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $rhythm['version']
            );
            $item = array(
                'rhythm' => $rhythm,
                'display_order' => $row['display_order'],
                'locked' => $row['locked'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_config data for a user.
     *
     * @param integer $user_id The id of the user to fetch user_config data for.
     *
     * @return array
     */
    private static function getSiteAccess($user_id) {
        $rows = SiteAccess::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'domain' => Site::getDomain($row['site_id']),
                'login_time' => time($row['login_time']),
                'login_expires' => time($row['login_expires']),
                'session_id' => $row['session_id'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_config data for a user.
     *
     * @param integer $user_id The id of the user to fetch user_config data for.
     *
     * @return array
     */
    private static function getUserConfig($user_id) {
        $rows = UserConfig::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'code' => $row['code'],
                'value' => $row['value'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_secret data for a user.
     *
     * @param integer $user_id The id of the user to fetch user_secret data for.
     *
     * @return array
     */
    private static function getUserSecrets($user_id) {
        $rows = UserSecret::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'secret' => $row['secret'],
                'time_created' => time($row['date_created']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array of feature_usage data for a user.
     *
     * @param integer $user_id The id of the user to fetch feature_usage data for.
     *
     * @return array
     */
    private static function getUserFeatureUseage($user_id) {
        $rows = UserFeatureUsage::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $feature = LookupHelper::getValue($row['feature']);
            $rhythm = '';
            $stream = '';
            if ($feature === 'rhythm') {
                $rhythm = RhythmMulti::getRhythmNameArray($row['extra_id']);
            } else if ($feature === 'rhythm') {
                $stream = StreamBedMulti::getStreamNameArray($row['extra_id']);
            }
            $item = array(
                'date_used' => $row['date_used'],
                'qty' => $row['qty'],
                'feature' => $feature,
                'rhythm' => $rhythm,
                'stream' => $stream,
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array of user_client_data for a user.
     *
     * @param integer $user_id The id of the user to fetch data for.
     *
     * @return array
     */
    private static function getUserClientData($user_id) {
        $rows = UserClientData::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'domain' => Site::getDomain($row['site_id']),
                'client_key' => $row['client_key'],
                'depth_key' => $row['depth_key'],
                'data_type' => $row['data_type'],
                'data' => $row['data'],
                'lft' => $row['lft'],
                'rgt' => $row['rgt'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets user_level data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getUserSignupCode($user_id) {
        $row = SignupCode::getRowForUserId($user_id);
        $level_array = array(
            'code' => $row['code'],
            'level_name' => $row['primary_category'],
            'level_name' => $row['secondary_category'],
            'hold_for_domain' => $row['hold_for_domain'],
            'hold_for_username' => $row['hold_for_username'],
        );
        return $level_array;
    }

    /**
     * Gets user_level data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getUserLevel($user_id) {
        $level_row = UserLevel::getRowForUserId($user_id);
        $level_array = array(
            'tutorial_set' => LookupHelper::getValue($level_row['tutorial_set']),
            'level_name' => LookupHelper::getValue($level_row['level_name']),
            'enabled' => boolval($level_row['enabled']),
        );
        return $level_array;
    }

    /**
     * Gets an array of posts linked in the post_user table for a user.
     *
     * @param integer $user_id The id of the user to fetch post_user data for.
     *
     * @return array
     */
    private static function getPostUsers($user_id) {
        $rows = PostUser::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'post' => ExportMulti::getPost($row['post_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array of kindred data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getUserStreamCounts($user_id) {
        $rows = UserStreamCount::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'stream' => StreamBedMulti::getStreamNameArray($row['stream_extra_id']),
                'total' => $row['total'],
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array of kindred data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getKindred($user_id) {
        $rows = Kindred::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'kindred_user' => UserMulti::getUserNameArray($row['kindred_user_id']),
                'time_updated' => time($row['time_updated']),
                'rhythm' => UserRhythm::getRhythmNameArray($row['user_rhythm_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array of waiting_post_time data for a user. (Last time a user accessed thier inbox).
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getWaitingPostTimes($user_id) {
        $rows = WaitingPostTime::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $item = array(
                'domain' => Site::getDomain($row['site_id']),
                'time_updated' => time($row['time_updated']),
                'type' => LookupHelper::getValue($row['type_id']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array profile data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getSuggestionsDeclined($user_id) {
        $rows = SuggestionsDeclined::getRowsForUserId($user_id);
        $final = array();
        foreach ($rows as $row) {
            $rhythm_name = RhythmMulti::getRhythmNameArray($row['declined_rhythm_extra_id']);
            $rhythm_name['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $rhythm_name['version']
            );
            $stream_name = StreamBedMulti::getStreamNameArray($row['declined_stream_extra_id']);
            $stream_name['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
                $row['version_type'],
                $stream_name['version']
            );
            $item = array(
                'client_domain' => Site::getDomain($row['site_id']),
                'rhythm_cat' => RhythmCat::getCategoryFromID($row['rhythm_cat_id']),
                'declined_rhythm' => $rhythm_name,
                'declined_stream' => $stream_name,
                'declined_user' => UserMulti::getUserNameArray($row['declined_user_id']),
                'time_declined' => time($row['date_declined']),
            );
            array_push($final, $item);
        }
        return $final;
    }

    /**
     * Gets an array profile data for a user.
     *
     * @param integer $user_id The id of the user to fetch profile data for.
     *
     * @return array
     */
    private static function getUserProfile($user_id) {
        $profile_row = UserProfile::getRowForUserId($user_id);
        $profile_array = array(
            'real_name' => $profile_row['real_name'],
            'about' => $profile_row['about'],
        );
        return $profile_array;
    }

    /**
     * Get post data.
     *
     * @param integer $post_id The id of the post to fetch.
     *
     * @return array An array of post data.
     */
    private static function getPost($post_id) {
        $post_row = Post::getRowByPostId($post_id);

        $post_data = array(
            'domain' => Site::getDomain($post_row['site_id']),
            'site_post_id' => $post_row['site_post_id'],
            'stream' => StreamBedMulti::getStreamNameArray($post_row['stream_extra_id']),
            'date_created' => time($post_row['site_post_id']),
            'parent_post_id' => ExportMulti::getPostName($post_row['parent']), // domain and site_post_id
            'top_parent_post_id' => ExportMulti::getPostName($post_row['top_parent']), // domain and site_post_id
            'stream_block' => $post_row['block'],
            'tree_block' => $post_row['block_tree'],
            'status' => LookupHelper::getValue($post_row['status']),
            'child_count' => $post_row['child_count'],

            'takes' => ExportMulti::getTakesForPost($post_id),
            //'user_takes' => ExportMulti::getUserTakesForPost($post_id), //Taken care of in getTakesForPost.
            'post_users' => ExportMulti::getPostUsersForPost($post_id),
            'child_posts' => ExportMulti::getPostChildrenForPost($post_id),    //post_descendent
            'parent_post' => ExportMulti::getPostParentsForPost($post_id),      //post_descendent
            'content' => ExportMulti::getContentForPost($post_id),
            'stream_block_tree' => ExportMulti::getStreamBlockTreeForPost($post_id),
        );
        return $post_data;
    }

    /**
     * Gets an array stream_block_tree data for a post.
     *
     * @param integer $post_id The id of the post to fetch content for.
     *
     * @return array
     */
    private static function getStreamBlockTreeForPost($post_id) {
        $stream_block_tree_rows = StreamBlockTree::getRowsForPostId($post_id);
        $stream_block_tree_array = array();
        foreach ($stream_block_tree_rows as $row) {
            $content = array(
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'block_number' => $row['block_number'],
            );
            array_push($stream_block_tree_array, $content);
        }
        return $stream_block_tree_array;
    }

    /**
     * Gets a array of all content for a post.
     *
     * @param integer $post_id The id of the post to fetch content for.
     *
     * @return array
     */
    private static function getContentForPost($post_id) {
        $content_rows = PostContent::getRowsForPostId($post_id);
        $content_array = array();
        foreach ($content_rows as $row) {
            $content = array(
                'date_created' => time($row['date_created']),
                'revision' => $row['revision'],
                'display_order' => $row['display_order'],
                'text' => $row['text'],
                'link' => $row['link'],
                'link_thumbnail_url' => $row['link_thumbnail_url'],
                'link_content' => $row['link_content'],
                'checked' => boolval($row['checked']),
                'selected' => $row['selected'],
                'value_max' => $row['value_max'],
                'value_min' => $row['value_min'],
            );
            array_push($content_array, $content);
        }
        return $content_array;
    }

    /**
     * Gets a array of all post_descendent.descendent posts.
     *
     * @param integer $post_id The id of the post to fetch rows for.
     *
     * @return array
     */
    private static function getPostChildrenForPost($post_id) {
        $child_posts = PostMulti::getChildPostRows($post_id);
        $child_posts_array = array();
        foreach ($child_posts as $row) {
            $child_post = array(
                'domain' => Site::getDomain($row['site_id']),
                'id' => $row['site_post_id'],
            );
            array_push($child_posts_array, $child_post);
        }
        return $child_posts_array;
    }

    /**
     * Gets a array of all post_descendent.ancestor posts.
     *
     * @param integer $post_id The id of the post to fetch rows for.
     *
     * @return array
     */
    private static function getPostParentsForPost($post_id) {
        $parent_posts = PostMulti::getParentPostRows($post_id);
        $parent_posts_array = array();
        foreach ($parent_posts as $row) {
            $parent_post = array(
                'domain' => Site::getDomain($row['site_id']),
                'id' => $row['site_post_id'],
            );
            array_push($parent_posts_array, $parent_post);
        }
        return $parent_posts_array;
    }

    /**
     * Gets a array of all post_user values for a post.
     *
     * @param integer $post_id The id of the post to fetch post_user rows for.
     *
     * @return array
     */
    private static function getPostUsersForPost($post_id) {
        $user_post_rows = PostUser::getRowsForPost($post_id);
        $user_post_array = array();
        foreach ($user_post_rows as $row) {
            $user_post = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
            );
            array_push($user_post_array, $user_post);
        }
        return $user_post_array;
    }

    /**
     * Gets a array of all take values for a post.
     *
     * @param integer $post_id The id of the post to fetch.
     *
     * @return array An array of post data.
     */
    private static function getTakesForPost($post_id) {
        $take_rows = Take::getRowsForPostId($post_id);
        $take_array = array();
        foreach ($take_rows as $row) {
            $take = array(
                'time_taken' => time($row['date_taken']),
                'user' => UserMulti::getUserNameArray($row['user_id']),
                'value' => $row['value'],
                'stream_field_id' => $row['field_id'],
                'user_block' => $row['block_id'],
                'stream_block' => $row['stream_block_id'],
                'stream_block' => $row['tree_block_id'],
                'user_takes' => ExportMulti::getUserTakesForTake($row['take_id']),
            );
            array_push($take_array, $take);
        }
        return $take_array;
    }

    /**
     * Gets a array of all user_take values for a take.
     *
     * @param integer $take_id The id of the take to fetch user takes for.
     *
     * @return array
     */
    private static function getUserTakesForTake($take_id) {
        $user_take_rows = UserTake::getRowsForTake($take_id);
        $user_take_array = array();
        foreach ($user_take_rows as $row) {
            $user_take = array(
                'user' => UserMulti::getUserNameArray($row['user_id']),
                'stream' => StreamBedMulti::getStreamNameArray($row['stream_extra_id']),
                'take_user' => UserMulti::getUserNameArray($row['take_user_id']),
            );
            array_push($user_take_array, $user_take);
        }
        return $user_take_array;
    }

    /**
     * Get a post name array consiting of the home domain of the post and the id for the post on that domain.
     *
     * @param integer $post_id The id of the post to fetch.
     *
     * @return array An array of post data.
     */
    private static function getPostName($post_id) {
        $post_name = array(
            'domain' => PostMulti::getHomeDomain($post_id),
            'id' => Post::getHomeId($post_id),
        );
        return $post_name;
    }

    /**
     * Generates the user data ready to export.
     *
     * @param integer $user_id The id of the user to prepare to export.
     *
     * @return array An array of user data ready for exporting to JSON.
     */
    public static function getUser($user_id) {

        $user_row = User::getRowByUserId($user_id);

        $user_data = array(
            'username' => $user_row['username'],
            'domain' => Site::getDomain($user_row['site_id']),
            'password' => $user_row['password'],
            'salt' => $user_row['salt'],
            'email' => $user_row['email'],
            'role' => LookupHelper::getValue($user_row['role']),
            'is_ring' => boolval($user_row['is_ring']),
            'test_user' => boolval($user_row['test_user']),
            'meta_post' => ExportMulti::getPost($user_row['meta_post_id']),
            'reset_secret' => $user_row['reset_secret'],
            'reset_secret' => $user_row['reset_time'],
            'csfr' => $user_row['csfr'],

            // User data
            'profile' => ExportMulti::getUserProfile($user_id),
            'suggestions_declined' => ExportMulti::getSuggestionsDeclined($user_id),
            'waiting_post_times' => ExportMulti::getWaitingPostTimes($user_id),
            'kindred' => ExportMulti::getKindred($user_id),

            // User data - stream.kind = user
            'user_stream_counts' => ExportMulti::getUserStreamCounts($user_id),
            'post_users' => ExportMulti::getPostUsers($user_id),

            // Client data
            'user_level' => ExportMulti::getUserLevel($user_id),
            'signup_codes' => ExportMulti::getUserSignupCode($user_id),
            'client_data' => ExportMulti::getUserClientData($user_id),
            'feature_usage' => ExportMulti::getUserFeatureUseage($user_id),
            'user_secrets' => ExportMulti::getUserSecrets($user_id),
            'user_config' => ExportMulti::getUserConfig($user_id),
            'site_access' => ExportMulti::getSiteAccess($user_id),

            // client Subscriptions
            'stream_subscriptions' => ExportMulti::getUserStreamSubscriptions($user_id),
            'user_rhythm_subscriptions' => ExportMulti::getUserRhythms($user_id),
            // This can not be nested in user_rhythm_subscriptions because it is linked directly to the
            // rhythm_extra_id of the rhythm that stored it and not the version_type that is stored in the
            // rhythm_subscription
            'rhythm_user_data' => ExportMulti::getRhythmUserData($user_id),

            // Rhythms
            'rhythms' => ExportMulti::getRhythms($user_id),

            // Streams
            'streams' => ExportMulti::getStreams($user_id),

            // Posts
            'posts' => ExportMulti::getPostsForUser($user_id),
            // IMPORTANT. When exporting this data to another domain. This includes delted rows, which would need
            // removing.
            'recieved_posts' => ExportMulti::getPostPrivateRecipientsRecieved($user_id),

            // Ring - access
            'user_ring_passwords' => ExportMulti::getUserRingPasswords($user_id),

            // Ring - this user is also a ring!
            'ring' => ExportMulti::getRing($user_id),

      //      .. up to here
            // Takes
            'takes' => ExportMulti::getTakes($user_id),
        );
        return $user_data;
    }
}

?>
