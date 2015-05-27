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
 * Shared methods for the Stream and stream_extra models.
 */
class StreamBedMulti
{
    /**
     * Check that this stream extra is owned by the specified user.
     *
     * @param integer $stream_extra_id The extra id of the stream we are checking.
     * @param integer $user_id The id of the user we are checking owns this stream.
     * @param boolean $private Also check if the stream is in private(draft) mode.
     *
     * @return boolean
     */
    public static function checkOwnerExtra($stream_extra_id, $user_id, $private=false) {
        $priv_cond = "";
        if ($private === true) {
            $priv_cond = "AND status_id=" . StatusHelper::getID("private");
        }

        $model = Stream::model()->with('extra')->find(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id AND user_id=:user_id ' . $priv_cond,
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                    ':user_id' => $user_id,
                )
            )
        );
        if (isset($model) === true) {
            return true;
        }
        return false;
    }

    /**
     * Get an stream as long as it owned by the user who is passed in.
     *
     * @param integer $stream_id The id of the stream we are fetching.
     * @param integer $user_id The id of the user who owns this stream.
     *
     * @return Stream Model
     */
    public static function getPost($stream_id, $user_id) {
        return Stream::model()->with('version')->find(
            array(
                'condition' => 'stream_id=:stream_id AND user_id=:user_id',
                'params' => array(
                    ':stream_id' => $stream_id,
                    ':user_id' => $user_id,
                )
            )
        );
    }

    /**
     * Retrieve an stream from it's version_id.
     *
     * Version_ID should be one to one with stream_id
     *
     * @param integer $version_id The id of the version we are fetching an stream from.
     *
     * @return integer stream_id
     */
    public static function getIDFromVersion($version_id) {
        $stream = Stream::model()->find(
            array(
                'select' => 'stream_id',
                'condition' => 'version_id=:version_id',
                'params' => array(
                    ':version_id' => $version_id,
                )
            )
        );
        if (isset($stream) === true) {
            return $stream->stream_id;
        }
    }

    /**
     * Get any version ID for an stream via its name. (Used to look up version family).
     *
     * @param integer $user_id The id of the user we are fetching a version for.
     * @param string $name The name of the stream we are fetching a version for.
     *
     * @return integer|boolean A version_id or false.
     */
    public static function getAnyVersionID($user_id, $name) {
        $row =  Stream::model()->find(
            array(
                'select' => 'version_id',
                'condition' => 'name=:name AND user_id=:user_id',
                'params' => array(
                    ':name' => $name,
                    ':user_id' => $user_id,
                )
            )
        );
        if (isset($row) === true) {
            return $row->version_id;
        } else {
            return false;
        }
    }

    /**
     * Checks if an stream name is unique.
     *
     * @param integer $user_id The id of the user whose stream name we are checking.
     * @param string $name The name of the stream to check.
     *
     * @return boolean True if name is unique
     */
    public static function isNameUnique($user_id, $name) {
        return !Stream::model()->exists(
            array(
                'condition' => 'name=:name AND user_id=:user_id',
                'params' => array(
                    ':name' => $name,
                    ':user_id' => $user_id,
                )
            )
        );
    }

    /**
     * Inserts a new row from a prepoulated activerecord.
     *
     * @param Stream $stream The pre existing stream to copy.
     *
     * @return Stream Model
     */
    public static function insertFromUpdate($stream) {
        $model = new Stream;
        $model->name = $stream->name;
        $model->description = $stream->description;
        $model->user_id = $stream->user_id;
        $model->version_id = $stream->version_id;
        $model->save();
        return $model;
    }

    /**
     * Set the status of the stream (Updates StreamExtra).
     *
     * @param integer $stream_extra_id The extra id of the stream we are setting as unique.
     * @param integer $status_id The status id we are updating the stream to.
     *
     * @return void
     */
    public static function updateStatus($stream_extra_id, $status_id) {
        StreamExtra::model()->updateAll(
            array(
                'status_id' => $status_id,
            ),
            array(
                'condition' => 'stream_extra_id = :stream_extra_id',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                )
            )
        );
    }

    /**
     * Fetch an stream by its extra id.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching.
     *
     * @return Stream Result row.
     */
    public static function getByID($stream_extra_id) {
        return Stream::model()->with('extra')->find(
            array(
                "condition" => "stream_extra_id=:stream_extra_id",
                "params" => array(
                    ":stream_extra_id" => $stream_extra_id,
                )
            )
        );
    }


    /**
     * Fetch an stream with user, site, extra and version sub models.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching.
     *
     * @return Stream Result row.
     */
    public static function getByIDWithExtra($stream_extra_id) {
        return Stream::model()->with('extra', 'extra.version', 'user', 'user.site')->find(
            array(
                "condition" => "stream_extra_id=:stream_extra_id",
                "params" => array(
                    ":stream_extra_id" => $stream_extra_id,
                )
            )
        );
    }

    /**
     * Delete an stream by primary key (Also deletes relevant stream extra row).
     *
     * Only streams with a status of zero (private) can be deleted.
     * Public and deprecated streams must not be deleted.
     *
     * @param integer $stream_extra_id The extra id of the stream to delete.
     *
     * @return void
     */
    public static function delete($stream_extra_id) {
        $stream = StreamBedMulti::getbyID($stream_extra_id);
        if (isset($stream) === false) {
            return;
        }

        StreamExtra::model()->deleteByPk(
            $stream_extra_id,
            array(
                'condition' => 'status_id=:status_id',
                'params' => array(
                    ':status_id' => StatusHelper::getID("private"),
                )
            )
        );

        // Remove the primary refference if all sub rows have been deleted
        $any_left = Stream::model()->find(
            array(
                'condition' => 'stream_id=:stream_id',
                'params' => array(
                    ':stream_id' => $stream->stream_id,
                )
            )
        );
        if (isset($any_left) === false) {
            Stream::model()->deleteByPk($stream->stream_id);
        }
    }

    /**
     * Check if an stream exists.
     *
     * @param integer $stream_extra_id The stream extra ID to check the existance of.
     *
     * @return boolean
     */
    public static function exists($stream_extra_id) {
        return StreamExtra::model()->exists(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                )
            )
        );
    }

    /**
     * Fetches an Stream in an array formated for JSON encoding.
     *
     * @param Stream $stream Model with StreamExtra and version sub models.
     * @param string $username The username of the owner of the stream we are returning.
     * @param string $domain The domain of the user who owns the stream we are returning.
     *
     * @return array|null JSON array containg stream or null if not found.
     */
    public static function getJSON($stream, $username, $domain=null) {
        if ($domain === null) {
            $domain = Yii::app()->params['host'];
        }

        $fields = StreamField::getFields($stream->extra->stream_extra_id);

        $meta_url = Post::getMetaUrl($stream->extra->meta_post_id);

        $stream_children = StreamChild::getChildrenForParent($stream->extra->stream_extra_id);
        $children_json = array();
        foreach ($stream_children as $child) {
            $child_version_id = StreamExtra::getVersionID($child['child_id']);
            // fetch latest version
            $version_id = Version::getLatestVersionID(
                $child_version_id,
                LookupHelper::getValue($child['version_type']),
                LookupHelper::getID("version.type", "stream")
            );
            $child_stream_id = StreamBedMulti::getExtraIdFromVersionId($version_id);
            $child_details = StreamBedMulti::getByID($child_stream_id);

            $child_version = $child_details->extra->version;
            $child_json = array(
                'name' => $child_details->name,
                'domain' => $child_details->user->site->domain,
                'username' => $child_details->user->username,
                'version' => $child_version->major . "/" . $child_version->minor . "/" . $child_version->patch,
                'post_mode' => LookupHelper::getValue($child_details->extra->post_mode),
            );
            array_push($children_json, $child_json);
        }

        // Convert Models to arrays ready for JSON encoding
        $json = array();
        $json['name'] = $stream->name;
        $json['local_id'] = $stream->extra->stream_extra_id;
        $json['kind'] = LookupHelper::getValue($stream->kind);
        $json['domain'] = $domain;
        $json['username'] = $username;
        $json['timestamp'] = strtotime($stream->extra->date_created);
        $json['description'] = $stream->extra->description;
        $json['meta_url'] = $meta_url;
        $json['status'] = StatusHelper::getValue($stream->extra->status_id);
        $version = $stream->extra->version;
        $json['version'] = $version->major . "/" . $version->minor . "/" . $version->patch;
        $json['post_mode'] = LookupHelper::getValue($stream->extra->post_mode);
        $json['child_streams'] = $children_json;
        $json['cooldown'] = Yii::app()->params["post_cooldown"];   // Cooldown time before a new post is displayed.
        $json['edit_mode'] = LookupHelper::getValue($stream->extra->edit_mode);
        $json['default_rhythms'] = StreamDefaultRhythm::getDefaults($stream->extra->stream_extra_id);
        $json['presentation_type'] = LookupHelper::GetValue($stream->extra->presentation_type_id);

        $json_fields = array();
        $json_fields[] = null;   // Enter a null row for the first field as they are 1 based.
        foreach ($fields as $field) {
            $field_array = array();
            $field_array['type'] = LookupHelper::getValue($field['field_type']);
            $field_array['label'] = $field['label'];
            $field_array['display_order'] = $field['display_order'];

            if ($field_array['type'] === "textbox") {
                $field_array['max_size'] = $field['max_size'];
                if ((bool)$field['required'] === true) {
                    $field_array['required'] = true;
                } else {
                    $field_array['required'] = false;
                }
                $field_array['regex'] = $field['regex'];
                $field_array['text_type'] = LookupHelper::getValue($field['text_type_id']);
                $field_array['valid_html'] = StreamTextFieldTypeHelper::getValidHTML(
                    LookupHelper::getValue($field['text_type_id'])
                );
                $field_array['regex_error'] = $field['regex_error'];
            }

            if ($field_array['type'] === "link") {
                $field_array['max_size'] = $field['max_size'];
                if ((bool)$field['required'] === true) {
                    $field_array['required'] = true;
                } else {
                    $field_array['required'] = false;
                }
            }

            if ($field_array['type'] === "checkbox") {
                if ((int)$field['checkbox_default'] === 1) {
                    $field_array['checkbox_default'] = true;
                } else {
                    $field_array['checkbox_default'] = false;
                }
            }

            if ($field_array['type'] === "value") {
                $field_array['value_min'] = $field['value_min'];
                $field_array['value_max'] = $field['value_max'];
                $field_array['value_type'] = LookupHelper::getValue($field['value_type']);
                if ($field['value_options'] !== null) {
                    $field_array['value_options'] = LookupHelper::getValue($field['value_options']);
                }
                $field_array['rhythm_check_url'] = $field['rhythm_check_url'];
                //$field_array['taken_records'] = $field['taken_records'];

                if ($field_array['value_type'] === 'list') {
                    $field_array['value_list'] = TakeValueList::getListForJson($field['stream_field_id']);
                }

                $field_array['who_can_take'] = LookupHelper::getValue($field['who_can_take']);
            }

            if ($field_array['type'] === "list" || $field_array['type'] === "openlist") {
                $field_array['select_qty_max'] = $field['select_qty_max'];
                $field_array['select_qty_min'] = $field['select_qty_min'];
            }
            if ($field_array['type'] === "list") {
                $list = StreamList::getList($field['stream_field_id']);
                $list_array = array();
                foreach ($list as $item) {
                    $list_array[] = $item['name'];
                }
                $field_array['list'] = $list_array;
            }

            $json_fields[] = $field_array;
        }
        $json['fields'] = $json_fields;

        return $json;
    }

    /**
     * Get an stream by name. Also returns version models.
     *
     * @param integer $user_id The username of the user who owns the stream we are fetching.
     * @param string $name The name of the user who owns the stream we are fetching.
     * @param integer $major The major version number of the user who owns the stream we are fetching.
     * @param integer $minor The minor version number of the user who owns the stream we are fetching.
     * @param integer $patch The patch version number of the user who owns the stream we are fetching.
     *
     * @return Stream|null Model.
     */
    public static function getByName($user_id, $name, $major, $minor, $patch) {
        // !!! this is temporary until all versioning uses 'latest' instead of null.
        if ($major === null) {
            $major = 'latest';
        }
        if ($minor === null) {
            $minor = 'latest';
        }
        if ($patch === null) {
            $patch = 'latest';
        }

        $criteria = new CDbCriteria;
        $criteria->order = 'major DESC, minor DESC, patch DESC';
        $criteria->addCondition('t.user_id=:user_id');
        $criteria->addCondition('t.name=:name');
        $criteria->addCondition('version.family_id=t.stream_id');
        $criteria->addCondition('version.type=:version_type');
        $criteria->addCondition('(version.major=:major OR :major IS NULL)');
        $criteria->addCondition('(version.minor=:minor OR :minor IS NULL)');
        $criteria->addCondition('(version.patch=:patch OR :patch IS NULL)');
        $criteria->params = array(
            ':user_id' => $user_id,
            ':name' => $name,
            ':version_type' => LookupHelper::getID("version.type", "stream"),
            ':major' => $major,
            ':minor' => $minor,
            ':patch' => $patch,
        );
        $model = Stream::model()->with('extra', 'extra.version', 'user', 'user.site')->find($criteria);
        return $model;
    }

    /**
     * Get an stream extra ID from the streams full name.
     *
     * @param integer $user_id The username of the user who owns the stream we are fetching.
     * @param string $name The name of the stream we are fetching.
     * @param integer $major The major version number of the user who owns the stream we are fetching.
     * @param integer $minor The minor version number of the user who owns the stream we are fetching.
     * @param integer $patch The patch version number of the user who owns the stream we are fetching.
     * @param string [$domain] The domain that owns the stream. We only need this if is not stored locally
     *      and we want to fetch it from its source.
     * @param string [$username] The usenrame that owns the stream. We only need this if is not stored locally
     *      and we want to fetch it from its source.
     *
     * @return integer|boolean stream_extra_id or false
     */
    public static function getIDByName(
        $user_id, $name, $major='latest', $minor='latest', $patch='latest', $domain=null, $username=null
    ) {
        $criteria = new CDbCriteria;
        $criteria->select = 'stream_id';
        $criteria->with = array(
            'extra' => array(
                'select' => 'stream_extra_id',
            ),
            'user' => array(
                'select' => 'user_id',
            ),
            'extra.version' => array(
                'select' => 'version_id',
            ),
        );
        $criteria->order = 'major DESC, minor DESC, patch DESC';
        $criteria->addCondition('t.user_id=:user_id');
        $criteria->addCondition('t.name=:name');
        // Ensure private versions are not returned if not owned
        if ($user_id !== intval(Yii::app()->user->getId())) {
            $criteria->addCondition('extra.status_id != ' . StatusHelper::getID("private"));
        }
        $criteria->addCondition('version.family_id=t.stream_id');
        $criteria->addCondition('version.type=:version_type');
        $criteria->addCondition("(version.major=:major OR :major = 'latest')");
        $criteria->addCondition("(version.minor=:minor OR :minor = 'latest')");
        $criteria->addCondition("(version.patch=:patch OR :patch = 'latest')");
        $criteria->params = array(
            ':user_id' => $user_id,
            ':name' => $name,
            ':version_type' => LookupHelper::getID("version.type", "stream"),
            ':major' => $major,
            ':minor' => $minor,
            ':patch' => $patch,
        );
        $model = Stream::model()->find($criteria);
        if (isset($model) === true) {
            return (int)$model->extra->stream_extra_id;
        }
        if (isset($domain) === true && isset($username) === true) {
            $odh = new OtherDomainsHelper($domain);
            $odh->getStream($domain, $username, $name, $major, $minor, $patch);
            // IMPORTANT: recursive call.
            return StreamBedMulti::getIDByName($user_id, $name, $major, $minor, $patch);
        } else {
            return false;
        }
    }

    /**
     * Get the version family id for a stream.
     *
     * @param integer $user_id The id of the user who owns the stream we are fetching a version family id for.
     * @param string $name The name of the stream we are fetching a version for.
     *
     * @return integer The family id of a version.
     */
    public static function getVersionFamily($user_id, $name) {
        $stream = Stream::model()->with('version')->find(
            array(
                'condition' => 'user_id=:user_id AND name=:name',
                'params' => array(
                    ':user_id' => $user_id,
                    ':name' => $name,
                )
            )
        );

        if (isset($stream) === false) {
            throw new Exception("Stream Family not found");
        }

        return $stream->version->family_id;

    }

    /**
     * Cache an stream fetched from another source.
     *
     * @param array $stream An array of stream data.
     *                          Formated in the same format as described in StreamBedMulti::getJSON.
     *
     * @return boolean Success
     */
    public static function cache($stream) {
        // Get site id (insert if not present)
        $site_id = SiteMulti::getSiteID($stream['site']);

        // Get user id (insert if not present)
        $user_multi = new UserMulti($site_id);
        $user_model = $user_multi->getUserFromUserName($stream['user']);
        if (isset($user_model) === false) {
            $user_model = $user_multi->insertRemoteUser($stream['user']);
        }

        // Check stream not allready inserted
        $exists = StreamBedMulti::getByName(
            $user_id,
            $stream['name'],
            $stream['major'],
            $stream['minor'],
            $stream['patch']
        );
        if (isset($exists) === true) {
            return true;
        }

        $stream_model = new Stream;
        $stream_model->name = $stream['name'];
        $stream_model->date_created = $stream['date_created'];
        $stream_model->description = $stream['description'];
        $stream_model->user_id = $user_model->user_id;
        $stream_model->version_id = Version::insertNew(LookupHelper::getID("version.type", "stream"));
        $stream_model->status_id = StatusHelper::getid($stream['status']);

        if ($stream_model->validate() === false) {
            return false;
        }

        // Prepare and test fields
        $field_models = array();
        foreach ($stream->fields as $field) {
            $field_model = new StreamField;
            $field_model->stream_id = 1;
            $field_model->field_type = LookupHelper::getID("stream_field.field_type", $field->field_type);
            $field_model->max_size = $field->max_size;
            $field_model->required  = $field->required;
            $field_model->regex = $field->regex;
            $field_model->checkbox_default = $field->checkbox_default;
            $field_model->taken_records = $field->taken_records;
            $field_model->ladisplay_orderbel = $field->display_order;
            $field_model->value_min = $field->value_min;
            $field_model->value_max = $field->value_max;
            $field_model->value_type = LookupHelper::getID("stream_field.value_type", $field->field_type);
            $field_model->value_options = $field->value_options;
            $field_model->select_qty_max = $field->select_qty_max;
            $field_model->select_qty_min = $field->select_qty_min;
            $field_model->rhythm_check_url = $field->rhythm_check_url;

            if ($field_model->validate() === false) {
                return false;
            }
            $field_models[] = $field_model;
        }

        // All passed; Insert
        $stream_model->save();
        foreach ($field_models as $field) {
            $field->stream_id = $stream_model->stream_id;
            $field->save();
        }
        return true;
    }

    /**
     * Fetch the stream_extra_id for this version array and stream.
     *
     * @param array $version_ary  Major, minor and patch version numbers used to fetch an stream_extra_id.
     * @param integer $stream_id The stream id of the extra stream that we are fetching.
     *
     * @return integer stream_extra_id
     */
    public static function getExtraIDFromVersion($version_ary, $stream_id) {
        $model = StreamExtra::model()->with('version')->find(
            array(
                'condition' => 'version.family_id = :family_id '
                    . 'AND version.major = :major '
                    . 'AND version.minor = :minor '
                    . 'AND version.patch = :patch',
                'params' => array(
                    ':family_id' => $stream_id,
                    ':major' => $version_ary[0],
                    ':minor' => $version_ary[1],
                    ':patch' => $version_ary[2],
                )
            )
        );
        if (isset($model) === false) {
            throw new Excption("No version found.");
        }
        return $model->stream_extra_id;
    }

    /**
     * Adds a child stream.
     *
     * @param integer $parent_id The stream_extra_id we are adding a child to.
     * @param integer $child_id The stream_extra_id we are adding a child to.adding to a parent.
     * @param integer $version_type The type of version. Defaults to latest/latest/latest.
     *                              See lookup table for details.
     *
     * @return integer|boolean primary key or false.
     */
    public static function addChild($parent_id, $child_id, $version_type=32) {
        // Check it doesn't already exist.
        $child_exists = StreamChild::doesChildExist($parent_id, $child_id, $version_type);
        if ($child_exists !== false) {
            return false;
        }

        $model = new StreamChild;
        $model->parent_id = $parent_id;
        $model->child_id = $child_id;
        $model->version_type = $version_type;
        if ($model->save() === true) {
            return $model->getPrimaryKey();
        }
        return false;
    }

    /**
     * Removes a child link.
     *
     * @param integer $stream_child_id The child link id we are removing.
     * @param integer $parent_id The parent stream_extra_id we are removing a child from.
     *                           Needs prior testing for ownership.
     *
     * @return boolean success
     */
    public static function removeChild($stream_child_id, $parent_id) {
        $count = StreamChild::model()->deleteAll(
            'stream_child_id=:stream_child_id AND parent_id=:parent_id',
            array(
                ':stream_child_id' => $stream_child_id,
                ':parent_id' => $parent_id,
            )
        );
        if ($count === 0) {
            return false;
        }
        return true;
    }

    /**
     * Update the version of a child stream.
     *
     * @param integer $stream_child_id The child link id we are updating to a new version.
     * @param integer $parent_id The parent stream_extra_id of the child whose version we are updating.
     *                           This needs to have been checked for ownership.
     * @param string $version This may be a partial version. Eg 1.0.latest.
     *
     * @return string version part of the url.
     */
    public static function updateVersion($stream_child_id, $parent_id, $version) {

        // Fetch the child stream_extra id for this child relationship.
        $child_id = StreamChild::getChildId($stream_child_id);

        // Fetch new stream id
        $version_ary = explode("/", $version);
        $new_extra_id = StreamBedMulti::getNewTypeVersion($child_id, $version_ary);

        // Work out version type
        $version_type_id = Version::getTypeId($version);

        // Update extra ID and version type
        StreamBedMulti::updateChild($stream_child_id, $parent_id, $new_extra_id, $version_type_id);

        // Return  version url.
        return $version;
    }


    /**
     * Update a version type and stream id.
     *
     * @param integer $stream_child_id The id of the child link we are updating.
     * @param integer $parent_id The parent stream_extra_id. This needs to have been checked for ownership.
     *                           Included here to assertain the child belongs to it.
     *                           If 0 is passed in, then not checked. (used imediately after insert).
     * @param integer $new_child_stream_extra_id The new stream_extra_id.
     * @param integer $version_type_id The new version type that we are associating with this child.
     *                                 See version_type in lookup table for options.
     *
     * @return void
     */
    public static function updateChild($stream_child_id, $parent_id, $new_child_stream_extra_id, $version_type_id) {
        StreamChild::model()->updateAll(
            array(
                'child_id' => $new_child_stream_extra_id,
                'version_type' => $version_type_id,
            ),
            "stream_child_id=:stream_child_id AND (parent_id=:parent_id OR :parent_id=0)",
            array(
                "stream_child_id" => $stream_child_id,
                "parent_id" => $parent_id,
            )
        );
    }

    /**
     * Duplicates the children of an stream to another one.
     *
     * @param integer $old_parent_id The stream_extra_id that children are being copied from.
     * @param integer $new_parent_id  The stream_extra_id that children are being copied to.
     *
     * @return void
     */
    public static function copyChildren($old_parent_id, $new_parent_id) {
        $children = StreamChild::model()->findAll(
            "parent_id=:parent_id",
            array(
                "parent_id" => $old_parent_id,
            )
        );
        foreach ($children as $child) {
            $model = new StreamChild;
            $model->parent_id = $new_parent_id;
            $model->child_id = $child->child_id;
            $model->version_type = $child->version_type;
            $model->save();
        }
    }

    /**
     * Return an array of child urls for this stream.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching children for.
     *
     * @return array
     */
    public static function getArrayChildUrls($stream_extra_id) {
        $children = StreamBedMulti::getChildren($stream_extra_id);
        $urls = array();
        foreach ($children as $child) {
            $url = UrlHelper::getVersionUrl(
                $child->stream_extra->stream->user->username,
                "stream",
                "view",
                $child->stream_extra->stream->name,
                $child->stream_extra->version->major,
                $child->stream_extra->version->minor,
                $child->stream_extra->version->patch,
                $child->version_type,
                $child->stream_extra->stream->user->site->domain
            );
            $urls[] = $url;
        }
        return $urls;
    }

    /**
     * Fetch all the children streams of this stream extra, with full version, site and user info.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching children for.
     *
     * @return StreamChild
     */
    public static function getChildren($stream_extra_id) {
        return StreamChild::model()->with(
            'stream_extra',
            'stream_extra.stream',
            'stream_extra.version',
            'stream_extra.stream.user',
            'stream_extra.stream.user.site'
        )->findAll(
            "parent_id=:parent_id",
            array(
                "parent_id" => $stream_extra_id,
            )
        );
    }

    /**
     * Fetch the stream_extra_id for this stream_extra_id with a new version string.
     *
     * @param integer $stream_extra_id The extra id of the stream we updating.
     * @param array $version_ary The major, minor and patch version numbers we are updating.
     *
     * @return integer stream_extra_id
     */
    public static function getNewTypeVersion($stream_extra_id, $version_ary) {
        $stream_id = StreamBedMulti::getIDFromExtraID($stream_extra_id);

        $new_stream_extra_id = StreamBedMulti::getExtraIDFromVersion($version_ary, $stream_id);
        return $new_stream_extra_id;
    }

    /**
     * Gets the stream_id from an stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching an stream id for.
     *
     * @return $stream_id
     */
    public static function getIDFromExtraID($stream_extra_id) {
        $model = StreamExtra::model()->find(
            array(
                'select' => 'stream_id',
                'condition' => 'stream_extra_id=:stream_extra_id',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                ),
            )
        );
        if (isset($model) === false) {
            throw new Exception("Stream Extra not found.");
        }
        return $model->stream_id;
    }

    /**
     * Get an streams kind value from one of its stream_extra versions.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching a kind value for.
     *
     * @return integer kind id. User LookupHelper to convert to string value.
     */
    public static function getKindFromStreamExtra($stream_extra_id) {
        $query = "
            SELECT stream.kind
            FROM
                stream
                INNER JOIN stream_extra ON stream_extra.stream_id = stream.stream_id
            WHERE stream_extra.stream_extra_id = :stream_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $kind_id = $command->queryScalar();
        if (isset($kind_id) === false) {
            throw new Exception("Stream kind not found. stream_extra_id = " . $stream_extra_id);
        }
        return (int)$kind_id;
    }

    /**
     * Generate JSON search results for an stream.
     *
     * @param GetSelectionForm $fmodel Contains the search paramaters for the stream we are fetching.
     *
     * @return array
     */
    public static function generateJSONSearch($fmodel) {
        // Split the version filter into major, minor and patch
        $versions = Version::splitPartialVersionString($fmodel->version_filter);

        $connection = Yii::app()->db;
        $sql =  "SELECT
                      'stream' AS type
                     ,stream_extra.stream_extra_id
                     ,site.domain
                     ,user.username
                     ,stream.name ";

        if ($fmodel->show_version === true) {
            $sql .= ",version.major, version.minor, version.patch ";
        }

        $sql .= "FROM
                     stream
                     INNER JOIN stream_extra ON stream.stream_id = stream_extra.stream_id
                     INNER JOIN user ON stream.user_id = user.user_id
                     INNER JOIN site ON user.site_id = site.site_id ";

        if ($fmodel->show_version === true) {
            $sql .= "INNER JOIN version ON stream_extra.version_id = version.version_id ";
        }

        $sql .= "WHERE
                     (site.domain LIKE :site_filter OR :site_filter = '%%')
                     AND (user.username LIKE :user_filter OR :user_filter = '%%')
                     AND (stream.name LIKE :name_filter OR :name_filter = '%%') ";

        if ($fmodel->show_version === true) {
            $sql .= "AND (version.major = :major OR :major = '')
                     AND (version.minor = :minor OR :minor = '')
                     AND (version.patch = :patch OR :patch = '') ";
        }

        if ($fmodel->stream_kind !== '') {
            $sql .= "AND stream.kind = :stream_kind ";
        }

        $sql .= "GROUP BY site.domain, user.username, stream.name
                 ORDER BY site.domain, user.username, stream.name ";

        if ($fmodel->show_version === true) {
            $sql .= ", version.major DESC, version.minor DESC, version.patch DESC ";
        }

        $sql .= "LIMIT " . ($fmodel->page -1) * $fmodel->rows . "," . $fmodel->rows;

        $command = $connection->createCommand($sql);
        $site_filter = "%" . $fmodel->site_filter . "%";
        $command->bindValue(":site_filter", $site_filter, PDO::PARAM_STR);
        $user_filter = "%" . $fmodel->user_filter . "%";
        $command->bindValue(":user_filter", $user_filter, PDO::PARAM_STR);
        $name_filter = "%" . $fmodel->name_filter . "%";
        $command->bindValue(":name_filter", $name_filter, PDO::PARAM_STR);
        if ($fmodel->show_version === true) {
            // If the version is invalid then return an empty result set.
            // This filters out strings in the version filter which otherwise get converted to 0 - causing a
            // match with all 0 versions.
            if (Version::isLookValid($fmodel->version_filter, false) === false) {
                return array();
            }
            $command->bindValue(":major", $versions['major'], PDO::PARAM_INT);
            $command->bindValue(":minor", $versions['minor'], PDO::PARAM_INT);
            $command->bindValue(":patch", $versions['patch'], PDO::PARAM_INT);
        }
        if ($fmodel->stream_kind !== '') {
            $stream_kind = LookupHelper::getID("stream.kind", $fmodel->stream_kind, false);
            if (isset($stream_kind) === false) {
                $stream_kind = "";
            }
            $command->bindValue(":stream_kind", $stream_kind, PDO::PARAM_STR);
        }
        $streams = $command->queryAll();

        return $streams;
    }

    /**
     * Fetch the id of an stream from it's url.
     *
     * @param string $url The stream url.
     * @param boolean [$extra=false] An option to return the stream_extra_id instead of the stream_id.
     * @param boolean [$remote=true] An option to fetch the stream from its home domain if it is not local.
     *
     * @return integer|false|string stream_id or stream_extra_id or false if not found,
     *                                'url broken' if an error
     */
    public static function getIdFromUrl($url, $extra=false, $remote=true) {
        // remove http if present
        if (strpos($url, "http://") !== false && strpos($url, "http://") === 0) {    // Present AND start of string
            $url = substr($url, 7);
        }

        $url_parts = explode("/", $url);

        if ((count($url_parts) < 7 || count($url_parts) > 8) && $extra === true) {
            return "url broken";
        } else if (count($url_parts) < 4 || count($url_parts) > 8) {
            return "url broken";
        }

        if ($url_parts[2] !== "stream") {
            return "url broken";
        }

        $major = "latest";
        if (isset($url_parts[4]) === true) {
            $major = $url_parts[4];
        }

        $minor = "latest";
        if (isset($url_parts[5]) === true) {
            $minor = $url_parts[5];
        }

        $patch = "latest";
        if (isset($url_parts[6]) === true) {
            $patch = $url_parts[6];
        }

        $name = str_replace('+', ' ', $url_parts[3]);

        $query = "
            SELECT
                 stream_extra.stream_extra_id
                ,stream_extra.stream_id
            FROM
                stream
                INNER JOIN stream_extra ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
            WHERE
                stream.name = :name
                AND user.username = :username
                AND site.domain = :domain
                AND (version.major = :major OR 'latest' = :major)
                AND (version.minor = :minor OR 'latest' = :minor)
                AND (version.patch = :patch OR 'latest' = :patch)";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $command->bindValue(":username", $url_parts[1], PDO::PARAM_STR);
        $command->bindValue(":domain", $url_parts[0], PDO::PARAM_STR);
        $command->bindValue(":major", $major, PDO::PARAM_STR);
        $command->bindValue(":minor", $minor, PDO::PARAM_STR);
        $command->bindValue(":patch", $patch, PDO::PARAM_STR);
        $row = $command->queryRow();

        if ($row === false) {
            if ($url_parts[0] !== Yii::app()->params['host']) {
                $odh = new OtherDomainsHelper($url_parts[0]);
                $odh->getStream($url_parts[0], $url_parts[1], $url_parts[3], $major, $minor, $patch);
                StreamBedMulti::getIdFromUrl($url, $extra, false);   // ! Infinite loop created if remote is set to true
            } else {
                return false;
            }
        }
        if ($extra === true) {
            return $row['stream_extra_id'];
        } else {
            return $row['stream_id'];
        }
    }

    /**
     * Gets the url for an stream from its id (not extra id).
     *
     * @param integer $stream_id The id of the stream we are fetching an url for.
     *
     * @return string|boolean The url or false
     */
    public static function getUrlFromStreamID($stream_id) {
        $query = "
            SELECT
                 stream.name
                ,user.username
                ,site.domain
            FROM
                stream
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                stream.stream_id = :stream_id";

        $command = Yii::app()->db->createCommand($query);

        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        if ($row === false) {
            return false;
        }

        $url = $row['domain'] . "/" . $row['username'] . "/stream/" . $row['name'];
        return $url;
    }

    /**
     * Fetches the stream extra id from its version id.
     *
     * @param {integer} $version_id The id of the version we are looking up an stream extra id for.
     *
     * @return {integer} The stream_extra_id
     */
    public static function getExtraIdFromVersionId($version_id) {
        $query = "
            SELECT stream_extra_id
            FROM stream_extra
            WHERE version_id = :version_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":version_id", $version_id, PDO::PARAM_INT);
        $stream_extra_id= $command->queryScalar();
        if ($stream_extra_id === false) {
            throw new Exception("stream_extra_id not found for version_id : " . $version_id);
        }
        return $stream_extra_id;
    }

    /**
     * Returns the sites default filter information.
     *
     * @return array
     */
    public static function getDefaultFilters() {
        $filters = Yii::app()->params["default_sort_filters"];
        $version_type = LookupHelper::getID('version.type', 'rhythm');
        foreach ($filters as $key => $filter) {
            $site_id = SiteMulti::getSiteID($filter['domain']);
            $user_multi = new UserMulti($site_id);
            $user_id = $user_multi->getIDFromUsername($filter['username']);
            $rhythm_id = Rhythm::getVersionFamily($user_id, $filter['name']);
            $version_array = Version::getLatestVersionFromString($filter['version'], $rhythm_id, $version_type);
            $rhythm = Rhythm::getByName(
                $user_id,
                $filter['name'],
                $version_array['major'],
                $version_array['minor'],
                $version_array['patch']
            );
            $filters[$key]['description'] = $rhythm->extra->description;
            $filters[$key]['params'] = RhythmParam::getForFilter($rhythm->extra->rhythm_extra_id);
        }
        return $filters;
    }

    /**
     * Insert a stream from its JSON array(Retrieved from another Babbling Brook store.)
     *
     * @param object $stream A standard object of converted json data as defined by the Babbling Brook protocol.
     *
     * @return boolean Success or not.
     */
    public static function insertFromJSONArray($stream) {

        $stream_form = new StreamForm;
        if (isset($stream->domain) === true) {
            $stream_form->domain = $stream->domain;
        }
        if (isset($stream->username) === true) {
            $stream_form->username = $stream->username;
        }
        if (isset($stream->name) === true) {
            $stream_form->name = $stream->name;
        }
        if (isset($stream->version) === true) {
            $stream_form->name = $stream->name;
            $version = explode('/', $stream->version);
            if (isset($version[0]) === true) {
                $stream_form->major = $version[0];
            }
            if (isset($version[1]) === true) {
                $stream_form->minor = $version[1];
            }
            if (isset($version[2]) === true) {
                $stream_form->patch = $version[2];
            }
        }
        if (isset($stream->post_mode) === true) {
            $stream_form->post_mode = $stream->post_mode;
        }
        if (isset($stream->edit_mode) === true) {
            $stream_form->edit_mode = $stream->edit_mode;
        }
        if (isset($stream->status) === true) {
            $stream_form->status = $stream->status;
        }
        if (isset($stream->meta_url) === true) {
            $stream_form->meta_url = $stream->meta_url;
        }
        if (isset($stream->description) === true) {
            $stream_form->description = $stream->description;
        }
        if (isset($stream->cooldown) === true) {
            $stream_form->cooldown = $stream->cooldown;
        }
        if (isset($stream->date_created) === true) {
            $stream_form->date_created = $stream->date_created;
        }
        if (isset($stream->kind) === true) {
            $stream_form->kind = $stream->kind;
        }

        if (isset($stream->fields) === true) {
            foreach ($stream->fields as $field) {
                if ($field !== null) {
                    $stream_field_form = new StreamFieldForm;
                    if (isset($field->field_type) === true) {
                        $stream_field_form->field_type = $field->field_type;
                    }
                    if (isset($field->max_size) === true) {
                        $stream_field_form->max_size = $field->max_size;
                    }
                    if (isset($field->required) === true) {
                        $stream_field_form->required = $field->required;
                    }
                    if (isset($field->regex) === true) {
                        $stream_field_form->regex = $field->regex;
                    }
                    if (isset($field->regex_error) === true) {
                        $stream_field_form->regex_error = $field->regex_error;
                    }
                    if (isset($field->checkbox_default) === true) {
                        $stream_field_form->checkbox_default = $field->checkbox_default;
                    }
                    if (isset($field->taken_records) === true) {
                        $stream_field_form->taken_records = $field->taken_records;
                    }
                    if (isset($field->display_order) === true) {
                        $stream_field_form->display_order = $field->display_order;
                    }
                    if (isset($field->value_min) === true) {
                        $stream_field_form->value_min = $field->value_min;
                    }
                    if (isset($field->value_max) === true) {
                        $stream_field_form->value_max = $field->value_max;
                    }
                    if (isset($field->value_type) === true) {
                        $stream_field_form->value_type = $field->value_type;
                    }
                    if (isset($field->value_options) === true) {
                        $stream_field_form->value_options = $field->value_options;
                    }
                    if (isset($field->select_qty_max) === true) {
                        $stream_field_form->select_qty_max = $field->select_qty_max;
                    }
                    if (isset($field->select_qty_min) === true) {
                        $stream_field_form->select_qty_min = $field->select_qty_min;
                    }
                    if (isset($field->rhythm_check_url) === true) {
                        $stream_field_form->rhythm_check_url = $field->rhythm_check_url;
                    }
                    if (isset($field->who_can_take) === true) {
                        $stream_field_form->who_can_take = $field->who_can_take;
                    }
                    array_push($stream_form->field_forms, $stream_field_form);
                }
            }
        }
        if ($stream_form->validate() === true) {
            return false;
        }

        $stream_id = $stream_form->insertOrUpdateStream();

        // Now that we have the stream, we need to fetch any needed child streams.
        $child_stream_ids = array();
        foreach ($stream->child_streams as $child_stream) {
            $version_parts = explode('/', $child_stream->version);
            $child_site_id = SiteMulti::getSiteID($child_stream->domain, true);
            if ($child_site_id === false) {
                return false;
            }
            $user_multi = new UserMulti($child_site_id);
            $child_stream_user_id = $user_multi->getIDFromUsername($child_stream->username, false, true);
            if ($child_stream_user_id === false) {
                return false;
            }
            $child_stream_id = StreamBedMulti::getIDByName(
                $child_stream_user_id,
                $child_stream->name,
                $version_parts[0],
                $version_parts[1],
                $version_parts[2],
                $child_stream->domain,
                $child_stream->username
            );
            $child_model = new StreamChild;
            $child_model->parent_id = $stream_id;
            $child_model->child_id = $child_stream_id;
            $child_model->version_type = Version::getTypeId($child_stream->version);
            array_push($child_stream_ids, $child_stream_id);
        }

    }

    /**
     * Delete a streams field.
     *
     * IMPORTANT: Assumes that the field is allowed to be deleted.
     *
     * @param integer $stream_field_id The primary key of the field is being deleted.
     * @param boolean $all True if we can delete the main title and value fields.
     *
     * @return boolean Was the delete successful.
     */
    public static function deleteStreamField($stream_field_id, $all=false) {
        StreamField::getDisplayOrder($stream_field_id);
        if ($stream_field_id <= 2 && $all === false) {
            return;
        }

        $stream_extra_id = StreamField::getStreamExtraIdFromFieldID($stream_field_id);
        $delete_multi = new DeleteMulti;
        $deleted = $delete_multi->deleteStreamFieldByStreamFieldId($stream_field_id);

        // The display order needsd resorting, otherwise there will be gaps due to the deletion.
        StreamField::resetDisplayOrder($stream_extra_id);

        return $deleted;
    }

    /**
     * Gets A full stream name array for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream to fetch a name array for.
     *
     * @return array
     */
    public static function getStreamNameArray($stream_extra_id) {
        $stream_model = StreamBedMulti::getByIDWithExtra($stream_extra_id);
        $stream_name = array(
            'domain' => $stream_model->user->site->domain,
            'username' => $stream_model->user->username,
            'name' => $stream_model->name,
            'version' => array(
                'major' => $stream_model->extra->version->major,
                'minor' => $stream_model->extra->version->minor,
                'patch' => $stream_model->extra->version->patch,
            ),
        );
        return $stream_name;
    }
}

?>