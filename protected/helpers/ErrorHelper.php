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
 * Helper methods that are related to errors
 *
 * @package PHP_Helper
 */
class ErrorHelper
{

    /**
     * Returns a string of all errors in a Yii model
     *
     * @param array $errors Result of calling $model->getErrors().
     *
     * @return string
     */
    static public function model($errors, $seperator=". ") {
        $error_string = "";
        foreach ($errors as $error) {
            foreach ($error as $err) {
                $error_string .= $err . $seperator;
            }
        }
        return $error_string;
    }

    /**
     * Returns a string of all errors in an array
     *
     * @param array $errors Result of calling $model->getErrors().
     *
     * @return string
     */
    static public function ary($errors, $seperator=", ") {
        $error_string = "";
        foreach ($errors as $error) {
            $error_string .= $error . $seperator;
        }
        return $error_string;
    }

}

?>