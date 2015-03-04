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
 * Returns help text for the profile page
 *
 * @package PHP_Help
 */
class ProfileHelp extends Help
{

    /**
     * Real Name help.
     *
     * @return string Help tags.
     */
    public function kindredScore() {
        $title = "Kindred Score";
        $description = "<p>The higher the kindred score, the better your kindred think of this user.</p>"
            . "<p>Your kindred are the users that are closest to you in the Babbling Brook network. Those whose
                posts or comments you have liked the most.</p>";
        return $this->dialogue->inline($title, $description);
    }

}

?>
