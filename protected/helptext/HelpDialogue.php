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
 * Creates a popup help dialogue
 *
 * @package PHP_Help
 */
class HelpDialogue
{

    /**
     * Create a help dialog using the text pased into the method.
     *
     * @param string $title The title of the dialog box.
     * @param string $content The descriptive text for the dialoge text box.
     *
     * @return string The html to be echoed to the page.
     */
    public function inline($title, $content) {
        return $this->generateTags($title, $content);
    }

    /**
     * Creates the tags required to make a help item.
     *
     * @param string $title The title of the dialog box.
     * @param string $content The descriptive text for the dialoge text box.
     *
     * @return string The html to be echoed to the page.
     */
    protected function generateTags($title, $content) {

        $ch = new CryptoHelper;
        $guid = $ch->makeUniqueSecret();

        $icon = CHtml::tag(
            "span",
            array(
                "class" => "help-icon",
                "id" => "help_" . $guid,
            )
        );

        $title_tag = CHtml::tag(
            "span",
            array(
                "class" => "help-title hide",
                "id" => "help_title_" . $guid,
            ),
            $title
        );

        $content_tag = CHtml::tag(
            "span",
            array(
                "class" => "help-content hide",
                "id" => "help_content_" . $guid,
            ),
            $content
        );

        return $icon . $title_tag . $content_tag . '</span>';
    }
}

?>
