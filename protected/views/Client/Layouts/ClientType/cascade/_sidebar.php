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
 * View for the sidebar layout template.
 */

// Main operations menu
if (count($this->menu) > 0) {
    $this->beginWidget(
        'zii.widgets.CPortlet',
        array(
            'title' => $this->menu_title,
            'id' => 'sidebar',
        )
    );
    $this->widget(
        'zii.widgets.CMenu',
        array(
            'items' => $this->menu,
            'htmlOptions' => array('class' => 'operations'),
        )
    );

    // Attatch a dropdown to the portlet if it exists.
    if (isset($this->menu_drop_down) === true) {
        echo $this->menu_drop_down;
    }

    $this->endWidget();
}

// Menu to list other sites that are using this datastore
//$sites = SiteAccess::getAll();
//if (count($sites) > 0) {
//    $sites_menu = array();
//    foreach ($sites as $site) {
//        $sites_menu[] = array('label' => $site['domain'], 'url' => 'http://' . $site['domain']);
//    }
//
//    $this->beginWidget(
//        'zii.widgets.CPortlet',
//        array(
//            'title' => 'Client Sites',
//        )
//    );
//    $this->widget(
//        'zii.widgets.CMenu',
//        array(
//            'items' => $sites_menu,
//            'htmlOptions' => array('class' => 'operations'),
//        )
//    );
//    $this->endWidget();
//}

// General place to put extra stuff on the side nav
echo $this->menu_extra;
?>
