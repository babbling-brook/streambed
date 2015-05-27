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

// Includes all the shared/core and activatated component and user javascript, with theme overrides if they are present.

// shared files
HTMLHelper::includeNativeOrTheme('/js/Shared', 'js');
HTMLHelper::includeNativeOrTheme('/js/Shared/Backbone', 'js');
HTMLHelper::includeNativeOrTheme('/js/Client/Core', 'js');
HTMLHelper::includeNativeOrTheme('/js/Client/Component', 'js');
HTMLHelper::includeNativeOrTheme('/js/Client/User', 'js');

// ready script
if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Client/ready.js') === true) {
    ?>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl; ?>/js/Client/ready.js"></script>
    <?php
} else {
    ?>
    <script type="text/javascript" src="/js/Client/ready.js"></script>
    <?php
}

?>
