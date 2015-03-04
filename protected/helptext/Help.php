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
 * Base class for all help files.
 * see {@link HelpDialogue} for the dialogue class
 *
 * @package PHP_Help
 */
class Help
{

    /**
     * Holds the help popup object.
     *
     * @var HelpDialogue
     */
    protected $dialogue;

    /**
     * Creates a new Help object for pop up help icons.
     *
     * Creates a new HelpDialogue if one is not passed in.
     *
     * @param HelpDialogue $dialogue If there is an existing $dialogue then pass it in
     *                               or there will be css id conflicts.
     */
    public function __construct() {
        if (isset($dialogue) === true) {
            $this->dialogue = $dialogue;
        } else {
            $this->dialogue = new HelpDialogue();
        }
    }

    /**
     * Fetch the dialogue object so that it can be reused without causing conflicts.
     *
     * @return HelpDialogue Passed back out for continued use.
     */
    public function getDialogue() {
        return $this->dialogue;
    }



}

?>
