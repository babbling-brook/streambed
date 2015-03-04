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
 * Stream Subscription controller.
 *
 * Manages all aspects of subscribing and unsubscribing streams (and their filters and rings)
 * for a user on a client website.
 *
 * @package PHP_Controllers
 */
class StreamSubscriptionController extends Controller
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
                'allow',    // All users.
                'actions' => array(''),
                'users' => array('*'),
            ),
            array(
                'allow', // Authenticated users
                'actions' => array(
                    'GetSubscriptions',
                    'SubscribeStream',
                    'UnsubscribeStream',
                    'SubscribeStreamFilter',
                    'UnsubscribeStreamFilter',
                    'SubscribeStreamRing',
                    'UnsubscribeStreamRing',
                    'ChangeStreamVersion',
                    'ChangeFilterVersion',
                ),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(),
                'users' => array(),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Fetch the subscriptions for this
     *
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     */
    public function actionGetSubscriptions($p_client_domain) {
        $json = array();
        $site_id = SiteMulti::getSiteID($p_client_domain, false, true);
        if ($site_id === false) {
            $json['error'] = 'Client domain is not registered as a client website.';
        } else {
            $result = StreamSubscriptionForm::getStreamSubscriptions(
                Yii::app()->user->getId(),
                Yii::app()->params['site_id']
            );
            if (is_string($result) === true) {
                $json['error'] = $result;
            } else {
                $json['subscriptions'] = $result;

            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Subcribes a user to a stream for a client website.
     *
     * @param Array $p_stream A standard stream name object representing the stream that is being subscribed.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionSubscribeStream(array $p_stream, $p_client_domain) {

        $json = array();
        $stream_subscription_form = new StreamSubscriptionForm('subscribe');
        $stream_subscription_form->stream_name = $p_stream;
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->subscribeStream();
            if (is_string($result) === true) {
                $json['error'] = $result;
            } else {
                $json['subscription'] = $result;

            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Unsubcribes a user from a stream for a client website.
     *
     * @param String $p_subscription_id The id of the subscription to unsubscribe.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionUnsubscribeStream($p_subscription_id, $p_client_domain) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('unsubscribe');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->unsubscribeStream($p_subscription_id);
            if (is_string($result) === true) {
                $json['error'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Subcribes a user to a stream for a client website.
     *
     * @param String $p_stream_subscription_id
     *      The id of the stream subscription that a new filter subscription is being added to.
     * @param Array $p_rhythm The rhythm name object that represents the filter being subscribed.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionSubscribeStreamFilter($p_stream_subscription_id, array $p_rhythm, $p_client_domain) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_subscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->subscribeFilter($p_stream_subscription_id, $p_rhythm);
            if (is_string($result) === true) {
                $json['error'] = $result;
            } else {
                $json['subscription'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Subcribes a user to a stream for a client website.
     *
     * @param String $p_stream_subscription_id The id of the stream subscription that a filter
     *      is being unsubscribed from.
     * @param String $p_filter_subscription_id The id of the filter subscription that is being unsubscribed.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionUnsubscribeStreamFilter($p_stream_subscription_id, $p_filter_subscription_id,
        $p_client_domain
    ) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_unsubscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->unsubscribeFilter(
                $p_stream_subscription_id,
                $p_filter_subscription_id
            );
            if (is_string($result) === true) {
                $json['error'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Subcribes a user to a stream for a client website.
     *
     * @param String $p_stream_subscription_id The id of the stream subscription that a ring is being subscribed to.
     * @param Array $p_ring A ring name object for the ring that is being subscribed.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionSubscribeStreamRing($p_stream_subscription_id, array $p_ring, $p_client_domain) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_unsubscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->subscribeRing($p_stream_subscription_id, $p_ring);
            if (is_string($result) === true) {
                $json['error'] = $result;
            } else {
                $json['subscription'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Subcribes a user to a stream for a client website.
     *
     * @param String $p_stream_subscription_id The id of the stream subscription that a ring is being unsubscribed from.
     * @param String $p_ring_subscription_id The id of the ring subscription that is being unsubscribed.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionUnsubscribeStreamRing($p_stream_subscription_id, $p_ring_subscription_id, $p_client_domain) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_unsubscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->unsubscribeRing($p_stream_subscription_id, $p_ring_subscription_id);
            if (is_string($result) === true) {
                $json['error'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Changes the version of a subscribed stream.
     *
     * @param String $p_stream_subscription_id The id of the stream subscription that is having its version changed.
     * @param String $p_new_version A version object representing the new version.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionChangeStreamVersion($p_stream_subscription_id, array $p_new_version, $p_client_domain) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_unsubscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->changeStreamVersion($p_stream_subscription_id, $p_new_version);
            if (is_string($result) === true) {
                $json['error'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }

    /**
     * Changes the version of a subscribed stream.
     *
     * @param String $p_stream_subscription_id The id of the stream subscription that owns
     *      the filter that is having its version changed.
     * @param String $p_filter_subscription_id The id of the filter subscription that is having its version changed.
     * @param String $p_new_version A version object representing the new version.
     * @param String $p_client_domain The domain of the client website that the subscription is for.
     *
     * @return void
     */
    public function actionChangeFilterVersion($p_stream_subscription_id, $p_filter_subscription_id,
        array $p_new_version, $p_client_domain
    ) {
        $json = array();

        $stream_subscription_form = new StreamSubscriptionForm('filter_unsubscription');
        $stream_subscription_form->client_domain = $p_client_domain;
        $stream_subscription_form->user_id = Yii::app()->user->getId();

        if ($stream_subscription_form->validate() === false) {
            $json['error'] =  ErrorHelper::model($stream_subscription_form->getErrors());
        } else {
            $result = $stream_subscription_form->changeFilterVersion(
                $p_stream_subscription_id,
                $p_filter_subscription_id,
                $p_new_version
            );
            if (is_string($result) === true) {
                $json['error'] = $result;
            }
        }

        if (isset($json['error']) === true) {
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }

        echo JSON::encode($json);
    }


}

?>