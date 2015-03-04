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
 * Checks that the name and version numbers are valid.
 *
 * See VersionFilter to load a model once name is validated.
 * user_id must be set in the controller before this is called.
 *
 * @package PHP_Filter
 */
class VersionNoModelFilter Extends CFilter
{

    /**
     *  The name of the data type on the url (Usually connected to a table name).
     *
     * @var string
     */
    public $data_type;

    /**
     * Work to be done before an action is called.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     * @link http://www.yiiframework.com/doc/api/1.1/CFilter#preFilter-detail
     */
    protected function preFilter($filterChain) {
        $c = $filterChain->controller;
        if (isset($_GET[$this->data_type]) === false) {
            throw new CHttpException(400, 'Bad Request. ' . $this->data_type . ' is not set.');
        }
        $name = $_GET[$this->data_type];

        $major = isset($_GET['major']) === true ? $_GET['major'] : 'latest';
        $minor = isset($_GET['minor']) === true ? $_GET['minor'] : 'latest';
        $patch = isset($_GET['patch']) === true ? $_GET['patch'] : 'latest';

        if ($major !== 'latest' && $major !== 'all' && ctype_digit($major) === false) {
            throw new CHttpException(400, 'Bad Request. Major version is not an integer');
        }
        if ($minor !== 'latest' && $minor !== 'all'  && ctype_digit($minor) === false) {
            throw new CHttpException(400, 'Bad Request. Minor version is not an integer');
        }
        if ($patch !== 'latest' && $patch !== 'all'  && ctype_digit($patch) === false) {
            throw new CHttpException(400, 'Bad Request. Patch version is not an integer');
        }
        // If any version numbers are null, then this sets it too the highest available for that version part
        $c->model_id = StreamBedMulti::getIDByName($c->user_id, $name, $major, $minor, $patch);

        $filterChain->run();
    }
}

?>
