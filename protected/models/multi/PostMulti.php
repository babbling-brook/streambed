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
 * A collection of static functions that affect multiple db tables to do with posts.
 */
class PostMulti
{

    /**
     * Get the most popular posts for this set streams.
     *
     * @param array $types The types of popular posts to fetch.
     * @param integer $sort_type The post_popular_id that defines the sort type.
     * @param integer $qty The quantity of popular posts to fetch.
     *
     * @return void
     */
    public static function getPopularPosts($types=array(), $sort_type=null, $qty=25) {
        if ($sort_type === null) {
            $sort_type = LookupHelper::getID("post_popular.type", "best");
        }

        //        $model = Post::model()->with('post_popular')->findAll(
        //            "criteria" => "post_popular == id",
        //            "params" => array(
        //
        //            )
        //        );

    }

    /**
     * Verify the fields in the array against the stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that the post we are verifying belongs to.
     * @param array $fields Array of post fields.
     *
     * @return boolean|array true or array of error messages.
     */
    public static function verifyPost($stream_extra_id, &$fields) {
        $type_fields = StreamField::getFields($stream_extra_id);
        $matches = array();        // Used to store display_order of fields that have been verified
        $errors = array();

        // If this post is for an stream.kind = 'user'
        // then check that the first columns link field looks like a profile page
        $kind_id = StreamBedMulti::getKindFromStreamExtra($stream_extra_id);
        if ($kind_id === LookupHelper::getId("stream.kind", "user")) {
            if (LookupHelper::getID("stream_field.field_type", "link") !== (int)$type_fields[0]['field_type']) {
                $errors[]
                    = "Trying to sumbit an post for a 'user' kind of stream without the first field being a link.";
            }
            $link_error = UserProfile::verifyLink($fields[0]['link']);
            if ($link_error === true) {
                $errors[] = $link_error;
            }
            $link_error = UserProfile::verifyLinkTitle($fields[0]['link_title']);
            if ($link_error === true) {
                $errors[] = $link_error;
            }
        }

        foreach ($fields as $key => $field) {
            // Match submited field to post
            foreach ($type_fields as $t_field) {
                if ($t_field['display_order'] === $field['display_order']) {
                    $fields[$key]['field_type'] = $t_field['field_type'];
                    switch ($t_field['field_type']) {
                        case LookupHelper::getID("stream_field.field_type", "textbox"):
                            // Check text exists
                            if (array_key_exists('text', $field) === false) {
                                $errors[] = "'text' field missing on textbox field : " . $t_field['display_order'];
                                break;
                            }
                            // If required, check it has content
                            if ((bool)$t_field['required'] === true && strlen($field['text']) === 0) {
                                $errors[] = "Required 'text' field is empty on textbox field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            // Check length not too long
                            if ($t_field['max_size'] !== null && $t_field['max_size'] < strlen($field['text'])) {
                                $errors[] = "'text' field is too long on textbox field : " . $t_field['display_order'];
                                break;
                            }
                            // Check regex
                            if (strlen($field['text']) > 0
                                && $t_field['regex'] !== null
                                && preg_match("/" . $t_field['regex'] . "/", $field['text']) === 0
                            ) {
                                $errors[] = "Regular Expression failed for field : " . $t_field['display_order'];
                                break;
                            }

                            $purifier = new CHtmlPurifier();
                            $field_html_options = StreamTextFieldTypeHelper::getValidHTML(
                                LookupHelper::getValue($t_field['text_type_id'])
                            );
                            $allowed_html = StreamPostPurifyHTMLHelper::createPurifyElements($field_html_options);
                            $purifier->options = array(
                                'HTML.Allowed' => $allowed_html,
                            );
                            $fields[$key]['text'] = $purifier->purify($field['text']);
                            if (trim($fields[$key]['text']) !== trim($field['text'])) {
                                $errors[] = "Text field data contains illegal html/attributes/styles for this stream : "
                                    . $t_field['display_order'];
                                break;
                            }

                            $matches[] = $field['display_order'];
                            break;

                        case LookupHelper::getID("stream_field.field_type", "link"):
                            // Check link exists
                            if (array_key_exists('link', $field) === false) {
                                $errors[] = "'link' field missing on link field : " . $t_field['display_order'];
                                break;
                            }
                            // Check link title exists
                            if (array_key_exists('link_title', $field) === false) {
                                $errors[] = "'link_title' field missing on link field : " . $t_field['display_order'];
                                break;
                            }
                            // Check link  contatins a protocol
                            if ((bool)$t_field['required'] === true && strpos($field['link'], "://") === false) {
                                $errors[] = "'link' protocol missing on link field : " . $t_field['display_order'];
                                break;
                            }

                            $purifier = new CHtmlPurifier();
                            $purifier->options = array(
                                'HTML.Allowed' => '',
                            );
                            $fields[$key]['link_title'] = $purifier->purify($field['link_title']);

                            if (array_key_exists('link_thumbnail_small_base64', $field) === true
                                && strlen($field['link_thumbnail_small_base64']) > 0
                            ) {
                                $png_valid = ImageHelper::checkPNGFromBase64StringIsValid(
                                    $field['link_thumbnail_small_base64']
                                );
                                if ($png_valid === false) {
                                    $errors[] = "'link' small thumbnail is not a valid base64 PNG string : "
                                        . $t_field['display_order'];
                                }
                            }

                            if (array_key_exists('link_thumbnail_large_base64', $field) === true
                                && strlen($field['link_thumbnail_large_base64']) > 0
                            ) {
                                $png_valid = ImageHelper::checkPNGFromBase64StringIsValid(
                                    $field['link_thumbnail_large_base64']
                                );
                                if ($png_valid === false) {
                                    $errors[] = "'link' large thumbnail is not a valid base64 PNG string : "
                                        . $t_field['display_order'];
                                }
                            }

                            if (array_key_exists('link_thumbnail_large_proportional_base64', $field) === true
                                && strlen($field['link_thumbnail_large_proportional_base64']) > 0
                            ) {
                                $png_valid = ImageHelper::checkPNGFromBase64StringIsValid(
                                    $field['link_thumbnail_large_proportional_base64']
                                );
                                if ($png_valid === false) {
                                    $errors[] = "'link' large proportional thumbnail is not a valid base64 PNG string : "
                                        . $t_field['display_order'];
                                }
                            }

                            if (array_key_exists('link_thumbnail_url', $field) === true
                                && strlen($field['link_thumbnail_url']) > 0
                            ) {
                                // Check link  contatins a protocol
                                if (strpos($field['link_thumbnail_url'], "//") === false) {
                                    $errors[] = "'link' protocol missing on thumbnail url : "
                                        . $t_field['display_order'];
                                    break;
                                }
                            }

                            $matches[] = $field['display_order'];
                            break;

                        case LookupHelper::getID("stream_field.field_type", "checkbox"):
                            // Check the checked field exists
                            if (array_key_exists('checked', $field) === false) {
                                $errors[] = "'checked' field missing on checkbox field : " . $t_field['display_order'];
                                break;
                            }
                            // Check the checked field has a value of true or false
                            if ($field['checked'] !== "true" && $field['checked'] !== "false") {
                                $errors[] = "'checked' field value is not valid : " . $t_field['display_order'];
                                break;
                            }
                            $matches[] = $field['display_order'];
                            break;

                        case LookupHelper::getID("stream_field.field_type", "list"):

                            // Escape if nothing is expected.
                            if ($t_field['select_qty_min'] === '0' && array_key_exists('selected', $field) === false) {
                                $matches[] = $field['display_order'];
                                continue;
                            }

                            // Check the selected field exists
                            if (array_key_exists('selected', $field) === false) {
                                $errors[] = "'selected' field missing on list field : " . $t_field['display_order'];
                                break;
                            }
                            // Check the selected field is an array
                            if (is_array($field['selected']) === false) {
                                $errors[] = "'selected' field is not an array on list field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            // Check all the items are valid
                            foreach ($field['selected'] as $item) {
                                if (StreamList::doesItemExist($item, $t_field['stream_field_id']) === false) {
                                    $errors[] = "An item in 'selected' on list field is missing: "
                                        . $t_field['display_order'] . " , " . $item;
                                    break;
                                }
                            }
                            // Check the correct number of items has been selected
                            $select_count = count($field['selected']);
                            if ($select_count < $t_field['select_qty_min']) {
                                $errors[]
                                    = "Not enough items have been selected from the list field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            if ($select_count > $t_field['select_qty_max']) {
                                $errors[]
                                    = "Too many items have been selected from the list field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            $matches[] = $field['display_order'];
                            break;

                        case LookupHelper::getID("stream_field.field_type", "openlist"):
                            // Early exit if there is nothing to check and this is ok.
                            if (array_key_exists('selected', $field) === false
                                && intval($t_field['select_qty_min']) === 0
                            ) {
                                $matches[] = $field['display_order'];
                                break;
                            }
                            if (array_key_exists('selected', $field) === false && $t_field['select_qty_min'] > 0) {
                                $errors[] = "'selected' field missing on list field : " . $t_field['display_order'];
                                break;
                            }
                            // Check the selected field is an array
                            if (array_key_exists('selected', $field) === true
                                && is_array($field['selected']) === false
                            ) {
                                $errors[]
                                    = "'selected' field is not an array on list field : "
                                    . $t_field['display_order'];
                                break;
                            }

                            // @fixme When this is converted to a form, this needs a tighter check to just allow
                            // alphanumberic or suchlike.
                            $purifier = new CHtmlPurifier();
                            $purifier->options = array(
                                'HTML.Allowed' => '',
                            );
                            foreach ($field['selected'] as $item_key => $item) {
                                $fields[$key]['selected'][$item_key] = $purifier->purify($item);
                            }

                            $select_count = count($field['selected']);
                            if ($select_count < $t_field['select_qty_min']) {
                                $errors[]
                                    = "Not enough items have been selected from the open list field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            if ($select_count > $t_field['select_qty_max']) {
                                $errors[]
                                    = "Too many items have been selected from the open list field : "
                                    . $t_field['display_order'];
                                break;
                            }
                            $matches[] = $field['display_order'];
                            break;

                        case LookupHelper::getID("stream_field.field_type", "value"):
                            // Check max and min values are valid, if appropriate
                            $maxmin_id = LookupHelper::getID("stream_field.value_options", "maxminpost");
                            if ($t_field['value_options'] === $maxmin_id) {
                                if (array_key_exists('value_max', $field) === false) {
                                    $errors[] = "'value_max' field missing on list field : "
                                        . $t_field['display_order'];
                                    break;
                                }
                                if (array_key_exists('value_min', $field) === false) {
                                    $errors[] = "'value_min' field missing on list field : "
                                        . $t_field['display_order'];
                                    break;
                                }
                                if (intval($field['value_max']) !== $field['value_max']) {
                                    $errors[] = "'value_max' field is not a number : " . $t_field['display_order'];
                                    break;
                                }
                                if ((int)$field['value_min'] !== $field['value_min']) {
                                    $errors[] = "'value_min' field is not a number : " . $t_field['display_order'];
                                    break;
                                }
                                if ((int)$field['value_min'] >= (int)$field['value_max']) {
                                    $errors[] = "'value_min' field is greater than 'value_max' field : "
                                        . $t_field['display_order'];
                                    break;
                                }
                                // Extra check for stars. Ensure maximum is positive.
                                $value_type_id = LookupHelper::getID("stream_field.value_type", "stars");
                                if ($value_type_id === $t_field['value_type']) {
                                    if ((int)$field['value_max'] < 1) {
                                        $errors[] = "'value_max' field must be greater than 0 for star values : "
                                            . $t_field['display_order'];
                                        break;
                                    }
                                }
                                // Extra check for logarithmic max/min values.
                                // Ensure they are all 0 or powers of ten, but not 1.
                                $value_type_id = LookupHelper::getID("stream_field.value_type", "logarithmic");
                                if ($value_type_id ===  $t_field['value_type']) {
                                    $regexp = "/^0$|^((1|-1)0+)$/";
                                    if (preg_match($regexp, $field['value_min']) === 0) {
                                        $errors[] = "'value_min' field is not 0 or a power of 10 :"
                                            . $t_field['display_order'];
                                        break;
                                    }
                                    if (preg_match($regexp, $field['value_max']) === 0) {
                                        $errors[] = "'value_max' field is not 0 or a power of 10 :"
                                            . $t_field['display_order'];
                                        break;
                                    }
                                }

                            }
                            // Check Rhythm is valid, if appropriate
                            $value_options_id = LookupHelper::getID("stream_field.value_options", "rhythmpost");
                            if ($t_field['value_options'] === $value_options_id) {
                                if (array_key_exists('rhythm', $field) === false) {
                                    $errors[] = "'rhythm' field missing on list field : " . $t_field['display_order'];
                                    break;
                                }
                                $r = Rhythm::getIDFromUrl(urldecode($field['rhythm']));
                                if (ctype_digit(Rhythm::getIDFromUrl(urldecode($field['rhythm']))) === false) {
                                    $errors[] = "Rhythm is not valid in field : " . $t_field['display_order'];
                                    break;
                                }
                            }
                            $matches[] = $field['display_order'];
                            break;
                    }
                }
            }
        }

        // Check all fields are accounted for. Do not need to account for value fields that are not
        // required.
        if (count($errors) === 0) {
            foreach ($type_fields as $t_field) {   // Cycle through the stream fields
                $error = false;
                if (in_array($t_field['display_order'], $matches) === false) {
                    if ((int)$t_field['field_type'] === LookupHelper::getID("stream_field.field_type", "value")) {
                        $value_options = "stream_field.value_options";
                        if ((int)$t_field['value_options'] === LookupHelper::getID($value_options, "maxminpost")) {
                            $error = true;
                        }
                        if ((int)$t_field['value_options'] === LookupHelper::getID($value_options, "rhythmpost")) {
                            $error = true;
                        }
                    } else {
                        $error = true;
                    }

                }
                if ($error === true) {
                     $errors[] = "Field is missing : " . $t_field['display_order'];
                }
            }
        }

        if (empty($errors) === true) {
            return true;
        }
        return $errors;
    }

    /**
     * Insert an post for a user. Validates before insert.
     *
     * @param integer $stream_extra_id The extra id of the stream we are inserting an post for.
     * @param array $fields Posted fields.
     * @param integer $user_id The id of the user who is making the post.
     * @param integer|null $site_id If null, then it is set to this site.
     * @param integer|null $site_post_id The post_id that is uniqe to the site_id.
     * @param integer|null $parent_id Id of the parent post, if this is a sub post.
     * @param integer|null $top_parent_id Id of the top parent post, if this is a sub post.
     * @param integer|null $post_id Provide the existing post_id to update.
     * @param integer|null $status The status of the post. See post.status in the lookup table for options.
     *
     * @return Post|array|boolean model containg primary key or array of errors or false; remote site did not accept.
     */
    public static function insertPost($stream_extra_id, $fields, $user_id, $site_id=null,
        $site_post_id=null, $parent_id=null, $top_parent_id=null, $post_id=null, $status='public'
    ) {

        // Check post exists for an edit
        if (isset($post_id) === true && isset($user_id) === false) {
            throw new Exception("user_id required for an edit");
        }

        // Validate
        $result = PostMulti::verifyPost($stream_extra_id, $fields);
        if (is_array($result) === true) {
            return $result;
        }

        // Duplicated from validate code, becuase validate cannot edit fields.
        // When this validation is moved into a FormModel class, this will need considering.
        $kind_id = StreamBedMulti::getKindFromStreamExtra($stream_extra_id);
        if ($kind_id === LookupHelper::getId("stream.kind", "user")) {
            $fields[0]['link'] = UserProfile::convertRemoteProfileLink($fields[0]['link']);
        }

        // if parent_id or top_parent_id is set then both must be.
        if (isset($parent_id) === true || isset($top_parent_id) === true) {
            if (isset($parent_id) === false || isset($top_parent_id) === false) {
                throw new Exception("if parent_id is set then so must top_parent_id, and vice versa.");
            }
        }

        // If parent_id or top_parent_id is set then ensure that it is present.


        // check user owns the post if it is being edited
        if (isset($post_id) === true) {
            $revision = Post::getLatestRevisionNumber($post_id);
            if ($revision === 0) {
                throw new Exception('Post revision not found.');
            }
            $revision++;
            $post = true;
        } else {
            // Only create a new post if the post_id is null
            if (isset($site_id) === false) {
                $site_id = Yii::app()->params['site_id'];
            }

            $post = new Post;
            if (isset($site_post_id) === true) {
                $post->site_post_id = $site_post_id;
            }
            $post->stream_extra_id = $stream_extra_id;
            $post->user_id = $user_id;
            $post->site_id = $site_id;
            $post->parent = $parent_id;
            $post->top_parent = $top_parent_id;
            $post->status = LookupHelper::getID('post.status', $status);
            if ($post->save() === false) {
                throw new Exception('Post validation failed :' . ErrorHelper::model($post->getErrors()));
            }
            $post_id = $post->post_id;

            // Copy new post_id accross to site_post_id if site_post_id is null
            if (isset($site_post_id) === false) {
                $post->site_post_id = $post_id;
                $post->save();
            }
            $revision = 1;

            // If this is an post with an stream.kind = 'user' then it needs inserting into post_user table.
            if ((int)$kind_id === LookupHelper::getID("stream.kind", "user")) {
                PostUser::insertPost($post_id, $fields[0]);
            }
        }

        PostMulti::insertContent($post_id, $fields, $revision, $stream_extra_id);

        PostDescendent::insertAncestors($post_id, $parent_id);

        Post::recalculateChildCountForAncestors($post_id);

        return $post;
    }

    /**
     * Inserts a revision of a post.
     *
     * @param integer $post_id The id of the post we are inserting content for.
     * @param array $fields Posted fields.
     * @param integer $revision If null then the next available revision is inserted.
     * @param integer $stream_extra_id The extra id of the stream the post is being inserted into
     *
     * @return void
     */
    public static function insertContent($post_id, $fields, $revision, $stream_extra_id) {
        foreach ($fields as $key => $field) {
            $content = new PostContent;
            $content->post_id = $post_id;
            $content->revision = $revision;
            $content->display_order = $field['display_order'];

            $insert = false;
            if (isset($field['text']) === true) {
                $content->text = $field['text'];
                $insert = true;
            }
            if (isset($field['link']) === true) {
                $content->link = $field['link'];
                $insert = true;
            }
            if (isset($field['link_title']) === true) {
                $content->link_title = $field['link_title'];
                $insert = true;
            }
            if (isset($field['link_thumbnail_url']) === true) {
                $content->link_thumbnail_url = $field['link_thumbnail_url'];
                $insert = true;
            }
            if (isset($field['checked']) === true) {
                if ($field['checked'] === "true") {
                    $content->checked = true;
                } else {
                    $content->checked = false;
                }
                $insert = true;
            }
            if (isset($field['selected']) === true) {
                // Escape existing commas and backslashes.
                foreach ($field['selected'] as $list_item) {
                    str_replace("\\", "\\\\", $list_item);    // replaces backslashes with double backslashes.
                    str_replace(",", "\\,", $list_item);    // replaces commas with backslash commas.
                }
                $content->selected = implode(",", $field['selected']);
                $insert = true;
            }
            if (isset($field['value_min']) === true) {
                $content->value_min = $field['value_min'];
                $insert = true;
            }
            if (isset($field['value_max']) === true) {
                $content->value_max = $field['value_max'];
                $insert = true;
            }
            if ($insert === true) {
                if ($content->save() === false) {
                    throw new Exception('Post failed to insert : ' . ErrorHelper::model($content->getErrors()));
                }

                if (isset($field['link_thumbnail_small_base64']) === true
                    && strlen($field['link_thumbnail_small_base64']) > 0
                ) {
                    // Post fields are currently 1 based rather than 0;
                    $png_valid = ImageHelper::createPNGFromBase64String(
                        $field['link_thumbnail_small_base64'],
                        'user/' . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/small/' . $post_id . '/',
                        $key + 1 . '.png'
                    );
                    if ($png_valid === false) {
                        throw new Exception('Post image thumbnail failed to save.');
                    }
                } else {
                    // Need to check for guest due to meta posts being made for new users causing
                    // errors due to not being logged in yet.
                    if (Yii::app()->user->isGuest === false) {
                        $small_image_file = dirname(Yii::app()->request->scriptFile) . '/images/user/'
                            . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/small/' . $post_id . '/' . ($key + 1) . '.png';
                        if (file_exists($small_image_file) === true) {
                            unlink($small_image_file);
                        }
                    }
                }

                if (isset($field['link_thumbnail_large_base64']) === true
                    && strlen($field['link_thumbnail_large_base64']) > 0
                ) {
                    // Post fields are currently 1 based rather than 0;
                    $png_valid = ImageHelper::createPNGFromBase64String(
                        $field['link_thumbnail_large_base64'],
                        'user/' . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/large/' . $post_id . '/',
                        $key + 1 . '.png'
                    );
                    if ($png_valid === false) {
                        throw new Exception('Post image thumbnail failed to save.');
                    }
                } else {
                    // Need to check for guest due to meta posts being made for new users causing
                    // errors due to not being logged in yet.
                    if (Yii::app()->user->isGuest === false) {
                        $large_image_file = dirname(Yii::app()->request->scriptFile) . '/images/user/'
                            . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/large/' . $post_id . '/' . ($key + 1) . '.png';
                        if (file_exists($large_image_file) === true) {
                            unlink($large_image_file);
                        }
                    }
                }

                if (isset($field['link_thumbnail_large_proportional_base64']) === true
                    && strlen($field['link_thumbnail_large_proportional_base64']) > 0
                ) {
                    // Post fields are currently 1 based rather than 0;
                    $png_valid = ImageHelper::createPNGFromBase64String(
                        $field['link_thumbnail_large_proportional_base64'],
                        'user/' . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/large-proportional/' . $post_id . '/',
                        $key + 1 . '.png'
                    );
                    if ($png_valid === false) {
                        throw new Exception('Post image thumbnail failed to save.');
                    }
                } else {
                    // Need to check for guest due to meta posts being made for new users causing
                    // errors due to not being logged in yet.
                    if (Yii::app()->user->isGuest === false) {
                        $large_image_file = dirname(Yii::app()->request->scriptFile) . '/images/user/'
                            . Yii::app()->user->getDomain() . '/'. Yii::app()->user->getName()
                            . '/post/thumbnails/large-proportional/' . $post_id . '/' . ($key + 1) . '.png';
                        if (file_exists($large_image_file) === true) {
                            unlink($large_image_file);
                        }
                    }
                }

                if (LookupHelper::getID('stream_field.field_type', 'openlist') === (int)$field['field_type']
                    && isset($field['selected']) === true
                ) {
                    StreamOpenListItem::insertForPost($stream_extra_id, $key + 1, $field['selected']);
                }
            }

        }

    }

    /**
     * Checks if an post is owned by an stream.
     *
     * @param integer $post_id The id of the post we are cheing ownership for.
     * @param integer $stream_extra_id The extra id of the stream we are cheking is the owner.
     *
     * @return boolean
     */
    public static function owned($post_id, $stream_extra_id) {
        return Post::model()->exists(
            array(
                'condition' => 'post_id=:post_id AND stream_extra_id=:stream_extra_id',
                'params' => array(
                    ':post_id' => $post_id,
                    ':stream_extra_id' => $stream_extra_id,
                ),
            )
        );
    }

    /**
     * Fetch the full post, stream and sub streams and return as an array ready for converting to json.
     *
     * Used for the public display of posts.
     *
     * @param integer $post_id The id of the post we are fetching.
     *
     * @return array|string Results or an error message.
     */
    public static function getFullPost($post_id) {
        $post_details = PostMulti::getPost($post_id);

        if ($post_details === false || $post_details === 'GetPost_not_found') {
            return "Post not found";
        }

        $post = array();
        $post['post'] = $post_details;

        $stream = StreamBedMulti::getByIDWithExtra($post_details['stream_id']);
        $post['stream'] = StreamBedMulti::getJSON($stream, $stream->user->username, $stream->user->site->domain);

        // Select all sub post stream details that are needed for making comments
        $post['child_streams'] = PostMulti::getAllChildStreams($post_details['stream_id']);

        return $post;
    }


    /**
     * Return post details in an array.
     *
     * Only fetches posts that are in their cooldown period if the logged on user owns the post.
     *
     * @param integer $post_id The id of the post we are fetching.
     * @param integer|string [$revision='latest'] The revision of the post to fetch. Defaults to latest.
     * @param string [domain] The home domain of the post. If not included then assumed that the post is local.
     *
     * @return array
     */
    public static function getPost($post_id, $revision='latest', $domain=null) {

        $site_id = Yii::app()->params['site_id'];
        if (isset($domain) === true) {
            $site_id = Site::getSiteId($domain);
        }

        $sql = "
            SELECT
                 UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,post.post_id AS post_id
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT_WS('/',version.major, version.minor, version.patch) AS stream_version
                ,stream_child.child_id AS stream_child_id
                ,post_mode_lookup.value AS stream_post_mode
                ,post.status
                ,post.block AS stream_block
                ,post.block_tree AS tree_block
                ,post.child_count
            FROM post
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                LEFT JOIN stream_child
                    ON stream_extra.stream_extra_id = stream_child.parent_id
            WHERE
                post.site_post_id = :post_id
                AND post.site_id = :site_id
                AND (post.date_created < :cooldown OR post.user_id = :user_id)";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $command->bindValue(':site_id', $site_id, PDO::PARAM_INT);
        $command->bindValue(':user_id', Yii::app()->user->getId(), PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(':cooldown', $cooldown, PDO::PARAM_STR);

        $post_details = $command->queryRow();

        if ($post_details === false) {
            if ($domain !== HOST) {
                // @todo fetch from the hosts domain.
            } else  {
                return 'GetPost_not_found';
            }
        }

        // Manually adding revision, it is faster than making an extra table join
        if ($revision === 'latest') {
            $revision = Post::getLatestRevisionNumber($post_id);
        }
        $post_details['revision'] = $revision;

        $post_details['status'] = LookupHelper::getValue($post_details['status']);
        // Only the creator and recipeent can see private posts.
        if ($post_details['status'] === 'private') {
            $post_user_id = Post::getUserId($post_id);

            $current_user_id = Yii::app()->user->getId();
            $same_user = $current_user_id === $post_user_id;
            if ($same_user === false && PostPrivateRecipient::isRecipient($post_id, $current_user_id) === false) {
                return 'GetPost_not_found';
            }
        } else if ($post_details['status'] === 'deleted') {
            // Deleted posts can still be fetched by those who have an post_private_recipient link
            // a take of the post or a child.
            $owns_child = Post::doesOwnChild(Yii::app()->user->getId(), $post_id);
            $has_taken = Take::hasUserTaken(Yii::app()->user->getId(), $post_id);
            $has_private_link = !PostPrivateRecipient::getDeleted($post_id, Yii::app()->user->getId());
            if ($has_private_link === false && $owns_child === false && $has_taken === false) {
                return 'GetPost_not_found';
            }

        // If the post status is not as requested then return not found
        } else if ($post_details['status'] === 'deleted') {
            return 'GetPost_not_found';
        }

        if ($post_details !== false) {
            $post_details['content'] = PostContent::getPostContent(
                $post_details['post_id'],
                $post_details['revision']
            );
        }

        //if (empty($post_details['content']))
        //    $post_details['content'] = new stdClass();        // Forces an empty object
        //    rather than an array. See http://www.php.net/manual/en/language.oop5.basic.php#92123

        return $post_details;
    }

    /**
     * Recursively fetch all child streams.
     *
     * Used for the public display of posts.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching children for.
     *
     * @return array suitable for converting to JSON.
     */
    public static function getAllChildStreams($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql =    "CALL generic_tree('stream_child','child_id','parent_id', " . $stream_extra_id ."); ";
        $command = $connection->createCommand($sql);
        $child_streams = $command->execute();
        // Calls a stored procedure that walks through the parent child tree and returns all the raw child IDS
        $sql = "
            SELECT DISTINCT
                 _subtree.child_id
                ,_subtree.parent_id
                ,stream_child.version_type
                ,version.family_id
                ,version.major
                ,version.minor
                ,version.patch
            FROM _subtree
                INNER JOIN stream_child
                    ON stream_child.child_id = _subtree.child_id
                        AND stream_child.parent_id = _subtree.parent_id
                INNER JOIN stream_extra ON stream_child.child_id = stream_extra.stream_extra_id
                INNER JOIN version ON stream_extra.version_id = version.version_id";
        $command = $connection->createCommand($sql);
        $child_streams = $command->queryAll();

        // Loop through raw list and fetch the most recent version if it is requested
        $child_count = count($child_streams);
        for ($i = 0; $i < $child_count; $i++) {
            $child = $child_streams[$i];
            if ($child['version_type'] !== LookupHelper::getID("version_type", "major/minor/patch")) {
                $version_sql ="";
                if ($child['version_type'] !== LookupHelper::getID("version_type", "major/minor/latest")) {
                    $version_sql = "version.major = " . $child['major']
                        . " AND version.minor = " . $child['minor'] . " ";
                } else if ($child['version_type'] !== LookupHelper::getID("version_type", "major/latest")) {
                    $version_sql =    "version.major = " . $child['major'] . " ";
                }

                $sql = "
                    SELECT stream_extra.stream_extra_id
                    FROM stream_extra
                        INNER JOIN version ON stream_extra.version_id = version.version_id
                    WHERE
                        version.family_id = " . $child['family_id'] . "
                        AND " . $version_sql . "
                    ORDER BY version.major DESC, version.minor DESC, version.patch DESC
                    LIMIT 1";
                $command = $connection->createCommand($sql);
                $child_stream = $command->queryRow();

                if ($child_stream !== false) {
                    // Change the childs rows parent ID to be the updated version
                    for ($j = 0; $j < $child_count; $j++) {
                        if ($child_streams[$j]['parent_id'] === $child_streams[$i]['child_id']) {
                            $child_streams[$j]['parent_id'] = $child_stream['stream_extra_id'];
                        }
                    }
                    // Change the current rows child ID to be the updated version
                    $child_streams[$i]['child_id'] = $child_stream['stream_extra_id'];
                }
            }
        }

        //Fetch each stream, for entering posts
        $streams = array();
        foreach ($child_streams as $child) {
            $stream = StreamBedMulti::getByIDWithExtra($child['child_id']);
            $child_stream = StreamBedMulti::getJSON(
                $stream,
                $stream->user->username,
                $stream->user->site->domain
            );
            $streams[$child['child_id']] = $child_stream;
        }
        return $streams;
    }

    /**
     * Fetch the min and max values for an stream, via an post id and the stream field.
     *
     * @param integer $post_id The post id we are getting the min and max for.
     * @param integer $field_id The field of the post we are getting the min and max for.
     *
     * @return array
     */
    public static function getMinMax($post_id, $field_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 value_min
                ,value_max
                ,value_options
            FROM
                post
                INNER JOIN stream_field ON post.stream_extra_id=stream_field.stream_extra_id
            WHERE
                stream_field.display_order = :field_id
                AND post.post_id = :post_id";

        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id);
        $command->bindValue(":field_id", $field_id);
        $row = $command->queryRow();

        if ($row === false) {
            $exception = "post field not found when looking up max min values: "
                . "field_id = " . $field_id . ", post_id = " . $post_id;
            throw new Exception($exception);
        }

        if (empty($row['value_options']) === true) {
            $exception = "value_options not found when looking up max min values: "
                . "field_id = " . $field_id . ", post_id = " . $post_id;
            throw new Exception($exception);
        }

        $value_option = LookupHelper::getValue($row['value_options']);
        if ($value_option === "maxminpost") {
            $row = PostContent::getMaxMin($post_id, $field_id);
        }

        if ($row === false) {
            $exception = "post field not found when looking up max min values (constraint on the post): "
                . "field_id = " . $field_id . ", post_id = " . $post_id;
            throw new Exception();
        }

        return $row;
    }

    /**
     * Get the post row for an post_id.
     *
     * Does not check if the post has cooleddown.
     *
     * @param integer $post_id The id of the post we are getting a row for.
     *
     * @return array
     */
    public static function getPostRow($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT * FROM post WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        if (isset($row) === false) {
            throw new Exception("Post ID not found.");
        }
        return $row;
    }

    /**
     * Fetches a local post_id for a remote domains post_id
     *
     * Checks if the post exists locally, if not, it checks the remote domain for the post and inserts it localy.
     *
     * If a local post is passed in then it does not check the validity of the local post id.
     *
     * @param type $post_domain
     * @param type $domain_post_id
     *
     * @return intetger|string Error code if the message does not exist.
     *  Valid errors are domain_not_found and post_not_found
     */
    public static function getLocalPostId($post_domain, $domain_post_id) {
        if ($post_domain === Yii::app()->params['host']) {
            return $domain_post_id;
        }

        $site_id = SiteMulti::getSiteID($post_domain, true, true);
        if ($site_id === false) {
            return 'domain_not_found';
        }

        $local_post_id = Post::getLocalPostId($site_id, $domain_post_id);
        if ($local_post_id === false) {
            // !!! Need to try to fetch the post from the remote domain
            return 'post_not_found';
        }
        return $local_post_id;
    }

    /**
     * Get the home domain for a post.
     *
     * @param integer $post_id The id of the post we are getting a home domain for.
     *
     * @return string
     */
    public static function getHomeDomain($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT site.domain
                FROM post
                INNER JOIN site ON post.site_id = site.site_id
                WHERE post.post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $domain = $command->queryScalar();
        return $domain;
    }

    /**
     * Get the child post rows for a post.
     *
     * @param integer $post_id The id of the parent post to fetch children for.
     *
     * @return array
     */
    public static function getChildPostRows($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT post.*
                FROM post
                INNER JOIN post_descendent ON post_descendent.descendent_post_id = post.post_id
                WHERE post_descendent.ancestor_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Get the parent post rows for a post.
     *
     * @param integer $post_id The id of the child post to fetch parents for.
     *
     * @return array
     */
    public static function getParentPostRows($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT post.*
                FROM post
                INNER JOIN post_descendent ON post_descendent.ancestor_post_id = post.post_id
                WHERE post_descendent.descendent_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>