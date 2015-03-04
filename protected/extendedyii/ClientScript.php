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
 * Override CClientScript so that we can access some protected variables and ovrride theme selection for css files.
 *
 * @package PHP_ExtendedYii
 */
class ClientScript extends CClientScript
{

    function registerCssFile($url, $media='') {
        if (isset(Yii::app()->theme) === true && file_exists(Yii::app()->theme->basePath . $url) === true) {
            parent::registerCssFile(Yii::app()->theme->baseUrl . $url, $media);
        } else {
            parent::registerCssFile($url, $media);
        }
        return $this;
    }


    //protected $_coreScripts;

    /**
     * Fetch the css Files.
     *
     * @return array
     */
    public function getCSSfiles() {
        return $this->cssFiles;
    }

    /**
     * Fetch the javascript files.
     *
     * @return array
     */
    public function getScriptfiles() {
        return $this->scriptFiles;
    }

    /**
     * Get the inline javascript.
     *
     * @return array
     */
    public function getScripts() {
        return $this->scripts;
    }
}

?>