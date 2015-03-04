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
 * Checks if a user has access to a this action if its status is private.
 *
 * @package PHP_Filter
 */
class PrivateStatusFilter Extends CFilter
{

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
        if ($c->username !== Yii::app()->user->getName()) {
            if ($c->model->status_id === StatusHelper::getID("private")) {
                throw new CHttpException(403, 'The requested page is forbidden.');
            }
        }
        $filterChain->run();
    }
}

?>
