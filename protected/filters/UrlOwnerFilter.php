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
 * Test that $_GET['user'] is present.
 *
 * Sets username and id in the controller properties (these must be present)
 *
 * @package PHP_Filter
 */
class UrlOwnerFilter Extends CFilter
{

    /**
     * Checks that the username in the url is the logged in user.
     *
     * @param $filter_chain See http://www.yiiframework.com/doc/api/1.1/CFilter#preFilter-detail
     *
     * @return boolean
     * @link http://www.yiiframework.com/doc/api/1.1/CFilter#preFilter-detail
     */
    protected function preFilter($filter_chain) {
        if (isset($_GET['user']) === false || $_GET['user'] !== Yii::app()->user->getName()) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }
        return true;
    }
}

?>
