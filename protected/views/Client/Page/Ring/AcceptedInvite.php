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
 * View for an accepted ring invite.
 */

$this->pageTitle=Yii::app()->name . ' - Accepted Ring invitation';

?>
<h2>Accepted Ring invitation</h2>

    <p>
        You are now a member of the <strong><?php echo $ring; ?></strong> Ring.
    </p>

    <p>
        What do you want to do now?<br/>
        <?php if ($return !== false) { ?>
            <a href="<?php echo $return; ?>"> Go back to where you where</a><br/>
        <?php } ?>
        <?php if ($type === "admin") { ?>
            <a href="update">Go to this Rings administration section</a><br/>
        <?php } else { ?>
            <a href="members">Go to this Rings members section</a><br/>
        <?php } ?>
    </p>

    <p>

    </p>