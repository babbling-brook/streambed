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
 * Redirect to new version if requested
 *
 * @package PHP_Filter
 */
class VersionRedirectFilter Extends CFilter
{

    /**
     * The name of the data type on the url (Usually connected to a table name).
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
        if (isset($_POST['versions']) === true && isset($_POST['redirect']) === true) {
            $versions_array = explode("/", $_POST['versions']);
            $c->redirect(
                '/' . $c->username .
                '/' . $this->data_type . '/' .
                $_POST['redirect'] .
                "/" . $_GET[$this->data_type] .
                "/" . $versions_array[0] .
                "/" . $versions_array[1] .
                "/" . $versions_array[2] .
                $c->ajaxurl
            );
        }
        $filterChain->run();
    }
}

?>
