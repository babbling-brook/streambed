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
 * A collection of functions to aid in looking up values in the lookup table without making DB calls
 * Values here must reflect those in the DB.
 *
 * @package PHP_Helper
 */
class LookupHelper
{

    /**
     * An array of lookup values and their indexes from the lookup table.
     *
     * @var array(array)
     */
    static protected $lookup_value = array(
        "stream_field.field_type" => array(
            "textbox" => 2,
            "checkbox" => 3,
            "list" => 4,
            "value" => 12,
            "link" => 36,
            "openlist" => 37,
        ),
        "stream_field.value_type" => array(
            "updown" => 13,
            "linear" => 14,
            "logarithmic" => 15,
            "textbox" => 16,
            "stars" => 24,
            "button" => 46,
            "list" => 148,
        ),
        "stream_field.value_options" => array(
            "any" => 17,
            "maxminglobal" => 18,
            "maxminpost" => 19,
            "rhythmglobal" => 20,
            "rhythmpost" => 21,
        ),
        "user.role" => array(
            "standard" => 22,
            "admin" => 23,
        ),
        "post_popular.type" => array(
            "best" => 25,
        ),
        "version.type" => array(
            "stream" => 29,
            "rhythm" => 30,
        ),
        "version_type" => array(
            "latest/latest/latest" => 32,
            "major/latest/latest" => 33,
            "major/minor/latest" => 34,
            "major/minor/patch" => 35,

            "all/all/all" => 211,
            "major/all/all" => 212,
            "major/minor/all" => 213,
            "all/all/latest" => 214,
            "all/latest/all" => 215,
            "all/latest/latest" => 216,
            "latest/all/latest" => 217,
            "latest/latest/all" => 218,
            "latest/all/all" => 219,
            "major/all/latest" => 220,
            "major/latest/all" => 221,
        ),
        "tag.thing" => array(
            "rhythm" => 38,
            "stream" => 39,
        ),
        "stream_extra.group_period" => array(
            "hourly" => 41,
            "daily" => 42,
            "weekly" => 43,
            "fourweekly" => 44,
            "yearly" => 45,
        ),
        "user_feature_useage.feature" => array(
            "stream" => 47,
            "filter" => 49,
            "kindred" => 50,
        ),
        "stream.kind" => array(
            "standard" => 51,
            "user" => 52,
        ),
        "user_timming.name" => array(
            "popular_takes" => 53,
        ),
        "ring.membership_type" => array(
            "public" => 54,
            "admin_invitation" => 202,
            "invitation" => 55,
            "request" => 56,
            "super_ring" => 57,
        ),
        "ring.admin_type" => array(
            "only_me" => 58,
            "invitation" => 59,
            "super_ring" => 60,
        ),
        "invitation.type" => array(
            "admin" => 61,
            "member" => 62,
        ),
        "js_error.type" => array(
            "client_domus_error_error" => 64,
            "client_domus_fetch_suggestions" => 73,
            "client_domus_get_post" => 72,
            "client_domus_scientia_request" => 77,
            "client_domus_make_post" => 70,
            "client_domus_ring_status" => 76,
            "client_domus_ring_take" => 74,
            "client_domus_sort_request" => 68,
            "client_domus_take" => 71,
            "filter_domus_get_posts" => 80,
            "filter_domus_sort_finished" => 102,
            "generic" => 69,
            "iframe_not_loading" => 63,
            "scientia_error_error" => 94,
            "scientia_get_rhythm" => 116,
            "scientia_get_data" => 114,
            "scientia_get_post" => 123,
            "scientia_get_posts_block" => 120,
            "scientia_get_posts_block_number" => 121,
            "scientia_get_posts_update" => 122,
            "scientia_get_user_take_block" => 119,
            "scientia_get_user_take_block_number" => 118,
            "scientia_ring_take" => 106,
            "scientia_ring_take_status" => 107,
            "scientia_domus_rhythm" => 104,
            "scientia_domus_data_fetched" => 90,
            "scientia_domus_domain_ready" => 82,
            "scientia_domus_post" => 86,
            "scientia_domus_posts" => 85,
            "scientia_domus_ring_take_status" => 84,
            "scientia_domus_ring_taken" => 83,
            "scientia_domus_user_take_block_number" => 87,
            "scientia_user_take_block" => 88,
            "kindred_domus_receive_results" => 103,
            "kindred_domus_request_data" => 81,
            "ring_domus_request_data" => 78,
            "ring_domus_revceive_results" => 99,
            "domus_fetch_data" => 105,
            "domus_error_error" => 92,
            "domus_scientia_get_rhythm" => 115,
            "domus_scientia_get_data" => 113,
            "domus_scientia_get_post" => 110,
            "domus_scientia_get_posts" => 109,
            "domus_scientia_get_ring_take_status" => 108,
            "domus_scientia_get_user_takes_block" => 112,
            "domus_scientia_get_user_takes_block_number" => 111,
            "domus_scientia_ring_take" => 93,
            "domus_scientia_save_ring_results" => 117,
            "domus_kindred_data" => 98,
            "domus_make_post" => 96,
            "domus_suggestion_url" => 101,
            "domus_take" => 97,
            "domus_user_data" => 75,
            "suggestion_domus_generated" => 100,
            "suggestion_domus_get_data" => 79,
            "domus_get_takes_user" => 127,
            "domus_guid_invalid" => 132,
            "client_error_error" => 136,
            "timeout" => 128,
            "domus_action_data_invalid" => 130,
            "domus_action_error" => 131,
            "filter_error_error" => 137,
            "domus_filter_init" => 138,
            "suggestion_rhythm_init" => 161,
            "GetSuggestions_rhythm" => 162,
            "client_timeout" => 163,
        ),
        "js_error.location" => array(
            "scientia" => 65,
            "domus" => 66,
            "client" => 67,
            "suggestion" => 157,
            "ring" => 159,
            "kindred" => 160,
            "filter" => 158,
        ),
        "stream_extra.post_mode" => array(
            "anyone" => 139,
            "owner" => 140,
        ),
        "messaging_sort_type" => array(
            "sent" => 141,
            "received" => 142,
        ),
        "post.status" => array(
            "public" => 143,
            "private" => 144,
            "deleted" => 145,
        ),
        "stream_extra.edit_mode" => array(
            "owner" => 146,
            "anyone" => 147,
        ),
        "stream_field.who_can_edit" => array(
            "anyone" => 149,
            "owner" => 150,
        ),
        "ring_rhythm_data.type" => array(
            "admin" => 151,
            "member" => 152,
        ),
        "user_config_default.type" => array(
            "rhythm_url" => 153,
            "stream_url" => 154,
            "uint" => 155,
            "int" => 156,
            "string" => 206,
        ),
        "waiting_post_time.type_id" => array(
            "private" => 164,
            "public" => 165,
        ),
        "user_level.tutorial_set" => array(
            "main" => 166,
        ),
        "user_level.level_name" => array(
            "NOT_STARTED" => 167,
            "READ_POSTS" => 168,
            "VOTE_POSTS" => 169,
            "STREAM_NAV" => 170,
            "READ_COMMENTS" => 171,
            "VOTE_COMMENTS" => 172,
            "MAKE_COMMENT" => 173,
            "EDIT_COMMENT" => 174,
            "LINK_COMMENTS" => 175,
            "MAKE_SELF_POST" => 176,
            "STREAM_SORT" => 177,
            "SUGGESTION_MESSAGES" => 178,
            "SUBSCRIBE_LINK" => 179,
            "EDIT_SUBSCRIPTIONS_LINK" => 199,
            "FIND_SEARCH_STREAMS" => 181,
            "SEARCH_STREAMS" => 207,
            "CHANGE_STREAM_SORT_RHTYHM" => 200,
            "CHANGE_STREAM_MODERATION_RING" => 201,
            "BUGS" => 182,
            "KINDRED_SCORE" => 184,
            "VIEW_PROFILE" => 185,
            "EDIT_PROFILE" => 187,
            "PRIVATE_POSTS" => 188,
            "READ_PRIVATE_POSTS" => 203,
            "META_LINKS" => 189,
            "RING_MEMBERSHIP" => 191,
            "MODERATING_POSTS" => 196,
            "MAKING_RINGS" => 204,
            "SETTINGS" => 192,
            "MAKE_STREAMS" => 197,
            "MAKE_RHYTHMS" => 198,
            "FINISHED" => 205,
        ),
        "stream_field.text_type" => array(
            "just_text" => 208,
            "text_with_links" => 209,
            "simple_html" => 210,
        ),
    );

