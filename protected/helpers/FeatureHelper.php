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
 * A list of website features that can be turned on or off.
 *
 * @package PHP_Helper
 */
class FeatureHelper
{
    /**
     * @var Integer The users tutorial level.
     */
    private $user_level;

    /**
     * @var boolean Are tutorials enabled.
     */
    private $tutorials_enabled;

    /**
     * An array of lookup values and their indexes from the lookup table.
     *
     * @var array(array)
     */
    static protected $options = array(
        'NOT_STARTED' => true,
        'READ_POSTS' => true,
        'VOTE_POSTS' => true,
        'STREAM_NAV' => true,
        'READ_COMMENTS' => true,
        'VOTE_COMMENTS' => true,
        'MAKE_COMMENT' => true, // include cooldown.
        'EDIT_COMMENT' => true,
        'LINK_COMMENTS' => true,
        'MAKE_SELF_POST' => true,
        'STREAM_SORT' => true,
        'SUGGESTION_MESSAGES' => true,
        'SUBSCRIBE_LINK' => true,
        'EDIT_SUBSCRIPTIONS_LINK' => true,
        'SEARCH_STREAMS' => true,
        'CHANGE_STREAM_SORT_RHTYHM' => true,
        'CHANGE_STREAM_MODERATION_RING' => true,
        'BUGS' => true,
        'KINDRED_SCORE' => true,
        'VIEW_PROFILE' => true,
        'EDIT_PROFILE' => true,
        'PRIVATE_POSTS' => true,
        'READ_PRIVATE_POSTS' => true,
        'META_LINKS' => true,
        'RING_MEMBERSHIP' => true,
        'MODERATING_POSTS' => true,
        'MAKING_RINGS' => true,
        'SETTINGS' => true,
        'MAKE_STREAMS' => true,
        'MAKE_RHYTHMS' => true,
        'FINISHED' => true,
    );

    public static function getFeatures() {
        return self::$options;
    }

    public function isAtLevel($level_name) {

        if (isset($this->user_level) === false) {
            $user_level_model = UserLevel::getUserRow(Yii::app()->user->getId());
            $this->user_level = UserLevel::getLevelNumber($user_level_model->level_name);
            $this->tutorials_enabled = $user_level_model->enabled;
        }

        if ($this->tutorials_enabled === false) {
            return true;
        }

        $level_number = UserLevel::getLevelNumber(LookupHelper::getID('user_level.level_name', $level_name));
        if ($this->user_level >= $level_number) {
            return true;
        } else {
            return false;
        }

    }
}