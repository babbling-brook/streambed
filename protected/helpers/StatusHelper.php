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
 * Helper methods for fetching status values without using the DB.
 */
class StatusHelper
{

    /**
     * Map of database status ids for quick reference.
     *
     * @var array
     */
    static protected $status = array(
        "private" => 1,
        "public" => 2,
        "deprecated" => 3,
    );

    /**
     * Map of database status descriptions for quick reference.
     *
     * @var array
     */
    static protected $description = array(
        1 => "Private",
        2 => "Public",
        3 => "Deprecated",
    );

    /**
     * Gets the id for a textual value of a status.
     *
     * These must be mapped accurately from the DB.
     * This is a helper class to prevent unnecessary DB calls.
     *
     * @param string $value The value to get an id for.
     * @param boolean $throw_error Should an error be thrown if the status is not found.
     *
     * @return integer status_id|false
     */
    public static function getID($value, $throw_error=true) {
        if (isset(self::$status[$value]) === false) {
            if ($throw_error === true) {
                throw new Exception("Status value not found : " . $value);
            } else {
                return false;
            }
        }

        return self::$status[$value];
    }

    /**
     * Fetch the value of a status id.
     *
     * @param integer $id The id to get a value for.
     * @param boolean $throw_error Should an error be thrown if the status id is not found.
     *
     * @return string
     */
    public static function getValue($id, $throw_error=true) {
        $value = array_search($id, self::$status);
        if ($value !== false) {
            return $value;
        }
        if ($throw_error === true) {
            throw new Exception("Status value does not exist : " . $id);
        } else {
            return false;
        }
    }

    /**
     * Gets the description for a status id or status value.
     *
     * These must be mapped accurately from the DB.
     * This is a helper class to prevent unnecessary DB calls.
     *
     * @param string $value The vlaue to get a description for.
     *
     * @return string|integer A status description.
     */
    public static function getDescription($value) {
        // If string is passed in the get ID
        if (is_numeric($value) === false) {
            if (isset(self::$status[$value]) === false) {
                throw new Exception("Status value not found : " . $value);
            }
            $value = self::$status[$value];
        }

        if (isset(self::$description[$value]) === false) {
            throw new Exception("Status description not found : " . $id);
        }

        return self::$description[$value];
    }

    /**
     * Return all the status descriptions.
     *
     * @return array
     */
    public static function getDescriptions() {
        return self::$description;
    }

    /**
     * Return all the status descriptions indexed by value.
     *
     * @return array
     */
    public static function getValueDescriptions() {
        $values = self::$status;
        foreach ($values as $key => $value) {
            $values[$key] = self::$description[$value];
        }
        return $values;
    }

}

?>
