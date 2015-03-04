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
 * View for updating regex expressions.
 */
$this->menu=array(
    array('label' => 'List StreamRegex', 'url' => array('index')),
    array('label' => 'Create StreamRegex', 'url' => array('create')),
    array('label' => 'View StreamRegex', 'url' => array('view', 'id' => $model->stream_regex_id)),
    array('label' => 'Manage StreamRegex', 'url' => array('admin')),
);
?>

<h1>Update StreamRegex <?php echo $model->stream_regex_id; ?></h1>

<?php echo $this->renderPartial('/Client/Admin/StreamRegex/_form', array('model' => $model)); ?>