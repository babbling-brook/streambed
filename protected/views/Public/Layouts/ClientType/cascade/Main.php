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
    <script src="/js/Public/Login.js<?php echo $this->js_version_number; ?>" type="text/javascript"></script>

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

    <div id="login-modal" class="login-modal-fade">
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
            <div id="modal_content">
                <?php $this->renderPartial('/Public/Page/Site/Login'); ?>
            </div>
        </div>
    </div>

    <div id="page">

        <?php
        if ($this->id === 'site' && $this->action->id === 'password'
            && $_GET['client_domain'] !== Yii::app()->params['host']
        ) { ?>
            <div id="top_warning">
                <img src="/images/ui/domain-warning-arrow.png" />
                Always check that the domain name is same as the start of your username when you enter your password!
            </div>
        <?php } else { ?>
            <nav id="top_nav" class="alt-text">
                <header id="header">
                    <a href='/' title="Cobalt Cascade"><img src="/images/ui/fade-logo-small.png" /></a>
                </header>
                <?php $this->renderPartial('/Client/Layouts/ClientType/cascade/_top_nav'); ?>
            </nav>
        <?php } ?>

        <div class="yellow_orange_bar"></div>


        <nav id="streams_nav" class="alt-text">
            <ul>
                <?php foreach (Yii::app()->params['default_subscriptions'] as $subscription) { ?>
                    <?php
                    $url = '/' . $subscription['username'] . '/stream/'
                        . str_replace(' ', '+', $subscription['name']) . '/'
                        . $subscription['version']['major'] . '/' . $subscription['version']['patch'] . '/'
                        . $subscription['version']['minor'];
                    ?>
                    <li>
                        <a href="<?php echo $url;?>" title="<?php echo $subscription['domain'];?><?php echo $url;?>">
                            <?php echo $subscription['name'];?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>

        <?php // Changes to the contents of the article tag need to be reflected in Controller.php for ajax requests ?>
        <?php
        // this is a dirty hack to correct the display of the sidebar on public stream views
        $article_stream_class = '';
        if (Yii::app()->user->isGuest === true && $this->public_stream_view === true
            && isset($_GET['loggedout']) === false
        ) {
            $article_stream_class = 'class = "article-stream"';
        }
        ?>
        <article id="content" <?php echo $article_stream_class; ?>>
            <div id="sidebar_container">
                <?php $this->renderPartial('/Client/Layouts/ClientType/cascade/_sidebar'); ?>
            </div>
            <div id="content_page">
                <?php echo $content; ?>
            </div>
        </article>

    </div>

    </div>

</body>
</html>