    /**
     * An array of lookup indexes and thier descriptions from the lookup table.
     *
     * @var array
     */
    static protected $description = array(

        "stream_field.field_type" => array(
            2 => "",
            3 => "",
            4 => "",
            12 => "",
            36 => "",
            37 => "An open list, where the taker can enter values, eg tags or poll questions for child posts",
        ),
        "stream_field.value_type" => array(
            13 => "A simple up and down vote arrows",
            14 => "A linear scale",
            15 => "A logarithmic scale",
            16 => "A textbox for a number",
            24 => "Stars representing values",
            46 => "Button with a take value of 1",
            148 => "Each value is ascribed a text value",
        ),
        "stream_field.value_options" => array(
            17 => "User can enter any value",
            18 => "Maximum and minimum constraints. Defined here",
            19 => "Maximum and minimum constraints. Defined on the post",
            20 => "Rhythm constraints. Defined here",
            21 => "Rhythm constraints. Defined on the post",
        ),
        "user.role" => array(
            22 => "Standard user",
            23 => "Administrator user",
        ),
        "post_popular.type" => array(
            25 => "How popular an post is using the best Rhythm",
        ),
        "version.type" => array(
            29 => "stream",
            30 => "rhythm",
        ),
        "version_type" => array(
            32 => "Latest, Latest, Latest",
            33 => "Major, Latest, Latest",
            34 => "Major, Minor, Latest",
            35 => "Major, Minor, Patch",
        ),
        "tag.thing" => array(
            38 => "Rhythm",
            39 => "Stream",
        ),
        "stream_extra.group_period" => array(
            41 => "Group into hourly blocks",
            42 => "Group into daily blocks",
            43 => "Group into weekly blocks",
            44 => "Group into monthly blocks",
            45 => "Group into yearly blocks",
        ),
        "user_feature_useage.feature" => array(
            47 => "",
            48 => "",
            49 => "",
            50 => "",
        ),
        "stream.kind" => array(
            51 => "A standard stream",
            52 => "An stream only used for users",
        ),
        "user_timming.name" => array(
            53 => "",
        ),
        "ring.membership_type" => array(
            54 => "Open to the public",
            202 => "By an admins invitation",
            55 => "By a members invitation",
            56 => "By request",
            57 => "By another ring",
        ),
        "ring.admin_type" => array(
            58 => "Only me",
            59 => "By invitation",
            60 => "Another Ring",
        ),
        "invitation.type" => array(
            61 => "An admin invitation",
            62 => "A member invitation",
        ),
        "js_error.type" => array(),
        "js_error.location" => array(
            65 => "An error reported by the scientia domain JS",
            66 => "An error reported by the domus domain JS",
            67 => "An error reported by the client domain JS",
            157 => "An error reported by the suggestiondomain JS",
            158 => "An error reported by the filter domain JS",
            159 => "An error reported by the ring domain JS",
            160 => "An error reported by the kindred domain JS",
        ),
        "stream_extra.post_mode" => array(
            139 => "Anyone",
            140 => "Only Me",
        ),
        "messaging_sort_type" => array(
            141 => "Posts that have been sent by a user.",
            142 => "Posts that have been received by a user.",
        ),
        "post.status" => array(
            143 => "A publicly viewable post",
            144 => "An post that can only be viewed by its owner and those it was sent to.",
            145 => "An post that has been deleted, but is still visible to the makers of children and takers.",
        ),
        "stream_extra.edit_mode" => array(
            146 => "Only the owner can edit an post in this stream.",
            147 => "Anyone can edit an post in this stream.",
        ),
        "stream_field.who_can_edit" => array(
            149 => "Anyone can take this value field",
            150 => "Only the stream owner can take this value field",
        ),
        "ring_rhythm_data.type" => array(
            151 => "This data was produced by an admin rhythm.",
            152 => "This data was produced by a member rhythm.",
        ),
        "user_config_default.type" => array(
            153 => "This config item is a rhythm url",
            154 => "This config item is a streamurl",
            155 => "This config item is an unsigned integer",
            156 => "This config item is a signed integer",
            206 => "This config item is a string",
        ),
        "waiting_post_time.type_id" => array(
            164 => "Time since private post count fetched.",
            165 => "Time since public post count fetched.",
        ),
        "stream_field.text_type" => array(
            208 => "Just text",
            209 => "Text with links",
            210 => "Simple HTML",
        ),
    );

