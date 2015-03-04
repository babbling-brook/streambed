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
 * **************************************************************
 * IMPORTANT THIS FILE IS DEPRECATED. DO NOT ADD ANYTHING TO IT
 * *************************************************************
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
                'actions' => array(
                    'GetStreamSubscriptions',
                    'GetStreamFilterSubscriptions',
                    'GetDeclinedSuggestions',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(),
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
     * Returns a users stream subscriptions for a client website.
     *
     * @fixme remove this (will need to update rhythms that use it.)
     *
     * This must be accessed through the domus domain of the user whose subscriptions are being fetched.
     *
     * @param string $g_client_domain The domain of the client that the subscriptions are for.
     *
     * @return void
     */
    public function actionGetStreamSubscriptions($g_username, $g_client_domain) {
        $controller = new StreamSubscriptionController('subscription');
        $controller->actionGetSubscriptions($g_client_domain);
    }


    /**
     *
     * @fixme remove this (will need to update rhythms that use it.)
     *
     * Returns a users stream subscriptions with the filters for a client website.
     *
     * This must be accessed through the domus domain of the user whose subscriptions are being fetched.
     *
     * @param string $g_client_domain The domain of the client that the subscriptions are for.
     *
     * @return void
     */
    public function actionGetStreamFilterSubscriptions($g_username, $g_client_domain) {
        $controller = new StreamSubscriptionController('subscription');
        $controller->actionGetSubscriptions($g_client_domain);
    }

    /**
     * Returns a a list of declined suggestions by a user.
     *
     * @fixme move this somewhere else.
     *
     * This must be accessed through the domus domain of the user whose declined subscrptions are being fetched.
     *
     * @param string $g_username The username of the user to fetch declined suggestions for.
     * @param string $g_type The type of suggestion to fetch declines for.
     * @param string $g_client_domain The domain of the client that the suggestions were generated for.
     *
     * @return void
     */
    public function actionGetDeclinedSuggestions($g_username, $g_type, $g_client_domain) {
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($g_username, false);
        $site_id = Site::getSiteId($g_client_domain);
        $rhythm_cat_id = RhythmCat::getRhythmCatID($g_type);

        if ($user_id === false) {
            $error = 'User does not exist.';
        } else if ($user_id === false) {
            $error = 'client_domain does not exist.';
        } else if ($rhythm_cat_id === false) {
            $error = 'rhythm type is not valid.';
        }
        if (isset($error) === true) {
            echo JSON::encode(
                array(
                    "error" => $error,
                )
            );

            return;
        }

        $suggestions_declined = SuggestionsDeclined::getForUserAndSite($user_id, $site_id, $rhythm_cat_id);

        echo JSON::encode(
            array(
                "suggestions_declined" => $suggestions_declined,
            )
        );
    }
}

?>