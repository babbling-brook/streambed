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
 * Returns help text for Messaging views.
 *
 * @package PHP_Help
 */
class MessagingHelp extends Help
{

    /**
     * Help for the to_user text box on the compose page.
     *
     * @return string Help tags.
     */
    public function toUser() {
        $title = 'To';
        $description = '
            <p>Enter the username of the user you want to send a message to.</p>
            <p>
                Short usernames can be used if the users data store is on this site.
                Otherwise enter the full username.
            </p>
            <p>
                For example: entering "joe" will send a message to joe@' . yii::app()->params['host'] . '
                and entering joe@example.com would use the messsage to joe@example.com.
                username@example.com
            </p>';
        return $this->dialogue->inline($title, $description);
    }
}

?>
