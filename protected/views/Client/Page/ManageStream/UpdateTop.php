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
 * @param $update_template The name of the update template to render ('update', 'edit', 'edit_fields')
 * @param $model A model of the stream and its dependencies.
 */

$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageStream/Update'));

$this->menu = $this->operationsMenu("actions");
$this->menu_drop_down = '<div id="switch_version_operation">';
$this->menu_drop_down .='<h4 id="side_bar_switch_versions">Switch Versions</h4>';
$this->menu_drop_down .= Version::switchVersions("update", $model->extra->version_id, 'stream');
$this->menu_drop_down .= '</div>';

$help = new StreamHelp();
echo CHtml::activeHiddenField($model, "stream_id");
echo CHtml::activeHiddenField($model->extra, "stream_extra_id");
?>

<h2>Update Stream</h2>

<div id="stream_overview" class="form content-indent">
    <p>
        See the <a href="http://www.babblingbrook.net/page/docs/streams" target="_blank">Babbling Brook</a>
        documentation for details on how streams are constructed.
    </p>

    <div class="row">
        <span class="inline-label">Name:</span> <?php echo $model->name; ?>
    </div>

    <div class="row">
        <span class="inline-label">Version:</span> <?php echo $this->version_string ?>
    </div>

    <div class="row">
        <span class="inline-label">Date Created:</span> <?php echo $model->extra->date_created; ?>
    </div>

    <div class="row">
        <span class="inline-label">Status:</span>
        <span class="stream_status">
            <?php echo StatusHelper::getDescription($model->extra->status_id); ?>
        </span>
    </div>
    <?php echo CHtml::activeHiddenField($model->extra, "status_id"); ?>


    <div class="row">
        <span class="inline-label">Kind of Stream:</span> <?php echo LookupHelper::getValue($model->kind); ?>
    </div>
</div>

<?php
$actions_active = '';
$edit_active = '';
$fields_active = '';
switch ($update_template) {
    case 'Update':
        $actions_active = 'active';
        break;

    case 'Edit':
        $edit_active = 'active';
        break;

    case 'EditFields':
        $fields_active = 'active';
        break;

}
?>

<?php // This nav has been disabled. I havn't decided if I should delete it permanently yet. ?>
<ul id="update_stream_tabs" class="content-indent hide">
    <li id="actions_tab" class="<?php echo $actions_active; ?>">
        <a href="update">Actions</a>
    </li>
    <li id="edit_stream_tab" class="<?php echo $edit_active; ?>">
        <a href="edit">Edit Stream</a>
    </li>
        <li id="edit_fields_tab" class="<?php echo $fields_active; ?>">
        <a href="editfields">Edit Fields</a>
    </li>
</ul>


<div id="stream_form" class="form content-indent">
    <?php
    $this->renderPartial(
        '/Client/Page/ManageStream/' . $update_template,
        array(
            'model' => $model,
        )
    );
    ?>
</div>
