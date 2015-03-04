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
 * Helper methods that are related to images
 *
 * @package PHP_Helper
 */
class ImageHelper
{
    /**
     * Checks that
     * @param type $base64
     * @param string $local_path The path of the new image local to the images folder. Should include a trailing slash.
     * @return boolean
     */
    public static function checkPNGFromBase64StringIsValid($base64) {
        $img = @imagecreatefromstring(base64_decode($base64));
        if ($img === false) {
            return false;
        }

        $path = dirname(Yii::app()->request->scriptFile) . '/images/tmp/tmp.png';

        imagepng($img, $path);
        $info = getimagesize($path);
        unlink($path);

        if ($info[0] > 0 && $info[1] > 0 && isset($info['mime']) === true) {
            return true;
        }
        return false;
    }

    /**
     * Checks that
     * @param type $base64
     * @param string $local_path The path of the new image local to the images folder. Should include a trailing slash.
     * @return boolean
     */
    public static function createPNGFromBase64String($base64, $local_path, $file_name) {
        $img = imagecreatefromstring(base64_decode($base64));
        if ($img === false) {
            return false;
        }

        $path = dirname(Yii::app()->request->scriptFile) . '/images/' . $local_path;

        if (file_exists($path) === false) {
            mkdir($path, 0777, true);
        }

        imagepng($img, $path . $file_name);
        $info = getimagesize($path . $file_name);

        if ($info[0] > 0 && $info[1] > 0 && isset($info['mime']) === true) {
            return true;
        }

        unlink($path . $file_name);
        return false;
    }
}

?>
