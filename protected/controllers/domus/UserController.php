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
 * User Controller
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
                    'GetKindred',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(
                    'StoreKindred',
                    'GetKindredTags',
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
     * Stores kindred rhythm results on take data.
     *
     * @FIXME Needs moving to the scientia domain.
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
                    // @note Could set this to retry fetching this user at a later date before skipping it?
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
     * @FIXME Needs moving to the scientia domain.
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
     * @FIXME Needs moving to the scientia domain.
     *
     * @return void
     */
    public function actionGetKindredTags() {
        $kindred_data = KindredMulti::getKindredTags(Yii::app()->user->getId());
        echo JSON::encode($kindred_data);
    }

}

?>