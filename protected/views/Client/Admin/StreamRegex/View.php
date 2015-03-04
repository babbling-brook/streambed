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
 * View for viewing regex expressions.
 */

$this->menu = array(
    array('label' => 'List StreamRegex', 'url' => array('index')),
    array('label' => 'Create StreamRegex', 'url' => array('create')),
    array('label' => 'Update StreamRegex', 'url' => array('update', 'id' => $model->stream_regex_id)),
    array(
        'label' => 'Delete StreamRegex',
        'url' => '#',
        'linkOptions' => array(
            'submit' => array('delete', 'id' => $model->stream_regex_id),
            'confirm' => 'Are you sure you want to delete this item?',
        ),
    ),
    array('label' => 'Manage StreamRegex', 'url' => array('admin')),
);
?>

<h1>View StreamRegex #<?php echo $model->stream_regex_id; ?></h1>

<?php $this->widget(
    'zii.widgets.CDetailView',
    array(
        'data' => $model,
        'attributes' => array(
            'stream_regex_id',
            'name',
            'regex',
            'display_order',
        ),
    )
);
?>
