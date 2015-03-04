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
 * View for the base template for the scientia subdomain.
 */

$this->layout='blank';
$cs = Yii::app()->getClientScript();
if (Yii::app()->params['minify'] === true) {
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Minified/scientia.js' . $this->js_version_number);
} else {
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jquery.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/json2.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Library.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Test.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/TestErrors.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Models.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/LocalStorage.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Interact.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/DataConversion.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/Cache.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/Controller.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/FetchPost.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/FetchPosts.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/FetchUserTakes.js' . $this->js_version_number);
    $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Scientia/ready.js' . $this->js_version_number);
}


$user_array = array(
    "username" => Yii::app()->user->isGuest === false ? Yii::app()->user->getName() : "",
    "domain" => Yii::app()->user->isGuest === false ? Yii::app()->user->getDomain() : "",
);
$user_json = CJSON::encode($user_array);
?>
<script>
console.debug('scientia script ready : ' + new Date().getTime());

if (typeof BabblingBrook !== "object") {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Scientia !== "object") {
    BabblingBrook.Scientia = {};
}
BabblingBrook.Scientia.User = <?php echo $user_json; ?>;
BabblingBrook.Settings = <?php $this->renderPartial('/Client/Layouts/_settings'); ?>;
BabblingBrook.csfr_token = '<?php echo Yii::app()->user->getCSFRToken(); ?>';
</script>