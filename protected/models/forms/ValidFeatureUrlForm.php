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
 * Form Model to validate that an url is a valid feature url. Eg an stream or Rhythm url.
 *
 * @package PHP_Model_Forms
 */
class ValidFeatureUrlForm extends CFormModel
{
    /**
     * The url that represents this user.
     *
     * @var string
     */
    public $url;

    /**
     * A flag to indicate if vlaidation should check for a domain.
     *
     * @var boolean
     */
    public $include_domain = false;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('url', 'required'),
            array('url', 'ruleUrlValid'),            // Generated username and domain
            array('url', 'ruleVersionValid'),        // Generates site_id
        );
    }

    /**
     * Checks if the url is a valid feature url.
     *
     * @return void
     */
    public function ruleUrlValid() {
        $url = $this->url;
        $expected_parts = 7;
        $domain_error = '';

        if ($this->include_domain === true) {
            // Remove http:// if present at the start of the url
            if (strpos($url, 'http://') !== false && strpos($url, 'http://') === 0) {
                $url = substr($url, 7);
            }
            $expected_parts = 8;
            $domain_error = 'domain';
        }

        // If the end or start of the url is a slash then remove it.
        if (substr($url, -1) === '/') {
            $url = substr($url, 0, -1);
        }
        if (substr($url, 0, 1) === '/') {
            $url = substr($url, 1);
        }

        $url_parts = explode('/', $url);
        if (count($url_parts) !== $expected_parts) {
            $url_format = $domain_error . '/username/feature/action/name/major/minor/patch';
            $this->addError('url', 'The url is not in the correct format. It should be in the form ' . $url_format);
        }
    }

    /**
     * Checks if the verion number looks valid.
     *
     * Version numbers must be positive integers or 'latest'.
     *
     * @return void
     */
    public function ruleVersionValid() {

        $version = Version::splitFromEndOfUrl($this->url);
        $valid = Version::isLookValid($version);
        if ($valid === false) {
            $version_format = 'major/minor/patch where version numbers are whole numbers or "latest".';
            $this->addError('url', 'The url version is not valid. It should be in the form ' . $version_format);
        }
    }

}

?>
