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
 * View for a list of streams.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageStream/FieldsEdit'));

$has_posts = Post::areTherePostsInStream($model->extra->stream_extra_id);
$help = new StreamHelp();

?>

<div id="edit_fields_plane">

    <?php if (intval($model->extra->status_id) !== 1) { ?>
        <div class="no-editing" class="row">
            <p>
                Stream details are only editable if the stream is private.
                If no one else has made any posts then you can revert a published stream
                back to private on the <a href="update">actions tab</a>.
            </p>
            <p>
                Alternatively create a new version or duplicate to return to private mode.
            </p>
        </div>
    <?php }  else if ($has_posts === true) { ?>
        <div class="no-editing" class="row">
            <p>
                You have made some posts in this stream. The posts must be deleted before the fields can be edited.
            </p>
            <p>
                <button id="delete_all_posts" class="standard-button">Delete all posts</button>
            </p>
        </div>
    <?php }  else { ?>
        <h3 class="form">Fields</h3>

        <?php
            $update_url = '/' . $this->username . '/streamfield/';
            echo CHtml::HiddenField('field_url', $update_url);
        ?>

        <?php if (LookupHelper::getValue($model->kind) === 'user') { ?>
            <div id="no_stream_fields">
                Stream fields are not editable if the kind of stream is set to 'user'.
            </div>
        <?php } else { ?>
            <div id="fields_container">
                <div id="stream_fields">

                    <?php
                    $rows = StreamField::getStreamFields($model->extra->stream_extra_id);
                    foreach ($rows as $row) {
                        $this->renderPartial(
                            '/Client/Page/ManageStream/StreamField/_update',
                            array(
                                'data' => $row,
                            )
                        );
                    }
                    ?>

               </div>
                <div id="add_new_field" class="field-container">
                    <img src="/images/ui/add.png" title="Add a new field">
                    <h4 class="stream_field_header">
                        Add a new field
                    </h4>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<div id="stream_fields_edit_templates" class="hide">
    <select id="stream_fileds_edit_any_value_template">
        <option class="" value="17">User can enter any value</option>
    </select>
</div>