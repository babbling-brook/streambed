<?php
/**
 *
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
 * Collection of actions dealing with a displaying posts specific to a user.
 *
 * @package PHP_Controllers
 */
class MailController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl',
            array(
                'application.filters.UrlOwnerFilter'
            ),
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
                'users' => array(''),
            ),
            array(
                'allow',
                'actions' => array(
                    'Index',
                    'LocalPost',
                    'GlobalPost',
                    'LocalSent',
                    'GlobalSent',
                    'Compose',
                ),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Displays a users messaging inbox for the client site they are currently on.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Client/Page/Mail/LocalInbox');
    }

    /**
     * Displays a users messaging inbox for the client site they are currently on.
     *
     * @return void
     */
    public function actionLocalPost() {
        $this->render('/Client/Page/Mail/LocalInbox');
    }

    /**
     * Displays a users global messaging inbox for all messages sent to this user.
     *
     * @return void
     */
    public function actionGlobalPost() {
        $this->render('/Client/Page/Mail/GlobalInbox');
    }


    /**
     * Displays a users messaging inbox.
     *
     * @return void
     */
    public function actionLocalSent() {
        $this->render('/Client/Page/Mail/LocalSent');
    }

    /**
     * Displays a users messaging inbox.
     *
     * @return void
     */
    public function actionGlobalSent() {
        $this->render('/Client/Page/Mail/GlobalSent');
    }

    /**
     * Displays a users messaging inbox.
     *
     * @return void
     */
    public function actionCompose() {
        $this->render('/Client/Page/Mail/Compose');
    }
}

?>