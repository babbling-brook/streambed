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
 * Model for the site DB table.
 * The table holds the information about different BabblingBrook sites in the network.
 *
 * @package PHP_Models
 */
class Site extends CActiveRecord
{

    /**
     * The primary key for this site.
     *
     * @var integer
     */
    public $site_id;

    /**
     * The domain name for this site.
     *
     * @var string
     */
    public $domain;

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
        return '{{site}}';
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'site_access' => array(self::HAS_MANY, 'SiteAccess', 'site_id'),
            'user' => array(self::HAS_MANY, 'User', 'user_id'),
        );
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('domain', 'required'),
            array('domain', 'length', 'max' => 255),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'domain' => 'Domain',
        );
    }

    /**
     * Fetch the site_id for a domain.
     *
     * @param string $domain The domain name to fetch a site_id for.
     *
     * @return integer|false The site_id or false.
     */
    public static function getSiteId($domain) {
        $query = "
            SELECT site_id
            FROM site
            WHERE domain = :domain";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $site_id = $command->queryScalar();
        return $site_id;
    }

    /**
     * Fetch valid BabblingBrook domain suggestions based on a partial_domain.
     *
     * @param string $partial_domain The partial domain to search for suggestions on.
     *
     * @return array A list of suggested valid domains.
     */
    public static function getSuggestions($partial_domain) {
        $query = "
            SELECT domain
            FROM site
            WHERE domain LIKE :partial_domain
            ORDER BY domain
            LIMIT 10";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":partial_domain", $partial_domain . "%", PDO::PARAM_STR);
        $domain_rows = $command->queryAll();

        $domains = array();
        foreach ($domain_rows as $domain_row) {
            $domains[] = $domain_row['domain'];
        }

        return $domains;
    }

    /**
     * Gets the domain from its site_id.
     *
     * @param integer $site_id The id of the site to get.
     *
     * @return string The domain.
     */
    public static function getDomain($site_id) {
        $query = "
            SELECT domain
            FROM site
            WHERE site_id = :site_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_STR);
        $domain = $command->queryScalar();
        return $domain;
    }
}

?>