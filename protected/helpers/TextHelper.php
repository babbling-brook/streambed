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
 * Miscellaneous string helper functions
 *
 * @package PHP_Helper
 */
class TextHelper
{

    /**
     * Truncates text via various options.
     *
     * @param string $text STRING The text to be truncated.
     * @param integer $limit Return if string is shorter than this.
     * @param string $break What character should we break on.
     * @param string $pad What should we pad the string with if it is truncated.
     *
     * @return string Truncated string.
     */
    public static function truncate($text, $limit, $break=" ", $pad="...") {

        // return with no change if string is shorter than $limit
        if (strlen($text) <= $limit) {
            return $text;
        }

        // is $break present between $limit and the end of the string?
        $text = substr($text, 0, $limit);
        if (false !== ($breakpoint = strrpos($text, $break))) {
            $text = substr($text, 0, $breakpoint);
        }
        return $text . $pad;
    }

    /**
     * Is this a negative or positive integer.
     *
     * @param string|integer $string The string to check.
     *
     * @return boolean
     */
    public static function isInt($value) {
        if (is_int($value) === true) {
            $value = (string)$value;
        }

        if (substr($value, 0, 1) === "-") {
            $value = substr($value, 1);
        }

        if (ctype_digit($value) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Implodes a nested array and returns a string of the results.
     *
     * No glue characters are used.
     *
     * @param $ary The array or nested array to implode.
     *
     * @return string The imploded array.
     */
    public static function nestedImplode($ary) {
        if (is_array($ary) === true) {
            foreach ($ary as $key => $element) {
                if (is_array($element) === true) {
                    $ary[$key] = self::nestedImplode($element);
                }
            }
            return implode($ary);
        }
        return $ary;
    }

}

?>
