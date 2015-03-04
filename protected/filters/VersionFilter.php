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
 * Checks that the name and version numbers are valid and then loads the controller model using these details.
 *
 * @package PHP_Filter
 */
class VersionFilter Extends CFilter
{

    /**
     * The name of the data type on the url (Usually connected to a table name).
     *
     * @var string
     */
    public $data_type;

    /**
     * The type of version. ('stream' or 'rhythm').
     *
     * @var Model
     */
    public $version_type;

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

        $major = isset($_GET['major']) === true ? $_GET['major'] : null;
        $minor = isset($_GET['minor']) === true ? $_GET['minor'] : null;
        $patch = isset($_GET['patch']) === true ? $_GET['patch'] : null;

        if ($major === "latest" || $major === "all") {
            $major = null;
        }
        if ($minor === "latest" || $minor === "all") {
            $minor = null;
        }
        if ($patch === "latest" || $patch === "all") {
            $patch = null;
        }

        if ($major !== null && ctype_digit($major) === false) {
            throw new CHttpException(400, 'Bad Request. Major version is not an integer');
        }
        if ($minor !== null && ctype_digit($minor) === false) {
            throw new CHttpException(400, 'Bad Request. Minor version is not an integer');
        }
        if ($patch !== null && ctype_digit($patch) === false) {
            throw new CHttpException(400, 'Bad Request. Patch version is not an integer');
        }

        if ($this->version_type === 'rhythm') {
            $c->model = Rhythm::getByName($c->user_id, $_GET[$this->data_type], $major, $minor, $patch);
        } else if ($this->version_type === 'stream') {
            $c->model = StreamBedMulti::getByName($c->user_id, $_GET[$this->data_type], $major, $minor, $patch);
        } else {
            throw new CHttpException(404, 'The requested version type is invalid.');
        }

        if (isset($c->model) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $c->version_string = $c->model->extra->version->major . "/"
            . $c->model->extra->version->minor . "/"
            . $c->model->extra->version->patch;
        $c->version_link = "/" . $_GET[$this->data_type] . "/"
            . $c->model->extra->version->major . "/"
            . $c->model->extra->version->minor . "/"
            . $c->model->extra->version->patch;
        $filterChain->run();
    }
}

?>
