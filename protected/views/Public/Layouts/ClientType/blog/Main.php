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
 * View for the main layout template.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0
    Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <meta name="viewport" content="width=device-width">

    <?php
    $this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Resize'));
    ?>

    <?php
    // this must be included before the <title> tag to ensure they appear before the page specifc css.
    $this->renderPartial('/Public/Layouts/ClientType/' . CLIENT_TYPE . '/_public_css');

    if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Client/Client.css') === true) {
        $favicon_url = Yii::app()->theme->baseUrl . '/images/favicon.png?v=' . Yii::app()->params['javascript_version'];
        ?>
        <link rel="icon" type="image/png" href="<?php echo $favicon_url; ?>" />
        <?php
    } else {
        ?>
        <link rel="icon" type="image/png"
            href="/images/favicon.png?v=<?php echo Yii::app()->params['javascript_version']; ?>" />
        <?php
    }
    ?>

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

    <div id="login-modal" class="hide">
        <div id="login_popup" class="ui-dialog">
            <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix ui-draggable-handle">
                <span id="ui-id-2" class="ui-dialog-title">Login</span>
                <button type="button"
                    class="ui-button ui-widget ui-state-default ui-corner-all
                        ui-dialog-titlebar-close ui-button-icon-only"
                    role="button" title="">
                    <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                    <span class="ui-button-text"></span>
                </button>
            </div>
            <div id="modal_content" class="login-loading" >
                <img src="/images/icons/loading.gif">
            </div>
        </div>
    </div>

<header>

    <div id="nav-corner-colour">
        <nav id="mainmenu">
            <ul id="yw0">
                <li><a href="/page/docs/index">Developers</a></li>
                <li><a href="/blog">Blog</a></li>
                <li><a href="/journal">Journal</a></li>
                <li><a href="/page/overview">Theory</a></li>
            </ul>
        </nav>
    </div>

    <div id="logo">
        <a href="/">
            <img src="/images/site-furniture/babbling-brook-logo-v5.png" id="image_logo">
        </a>
        <div>
                A new framework that enables any website to take part in a shared social networking platform
        </div>
    </div>
</header><!-- header -->

    <div id="page">

        <article id="content">
            <div id="content_page">
                <?php echo $content; ?>
            </div>
        </article>

    </div>

    </div>

</body>
</html>