    /**
     * Fetch the lookup table ID for this value.
     *
     * @param string $column DB column contents lookup.column_name.
     * @param string $key DB column contents lookup.value.
     * @param boolean $error Thow an error or not.
     *
     * @return integer The lookup table row ID.
     */
    public static function getID($column, $key, $error=true) {
        if (isset(self::$lookup_value[$column][$key]) === false) {
            if ($error === true) {
                throw new Exception("lookup value does not exist ['" . $column . "']['" . $key . "']");
            } else {
                return null;
            }
        }
        $id = self::$lookup_value[$column][$key];
        return $id;
    }

    /**
     * Fetch an array of descriptions and their keys for a column.
     *
     * @param string $column The column to fetch a description for.
     *
     * @return array
     */
    public static function getDescriptions($column) {
        if (array_key_exists($column, self::$description) === false) {
            throw new Exception("lookup description column doees not exist : " . $column);
        }
        return self::$description[$column];
    }

    /**
     * Fetch an array of values for a column_name.
     *
     * @param string $column_name The name of the column to fetch values for.
     *
     * @return array An array valid values - the index with the contents being the lookup_id.
     */
    public static function getValues($column_name) {
        return self::$lookup_value[$column_name];
    }

    /**
     * Fetch the value for this lookup table ID.
     *
     * @param integer $id The table id.
     * @param boolean $error Throw an error or return false.
     *
     * @return string The contents of lookup.value for this table id.
     */
    public static function getValue($id, $error=true) {
        foreach (self::$lookup_value as $column) {
            $result = array_search($id, $column);
            if ($result !== false) {
                return $result;
            }
        }
        if ($error === true) {
            throw new Exception("lookup id does not exist : " . $id);
        } else {
            return false;
        }
    }


