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
 * Model for the site_access DB table.
 * The table holds access privilages for each site that a user logs into.
 *
 * @package PHP_Models
 */
class SiteAccess extends CActiveRecord
{

    /**
     * The primary key of this site access row.
     *
     * @var integer
     */
    public $site_access_id;

    /**
     * The id of the user who has granted access to a particular site.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the site that access has been granted to.
     *
     * @var integer
     */
    public $site_id;

    /**
     * A timestamp of when access was granted.
     *
     * @var string
     */
    public $login_time;

    /**
     * A datetime of when access expires.
     *
     * @var string
     */
    public $login_expires;

    /**
     * The id of the session that granted access.
     *
     * @var string
     */
    public $session_id;

    /**
     * Returns the parent model.
     *
     * @param type $className The name of this class.
     *
     * @return Model
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * Getter for the tables name.
     *
     * @return string the associated database table name.
     */
    public function tableName() {
        return '{{site_access}}';
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'site' => array(self::BELONGS_TO, 'Site', 'site_id', 'joinType' => 'INNER JOIN'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Checks if a client site is currently logged in for a user.
     *
     * @param integer $user_id The id of the user that is being checked.
     * @param string $domain The domain of the client site that is being checked.
     *
     * @return boolean
     */
    public static function isClientDomainLoggedInForUser($user_id, $domain) {
        $sql = "
            SELECT site_access_id
            FROM
                site_access
                INNER JOIN site ON site.site_id = site_access.site_id
            WHERE
                site_access.user_id = :user_id
                AND site.domain = :domain
                AND login_expires > NOW()";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $has_access = $command->queryScalar();
        return (bool)$has_access;    // queryScalar returns false if nothing found
    }

    /**
     * Deletes site_access row by their user_id.
     *
     * @param integer $user_id The id of the user whose site access records are being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM site_access
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }



    /**
     * Logs when a user gives permission for a third party site to acccess their update process.
     *
     * @param string $domain Domain of the site being given access.
     * @param string $username The user granting access.
     * @param integer $remember_time The amount of time to remember this connection if the session is restarting.
     *
     * @return void
     */
    public static function storeSiteAccess($domain, $username, $remember_time) {
        $site_access_id = SiteAccess::getSiteAccessID($username, $domain);
        if ($site_access_id === false) {
            SiteAccess::insertSiteAccess($domain, $username, $remember_time);
        } else {
            SiteAccess::updateSiteAccess($remember_time, $site_access_id);
        }
    }

    /**
     * Does the user have access to the site.
     *
     * @param string $username The user to check for access permission.
     * @param string $domain The site to check for access permisison.
     * @param boolean $current Only return results that have not expired.
     *
     * @return SiteAccess Model
     */
    public static function getSiteAccess($username, $domain, $current=false) {
        $params = array(
            ':username' => $username,
            ':domain' => $domain,
        );
        $current_condition = "";
        if ($current === true) {
            $current_condition = " AND (login_expires>NOW() OR session_id=:session_id)";
            $params[':session_id'] = Yii::app()->session->sessionID;
        }


        $results = SiteAccess::model()->with('site', 'user')->find(
            array(
                'condition' => 'username=:username AND domain=:domain' . $current_condition,
                'params' => $params,
            )
        );
        return $results;
    }

    /**
     * The site access ID for a user.
     *
     * @param string $username The user to check for access permission.
     * @param string $domain The site to check for access permisison.
     * @param boolean $current Only return results that have not expired.
     *
     * @return number|boolean site_access_id from the site_access table or false.
     */
    public static function getSiteAccessID($username, $domain, $current=false) {
        $row = SiteAccess::getSiteAccess($username, $domain, $current);
        if (empty($row) === true) {
            return false;
        }
        return $row->site_access_id;
    }

    /**
     * Update the expiry time of for a third party sites access to a users IFrame.
     *
     * @param integer $remember_time The amount of time to remember this connection if the session is restarting.
     * @param integer $site_access_id DB primary key of the record to be updated.
     *
     * @return void
     */
    public static function updateSiteAccess($remember_time, $site_access_id) {
        $criteria = new CDbCriteria(
            array(
                'condition' => ':site_access_id',
                'params' => array(
                    ':site_access_id' => $site_access_id,
                )
            )
        );

        SiteAccess::model()->updateByPk(
            $site_access_id,
            array(
                'login_time' => new CDbExpression('NOW()'),
                'login_expires' => date('Y-m-d H:i:s', $remember_time),
                'session_id' => Yii::app()->session->sessionID,
            ),
            $criteria
        );
    }

    /**
     * Allow a third party site to have access to a users IFrame.
     *
     * @param string $domain The domain of the site being given access.
     * @param string $username The user granting access.
     * @param integer $remember_time The amount of time to remember this connection if the session is restarting.
     *
     * @return void
     */
    public static function insertSiteAccess($domain, $username, $remember_time) {
        $site_id = SiteMulti::getSiteID($domain);
        $user = new UserMulti;
        $user_id = $user->getIDFromUsername($username);
        $site_access = new SiteAccess();
        $site_access->user_id = $user_id;
        $site_access->site_id = $site_id;
        $site_access->login_expires = date('Y-m-d H:i:s', $remember_time);
        $site_access->session_id = Yii::app()->session->sessionID;
        $site_access->save();
    }

    /**
     * Return all the site_access records for the logged in user.
     *
     * @return array
     */
    public static function getAll() {
        $sql = "SELECT
                     site.domain
                FROM
                    site_access
                    INNER JOIN site ON site_access.site_id = site.site_id
                WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Remove site access entries for a user. Called on logout.
     *
     * @param integer|string $user_id A user ID or username. If username, assumes from this site.
     *
     * @return void
     */
    public static function removeAllForUser($user_id) {
        if (is_numeric($user_id) === false) {
            $user_multi = new UserMulti;
            $user_id = $user_multi->getIDFromUsername($user_id);
        }

        SiteAccess::model()->deleteAll(
            'user_id=:user_id',
            array(
                ':user_id' => $user_id,
            )
        );
    }

    /**
     * Remove a client site access entry for a user. Called on logout.
     *
     * @param integer $site_access_id The site to remove access to.
     *
     * @return void
     */
    public static function removeClient($user_id, $site_id) {
        $sql = "DELETE FROM site_access
                WHERE
                    user_id = :user_id
                    AND site_id = :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of site_access data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM site_access
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>