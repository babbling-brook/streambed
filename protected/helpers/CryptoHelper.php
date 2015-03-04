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
 *  Helper class containing crypto helper functions.
 *
 * @package PHP_Helper
 */
class CryptoHelper
{

    /**
     * Makes a unique string for use as a secret.
     *
     * @return string unique string. 32 chars long.
     */
    public function makeUniqueSecret() {
        return uniqid() . mt_rand(0, 999999999);
    }

    /**
     * Create a guid
     *
     * Taken from http://php.net/manual/en/function.com-create-guid.php
     *
     * @return string
     */
    public static function makeGuid() {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        } else {
            return sprintf(
                '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(16384, 20479),
                mt_rand(32768, 49151),
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(0, 65535)
            );
        }

    }

    /**
     * Checks that a GUID is formatted correctly.
     *
     * There should be no open/close brackets.
     *
     * @param string $guid The guid to check.
     *
     * @return boolean
     */
    public function checkGuid($guid) {
        //if (preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}) {4}[a-f\d]{8}(?(1)\})$/i", $guid))
        if (preg_match("/^[a-f\d]{8}(-[a-f\d]{4}) {4}[a-f\d]{8}$/i", $guid) === true) {
            return true;
        } else {
            return false;
        }
    }

}

?>