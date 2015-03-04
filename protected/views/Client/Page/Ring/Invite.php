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
 * View for a user sending an invite.
 */

$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Page/Ring/Invite.js');
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/Invite'));

$this->pageTitle=Yii::app()->name . ' - Invite a new ' . $invite_type;

$help = new RingHelp();
if ($invite_type === "member") {
    $invite_help = $help->inviteMember();
    $this->menu_title = "Ring Members";
} else {
    $invite_help = $help->inviteAdmin();
    $this->menu_title = "Ring Admin";
}

if ($menu_type === "admin") {
    $this->menu = RingController::adminMenu($admin_type, $invite_type);
} else {
    $this->menu = RingController::memberMenu($invite_type);
}

$invitation_phrase = 'a member';
if ($type === 'admin') {
    $invitation_phrase = 'an admin';
}

?>
<h2>Send an invitation</h2>

<div id="send_invitation" class="content-indent">

    <div class="larger">Send an invitation to join the
        <em><?php echo $ring_name; ?></em>
        Ring as <?php echo $invitation_phrase; ?>.
        <?php echo $invite_help; ?>
    </div>

    <div class="form">

        <input type="hidden" id="ring_name" value="<?php echo $ring_name; ?>" />

        <div class="row">
            <label for="invite" class="block internal-row">User to send an invitation to</label>
            <input type="text" id="invite" name="invite" value="" maxlength="128" size="60">
            <a href="" id="select_user">select</a>
            <div id="user_error" class="error internal-row hide"></div>
        </div>

        <div>
            <div class="hide" id="user_selector"></div>
        </div>

        <div class="row">
            <input type="button" class="standard-button" id="send_invite" value="Send" />
        </div>

        <div class="row hide" id="user_sent_message">
            <span class="success">An invitation has been sent to <span id="sent_to_username"></span>.</span>
        </div>

    </div>

</div>