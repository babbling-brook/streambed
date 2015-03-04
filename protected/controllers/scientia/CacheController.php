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
 * Actions related to cached data store items for users who are not memebers of this site.
 * Rhythms can use this controller to obtain cached versions if the source is not available.
 *
 * @package PHP_Controllers
 */
class CacheController extends Controller
{
    /**
     * The domain we are fetching cached content from.
     *
     * @var string
     */
    protected $site;

    /**
     * The type of cached contetn we are fetching.
     *
     * @var string
     */
    protected $type;

    /**
     * The primary key of the site table for the domain we are fetching cached data from.
     *
     * @var type
     */
    protected $site_id;

    /**
     * The uername of the user who owns the cached content we are fetching.
     *
     * @var string
     */
    protected $username;

    /**
     * The primary key of the user table fot the user who owns the cached content we are fetching.
     *
     * @var string
     */
    protected $user_id;

    /**
     * The name of the thing we are fetching cahed content for eg rhythm, stream.
     *
     * @var string
     */
    protected $name;

    /**
     * The major version number of the cached content we are fetching.
     *
     * @var integer
     */
    protected $major;

    /**
     * The minor version number of the cached content we are fetching.
     *
     * @var integer
     */
    protected $minor;

    /**
     * The patch version number of the cached content we are fetching.
     *
     * @var integer
     */
    protected $patch;

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'User',
            'Type + rhythm',
            'Version + rhythm, stream',
        );
    }

    /**
     * Checks if the user and site paramaters are present.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterUser($filterChain) {
        if (isset($_GET['user']) === true && isset($_GET['site']) === true && isset($_GET['name']) === true) {
            $this->username = $_GET['user'];
            $this->site = $_GET['site'];
            $this->name = $_GET['name'];
            $this->site_id = SiteMulti::getSiteID($this->site);
            $user_multi = new UserMulti($this->site_id);
            $this->user_id = $user_multi->getIDFromUsername($this->username);
            $filterChain->run();
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    /**
     * Checks if the site paramater is present.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterType($filterChain) {

        if (isset($_GET['site']) === true) {
            switch ($_GET['type']) {
                case "header":
                    $this->type = 0;
                    break;

                case "full":
                    $this->type = 1;
                    break;

                case "mini":
                    $this->type = 2;
                    break;

                default:
                    throw new CHttpException(400, 'Bad Data. Type not defined.');
            }
            $filterChain->run();
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    /**
     * Sets version number if present, otherwise gets default.
     *
     * Eg, if patch is missing it should get the highest patch version available (null might work).
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterVersion($filterChain) {
        if (isset($_GET['version_major']) === true) {
            $this->major = $_GET['version_major'];
        } else {
            $this->major = null;
        }
        if (isset($_GET['version_minor']) === true) {
            $this->minor = $_GET['version_minor'];
        } else {
            $this->minor = null;
        }
        if (isset($_GET['version_patch']) === true) {
            $this->patch = $_GET['version_patch'];
        } else {
            $this->patch = null;
        }
        $filterChain->run();
    }

    /**
     * Specifies the access control rules.
     *
     * This method is used by the 'accessControl' filter
     *
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array(
                'allow',  // allow all users to perform 'index' and 'view' actions
                'actions' => array(),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array('rhythm', 'stream'),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * No index available. Throw this.
     *
     * @return void
     */
    public function indexAction() {
        throw new CHttpException(404, 'The requested page does not exist.');
    }

    /**
     * Retrieve a cached stream. If it is not in cache then retrive from source.
     *
     * Echos as JSON
     *
     * @return void
     */
    public function actionStream() {
        $model = StreamBedMulti::getByName($this->user_id, $this->name, $this->major, $this->minor, $this->patch);
        $json = StreamBedMulti::getJSON(
            $model,
            $this->username,
            $this->site
        );

        if (is_null($stream_json) === false) {
            echo JSON::encode($stream_json);
            return;
        }

        // Failed to find locally; try to fetch from source
        $domain_helper = new OtherDomainsHelper;
        $stream_json = $domain_helper->getStream(
            $this->site,
            $this->username,
            $this->name,
            $this->major,
            $this->minor,
            $this->patch
        );

        $json_array = CJavaScript::jsonDecode($stream_json);
        if (is_null($json_array['error']) === false) {
            if (StreamBedMulti::cache($rhythm_json) === false) {
                throw new CHttpException(
                    400,
                    'Bad data when inserting cached stream : '
                        . $this->site . "/"
                        . $this->username . "/"
                        . $this->name . "/"
                        . $this->major . "/"
                        . $this->minor . "/"
                        . $this->patch
                );
            }
            // Refetch rhythm with correct type if not set to full
            if ($this->type !== 1) {
                $model = StreamBedMulti::getByName(
                    $this->user_id,
                    $this->name,
                    $this->major,
                    $this->minor,
                    $this->patch
                );
                $json = StreamBedMulti::getJSON(
                    $model,
                    $this->username,
                    $this->site
                );
            }
        }
        echo JSON::encode($stream_json);
    }

    /**
     * Retrieve a cached Rhythm. If it is not in cache then retrieve from source.
     *
     * Echos as JavasScript.
     *
     * @return void
     */
    public function actionRhythm() {
        $rhythm_json = Rhythm::getJSON(
            $this->type,
            $this->site_id,
            $this->username,
            $this->name,
            $this->major,
            $this->minor,
            $this->patch
        );

        if (is_null($rhythm_json) === false) {
            JSON::encode($json);
            return;
        }

        // Try to fetch from source
        $domain_helper = new OtherDomainsHelper;
        $rhythm_json = $domain_helper->getRhythm(
            1,
            $this->site,
            $this->username,
            $this->name,
            $this->major,
            $this->minor,
            $this->patch
        );

        $json_array = CJavaScript::jsonDecode($rhythm_json);
        if (is_null($json_array['error']) === false) {
            $result = Rhythm::cacheRhythm($rhythm_json);
            if (is_number($result) === false) {
                throw new CHttpException(
                    400,
                    'Bad data when inserting cached Rhythm: '
                        . $this->site . "/"
                        . $this->username . "/"
                        . $this->name . "/"
                        . $this->major . "/"
                        . $this->minor . "/"
                        . $this->patch
                        . "<br />" . $result
                );
            }

            // Refetch rhythm with correct type if not set to full
            if ($this->type !== 1) {
                $rhythm_json = Rhythm::getJSON(
                    $this->type,
                    $this->site_id,
                    $this->username,
                    $this->name,
                    $this->major,
                    $this->minor,
                    $this->patch
                );
            }
        }
        echo JSON::encode($json);
    }

}

?>