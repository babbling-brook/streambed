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
 * View for the page new users see after signing up.
 */
$this->pageTitle=Yii::app()->name . ' - Welcome';
?>

<h1>Welcome</h1>

<p>Thank you for signing up with the Babbling Brook network.</p>
<p>Your username is :
    <strong>
        <?php
            echo Yii::app()->params['host'];
            echo "/";
            echo $this->username;
        ?>
    </strong>
<br /> You can enter your username into the address bar of your browser to be taken directly to your login page.
</p>

