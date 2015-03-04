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
 * Static helper functions for URLS.
 *
 * @package PHP_Helper
 */
class ThemeHelper
{

    /**
     * Gets the correct relative themed version of an url if it exists. OTherwise passes back the $base_url.
     *
     * @param string A relative resource url to check.
     *
     * @return string The resource url to use.
     */
    public static function getUrl($base_url) {
        if (CLIENT_TYPE !== 'default') {
            if (is_string('/themes/' . CLIENT_TYPE . $base_url) === true) {
                return '/themes/' . CLIENT_TYPE . $base_url;
            }
        }
        return $base_url;
    }
}

?>
