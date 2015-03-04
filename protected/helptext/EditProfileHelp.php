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
 * Returns help text for the edit profile page
 *
 * @package PHP_Help
 */
class EditProfileHelp extends Help
{

    /**
     * Real Name help.
     *
     * @return string Help tags.
     */
    public function realName() {
        $title = "Name";
        $description = "<p>You can optionaly include your realname in your profile.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * About help.
     *
     * @return string Help tags.
     */
    public function about() {
        $title = "About";
        $description =    "<p>Let the world know anything you want it to know about you.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * About help.
     *
     * @return string Help tags.
     */
    public function profileImage() {
        $title = "Profile Image";
        $description = "
            <p>
                The selected image will be cropped to fit
                and saved at a resolution of 500 by 500 pixels.
            </p>
            <p>
                Click the buttton to upload an image,
                or alternatively images can be drag and dropped onto the page.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

}

?>
