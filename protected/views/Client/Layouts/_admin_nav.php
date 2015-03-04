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
 * View for the admin navigation menu
 */

$this->widget(
    'zii.widgets.CMenu',
    array(
        'encodeLabel' => false,
        'items' => array(
            array('label' => 'Delete Tests', 'url' => '/site/admin/deletetests'),
            array('label' => 'Minify', 'url' => '/site/admin/minify'),
            array('label' => 'Categories', 'url' => '/site/admin/cat/admin'),
            array('label' => 'Stream RegEx', 'url' => '/site/admin/streamregex/admin'),
            array('label' => 'Tests', 'url' => '/tests/index'),
            array('label' => 'Signup Codes', 'url' => '/site/admin/signupcodes/index'),
            array('label' => 'Export User', 'url' => '/site/admin/exportuser'),
        ),
    )
);
?>
