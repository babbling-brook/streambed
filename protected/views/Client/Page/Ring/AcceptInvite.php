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
 * View for an accept_invite ring form.
 */

$test = 3;
?>
<h2>Accept ring invitation</h2>

<form method="post">
    <div class="form">

        <p>
            You have been invited to join the <em><?php echo $ring; ?></em> Ring.
        </p>

        <div class="row">
            <?php
            echo CHtml::submitButton(
                'Accept invitation',
                array("name" => 'accept', 'class' => 'standard-button')
            );
            ?>
        </div>

    </div>
</form>