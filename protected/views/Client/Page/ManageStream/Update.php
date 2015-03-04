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
 *
 * @param $model A model of the stream and its dependencies.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageStream/Update'));

$help = new StreamHelp();

?>
<div id="actions_plane">
    <h3 class="form">Actions</h3>

    <div class="row new-version">
        <?php
            echo CHtml::label(
                'Create New Version' . $help->versions(),
                'version',
                array('class' => 'inline-label')
            );
            $versions = Version::getNextVersions(
                $this->model->extra->version->family_id,
                LookupHelper::getID('version.type', 'stream'),
                $this->model->extra->version->major,
                $this->model->extra->version->minor,
                $this->model->extra->version->patch,
                StatusHelper::getId('private') === $this->model->extra->status_id ? true : false
            );
            echo CHtml::dropDownList(
                'version',
                '',
                $versions
            );
            echo CHtml::Button(
                'New Version',
                array("id" => "new_version", "name" => "new_version", "class" => "standard-button")
            );
            echo CHtml::Tag('span', array('class' => 'label-loading hide', 'id' => 'new_version_loading'), "");
            echo CHtml::Tag('div', array('class' => 'error', 'id' => 'new_version_error'), "");
            echo CHtml::Tag('div', array('class' => 'success', 'id' => 'new_version_success'), "");
        ?>
    </div>

    <div id="stream_status_actions" class="row">
        <?php
            $update_url = Yii::app()->params['site_root'] . '/' . $this->username . "/stream/" . $model->name . '/'
                . $model->extra->version->major . '/' . $model->extra->version->minor . '/'
                . $model->extra->version->patch . '/';
            echo CHtml::HiddenField('update_url', $update_url);
        ?>
        <label class="inline-label">
            Change Status
            <?php echo ($help->status()); ?>
        </label>
        <?php
        $status = StatusHelper::getValue($model->extra->status_id);
        $deprecate = $publish = $delete = "";
        $revert = "hide";
        if ($status === "public") {
            $publish = "hide";
        }
        if ($status === "public" || $status === "deprecated" ) {
            $is_deletable = StreamMulti::isDeletable($model->extra->stream_extra_id, $model->user_id);
            if ($is_deletable === false) {
                $delete = "hide";
            } else {
                $revert = "";
            }
        }
        if ($status === "private" || $status === "deprecated" ) {
            $deprecate = "hide";
        }
        ?>
        <img src="/images/ui/revert.png" class="revert <?php echo $revert; ?>" title="Revert to draft status">
        <img src="/images/ui/publish.png" class="publish <?php echo $publish; ?>" title="Publish">
        <img src="/images/ui/delete.png" class="delete <?php echo $delete; ?>" title="Delete">
        <img
            alt="Deprecate"
            src="/images/ui/deprecate.png"
            class="deprecate <?php echo $deprecate; ?>"
            title="Deprecate">
    </div>

    <div id="stream_post_mode" class="row">
        <?php
            echo Html::label(
                'Who can submit posts',
                'post_mode',
                array('class' => 'inline-label'),
                $help->postMode()
            );
            echo CHtml::dropDownList(
                'post_mode',
                $model->extra->post_mode,
                LookupHelper::getDescriptions('stream_extra.post_mode')
            );
            echo CHtml::Tag('span', array('class' => 'label-loading hide', 'id' => 'new_post_mode_loading'), "");
        ?>
    </div>

    <div class="row">
        <?php
        echo Html::label('Duplicate: New Name', 'duplicate_name', array('class' => 'inline-label'), $help->duplicate());
        echo CHtml::textField('duplicate_name', $model->name, array('size' => 60, 'maxlength' => 128));
        echo CHtml::Button(
            'Duplicate',
            array("id" => "duplicate", "name" => "duplicate", "class" => "standard-button")
        );
        echo CHtml::Tag('span', array('class' => 'label-loading hide', 'id' => 'duplicate_loading'), "");
        echo CHtml::Tag('div', array('class' => 'error', 'id' => 'duplicate_error'), "");
        echo CHtml::Tag('div', array('class' => 'success', 'id' => 'duplicate_success'), "");
        ?>
    </div>

</div>


<div id="stream_update_templates" class="hide">

    <div id="stream_duplicated_template">
         Stream duplicated. Edit it <a class="duplicated-stream-url" href="">here</a>.
    </div>

    <div id="new_stream_version_template">
         New version created. Edit it <a class="new-stream-version-url" href="">here</a>.
    </div>
</div>
