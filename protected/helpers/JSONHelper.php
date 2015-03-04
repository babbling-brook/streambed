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
 * A collection of JSON helper functions
 *
 * @package PHP_Helper
 */
class JSONHelper
{

    /**
     * Echos an array as a JSON string within a JSONP callback function defined by $_GET["callback"].
     *
     * @param array $json_array An array of data to be converted to a jsonP string.
     *
     * @return void
     */
    public static function makeJSONP($json_array) {
        header("Content-Type: text/javascript");
        $callback = "";
        if (isset($_GET["callback"]) === true) {
            $callback = $_GET["callback"];
        }
        echo "$callback(";
        echo JSON::encode($json_array);
        echo ");";
    }

    /**
     * Converts a Yii model error into an array with html ready to be displayed.
     *
     * @param array $error_array An array of errors from a Yii Model.
     *
     * @return array
     */
    public static function convertYiiModelError($error_array) {
        foreach ($error_array as $key => $column) {
            $html = "";
            $column_qty = count($column);
            $count = 0;
            foreach ($column as $error) {
                $count ++;
                $html .= $error;
                if ($column_qty !== $count) {
                    $html .= "<br/>";
                }
            }
            $error_array[$key] = $html;

        }
        return $error_array;
    }

    /**
     * Converts a Yii model error object into a single string.
     *
     * @param array $error_array An array of errors from a Yii Model.
     *
     * @return array
     */
    public static function convertYiiModelErrortoString($error_array) {
        $string = "";
        foreach ($error_array as $key => $column) {
            foreach ($column as $error) {
                $string .= $error . ". ";
            }

        }
        return $string;
    }

    /**
     * Updates one field of a model.
     *
     * Loads the model, applies the one field. Saves.
     *
     * @param object $model The yii model to apply the field to
     * @param string $field The name of the field model to update.
     * @param string $value The value to update the field to.
     *
     * @return boolean|string True or an error message.
     */
    public static function oneField($model, $field, $value) {

        if ($model->hasAttribute($field) === false) {
            throw new CHttpException(
                400,
                'Attempting to update a model field that does not exist : ' . $field
            );
        }
        $model->$field = $value;

        if ($model->save() === true) {
            $json_array = array(
                'success' => true,
            );
        } else {
            $json_array = array(
                'success' => false,
                'error' => JSONHelper::convertYiiModelErrortoString($model->getErrors()),
            );
        }
        return $json_array;
    }

}

?>