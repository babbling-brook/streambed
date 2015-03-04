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
?>
<?php
if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Shared/Reset.css') === true) {
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/Shared/Reset.css" />
    <?php
} else {
    ?>
    <link rel="stylesheet" type="text/css" href="/css/Shared/Reset.css" />
    <?php
}
?>

<?php
if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Shared/Main.css') === true) {
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/Shared/Main.css" />
    <?php
} else {
    ?>
    <link rel="stylesheet" type="text/css" href="/css/Shared/Main.css" />
    <?php
}
?>

<?php
$file = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl .
    '/css/Shared/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css';
if (file_exists($file) === true) {
    $css_theme_file = Yii::app()->theme->baseUrl .
        '/css/Shared/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css';
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $css_theme_file; ?>" />
<?php } else { ?>
    <link rel="stylesheet" type="text/css"
          href="/css/Shared/Layouts/ClientType/<?php echo CLIENT_TYPE; ?>/SiteFurniture.css" />
<?php } ?>

<?php if (Yii::app()->user->isGuest === true) { ?>
    <?php
    $file = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl .
        '/css/Public/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css';
    if (file_exists($file) === true) {
        $css_theme_file = Yii::app()->theme->baseUrl .
            '/css/Public/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $css_theme_file; ?>" />
    <?php } else { ?>
        <link rel="stylesheet" type="text/css"
              href="/css/Public/Layouts/ClientType/<?php echo CLIENT_TYPE; ?>/SiteFurniture.css" />
    <?php } ?>
<?php } else { ?>
    <?php
    $file = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl .
        '/css/Client/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css';
    if (file_exists($file) === true) {
        $css_theme_file = Yii::app()->theme->baseUrl . '/css/Client/Layouts/ClientType/'
            . CLIENT_TYPE . '/SiteFurniture.css';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $css_theme_file; ?>" />
    <?php } else { ?>
        <link rel="stylesheet" type="text/css"
              href="/css/Client/Layouts/ClientType/<?php echo CLIENT_TYPE; ?>/SiteFurniture.css" />
    <?php } ?>
<?php } ?>


<?php
if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Shared/Theme.css') === true) {
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/Shared/Theme.css" />
    <?php
}
?>