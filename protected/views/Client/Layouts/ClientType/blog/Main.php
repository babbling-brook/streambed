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
/**
 * View for the main layout template.
 */

$base_url = Yii::app()->request->baseUrl;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <link href="//fonts.googleapis.com/css?family=Noto+Sans:400italic,400,700italic,700"
          rel="stylesheet" type="text/css">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <meta name="viewport" content="width=device-width">

    <?php
    // this must be included before the <title> tag to ensure they appear before the page specifc css.
    if (Yii::app()->params['minify'] === true) {
        ?>
            <link rel="stylesheet" type="text/css" href="/css/Minified/Client/css.css" />
        <?php
    } else {
        $this->renderPartial('/Shared/Layouts/_all_types_css');
        $this->renderPartial('/Client/Layouts/_all_component_css');
        $client_css_path = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Client/Client.css';
        if (file_exists($client_css_path) === true) {
            ?>
            <link rel="stylesheet" type="text/css"
                  href="<?php echo Yii::app()->theme->baseUrl; ?>/css/Client/Client.css" />
            <?php
        } else {
            ?>
            <link rel="stylesheet" type="text/css" href="/css/Client/Client.css" />
            <?php
        }
        if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/Theme.css') === true) {
            ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Theme.css" />
            <?php
        } ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Libraries/jquery.token-input.css" />
        <?php
    }

    $this->renderPartial('/Client/Layouts/_create_namespace');

    if (Yii::app()->params['minify'] === true) {
        ?>
        <script type="text/javascript" src="/js/Minified/client.js"></script>
        <script type="text/javascript" src="/js/resources/ckeditor/release/ckeditor.js"></script>
        <?php
    } else {
        ?>
        <script type="text/javascript" src="/js/resources/jquery.js"></script>
        <script type="text/javascript" src="/js/resources/json2.js"></script>
        <script type="text/javascript" src="/js/jquery_pluggins/jquery-ui.js"></script>
        <?php $this->renderPartial('/Client/Layouts/_all_core_and_component_js'); ?>
        <script type="text/javascript" src="/js/jquery_pluggins/autoresize.jquery.js"></script>
         <script type="text/javascript" src="/js/resources/ckeditor/release/ckeditor.js"></script>
        <?php
    }



    if (file_exists(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/images/favicon.png') === true) {
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

    <div id="bug_form_placement"></div>
    <div id="tutorial_placement"></div>

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

    <?php if (Yii::app()->user->isAdmin() === true) { ?>
        <nav id="admin_nav" class="alt-text">
            <?php $this->renderPartial('/Client/Layouts/_admin_nav'); ?>
        </nav>
    <?php } ?>

    <div class="yellow_orange_bar"></div>


    <nav id="streams_nav" class="alt-text">
        <?php if (Yii::app()->user->isGuest === true) { ?>
            <ul>
                <li>
                    <a href="/sky/stream/news/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/news/0/0/0">
                        news
                    </a>
                </li>
                <li>
                    <a href="/sky/stream/tasks/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/tasks/0/0/0">
                        tasks
                    </a>
                </li>
                <li>
                    <a href="/sky/stream/bugs/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/bugs/0/0/0">
                        bugs
                    </a>
                </li>
                <li>
                    <a href="/sky/stream/feature+requests/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/feature requests/0/0/0">
                        feature requests
                    </a>
                </li>
                <li>
                    <a href="/sky/stream/babbling+brook/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/babbling brook/0/0/0">
                        babbling brook
                    </a>
                </li>
                <li>
                    <a href="/sky/stream/cobalt+meta/0/0/0"
                       title="cobaltcascade.localhost/sky/stream/posts/cobalt meta/0/0/0">
                        cobalt meta
                    </a>
                </li>
            </ul>
        <?php } ?>
    </nav>

    <?php // Any changes to the contents of the article tag need to be reflected in Controller.php for ajax requests ?>
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

</div><!-- page -->

<?php //These are javascript templates that are required by the site furniture ?>
<div id="templates" class="hide">

    <?php // Used to display the list of subscribed streams to the user. ?>
    <div id="stream_nav_template">
        <ul>
            <li id="subscriptions_loading">
                <span class="text-loading">loading stream subscriptions</span>
            </li>
            <li class="more hide"><a>more...</a>
                <ul id="streams_more" class="hide">
                    <li class="edit-stream-subscriptions">
                        <a href="/*user*/streamsubscriptions">Edit&nbsp;Stream&nbsp;Subscriptions</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <?php // Template for items in the users stream nav. ?>
    <div id="stream_nav_item_template">
        <li>
            <a></a>
        </li>
    </div>

    <?php // Template for items in the users stream 'more...' nav. ?>
    <div id="stream_nav_more_item_template">
        <li>
            <a></a>
        </li>
    </div>

    <?php // A generic message for throwing an error ?>
    <div id="thread_execution_stopped_template">
        Thread execution stopped
    </div>

    <div id="client_data_error_template">
        Your user data is not loading. It might be just a timeout or your data store could be offline.
        Try again.
        If this problem persists for several days then something drastic has
        gone wrong and you will need to reset your account.
    </div>

    <div id="client_data_error_confirm_template">
        Are you sure you want to reset your account. Your config settings will reset to defaults.
        (You will not loose any posts, stream, rings or rhythms, subscriptions, tutorial progress that you hae made.)
    </div>



    <div id="client_data_new_option_template">
        An error occurred when adding a new configuration option to your account. Please reload the page.
    </div>


    <?php $this->renderPartial('/Client/Layouts/_all_component_templates'); ?>
    <?php $this->renderPartial('/Client/Core/core_templates'); ?>
</div>

</body>
</html>