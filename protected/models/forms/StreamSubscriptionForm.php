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
 * Form Model to validate a stream subscription.
 *
 * @package PHP_Model_Forms
 */
class StreamSubscriptionForm extends CFormModel
{
    /**
     * A standard stream name object that can be validated with StreamNameForm.
     *
     * @var type
     */
    public $stream_name;

    /**
     * The domain of the client website that is requesting this subscription for a user.
     *
     * @var type
     */
    public $client_domain;

    /**
     * @var integer The id of the user the subscription is being made for.
     */
    public $user_id;

    /**
     * The type of the stream. Must be one of $valid_types.
     *
     * @var string
     */
    public $type = 'stream';

    /**
     * IS the subscription locked or not.
     *
     * @var boolean
     */
    public $locked = false;

    /**
     * Valid types of stream.
     *
     * @var array(string)
     */
    private $valid_types = array('stream');

    /**
     * @var integer The id of the client site that this subscription is on.
     */
    private $client_site_id;

    /**
     * @var StreamNameForm The stream name form used to validate the stream name.
     */
    private $stream_name_form;

    /**
     * @var String The primary key for this subscription.
     */
    private $subscription_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('client_domain, user_id, type', 'required'),
            array('stream_name', 'required', 'on' => 'subscribe'),
            array('locked', 'boolean', 'trueValue' => true, 'falseValue' => false, 'on' => 'subscribe'),
            array('client_domain', 'ruleClientDomain'),
            array('stream_name', 'ruleStreamName', 'on' => 'subscribe'),
            array('user_id', 'ruleUserId'),
            array('type', 'ruleType'),
        );
    }

    /**
     * Check that the type is valid.
     *
     * @return void
     */
    public function ruleUserID() {
    }

    /**
     * Check that the type is valid.
     *
     * @return void
     */
    public function ruleStreamName() {
        $this->stream_name_form = new StreamNameForm;
        $this->stream_name_form->stream = $this->stream_name;

        if ($this->stream_name_form->validate() === false) {
            $this->addError(
                'stream_name', 'The stream is not a valid stream name object.'
                    . ErrorHelper::model($this->stream_name_form->getErrors())
            );
        }
    }

    /**
     * Check that the type is valid.
     *
     * @return void
     */
    public function ruleClientDomain() {
        $this->client_site_id = SiteMulti::getSiteID($this->client_domain, false);

        if ($this->client_site_id  === false) {
            $this->addError('client_domain', 'The client domain is not valid.');
        }
    }

    /**
     * Check that the type is valid.
     *
     * @return void
     */
    public function ruleType() {
        if (in_array($this->type, $this->valid_types) === false) {
            $this->addError(
                'type',
                'type is not valid. Must be one of : stream. Given : ' . $this->type
            );
        }
    }

    public function getSubscriptionId() {
        return $this->subscription_id;
    }

    /**
     * Subscribe a user to a stream.
     *
     * @param boolean [$insert_default_filters=true] Should the default filters be inserted.
     *
     * @return integer primary key.
     */
    public function subscribeStream($insert_default_filters=true) {
        $version = $this->stream_name['version'];
        $version_string = $version['major'] . '/' . $version['minor'] . '/' . $version['patch'];
        $version_type_id = Version::getTypeId($version_string);

        $display_order = StreamMulti::getNextDisplayOrder($this->user_id);

        $stream_extra_id = $this->stream_name_form->getFirstStreamExtraId();

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $user_stream_subscription = new UserStreamSubscription;
            $user_stream_subscription->user_id = $this->user_id;
            $user_stream_subscription->site_id = $this->client_site_id;
            $user_stream_subscription->stream_extra_id = $stream_extra_id;
            $user_stream_subscription->version_type = $version_type_id;
            $user_stream_subscription->display_order = $display_order;
            $user_stream_subscription->locked = $this->locked;

            if ($user_stream_subscription->validate() === false) {
                throw new Exception(
                    'Stream subscription does not validate: '
                    . ErrorHelper::model($user_stream_subscription->getErrors()),
                    99
                );
            }

            $user_stream_subscription->save();
            $this->subscription_id = $user_stream_subscription->getPrimaryKey();

            // Insert default moderation rings
            if ($this->type === 'stream') {
                $default_count = UserStreamSubscriptionRing::insertDefaults(
                    $user_stream_subscription->user_stream_subscription_id,
                    $stream_extra_id
                );
                if ($default_count === 0) {
                    UserStreamSubscriptionRing::insertSiteDefaults(
                        $user_stream_subscription->user_stream_subscription_id
                    );
                }
            }

            // Insert default sort rhythms.
            if ($insert_default_filters === true) {
                $filter_locked = false;
                $defaults = StreamDefaultRhythm::getForStream($stream_extra_id);
                foreach ($defaults as $default) {
                    UserStreamSubscriptionFilter::insertFilter(
                        $user_stream_subscription->user_stream_subscription_id,
                        $default['rhythm_extra_id'],
                        $default['version_type'],
                        $default['sort_order'],
                        $filter_locked
                    );
                }
            }

            $new_subscription = $this->stream_name;
            $new_subscription['display_order'] = $display_order;
            $new_subscription['stream_subscription_id'] = $this->subscription_id;
            $new_filters = UserStreamSubscriptionFilter::getForStream($this->subscription_id);
            $new_filters_indexed = array();
            foreach ($new_filters as $filter) {
                $new_filters_indexed[$filter['subscription_id']] = $filter;
                $new_filters_indexed[$filter['subscription_id']]['version'] = array(
                    'major' => $filter['major'],
                    'minor' => $filter['minor'],
                    'patch' => $filter['patch'],
                );
            }
            $new_subscription['filters'] = $new_filters_indexed;
            $new_rings = UserStreamSubscriptionRing::getForStream($this->subscription_id);
            $new_rings_indexed = array();
            foreach ($new_rings_indexed as $ring) {
                $new_rings_indexed[$ring['ring_subscription_id']] = $ring;
            }
            $new_subscription['rings'] = $new_rings;
            $new_subscription['locked'] = $this->locked;

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the stream.';
            }
        }
        return $new_subscription;
    }

    /**
     * Unsubscribes a stream for a user on a client webstie.
     *
     * Ensure that client_domain is populated before calling.
     *
     * @param Integer $subscription_id The subscription id of the
     * @param Boolean $delete_locked Should locked subscriptions be deleted?
     *
     * @return Boolean|String True or an error message.
     */
    public function unsubscribeStream($subscription_id, $delete_locked=false) {
        $this->subscription_id = $subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'locked' && $delete_locked === false) {
                throw new Exception('Unable to remove stream subscription.', 99);
            } else if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to unsubscribe stream. Subscription_id not found for this user and client site.',
                    99
                );
            }

            UserStreamSubscriptionFilter::deleteFilters($subscription_id);

            UserStreamSubscriptionRing::deleteModerationRings($_POST['subscription_id']);

            $result = UserStreamSubscription::deleteSubscription(
                $subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($result === 'false') {
                throw new Exception('Unable to remove stream subscription.', 99);
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst unsubscribing the stream.';
            }
        }
        return true;
    }

    /**
     * Add a new filter subscription to an existing stream subscription
     */
    public function subscribeFilter($subscription_id, $rhythm_name) {
        $this->subscription_id = $subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to subscribe filter rhythm. Subscription_id not found for this user and client site.',
                    99
                );
            }

            $rhythm_name_form = new RhythmNameForm;
            $rhythm_name_form->rhythm = $rhythm_name;
            $rhythm_name_form->check_remote_store = true;
            if ($rhythm_name_form->validate() === false) {
                throw new Exception('Filter rhythm is not a valid filter rhythm.', 99);
            }
            $rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();

            $version = $rhythm_name['version'];
            $version_string = $version['major'] . '/' . $version['minor'] . '/' . $version['patch'];
            $version_type_id = Version::getTypeId($version_string);

            $display_order = UserStreamSubscriptionFilter::getNextDisplayOrder($subscription_id);

            if (isset($rhythm_name['locked']) === true) {
                $locked = $rhythm_name['locked'];
            } else {
                $locked = false;
            }

            $user_stream_subscription_filter_id = UserStreamSubscriptionFilter::insertFilter(
                $subscription_id,
                $rhythm_extra_id,
                $version_type_id,
                $display_order,
                $locked
            );
            $description = RhythmExtra::getDescription($rhythm_extra_id);

            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        $rhythm_name['filter_subscription_id'] = $user_stream_subscription_filter_id;
        $rhythm_name['display_order'] = $display_order;
        $rhythm_name['description'] = $description;
        $rhythm_name['locked'] = $locked;

        return $rhythm_name;
    }

    /**
     * Add a new filter subscription to an existing stream subscription
     *
     * @param String $stream_subscription_id The id of the stream subscription that owns the filter subscription.
     * @param String String $filter_subscription_id The id of the filter subscription.
     *
     * @return String|True True or an error message.
     */
    public function unsubscribeFilter($stream_subscription_id, $filter_subscription_id) {

        $this->subscription_id = $stream_subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $stream_subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to unsubscribe filter rhythm. '
                        . 'The stream subscription_id not found for this user and client site.',
                    99
                );
            }

            UserStreamSubscriptionFilter::deleteFilter($stream_subscription_id, $filter_subscription_id);
            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        return true;
    }

    /**
     * Add a new ring subscription to an existing stream subscription
     *
     * @param String $stream_subscription_id The id of the stream subscription that a ring is being added to.
     * @param String $ring_name A standard ring name object representing the ring to subscribe.
     *
     * @return String|True True or an error message.
     */
    public function subscribeRing($stream_subscription_id, $ring_name) {

        $this->subscription_id = $stream_subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $stream_subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to subscribe ring. '
                        . 'The stream subscription_id is not found for this user and client site.',
                    99
                );
            }

            $ring_form = new RingNameForm('need_ring_id');
            $ring_form->ring = $ring_name;
            if ($ring_form->validate() === false) {
                throw new Exception(
                    ErrorHelper::model($ring_form->getErrors()),
                    99
                );
            }

            $subscription = UserStreamSubscriptionRing::insertRing($stream_subscription_id, $ring_form->getRingId());
            $subscription = array_merge($ring_name, $subscription);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        return $subscription;
    }

    /**
     * Add a new ring subscription to an existing stream subscription
     *
     * @param String $stream_subscription_id The id of the stream subscription that owns the ring subscription.
     * @param String $stream_subscription_id The id of the ring subscription that ois being unsubscribed.
     *
     * @return String|True True or an error message.
     */
    public function unsubscribeRing($stream_subscription_id, $ring_subscription_id) {

        $this->subscription_id = $stream_subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $stream_subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to unsubscribe ring. '
                        . 'The stream subscription_id is not found for this user and client site.',
                    99
                );
            }

            $result = UserStreamSubscriptionRing::removeRing($stream_subscription_id, $ring_subscription_id);
            if (is_string($result) === true) {
                throw new Exception($result, 99);
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        return true;
    }

    /**
     * Add a new ring subscription to an existing stream subscription
     *
     * @param String $stream_subscription_id The id of the stream subscription that is having its version changed.
     * @param String $stream_subscription_id The id of the ring subscription that ois being unsubscribed.
     *
     * @return String|True True or an error message.
     */
    public function changeStreamVersion($stream_subscription_id, $new_version) {

        $this->subscription_id = $stream_subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            // Needs user_id and client to enforce ownership.
            $stream_extra_id = UserStreamSubscription::getStreamExtraId(
                $stream_subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($stream_extra_id === false) {
                throw new Exception(
                    'The stream subscription_id is not found for this user and client site.',
                    99
                );
            }

            $version_form = new VersionForm(null, true, true);
            $version_form->version = $new_version;
            if ($version_form->validate() === false) {
                throw new Exception(
                    'The new version is not a valid version. ' . ErrorHelper::model($version_form->getErrors()),
                    99
                );
            }

            $stream = StreamBedMulti::getByIDWithExtra($stream_extra_id);

            $version_without_latest = $version_form->getWithoutLatest();

            $new_stream_version = StreamBedMulti::getByName(
                $stream->user->user_id,
                $stream->name,
                $version_without_latest['major'],
                $version_without_latest['minor'],
                $version_without_latest['patch']
            );
            if (is_null($new_stream_version) === true) {
                throw new Exception(
                    'The new version does not exist for this stream.',
                    99
                );
            }

            $new_version_type_id = Version::getTypeId($version_form->getString());
            if (is_null($new_version_type_id) === true) {
                throw new Exception(
                    'The version type is not valid: ' . $version_form->getString(),
                    99
                );
            }

            $result = UserStreamSubscription::changeVersion(
                $stream_subscription_id,
                $new_stream_version->extra->stream_extra_id,
                $new_version_type_id
            );
            if ($result === false) {
                throw new Exception('Stream subscription not found.', 99);
            }
            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        return true;
    }


    /**
     * Add a new ring subscription to an existing stream subscription
     *
     * @param String $stream_subscription_id The id of the stream subscription that is having its version changed.
     * @param String $stream_subscription_id The id of the ring subscription that ois being unsubscribed.
     *
     * @return String|True True or an error message.
     */
    public function changeFilterVersion($stream_subscription_id, $filter_subscription_id, $new_version) {

        $this->subscription_id = $stream_subscription_id;

        $transaction = Yii::app()->db->beginTransaction();
        try {
            $exists = UserStreamSubscription::checkDeleteable(
                $stream_subscription_id,
                $this->user_id,
                $this->client_site_id
            );
            if ($exists === 'not_found') {
                throw new Exception(
                    'Unable to unsubscribe ring. '
                        . 'The stream subscription_id is not found for this user and client site.',
                    99
                );
            }

            $version_form = new VersionForm(null, true);
            $version_form->version = $new_version;
            if ($version_form->validate() === false) {
                throw new Exception(
                    'The new version is not a valid version. ' . ErrorHelper::model($version_form->getErrors()),
                    99
                );
            }

            $old_rhythm_id = UserStreamSubscriptionFilter::getRhythmExtraIDFromFilterID($filter_subscription_id);
            $rhythm_extra = Rhythm::getByIDWithSite($old_rhythm_id);
            if (is_null($rhythm_extra) === true) {
                throw new Exception(
                    'Rhythm not found when changing filter version.',
                    99
                );
            }
            $version_without_latest = $version_form->getWithoutLatest();

            $new_rhythm_version = Rhythm::getByName(
                $rhythm_extra->rhythm->user->user_id,
                $rhythm_extra->rhythm->name,
                $version_without_latest['major'],
                $version_without_latest['minor'],
                $version_without_latest['patch']
            );
            if (is_null($new_rhythm_version) === true) {
                throw new Exception(
                    'The new version does not exist for this stream.',
                    99
                );
            }

            $new_version_type_id = Version::getTypeId($version_form->getString());

            $result = UserStreamSubscriptionFilter::changeVersion(
                $filter_subscription_id,
                $new_rhythm_version->extra->rhythm_extra_id,
                $new_version_type_id
            );
            if ($result === false) {
                throw new Exception('Filter subscription not found.', 99);
            }
            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            if ($e->getCode() === 99) {
                return $e->getMessage();
            } else {
                return 'A database error occurred whilst subscribing the filter.';
            }
        }

        return true;
    }


    /**
     * Return an array ready for json encoding containing.
     *
     * All the streams that a user is subscribed to.
     * Each stream contains an array containing all the filters and rings in use for that stream.
     *
     * @param integer $user_id The id of the user we are fetching streams for.
     * @param integer $site_id The id of the client website that the subscription is for.
     *
     * @return array
     */
    public static function getStreamSubscriptions($user_id, $site_id) {

        try {
            $subscriptions = UserStreamSubscription::getUsersSubscriptions($user_id, $site_id);

            $current_stream = array();
            $current_stream_subscription_id = 0;
            $current_filter_subscription_id = 0;
            $current_ring_subscription_id = 0;
            $json_streams = array();
            foreach ($subscriptions as $subscription) {
                if ($subscription['stream_subscription_id'] !== $current_stream_subscription_id) {
                    $current_stream_subscription_id = $subscription['stream_subscription_id'];
                    $current_filter_subscription_id = 0;
                    $current_ring_subscription_id = 0;
                    $current_stream = array();
                    // New stream, finish up the last one and start a new one
                    if ($subscription['stream_display_order'] !== 0) {
                        $version = Version::makeVersionFromVersionTypeId(
                            $subscription['stream_version_type_id'],
                            $subscription['stream_major'],
                            $subscription['stream_minor'],
                            $subscription['stream_patch']
                        );
                        $current_stream = array(
                            'stream_subscription_id' => $subscription['stream_subscription_id'],
                            'domain' => $subscription['stream_domain'],
                            'username' => $subscription['stream_username'],
                            'name' => $subscription['stream_name'],
                            'description' => $subscription['stream_description'],
                            'version' => $version,
                            'locked' => $subscription['stream_locked'] === '1' ? true : false,
                            'filters' => array(),
                            'rings' => array(),
                        );
                    }
                }

                if (isset($subscription['filter_subscription_id']) === true
                    && $subscription['filter_subscription_id'] !== $current_filter_subscription_id
                ) {
                    $current_filter_subscription_id = $subscription['filter_subscription_id'];
                    $version = Version::makeVersionFromVersionTypeId(
                        $subscription['filter_version_type_id'],
                        $subscription['rhythm_major'],
                        $subscription['rhythm_minor'],
                        $subscription['rhythm_patch']
                    );
                    $filter = array(
                        'filter_subscription_id' => $subscription['filter_subscription_id'],
                        'domain' => $subscription['rhythm_domain'],
                        'username' => $subscription['rhythm_username'],
                        'name' => $subscription['rhythm_name'],
                        'description' => $subscription['rhythm_description'],
                        'version' => $version,
                        'locked' => $subscription['filter_locked'] === '1' ? true : false,
                        'params' => array()
                    );
                    $current_stream['filters'][$subscription['filter_subscription_id']] = $filter;
                    $current_param_name = '';
                }

                if (isset($subscription['filter_subscription_id']) === true
                    && isset($subscription['param_name']) === true
                    && $current_param_name !== $subscription['param_name']
                ) {
                    array_push(
                        $current_stream['filters'][$subscription['filter_subscription_id']]['params'],
                        array(
                            'name' => $subscription['param_name'],
                            'hint' => $subscription['param_hint'],
                        )
                    );
                    $current_param_name = $subscription['param_name'];
                }

                if (isset($subscription['ring_subscription_id']) === true
                    && $subscription['ring_subscription_id'] !== $current_ring_subscription_id
                ) {
                    $current_ring_subscription_id = $subscription['ring_subscription_id'];
                    $ring = array(
                        'ring_subscription_id' => $subscription['ring_subscription_id'],
                        'domain' => $subscription['ring_domain'],
                        'username' => $subscription['ring_username'],
                        'locked' => $subscription['ring_locked'] === '1' ? true : false,
                    );
                    $current_stream['rings'][$subscription['ring_subscription_id']] = $ring;
                }

                $json_streams[$subscription['stream_display_order']] = $current_stream;
            }

            return $json_streams;
        } catch (Exception $e) {
            return 'There was a database error whilst fetching a users stream subscriptions.';
        }
    }
}

?>
