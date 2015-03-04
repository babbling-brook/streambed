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
 * View for a member/admin of a ring to leave that ring.
 */
$this->pageTitle=Yii::app()->name . ' - Resign from the ' . $ring_name . ' Ring';

$this->menu_title = "Ring Members";
$this->menu = RingController::adminMenu($admin_type, Ring::getMemberType($ring_id));
?>

<h2>Resign from the <strong><?php echo $ring_name; ?></strong> Ring</h2>

<p>
    Please confirm that you want to resign as an admin on the <strong><?php echo $ring_name; ?></strong> Ring.
</p>

<p>
    You will no longer be able to access any admin features of this Ring once you confirm.
</p>

<?php if ($admin_count === 1) { ?>
    <p class="warning">
        You are the only administrator of this ring. If you resign it will be orphaned and unrecoverable.
    </p>
<?php } ?>


<form method="post">
    <div class="form">

        <div class="row">
            <input id="hidden_confirm" type="hidden" name="confirm" value="true" />
            <input id="confirm_sumbit" type="submit" value="Confirm" name="confirm_button">
        </div>

    </div>
</form>