    /**
     * Fetch the value for this lookup table ID.
     *
     * @param integer $id The table id.
     *
     * @return string The contents of lookup.value for this table id.
     */
    public static function getDescription($id) {
        foreach (self::$description as $column) {
            if (isset($column[$id]) === true) {
                return $column[$id];
            }
        }
        throw new Exception("lookup id does not exist : " . $id);
    }

    /**
     * Fetch the column name for this lookup table ID.
     *
     * @param integer $id The table id.
     * @param boolean $throw_error When not found should an error be thrown or false returned
     *
     * @return string|false The contents of lookup.column_name for this table id. Or false.
     */
    public static function getColumn($id, $throw_error=true) {
        foreach (self::$lookup_value as $column) {
            $result = array_search($key, $column);
            if ($result === true) {
                return key($column);
            }
        }
        if ($throw_error === true) {
            throw new Exception("lookup id does not exist : " . $id);
        } else {
            return false;
        }
    }

    /**
     * Is the value valid for the given column.
     *
     * @param string $column The column to lookup.
     * @param string $value The value to check.
     *
     * @return boolean
     */
    public static function valid($column, $value) {
        if (array_key_exists($column, self::$lookup_value) === false) {
            throw new Exception($column . " is not a valid LookupHelper column.");
        }
        if (array_key_exists($value, self::$lookup_value[$column]) === true) {
            return true;
        }
        return false;
    }


    /**
     * Is the value valid for the given column and id.
     *
     * @param string $column The column to lookup.
     * @param string $id The lookup primary key.
     * @param boolean $throw_error Throw an error if not found.
     *
     * @return boolean
     */
    public static function validId($column, $id, $throw_error=true) {
        if (in_array($id, self::$lookup_value[$column]) === false) {
            if ($throw_error === true) {
                throw new Exception($column . " is not a valid LookupHelper column.");
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Return a dropdown list array with the lookup_id column as the dropdown value and the description as the text.
     *
     * @param string $column The column to lookup.
     *
     * @return array
     */
    public static function getDropDown($column) {
        $dropdown = array();
        foreach (self::$lookup_value[$column] as $value => $row) {
            $dropdown[$value] = self::$description[$column][$row];
        }
        return $dropdown;
    }
}

?>