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
 * Extends CJSON to add a token to all JSON encoding to prevent JSON hijacking.
 * see http://stackoverflow.com/questions/2669690/why-does-google-prepend-while1-to-their-json-responses?rq=1
 *
 * @package PHP_ExtendedYii
 */
class JSON Extends CJSON
{

    /**
     * @var String Cross site forgery request (crsf) token added to json to prevent hijacking.
     */
    private static $token = '&&&BABBLINGBROOK&&&';

    /**
     * Adds the crsf token to the json that is ecoded by the parent class.
     *
     * @param Array $var The array to encode to json.
     *
     * @return string
     */
    public static function encode($var) {
        $json = parent::encode($var);
        $json_with_token = self::$token . $json;
        return $json_with_token;
    }

}
