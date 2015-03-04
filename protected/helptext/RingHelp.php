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
 * Returns help text for Stream views.
 *
 * @package PHP_Help
 */
class RingHelp extends Help
{

    /**
     * Name help.
     *
     * @return string Help tags.
     */
    public function name() {
        $title = "Name";
        $description = "
            <p>The name for the ring.</p>
            <p>
                This is also the username
                for the user that represents this ring - all rings are also users.
            </p>
            <p>The name must be lowercase, as it is used as part of the url for its location.</p>
            <p>
                It also needs to be alpha numeric and start and end with a letter,
                single spaces are also allowed within the name.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Take Name help.
     *
     * @return string Help tags.
     */
    public function takeName() {
        $title = "Take Name";
        $description = "
            <p>When a user is viewing an post there is a drop down menu to select Ring actions.</p>
            <p>This is the name that appears on that list to represent this Ring.</p>
            <p>
                It must be lowercase, alpha numeric and start and end with a letter,
                spaces are also allowed.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Membership help.
     *
     * @return string Help tags.
     */
    public function membership() {
        $title = "Membership";
        $description = "
            <p>Select the method by which new members can join the ring.</p>
             <p><strong>Open to the public</strong> : Any user can join this ring.</p>
             <p>
                <strong>By an admins invitation</strong>
                : Any admin of this ring can send an invitaiton to another user.
             </p>
             <p>
                <strong>By a members invitation</strong>
                : Any member of this ring can send an invitaiton to another user.
             </p>
             <p>
                <strong>By request</strong> : Users can request to join the ring on the profile page.
                Admins can then admit new members. An optional rhythm can be assigned to process
                membership requests. It runs in the background on administrators accounts.
             </p>
             <p>
                <strong>By another ring</strong> : Another ring has the ability to invite members to join this one.
                The ring must be on the same domain as this one. This feature is primarily to enable the
                rhythm of another ring to decide membership of this one. It allows for membership to be
                be dependent on a heirarchy of rings.
             </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Membership Rhythm help.
     *
     * @return string Help tags.
     */
    public function membershipRhythm() {
        $title = "Membership Rhythm";
        $description = "
            <p>The location and name of the rhythm to use for the membership rhythm.</p>
            <p>It's main purpose is for vetting users who have applied for membership of this ring.</p>
            <p>It periodically runs in the background on the admin accounts of this ring.</p>
            <p>
                If this is a new ring you may want to leave this blank so that the rhythm can be created
                using the new rings account.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Membership Rhythm help.
     *
     * @return string Help tags.
     */
    public function ringRhythm() {
        $title = 'Ring Rhythm';
        $description = '
            <p>The location and name of the rhythm to use for the ring rhythm.</p>
            <p>
                This rhythm will run regularly on every members account.
                It can be used for any ring purpose you see fit,
                there are two built in options.
                To manage invitations and to ask members for a second opinion.
                See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">
                Babbling Brook documentation</a> for more details.
            </p>
            <p>
                If this is a new ring you may want to leave this blank so that the rhythm can be created
                using the new rings account.
            </p>';
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Membership Super Ring help.
     *
     * @return string Help tags.
     */
    public function membershipSuperRing() {
        $title = "Membership Super Ring";
        $description = "
            <p>
                The name of the ring that has administrative control
                over admiting new members to this ring.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Super Ring help.
     *
     * @return string Help tags.
     */
    public function adminType() {
        $title = "Administrator Type";
        $description = "
            <p>This allows you to change how this ring is administrated.
                Administrators have root access and are able to change all the settings on this page.
            </p>
            <p>
                By Invitation :
                This setting allows you to invite other members to administrate this ring with you.
                <br /><b>Important</b> - these users will also be able to change this setting,
                thus locking you out. They will also be able to send out their own invitations.
            </p>
            <p>
                Another Ring : Sets another ring as a <em>super ring</em>.
                Any members of that ring will be able to administrate this one.
                The <em>super ring</em> must be on the same domain as this one.
                <br /><strong>Important</strong> - if you are not a member of the <em>super ring</em> then you will
                no longer be an administrator of this ring.
            </p>";

        return $this->dialogue->inline($title, $description);
    }

    /**
     * Super Ring help.
     *
     * @return string Help tags.
     */
    public function adminSuperRing() {
        $title = "Admin Super Ring";
        $description = "
            <p>The name of the ring that has administrative controll of this ring.</p>
            <p>The <em>supper ring</em> will be able to change any setting in this ring.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Invite member help.
     *
     * @return string Help tags.
     */
    public function inviteMember() {
        $title = "Invite Member";
        $description = "
            <p>
                Enter the full username of the user you want
                to invite to become a member of this ring.</p>
            <p>Alternatively click the select link to search for a user.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Invite admin help.
     *
     * @return string Help tags.
     */
    public function inviteAdmin() {
        $title = "Invite Administrator";
        $description = "
            <p>
                Enter the full username of the user you want to
                invite to become an administrator of this ring.
            </p>
            <p>Alternatively click the select link to search for a user.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Ban users help.
     *
     * @return string Help tags.
     */
    public function banUsers() {
        $title = "Ban Users";
        $description = "
            <p>Select a user and then click 'Ban' to ban a user or 'Reinstate' to unban them.</p>
            <p>Columns can be sorted by clicking on them.</p>
            <p>Refine your search by typing into the textboxes at the top of the table.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Take stream help
     *
     * @return string Help tags.
     */
    public function takeStream() {
        $title = "Take stream";
        $description = "
            <p>
                Entering a stream url here will restrict the take to only this stream.
            </p>
            <p>
                If no stream is entered then the take can be used on all streams.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Take stream help
     *
     * @return string Help tags.
     */
    public function takeAmount() {
        $title = "Take Amount";
        $description = "
            <p>
                The value of the take.
            </p>
            <p>
                Often this just needs to be '1'. However you might have multiple take names for different actions.
            </p>
            <p>
                E.G.<br>
                'spam' might get a value of -10</br>
                'approved' might get a value of +10</br>
            </p>            ";
        return $this->dialogue->inline($title, $description);
    }

}

?>
