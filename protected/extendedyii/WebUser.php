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
 * Extends Yii class CWebUser so that additional properties can be returned via Yii::app()->user->property
 *
 * @package PHP_ExtendedYii
 */
class WebUser extends CWebUser
{

    /**
     * Store the model so that we do not need to repeat query.
     *
     * @var object
     */
    private $model;

    /**
     * @var boolean Should an auto renew cookie be issued.
     */
    public $autoRenewCookie = true;

    /**
     * Initialise the web user.
     *
     * @return void
     */
    public function init() {
        $conf = Yii::app()->session->cookieParams;
        $this->identityCookie = array(
            'path' => $conf['path'],
            'domain' => $conf['domain'],
        );

        parent::init();
        // Check the user has access to this domain in the DB.
        // This is necessary as the user is somtimes relogged on from a subdomain that shares the session.
        // Don't test the site access if the user is entering their password, as it will delete the session in progress.
        //
        // THIS IS NOT WORKING FOR REMOTE STORE LOGINS - IT CAUSES THEM TO LOG OUT
//        $d = substr($_SERVER['REQUEST_URI'], 0, 14);
//        if (substr($_SERVER['REQUEST_URI'], 0, 19) !== '/site/loginredirect'
//            && substr($_SERVER['REQUEST_URI'], 0, 14) !== '/site/password'
//            && substr($_SERVER['REQUEST_URI'], 0, 19) !== '/site/passwordcheck'
//            && substr($_SERVER['REQUEST_URI'], 0, 14) !== '/site/loggedin'
//            && substr($_SERVER['REQUEST_URI'], 0, 14) !== '/site/signup'
//        ) {
//            $user_id = $this->getState('__id');
//            $access_permitted = SiteAccess::isClientDomainLoggedInForUser($user_id, substr($conf['domain'], 1));
//            if ($access_permitted === false) {
//                $this->logout();
//            }
//        }

    }

    /**
     * Restores a users login status from cookie settings.
     *
     * Ensures that this site is in Site Acesss as the user may have just been logged off
     * and an asynchronous ajax request is trying to log them on again.
     *
     * @param boolean $fromCookie whether the login is based on cookie.
     *
     * @return void
     */
    public function afterLogin($fromCookie) {

        if ($fromCookie === true && $this->isGuest === false) {
            $access_permitted = SiteAccess::getSiteAccess($this->name, $this->getDomain(), true);
            if (isset($access_permitted) === false) {
                $this->logout();
            }
        }
    }

    /**
     * Return site ID for this user.
     *
     * Access it by Yii::app()->user->getSiteID().
     *
     * @return integer
     */
    public function getSiteID() {
        $user = $this->loadUser(Yii::app()->user->id);
        if (isset($user) === true) {
            return $user->site_id;
        }
    }

    /**
     * Return the domain of a user.
     *
     * Access it by Yii::app()->user->getDomain().
     *
     * @return string
     */
    public function getDomain() {
        $user = $this->loadUser($this->id);
        $domain = Site::model()->findByPk(
            $user->site_id
        );
        if (isset($domain) === true) {
            return $domain->domain;
        }
        throw new Exception("Domain not found for user.");
    }

    /**
     * Return the email address for a user.
     *
     * Access it by Yii::app()->user->getEmail().
     *
     * @return string
     */
    public function getEmail() {
        $user = $this->loadUser($this->id);
        return $user->email;
    }

    /**
     * @return boolean whether the current application user is a guest.
     */
    public function getIsGuest() {
        return $this->getState('__id')===null;
    }

    /**
     * This is a function that checks the field 'role'.
     *
     * Access it by Yii::app()->user->isAdmin().
     *
     * @return boolean
     */
    public function isAdmin() {
        $user = $this->loadUser(Yii::app()->user->id);
        if (isset($user) === true) {
            return LookupHelper::getID("user.role", "admin") === (int)$user->role ? true : false;
        }
        return false;
    }

    /**
     * This is a function that checks the field 'role'.
     *
     * Access it by Yii::app()->user->isAdmin().
     *
     * @return boolean
     */
    public function getCSFRToken() {
        $user = $this->loadUser(Yii::app()->user->id);
        if (isset($user) === true) {
            return $user->csfr;
        }
        return false;
    }

    /**
     * Load user model.
     *
     * @param integer $id The id of the user.
     *
     * @return User Model
     */
    protected function loadUser($id=null) {
        if ($this->model === null) {
            if ($id !== null) {
                $this->model = User::model()->findByPk($id);
            }
        }
        return $this->model;
    }
}

?>