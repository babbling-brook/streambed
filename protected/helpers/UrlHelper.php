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
class UrlHelper
{

    /**
     * Gets the correct version url if data is partial.
     *
     * @param string $username The username of the user who owns the versioned thing.
     * @param string $controller The thing that is versioned.
     * @param string $action The action in the version url.
     * @param string $name The name of the thing that is versioned.
     * @param integer $major The major version number.
     * @param integer $minor The minor version number.
     * @param integer $patch The patch version number.
     * @param integer $version_type Used to define partial versions, see lookup table for valid values.
     * @param string $site If set, then a full url is included.
     * @param boolean $version_span Include a span around the verison info.
     *
     * @return string The url
     */
    public static function getVersionUrl($username, $controller, $action, $name, $major=null, $minor=null, $patch=null,
        $version_type=null, $site=false, $version_span=false
    ) {
        $url = "/"
            . $username . "/"
            . $controller . "/"
            . urlencode($name);

        if ($version_span === true) {
            $url .= '<span class="url-version">';
        }

        if ($version_type === null) {
            $v_type = "major/minor/patch";
        } else {
            $v_type = LookupHelper::getValue((int)$version_type);
        }

        if ($v_type === "major/minor/patch") {
            $url .= "/" . $major . "/" . $minor . "/" . $patch;
        } else if ($v_type === "major/minor/latest") {
            $url .= "/" . $major . "/" . $minor . "/latest";
        } else if ($v_type === "major/latest/latest") {
            $url .= "/" . $major . "/latest/latest";
        } else if ($v_type === "latest/latest/latest") {
            $url .= "/latest/latest/latest";
        }

        if ($version_span === true) {
            $url .= '</span>';
        }

        if (isset($action) === true) {
            $url .= "/" . $action;
        }

        if ($site === true) {
            $url = "http://" . $site . $url;
        }
        return $url;

    }

    /**
     * Checks if an url exists.
     *
     * @param string $url The url to check.
     *
     * @return boolean
     */
    public static function exists($url) {
        $agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $page=curl_exec($ch);
        //echo curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (($httpcode >= 200 && $httpcode < 300) || $httpcode === 302) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a an url is a validly structured version url.
     *
     * @param string $url The url to check.
     * @param string $type The type of url, eg stream, rhythm.
     *                     (this should be a valid value in lookup table: version.type).
     *
     * @return integer|string The primary key of the type represented by the url or an error.
     *                        If version is partial, the latest valid version.
     */
    public static function checkVersionUrl($url, $type) {
        // Check type is valid
        LookupHelper::getID("version.type", $type);

        // break url into compontents
        // remove http if present
        if (strpos($url, "http://") !== false && strpos($url, "http://") === 0 ) {   // Present AND start of string
            $url = substr($url, 7);
        }
        $url_array = explode("/", $url);

        if (count($url_array) < 5 || count($url_array) > 8) {
            return "Url is invalid.";
        }
        if ($url_array[2] !== str_replace("_", "", $type)) {   // remove underscore to check the url form of the type
            return "This is not a valid " . str_replace("_", " ", $type) . " URL.";
        }
        if ($url_array[3] !== "view") {
            return "Must reference the 'view' action.";
        }

        // Check if versions numbers exist and are numeric integers
        $major = null;
        if (isset($url_array[5]) === true && $url_array[5] !== "") {
            if (ctype_digit($url_array[5]) === true || $url_array[5] === 'latest') {
                $major = $url_array[5];
            } else {
                return "Major version must be a whole number or empty";
            }
        }
        $minor = null;
        if (isset($url_array[6]) === true && $url_array[6] !== "") {
            if (ctype_digit($url_array[6]) === true || $url_array[6] === 'latest') {
                $minor = $url_array[6];
            } else {
                return "Minor version must be a whole number or empty";
            }
        }
        $patch = null;
        if (isset($url_array[7]) === true && $url_array[7] !== "") {
            if (ctype_digit($url_array[7]) === true || $url_array[7] === 'latest') {
                $patch = $url_array[7];
            } else {
                return "Patch version must be a whole number or empty";
            }
        }

        // If the url is relative then append the local host
        if ($url_array[0] === "") {
            $url_array[0] = Yii::app()->params['host'];
        }
        // if the url is not relative then check the site
        $site =  urldecode($url_array[0]);
        $site_id = SiteMulti::getSiteID($site, false);
        if ($site_id === false) {
            // Site not in db
            // Check if site is a valid Babbling Brook site
            if (OtherDomainsHelper::getSiteValid($site) === false) {
                return "The domain name '" . $site . "' is not a valid Babbling Brook domus";
            }
            $site_id = SiteMulti::getSiteID($site);    // This will insert the site and returns the id
        }

        // Check the user is valid
        $username =  urldecode($url_array[1]);
        $user_multi = new UserMulti($site_id);
        $osh = new OtherDomainsHelper($site);
        if ($user_multi->userExists($username) === false) {
            // User does not exist locally, if this is not a local user, check the remote site
            if ($site_id !== Yii::app()->params['site_id']) {
                if ($osh->checkUserExists($username) === false) {
                    return "The user '" . $username . "' is not a valid user";
                }
            } else {
                return "The user '" . $username . "' is not a valid user on site " . $site;
            }
        }
        $user_id = $user_multi->getIDFromUsername($username);

        // Check the version item exists and is public
        $name = urldecode($url_array[4]);

        $version_type = str_replace("_", " ", $type);
        $version_type = ucwords($version_type);
        $version_type = str_replace(" ", "", $version_type);

        if ($id === false && $site_id !== Yii::app()->params['site_id']) {
            // Try and get it from the remote site (caches it before returning here)
            $osh->getVersion($url, $type);
            if ($version_type === 'Stream') {
                $id = StreamBedMulti::getIDByName($user_id, $name, $major, $minor, $patch);
            } else if ($version_type === 'Rhythm') {
                $id = Rhythm::getIDByName($user_id, $name, $major, $minor, $patch);
            }

        }
        if ($id === false) {
            return "This " . str_replace("_", " ", $type) . " does not exist";
        }
        return $id;
    }

    /**
     * Remove the protocol from an url.
     *
     * @param string $url The url to remove the protocol from.
     *
     * @return string|boolean The url without a protocol. Or false.
     */
    public static function removeProtocol($url) {
        $url_parts = parse_url($url);

        if ($url_parts === false) {
            return false;
        }

        $url_minus_protocol = "";

        if (isset($url_parts['username']) === true) {
            $url_minus_protocol .= $url_parts['username'];

            if (isset($url_parts['password']) === true) {
                $url_minus_protocol .= ":" . $url_parts['password'];
            }

            $url_minus_protocol .= "@";
        }

        if (isset($url_parts['host']) === true) {
            $url_minus_protocol .= $url_parts['host'];
        }

        if (isset($url_parts['port']) === true) {
            $url_minus_protocol .= ":" . $url_parts['port'];
        }

        if (isset($url_parts['path']) === true) {
            $url_minus_protocol .= $url_parts['path'];
        }

        if (isset($url_parts['query']) === true) {
            $url_minus_protocol .= "?" . $url_parts['query'];
        }

        if (isset($url_parts['fragment']) === true) {
            $url_minus_protocol .= "#" . $url_parts['fragment'];
        }

        return $url_minus_protocol;
    }

    /**
     * Replace plus signs with spaces.
     *
     * @param type $url_part
     */
    public static function replacePluses($url_part) {
        $url_part = str_replace('+', ' ', $url_part);
        return $url_part;
    }
}

?>
