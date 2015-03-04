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
class UsernameFilter Extends CFilter
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
        if (isset($_GET['user']) === false) {
            throw new CHttpException(
                404,
                'The requested page does not exist. This action requires an url that includes the user.'
            );
        }

        $username = $_GET['user'];
        if (strpos($username, '@') !== false) {
            $username = substr($username, 0, strpos($username, '@'));
        }

        $c = $filterChain->controller;
        $c->username = $username;
        $user_multi = new UserMulti;
        $c->user_id = $user_multi->getIDFromUsername($username);
        $filterChain->run();
    }
}

?>
