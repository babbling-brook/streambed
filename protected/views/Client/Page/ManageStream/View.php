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
 * View for viewing streams.
 */

$this->pageTitle="View Stream : " .$model->name;
$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageStream/View'));
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/salt.jquery.yiilistview.js');

$this->menu = $this->operationsMenu("view");
$this->menu_drop_down = '<h4 id="side_bar_switch_versions">Switch Versions</h4>';
$this->menu_drop_down .= Version::switchVersions("update", $model->extra->version_id, 'stream');
?>

<h2>View Stream</h2>

<?php
$form=$this->beginWidget('CActiveForm');
$status = StatusHelper::getDescription($model->extra->status_id);
$json_url = UrlHelper::getVersionUrl(
    $this->username,
    "stream",
    "json",
    $model->name,
    $model->extra->version->major,
    $model->extra->version->minor,
    $model->extra->version->patch
);
$json_url = 'http://scientia.' . HOST . $json_url;
$urls = StreamBedMulti::getArrayChildUrls($model->extra->stream_extra_id);
$child_urls = "";
foreach ($urls as $url) {
    $child_urls .= '<a href="' . $url . '">' . $url . '</a><br/>';
}

$default_rings = StreamDefaultRing::getDefaults($model->extra->stream_extra_id);
$default_ring_lines = "";
foreach ($default_rings as $ring) {
    $default_ring_lines .= "<a href='http://"
        . $ring['domain']
        . "/"
        . $ring['username']
        . "/profile'>"
        . $ring['domain']
        . "/"
        . $ring['username']
        . "</a></br>";
}
$default_ring_lines = substr($default_ring_lines, 0, strlen($default_ring_lines) - 5);
$meta_url = '/postwithtree/' . Yii::app()->params['host'] . '/' . $model->extra->meta_post_id;

$this->widget(
    'zii.widgets.CDetailView',
    array(
        'data' => $model,
        'id' => 'view_details',
        'htmlOptions' => array('class' => 'content-indent yii-view'),
        'attributes' => array(
            'name',
            array(
                'label' => 'version',
                'type' => 'raw',
                'value' => $this->version_string,
            ),
            'extra.date_created',
            array(
                'label' => 'Kind of Stream',
                'type' => 'raw',
                'value' => LookupHelper::getValue($model->kind),
            ),
            array(
                'label' => 'Status',
                'type' => 'raw',
                'value' => $status,
            ),
            'extra.description',
            array(
                'label' => 'JSON link',
                'type' => 'raw',
                'value' => '<a href="' . $json_url . '">' . $json_url . '</a>',
            ),
            array(
                'label' => 'Children',
                'type' => 'raw',
                'value' => $child_urls,
            ),
            array(
                'label' => 'Meta Post',
                'type' => 'raw',
                'value' => '<a href="' . $meta_url . '">' . $meta_url . '</a>',
            ),
            array(
                'label' => 'Default Moderation Rings',
                'type' => 'raw',
                'value' => $default_ring_lines,
            ),
        ),
    )
);
?>

<div class="content-indent inline-label field-title">
    <span>Fields</span>
</div>
<?php
$rows =  StreamField::getStreamFields($model->extra->stream_extra_id);
foreach ($rows as $row) {
    echo ('<div class="content-indent">');
    $this->renderPartial(
        '/Client/Page/ManageStream/StreamField/_view',
        array(
            'data' => $row,
        )
    );
    echo ('</div><br/>');
}
$this->endWidget();
?>