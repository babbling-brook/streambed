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
 *  Helper class containing functions to access other domus.
 *
 * @package PHP_Helper
 */
class OtherDomainsHelper
{

    /**
     * The domain of the Babbling Brook site that is being communicated with.
     *
     * @var string
     */
    private $domain;


    /**
     * Constructor.
     *
     * @param string $domain The domain that is being communicated with.
     */
    public function __construct($domain) {
        $this->domain = $domain;
    }

    public function checkBabblingBrookDomain() {
        $url = "http://" . $this->domain . '/site/storeexists';
        $result = $this->get($url, false);
        if (substr($result, 10, 4) === 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Authenticates the username. Checks the username exists.
     *
     * @param string $username The full username of the user to check.
     *
     * @return boolean True if user is found.
     */
    public function checkUserExists($username) {
        $url = "http://" . $this->domain . '/' . $username . '/valid';
        $result = $this->get($url);
        if (substr($result, 9, 4) === 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notifies another users domus that a transaction has been made that inovlved them as the 'other'.
     *
     * Assumes data has already been validated and inserted into local domus.
     *
     * @param string $username The short username of the user who has made the transaction.
     * @param string $type Tag that best describes this transaction.
     * @param boolean $role Is this transaction an post or a take.
     * @param string $with The full username of the person this transaction is with.
     * @param integer $my_value This user appraisal of a transactions worth.
     * @param integer $with_value The with users appraisal of this transactions worth.
     * @param string $origin The URL of the site that initiated this transaction.
     * @param string $guid UNique global ID for this transaction.
     *
     * @return boolean|string True is succesful, else the error message.
     */
    public function notifyOfTransaction($username, $type, $role, $with, $my_value, $with_value, $origin, $guid) {
        // my_value and with_value are reversed, as they are received from the perspective of the other user.
        $url = $with
            . "/notifytransaction"
            . "?w=" . urlencode(Yii::app()->params['site_root'] . $username)
            . "&t=" . urlencode($type)
            . "&v=" . $with_value
            . "&x=" . $my_value
            . "&o=" . urlencode($origin)
            . "&g=" . urlencode($guid);

        $result = $this->get($url);
        if (substr($result, 0, 7) === 'success') {
            return true;
        } else {
            return $result;
        }
    }

    /**
     * Get an Rhythm from another domus.
     *
     * @param integer $type The type of rhythm to fetch.
     *                      0 = only header, 1 = full, 2 = minified with no description in header.
     * @param string $site The domain to fetch the rhythm from.
     * @param string $user The username of the user who owns the Rhythm.
     * @param string $name The Rhythm name.
     * @param integer $major Major version number.
     * @param integer $minor Minor version number.
     * @param integer $patch Patch version number.
     * @param string $protocol What protocol are we using to fetch this rhythm.
     *
     * @return string JSON containing rhythmrithim or error string formated as "{'error':'Error Message'}".
     */
    public function getRhythm($type, $site, $user, $name, $major=null, $minor=null, $patch=null, $protocol="http://") {
        // This is turned off until curl is fixed.
        return false;

        switch ($type) {
            case 0:
                $s_type = "header";
                break;

            case 1:
                $s_type = "full";
                break;

            case 2:
                $s_type = "mini";
                break;
        }

        $url = $protocol . $site . "/" . $user . "/rhythm/" . $s_type . "/" . $name;
        if (isset($major) === true) {
            $url .= "/" . $major;
            if (isset($minor) === true) {
                $url .= "/" . $minor;
                if (isset($patch) === true) {
                    $url .= "/" . $patch;
                }
            }
        }

        return $this->get($url, false);
    }

    /**
     * Fetch an stream from another site.
     *
     * @param string $domain The domain of that owns the stream we are fetching.
     * @param string $username The user that owns the stream we are fetching.
     * @param string $name The stream name.
     * @param integer $major The major version number of the stream.
     * @param integer $minor The minor version number of the stream.
     * @param integer $patch The patch version number of the stream.
     * @param string $protocol The protocol we are using to fetch the stream.
     * @param boolean $insert Should the stream be inserted into the DB.
     *
     * @return string JSON fromated result.
     */
    public function getStream($domain, $username, $name, $major, $minor, $patch, $protocol='http://', $insert=true) {
        $url = $protocol . $domain . '/' . $username . '/stream/' . $name . '/' . $major .
            '/' . $minor . '/' . $patch . '/json';

        $stream_json = $this->get($url);
        $stream = json_decode($stream_json);

        if ($insert === true) {
            StreamBedMulti::insertFromJSONArray($stream);
        }

        return $stream;
    }

    /**
     * Checks if a site is valid.
     *
     * @param string $site The domain of the site to check.
     *
     * @return boolean
     */
    public static function getSiteValid($site) {
        $result = $this->get("http://" . $site . "site/validsaltsite");
        $ary =  CJavaScript::jsonDecode($result);
        if (array_key_exists("site", $ary) === true && $ary['site'] === true) {
            return true;
        }
        return false;
    }

    /**
     * Get something via curl.
     *
     * @param string $url The url to fetch.
     * @param boolean $error Throw an error or silent fail.
     *
     * @return string|boolean Result of curl request or false.
     */
    protected function get($url, $error=true) {
        // currently disabled.
        return false;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            if ($error === true) {
                throw new CHttpException($httpCode, 'Curl Error : ' . $response);
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Post something via curl.
     *
     * @param string $url The url to post to.
     * @param array $fields Fields to pass with the post.
     * @param boolean $error If true then throw an error if one occurs. If false then return false.
     *
     * @return string Result of curl request.
     */
    protected function post($url, $fields, $error=true) {
        // currently disabled.
        return false;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            if ($error === true) {
                throw new CHttpException($httpCode, 'Curl Error : ' . $response);
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Retrives a particular version of any type of version, eg any stream or any rhythm.
     *
     * @param string $url The url to get a version for.
     * @param string $type The version type. See lookup table for options.
     *
     * @return boolean|string true or error.
     */
    public function getVersion($url, $type) {
        // Check type is valid
        LookupHelper::getID("version.type", $type);

        $json = $this->get($url, false);

        if ($json === null) {
            return str_replace("_", " ", $type) . " not found.";
        }

        $version_type = str_replace("_", " ", $type);
        $version_type = ucwords($version_type);
        $version_type = str_replace(" ", "", $version_type);
        $version_type = $version_type . "Behavior";
        $behavior = new $version_type;
        if ($version_type === 'Stream') {
            $cache = StreamBedMulti::cache($json);
        } else if ($version_type === 'Rhythm') {
            $cache = RhythmMulti::cache($json);
        }
        if ($cache === false) {
            return str_replace("_", " ", $type) . " found on remote site but is incorrectly formated.";
        }

        return true;
    }

    /**
     * Verify that a user secret is valid with the users home domain.
     *
     * @param string $domain The home domain of the user whose secret that is being validated.
     * @param string $username The username of the owner of the secret being validated.
     * @param string $secret The secret that is being validated.
     */
    public function verifySecret($domain, $username, $secret) {
        $url = 'https://scientia.' . $domain .  '/' . $username . '/verifysecret';
        $field = array(
            'secret' => $secret,
        );
        $response = $this->post($url);

        $json_array = CJSON::decode($response);
        if (isset($json_array['valid']) === true && $json_array['valid'] === true) {
            return true;
        } else {
            return false;
        }
    }

}

?>
