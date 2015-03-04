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
 * A collection of static functions that affect multiple db tables to do with sites.
 */
class SiteMulti
{

    /**
     * Fetch the site_id, given its domain.
     *
     * If the site does not exist, a new one can be optionaly created.
     *
     * @param string $domain Domain of the site.
     * @param boolean $insert Insert this domain if it does not exist.
     * @param boolean $check_remote Check if the remote domain exists before inserting.
     *
     * @return integer|false primary key.
     */
    public static function getSiteID($domain, $insert=true, $check_remote=false) {
        if ($domain === Yii::app()->params['host']) {
            return Yii::app()->params['site_id'];
        }

        $row = Site::model()->find(
            array(
                'select' => 'site_id',
                'condition' => 'domain=:domain',
                'params' => array(
                    ':domain' => $domain,
                )
            ),
            array()
        );

        if (isset($row) === true && isset($row->site_id) === true) {
            return $row->site_id;
        } else {
            if ($check_remote === true) {
                // Check if the remote site is a BabblingBrook site.
                $odh = new OtherDomainsHelper($domain);
                $domain_exists = $odh->checkBabblingBrookDomain();
                if ($domain_exists === true) {
                    return SiteMulti::insertSite($domain);

                } else {
                    return false;
                }
            }

            if ($insert === true) {
                return SiteMulti::insertSite($domain);
            } else {
                return false;
            }
        }
    }

    /**
     * Insert a new site.
     *
     * @param string $domain The domain name of the site to insert.
     *
     * @return integer The primary key from the insert.
     */
    public static function insertSite($domain) {
        $site = new Site;
        $site->domain = $domain;
        $site->save();
        return $site->getPrimaryKey();
    }

    /**
     * Fetch a domain from a site ID.
     *
     * @param integer $site_id The id of the site to fetch.
     * @param boolean [$throw_error=true] If the site is not found then throw an error if set to true
     *      , else return false if the user not found.
     *
     * @return string A domain name.
     */
    public static function getDomain($site_id, $throw_error=true) {
        $site = Site::model()->findByPk($site_id);

        if (isset($site) === false) {
            if ($throw_error === true) {
                throw new Exception("Site Not found.");
            } else {
                return false;
            }
        }

        return $site->domain;
    }


}

?>
