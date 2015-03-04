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
 * Does the logged on user own this model.
 *
 * @package PHP_Filter
 */
class OwnerFilter Extends CFilter
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
        if (Yii::app()->user->getId() !== intval($c->model->user_id)) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }
        $filterChain->run();
    }
}

?>
