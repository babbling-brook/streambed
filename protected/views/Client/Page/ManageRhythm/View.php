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
 * View for viewing Rhythms.
 */

$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/salt.jquery.yiilistview.js');
$cs->registerCssFile(Yii::app()->baseUrl . '/css/Client/Component/YiiView.css');

$cs->registerCssFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.css');
$cs->registerCssFile(Yii::app()->baseUrl . '/css/Client/Component/CodeMirror.css');
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageRhythm/View'));

// These files are compressed into the one above.
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/comment/continuecomment.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/mode/javascript/javascript.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/edit/matchbrackets.js');

$this->menu = $this->operationsMenu("view");
$this->menu_drop_down = '<h4 id="side_bar_switch_versions">Switch Versions</h4>';
$this->menu_drop_down .= Version::switchVersions("update", $model->extra->version_id, 'rhythm');
?>

<h2>View Rhythm</h2>

<?php
$url_start = "http://scientia." . HOST . "/" . $this->username . "/rhythm/"
    . urlencode($model->name) . "/" . $this->version_string . '/';
$meta_url = '/post/' . Yii::app()->params['host'] . '/' . $model->extra->meta_post_id;
$this->widget(
    'zii.widgets.CDetailView',
    array(
        'data' => $model,
        'id' => 'view_details',
        'htmlOptions' => array('class' => 'content-indent yii-view'),
        'attributes' => array(
            'name',
            array(
                'label' => 'Owner',
                'type' => 'raw',
                'value' => $this->username,
            ),
            array(
                'label' => 'Date Created',
                'type' => 'raw',
                'value' => $model->extra->date_created,
            ),
            array(
                'label' => 'Version',
                'type' => 'raw',
                'value' => $this->version_string,
            ),
            array(
                'label' => 'Status',
                'type' => 'raw',
                'value' => StatusHelper::getDescription($model->extra->status_id),
            ),
            array(
                'label' => 'Category',
                'type' => 'raw',
                // Yii throws a wobbly if the value = 'sort'. Adding a space solves it.
                'value' => ' ' . $model->extra->rhythm_cat->name,
            ),
            array(
                'label' => 'Description',
                'type' => 'raw',
                'value' => $model->extra->description,
            ),
            array(
                'label' => 'Meta Post',
                'type' => 'raw',
                'value' => '<a href="' . $meta_url . '">' . $meta_url . '</a>',
            ),
            array(
                'label' => 'JavaScript Full',
                'type' => 'raw',
                'value' => '<a href="' . $url_start . 'json">'
                    . $url_start . 'json</a>',
            ),
            array(
                'label' => 'JavaScript Minified',
                'type' => 'raw',
                'value' => '<a href="' . $url_start . 'minijson">'
                    . $url_start . 'minijson</a>',
            ),
        ),
    )
);
?>

<table class="content-indent yii-view">
    <tr>
        <th>Rhythm</th>
    </tr>
    <tr>
        <td class="block-loading round-borders">
            <textarea id="rhythm_javascript" class="hidden"><?php echo $model->extra->full; ?></textarea>
        </td>
    </tr>
</table>