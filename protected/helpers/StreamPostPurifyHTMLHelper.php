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
 * Helps convert a streams text field allowed html into the purify format.
 *
 * @package PHP_Helper
 */
class StreamPostPurifyHTMLHelper
{
    public static function createPurifyElements($stream_field_html) {
        $purify_html = '';
        foreach ($stream_field_html['elements'] as $element => $attributes) {
            $valid_atttributes = '[';
            $attribute_count = 0;
            foreach ($attributes as $attribute => $content) {
                $valid_atttributes .= $content['attribute'];
                $attribute_count++;
                if (count($attributes) < $attribute_count) {
                    $valid_atttributes .= '|';
                }
            }
            $valid_atttributes .= ']';
            if ($valid_atttributes === '[]') {
                $valid_atttributes = '';
            }
            $purify_html .= $element . $valid_atttributes . ',';
        }
        return $purify_html;
    }
}

?>
