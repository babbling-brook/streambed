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
 * UserClientDataController Access to data about the client websites that a user subscribes to.
 * These are scientia domain requests for public data about a users use of a client website and NOT
 * direct client site actions
 *
 * @package PHP_Controllers
 */
class UserClientDataController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules.
     */
    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array(),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(
                    'DeclineSuggestion',
                ),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(),
                'users' => array('admin'),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Called when the user declines a suggestion that has been made to the user.
     *
     * @param string $g_username The username of the user to decline a  suggestion for.
     * @param string $g_type The type of suggestion that is being declined.
     * @param array [$p_stream] If the type is for a stream then this is the stream that has been declined.
     * @param string [stream.name] The name of the declined stream.
     * @param string [stream.username] The username of the declined stream.
     * @param string [stream.domain] The domain of the declined stream.
     * @param array [stream.version] The version of the declined stream.
     * @param string [stream.version.major] The major version of the declined stream.
     * @param string [stream.version.major] The minor version of the declined stream.
     * @param string [stream.version.major] The patch version of the declined stream.
     * @param array $p_rhythm If the type is for a rhythm then this is the rhythm that has been declined.
     * @param string [rhythm.name] The name of the declined rhythm.
     * @param string [rhythm.username] The username of the declined rhythm.
     * @param string [rhythm.domain] The domain of the declined rhythm.
     * @param array [rhythm.version] The version of the declined rhythm.
     * @param string [rhythm.version.major] The major version of the declined rhythm.
     * @param string [rhythm.version.major] The minor version of the declined rhythm.
     * @param string [rhythm.version.major] The patch version of the declined rhythm.
     * @param array $p_user If the type is for a user then this is the user that has been declined.
     * @param string [user.username] The username of the declined user.
     * @param string [user.domain] The domain of the declined user.
     *
     * @return void
     */
    public function actionDeclineSuggestion($g_username, $p_type, $p_client_domain, array $p_stream=null,
        array $p_rhythm=null, array $p_user=null
    ) {
        $result = SuggestionsDeclined::saveByName(
            Yii::app()->user->getId(),
            $p_client_domain,
            $p_type,
            $p_stream,
            $p_rhythm,
            $p_user
        );

        if ($result === true) {
            $json = array('success' => true);
        } else {
            $json = array('errors' => $result);
        }

        echo JSON::encode($json);
    }

}

?>