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
 * Model to validate a getSelection request (see UserDataController).
 *
 * @package PHP_Model_Forms
 */
class FeatureUsage extends CFormModel
{
    /**
     * The date a feature was used in the format yyyy-mm-dd.
     *
     * @var string
     */
    public $date;

    /**
     * An associative array of features and thier usage.
     *
     * Array key is the feature_type, it value is a nested array of keys that are urls that represent that feature.
     * The value of each url is the number of times the feature has been used.
     *
     * @var array
     */
    public $feature_usage;

    /**
     * Array of Generated ids from $feature_usage.
     *
     * Each row contains an array with three elements :
     *  feature : The feature type. See the lookup table > user_feature_useage.feature
     *  extra_id : The extra id of the feature being used.
     *  qty : The number of times the feature has been used.
     *
     * @var array(array)
     */
    private $features = array();



    /**
     * An array of allowed feature names.
     *
     * N.B to add a new feature, ruleFeatureUsage needs adapting.
     *
     * @var array
     */
    private $valid_features = array("stream", "filter", "kindred");

    //

    /**
     * Used to store feature_usage line errors, which need reporting, but not invalidating the whole submission.
     *
     * @var array
     */
    private $line_errors = array();

    /**
     * Getter for $line_errors.
     *
     * @return array
     */
    public function getLineErrors() {
        return $this->line_errors;
    }

    /**
     * Getter for $features.
     *
     * @return array
     */
    public function getFeatures() {
        return $this->features;
    }

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('date, feature_usage', 'required'),
            array('date', "ruleDate"),
            array('feature_usage', 'ruleFeatureUsage'),
        );
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleDate() {
        $date_parts = explode("-", $this->date);
        if (checkdate($date_parts[1], $date_parts[2], $date_parts[0]) === false) {
            $this->addError('date', 'date is invalid must be in format yyyy-mm-dd : ' . $this->date);
        }
    }

    /**
     * A rule to check that the type is valid.
     *
     * @return void
     */
    public function ruleFeatureUsage() {
        if (is_array($this->feature_usage) === false) {
            $this->addError('feature_usage', 'feature_usage is not an array');
            return;
        }

        $keys = array_keys($this->feature_usage);
        foreach ($keys as $key) {

            // If the array is posted back empty, it will be a string, so we need to catch it
            if (is_array($this->feature_usage[$key]) === false) {
                continue;
            }

            if (in_array($key, $this->valid_features) === false) {
                $line_errors[] = 'feature name not recognised : ' . $key;
                continue;
            }

            $url_keys = array_keys($this->feature_usage[$key]);

            $line_error = array();
            foreach ($url_keys as $url) {

                if (empty($line_error) === false) {
                    $this->line_errors[] = $line_error;
                    $line_error = array();
                    unset ($this->feature_usage[$key][$last_url]);
                }
                $last_url = $url;

                if (is_string($url) === false) {
                    $line_error[] = 'Invalid url : ' . $url;
                    continue;
                }

                $url_parts = explode("/", $url);

                if (isset($url_parts[0]) === false) {
                    $line_error[] = 'Name is not valid; missing domain : ' . $url;
                    continue;
                }
                if (isset($url_parts[1]) === false) {
                    $line_error[] = 'Name is not valid; missing username : ' . $url;
                    continue;
                }
                if (isset($url_parts[4]) === false) {
                    $line_error[] = 'Name is not valid; missing feature name : ' . $url;
                    continue;
                }
                // Version numbers
                // @fixme shouldn't this be highest available?
                if (isset($url_parts[5]) === false) {
                    $url_parts[5] = 0;
                }
                if (isset($url_parts[6]) === false) {
                    $url_parts[6] = 0;
                }
                if (isset($url_parts[7]) === false) {
                    $url_parts[7] = 0;
                }

                $site_id = SiteMulti::getSiteID($url_parts[0], false);
                if (isset($site_id) === false) {
                    $line_error[] = 'Domain is not valid : ' . $url_parts[0] . ' in ' . $url;
                    continue;
                }

                $user_multi = new UserMulti($site_id);
                $user_id = $user_multi->getIDFromUsername($url_parts[1], false);
                if (isset($user_id) === false|| $user_id === false) {
                    $line_error[] = 'User is not valid : ' . $url_parts[1] . ' in ' . $url;
                    continue;
                }

                $extra_id = 0;
                switch($key) {
                    case "stream":
                        $extra_id = Stream::getIDByName(
                            $user_id,
                            $url_parts[4],
                            $url_parts[5],
                            $url_parts[6],
                            $url_parts[7]
                        );
                        break;

                    case "filter":
                        $extra_id = Rhythm::getIDByName(
                            $user_id,
                            $url_parts[4],
                            $url_parts[5],
                            $url_parts[6],
                            $url_parts[7]
                        );
                        break;

                    case "kindred":
                        $extra_id = Rhythm::getIDByName(
                            $user_id,
                            $url_parts[4],
                            $url_parts[5],
                            $url_parts[6],
                            $url_parts[7]
                        );
                        break;
                }
                if (isset($extra_id) === false || $extra_id === false) {
                    $line_error[] = 'feature does not exist : ' . $url;
                    continue;
                }

                if (ctype_digit($this->feature_usage[$key][$url]) === false) {
                    $line_error[] = 'Value is not a positive integer : ' . $this->feature_usage[$key][$url];
                    continue;
                }

                // Save the various ids so they do not have to be looked up again
                if (empty($line_error) === true) {
                    $this->features[] = array(
                        "qty" => $this->feature_usage[$key][$url],
                        "feature" => LookupHelper::getID("user_feature_useage.feature", $key),
                        "extra_id" => $extra_id,
                    );
                }
            }
            // Catch any errors in the last row.
            if (empty($line_error) === false) {
                unset ($this->feature_usage[$key][$url]);
                $this->line_errors[] = $line_error;
            }
        }
    }

}

?>