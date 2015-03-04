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
 * A helper file that hard codes the standard html options for text fields in streams.
 * Saves on many db lookups.
 *
 * @package PHP_Helper
 */
class StreamTextFieldTypeHelper
{

    private static function getJustText() {
        return array(
            'elements' => array(),
            'styles' => array(),
        );
    }

    private static function getTextWithLinks() {
        return array(
            'elements' => array(
                'a' => array(
                    array(
                        'attribute' => 'href',
                        'required' => true,
                    ),
                ),
                'p' => array(),
            ),
            'styles' => array(),
        );
    }

    private static function getSimpleHtml() {
        return array(
            'elements' => array(
                'a' => array(
                    array(
                        'attribute' => 'href',
                        'required' => true,
                    ),
                ),
                'strong' => array(),
                'em' => array(),
                's' => array(),
                'blockquote' => array(),
                'ol' => array(),
                'ul' => array(),
                'li' => array(),
                'p' => array(),
                'br' => array(),
            ),
            'styles' => array(),
        );
    }

    /**
     * Fetches the elements and style for preset stream text fields.
     *
     * @param string $type The type of elements to return.
     *
     * @return array An array of elements with attributs and styles as defined by the Babbling Brook protocol.
     */
    public static function getValidHTML($type) {
        switch ($type) {
            case 'just_text':
                $elements = self::getJustText();
                break;
            case 'text_with_links':
                $elements = self::getTextWithLinks();
                break;
            case 'simple_html':
                $elements = self::getSimpleHtml();
                break;
        }
        return $elements;
    }
}