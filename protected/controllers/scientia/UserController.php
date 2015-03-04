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
 * USer Controller
 *
 * @package PHP_Controllers
 */
class UserController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl',
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
                    'Valid',
                    'VerifySecret',
                    'GetUserSubscriptions',
                    'GetKindred',
                    'Profile',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(
                    'update',
                    'rings',
                    'NewRing',
                    'UserTakesFromStreams',
                    'GenerateSecret',
                    'GetKindredTags',
                    'StoreKindred',
                    'StoreUserClientData',
                    'GetUserClientData',
                    'UserTagsByUser',
                    'UserTagsGlobal',
                    'PopularUserStreams',
                ),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Stores some user data for a client website.
     *
     * @param string $p_client_domain The domain of the website that user data is being saved for.
     * @param string $p_key The key given to the data by the client domain.
     * @param string $p_data The data that is being stored.
     *
     * @return void
     */
    public function actionStoreUserClientData($p_client_domain, $p_key, $p_data) {

        $p_data = preg_replace("/([a-zA-Z0-9_]+?):/", "\"$1\":", $p_data); // fix variable names

        $data = json_decode($p_data, true);

        $site_id = SiteMulti::getSiteID($p_client_domain);
        $user_id = Yii::app()->user->getId();
        $ucdi = new UserClientDataInsert;
        $result = $ucdi->storeData($user_id, $site_id, $data, $p_key);
        $json = array();
        if ($result === true) {
            $json['success'] = true;
        } else {
            $json['success'] = false;
            $json['error'] = $result;
        }
        echo JSON::encode($json);
    }

    /**
     * Gets some user data for a client website.
     *
     * @param string $p_client_domain The domain of the website that user data is being fetched for.
     * @param string $p_key The key given to the data by the client domain.
     *
     * @return void
     */
    public function actionGetUserClientData($p_client_domain, $p_key) {
        $user_id = Yii::app()->user->getId();
        $site_id = Site::getSiteId($p_client_domain);
        $json = array();
        if ($site_id !== false) {
            $result = UserClientData::getRows($user_id, $site_id, $p_key);
            if (empty($result) === true) {
                $json['success'] = false;
                $json['error'] = 'This key does not exist for this user and client site.';
            } else {
                $json['success'] = true;
                $json['data'] = $result;
            }
        } else {
            $json['success'] = false;
            $json['error'] = 'This client domain has never inserted any user data.';
        }

        echo JSON::encode($json);
    }

    /**
     * JSON action to checks if a username is valid.
     *
     * @param $g_user The username to check to see if it exists.
     *
     * @return void
     */
    public function actionValid($g_user) {
        $user = new UserMulti;
        $valid = $user->userExists($g_user);
        echo JSON::encode(array('valid' => $valid));
    }

    /**
     * Verifies that a user secret generated for a user on another domain is valid.
     *
     * @param string $p_secret The secret to validate.
     *
     * @return void
     */
    public function actionVerifySecret($p_secret) {
        $this->ensureSSL();

        $valid = UserSecret::verifySecret($this->username, $p_secret);

        echo JSON::encode(array("valid" => $valid));
    }

    /**
     * Generates a secret for the logged on user.
     *
     * This secret can be used for any other data store to contatct this one directly to ensure that a particular
     * user generated this secret. For example, it is used when an post is made for the doman that owns the
     * stream the post is being made in to ensure that the given user is making the post.
     *
     * @return void
     */
    public function actionGenerateSecret() {
        $this->ensureSSL();

        $secret = UserSecret::createSecret(Yii::app()->user->getId());

        echo JSON::encode(array("secret" => $secret));
    }

    /**
     * Stores kindred rhythm results on take data.
     *
     * Scores must be indexed by take_id as their may be empty values and the need marking as having been processed.
     *
     * Request variables:
     * @param array $p_scores Nested array of scores. Indexed by take_id.
     * @param string $p_scores['take_id']['full_username'] The full username of who the score is agianst.
     * @param array $p_scores['take_id']['score'] The value of the score.
     * @param string $p_rhythm_id The user_rhythm_id that was used to generate the score.
     *
     * @fixme this needs changing. It should use the rhythm name and convert it to an it and check it exists.
     *
     * @return void
     */
    public function actionStoreKindred(array $p_scores, $p_rhythm_id) {
        if (isset($p_scores) === false) {
            throw new CHttpException(400, 'Bad Request. Score data missing.');
        }
        if (is_array($p_scores) === false) {
            throw new CHttpException(400, 'Bad Request. Score is not an array.');
        }
        if (empty($p_scores) === true) {
            throw new CHttpException(400, 'Bad Request. Scores are empty');
        }
        if (isset($p_rhythm_id) === false) {
            throw new CHttpException(400, 'Bad Request. rhythm_id is missing.');
        }
        if (ctype_digit($p_rhythm_id) === false) {
            throw new CHttpException(400, 'Bad Request. rhythm_id is not a positive integer : ' . rhythm_id);
        }

        $error = "";
        // Validate the take ids
        foreach ($p_scores as $take_id => $score_data) {
            if (isset($take_id) === false) {
                throw new CHttpException(400, 'Bad Request. take_id is missing.');
            }
            if (ctype_digit((string)$take_id) === false) {
                throw new CHttpException(400, 'Bad Request. take_id is not a positive integer : ' . $take_id);
            }
            // @fixme Validate the take id.

            // If the object is empty then just enter an empty take to mark the take as processed.
            if (empty($score_data) === true) {
                TakeKindred::insertRhythmScore(
                    $take_id,
                    $p_rhythm_id,
                    Yii::app()->user->getId(),
                    Yii::app()->user->getId(),
                    0
                );

            } else {
                if (isset($score_data['score']) === false) {
                    throw new CHttpException(400, 'Bad Request. score is missing.');
                }
                if (TextHelper::isInt($score_data['score']) === false) {
                    throw new CHttpException(400, 'Bad Request. score is not an integer : ' . $take_id);
                }

                if (isset($score_data['full_username']) === false) {
                    throw new CHttpException(400, 'Bad Request. full_username is missing.');
                }
                if (strpos($score_data['full_username'], '/') === false) {
                    throw new CHttpException(
                        400,
                        'Bad Request. full_username is invalid : ' . $score_data['full_username']
                    );
                }
                $user_parts = explode("/", $score_data['full_username']);
                $site_id = Site::getSiteId($user_parts[0]);
                $user_multi = new UserMulti($site_id);
                $scored_user_id = $user_multi->getIDFromUsername($user_parts[1], false, true);

                // Don't throw an error if a username is invalid. Just report it.
                // A bad username could happen because the host server is down or gone.
                if ($scored_user_id === false) {
                    // @note Could set this to retry fetching this user at a later date before skipping it.
                    $error .= 'Username is invalid : ' . $score_data['full_username']
                        . '. In user rhythm id : ' . $p_rhythm_id . ". ";
                } else {
                    TakeKindred::insertRhythmScore(
                        $take_id,
                        $p_rhythm_id,
                        Yii::app()->user->getId(),
                        $scored_user_id,
                        $score_data['score']
                    );
                }
            }
        }
        KindredMulti::recalculateTotalsForUserRhythm(Yii::app()->user->getId(), $p_rhythm_id);

        if (empty($error) === false) {
            $json_array = array(
                "error" => $error,
            );
        } else {
            $json_array = array(
                "success" => "true",
            );
        }
        echo JSON::encode($json_array);
    }

    /**
     * Get a users stored kindred data.
     *
     * @return void
     */
    public function actionGetKindred() {
        $kindred_data = Kindred::getKindredForUser(Yii::app()->user->getId());
        echo JSON::encode($kindred_data);
    }

    /**
     * Get a users stored kindred data.
     *
     * @return void
     */
    public function actionGetKindredTags() {
        $kindred_data = KindredMulti::getKindredTags(Yii::app()->user->getId());
        echo JSON::encode($kindred_data);
    }

    /**
     * Returns a users subscriptions to a client website.
     *
     * This must be accessed through the domus domain of the user whose subscriptions are being fetched.
     *
     * @param {string} $g_client_domain The domain of the client that the subscriptions are for.
     *
     * @return void
     */
    public function actionGetUserSubscriptions($g_username, $g_client_domain) {
        $user_id = $user_multi->getIDFromUsername($g_username, false);
        $site_id = Site::getSiteId($client_domain);

        if ($user_id === false) {
            $error = 'User does not exist.';
        } else if ($user_id === false) {
            $error = 'client_domain does not exist.';
        }
        if (is_set($error) === true) {
            echo JSON::encode(
                array(
                    "error" => $error,
                )
            );

            return;
        }

        $user_multi = new UserMulti;
        $subscriptions = $user_multi->getStreamSubscriptions($user_id, $site_id);

        echo JSON::encode(
            array(
                "subscriptions" => $subscriptions,
            )
        );
    }



    /**
     * Returns a a list of declined suggestions by a user.
     *
     * @FIXME Not currently using this feature. Should be.
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


    /**
     * Returns a json object containing profile information about this user.
     *
     * @return void
     */
    public function actionProfile() {
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername(Yii::app()->request->getQuery('user'), false);
        if ($user_id === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $profile_model = UserProfile::get($user_id);

        $meta_post_id = User::getMetaPostId($user_id);
        $meta_url = Post::getMetaUrl($meta_post_id);

        // If this profile is for a ring then fetch the type of membership.
        $ring_membership_type = Ring::getMemberTypeByUserID($user_id);

        $profile_array = array(
            'real_name' => $profile_model->real_name,
            'about' => $profile_model->about,
            'ring_membership_type' => $ring_membership_type,
            'meta_url' => $meta_url,
        );

        echo JSON::encode($profile_array);
    }

    /**
     * Display a JSON list of takes that have been made against a user by the logged in user.

     * @return void
     */
    public function actionUserTagsByUser($g_profile_domain, $g_profile_username, $g_start, $g_qty) {

        if (ctype_digit($g_start) === false) {
            throw new CHttpException(
                400,
                "Bad Request. paramter 'start' is not a positive integer :'" . $g_start
            );
        }
        if (ctype_digit($g_qty) === false) {
            throw new CHttpException(400, "Bad Request. paramter 'qty' is not a positive integer :'" . $g_qty);
        }

        $start = 0;
        if (isset($g_start) === true) {
            $start = $g_start;
        }
        $qty = 5;
        if (isset($g_qty) === true) {
            $qty = $g_qty;
        }

        $site_id = SiteMulti::getSiteID($g_profile_domain, true, true);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($g_profile_username, false);
        if ($user_id === false) {
            throw new CHttpException(400, "Bad Request. profile_user Not found: " . $g_profile_username);
        }

        $take_user_id = Yii::app()->user->getId();

        $ut = new UserTake;
        $takes = $ut->getForUserByUser($user_id, $start, $qty, $take_user_id);

        echo JSON::encode($takes);
    }

    /**
     * Display a list of JSON takes for a user.
     *
     * The results are expeced to be ordered by stream.domain, stream.username, stream.name, stream.version, date
     *
     * @param string $g_start The starting point to fetch tags from. Enables paging.
     * @param string $g_qty The quantity of tags to fetch.
     * @param string $g_full_username The full username of the user who is requesting the data.
     *
     * @return void
     */
    public function actionUserTagsGlobal($g_start, $g_qty, $g_full_username) {
        if (isset($g_start) === true && ctype_digit($g_start) === false) {
            throw new CHttpException(
                400,
                "Bad Request. paramter 'start' is not a positive integer :'" . $g_start
            );
        }
        if (isset($g_qty) === true && ctype_digit($g_qty) === false) {
            throw new CHttpException(400, "Bad Request. paramter 'qty' is not a positive integer :'" . $g_qty);
        }

        $start = 0;
        if (isset($g_start) === true) {
            $start = $g_start;
        }
        $qty = 5;
        if (isset($g_qty) === true) {
            $qty = $g_qty;
        }

        $user_id = User::getIDFromFullName($g_full_username);
        if ($user_id === false) {
            throw new CHttpException(400, "Bad Request. profile_user Not found: " . $_GET['user_id']);
        }

        $ut = new UserTake;
        $takes = $ut->getForUserByGlobal($user_id, $start, $qty);

        echo JSON::encode($takes);
    }


    /**
     * Fetch the most popular streams that a user takes posts with.
     *
     * Request variables:
     * $_POST['kind'] The stream kind to use.
     * $_POST['page'] The page number of results to fetch.
     * $_POST['qty'] The quantity to fetch per page.
     *
     * @return void
     */
    public function actionPopularUserStreams() {
        $page = 1;
        if (isset($_GET['page']) === true) {
            $page = $_GET['page'];
        }
        if (ctype_digit((String)$page) === false) {
            throw new CHttpException(400, 'Bad data. Page is not a possitive integer.');
        }

        $qty = 5;
        if (isset($_GET['qty']) === true) {
            $qty = $_GET['qty'];
        }
        if (ctype_digit((String)$qty) === false) {
            throw new CHttpException(400, 'Bad data. qty is not a possitive integer.');
        }

        $kind = null;
        if (isset($_GET['kind']) === true) {
            $kind = $_GET['kind'];
            if (LookupHelper::valid("stream.kind", $kind) === false) {
                throw new CHttpException(400, 'Bad data. kind is not a valid value.');
            }
        }

        $user_id = Yii::app()->user->getId();
        if ($user_id === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $popular = Take::getPopularStreamTakes($user_id, $page, $qty);

        echo JSON::encode($popular);
    }


}

?>