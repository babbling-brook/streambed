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
 * Model to validate that a request for a users private messages is valid
 *
 * @package PHP_Model_Forms
 */
class MessageRequestForm extends CFormModel
{

    /**
     * The sort type of this message request. See Lookup table for valid values.
     *
     * @var string
     */
    public $sort_type;

    /**
     * The page number of this message request. 0 is the ost recent, with the highest page being the oldest.
     *
     * @var integer
     */
    public $page;

    /**
     * The client domain that is requesting messages.
     *
     * @var string
     */
    public $client_domain;

    /**
     * The site_id of the client_domain.
     *
     * This is not passed in for validation.
     * It is worked out from the client_domain
     *
     * @var integer
     */
    public $client_site_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('sort_type, page, client_domain', 'required'),
            array('request', "ruleSortType"),
            array('request', "rulePage"),
            array('request', "ruleClientDomain"),
        );
    }

    /**
     * A rule to check that the sort_type is valid.
     *
     * @return void
     */
    public function ruleSortType() {
        if (LookupHelper::valid("messaging_sort_type", $this->sort_type) === false) {
            $this->addError('request', 'sort_type is not valid.');
            return;
        }
    }

    /**
     * A rule to check that the page number is an unsigned int
     *
     * @return void
     */
    public function rulePage() {
        if (ctype_digit($this->page) === false) {
            $this->addError('request', 'page is not a whole integer.');
            return;
        }
    }

    /**
     * A rule to check that the client domain is valid.
     *
     * @return void
     */
    public function ruleClientDomain() {
        if (strlen($this->client_domain) < 1) {
            $this->addError('request', 'client_domain is required.');
            return;
        }

        $this->client_site_id = SiteMulti::getSiteID($this->client_domain, true, true);
        if ($this->client_site_id === false) {
            $this->addError(
                'request',
                'client_domain does not implement the BabblingBrook protocol or is unavailable.'
            );
            return;
        }
    }
}

?>