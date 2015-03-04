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

$base_url = Yii::app()->request->baseUrl;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0
    Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />

    <?php

    ?>
<meta name="viewport" content="width=device-width">
    <?php if (Yii::app()->user->isGuest === false) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Client/Client.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Client/Component/Selector.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Shared/component/Help.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Client/Component/MakePost.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Shared/Component/Post.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Client/Component/Post.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/css/Client/Component/SuggestionHint.css" />

        <script type="text/javascript" src="/js/resources/jquery.js"></script>
        <script type="text/javascript" src="/js/jquery_pluggins/autoresize.jquery.js"></script>
        <script type="text/javascript" src="/js/jquery_pluggins/salt.jquery-ui.js"></script>
        <script type="text/javascript" src="/js/jquery_pluggins/jquery.tokeninput.js"></script>

        <script type="text/javascript" src="/js/resources/json2.js"></script>

        <script type="text/javascript" src="/js/Shared/Library.js"></script>
        <script type="text/javascript" src="/js/Shared/Test.js"></script>
        <script type="text/javascript" src="/js/Shared/TestErrors.js"></script>
        <script type="text/javascript" src="/js/Shared/Models.js"></script>
        <script type="text/javascript" src="/js/Shared/LocalStorage.js"></script>

        <script type="text/javascript" src="/js/Client/Core/User.js"></script>
        <script type="text/javascript" src="/js/Client/Core/Controller.js"></script>
        <script type="text/javascript" src="/js/Client/Core/DomusDataTests.js"></script>
        <script type="text/javascript" src="/js/Client/Core/Ajaxurl.js"></script>
        <script type="text/javascript" src="/js/Client/Core/Interact.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Selector.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Cascade.js"></script>
        <script type="text/javascript" src="/js/Client/Component/HelpHints.js"></script>
        <script type="text/javascript" src="/js/Client/Core/Suggestion.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Messages.js"></script>
        <script type="text/javascript" src="/js/Client/Component/SuggestionMessage.js"></script>
        <script type="text/javascript" src="/js/Client/Core/LookupUser.js"></script>
        <script type="text/javascript" src="/js/Client/Core/Streams.js"></script>
        <script type="text/javascript" src="/js/Client/Component/StreamNav.js"></script>
        <script type="text/javascript" src="/js/Client/UX/Resize.js"></script>
        <script type="text/javascript" src="/js/Client/Component/MakePost.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Post.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/Arrows.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/Textbox.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/Slider.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/Button.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/Stars.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Value/List.js"></script>
        <script type="text/javascript" src="/js/Client/Component/PostsWaiting.js"></script>
        <script type="text/javascript" src="/js/Client/Post.js"></script>
        <script type="text/javascript" src="/js/Client/Component/Help.js"></script>
        <script type="text/javascript" src="/js/Client/Core/FetchMore.js"></script>
        <script type="text/javascript" src="/js/Client/ready.js"></script>
        <script type="text/javascript" src="/js/Client/Component/ReportBug.js"></script>
        <script type="text/javascript" src="/js/Client/Component/PostRings.js"></script>

        <script>
            // Create the BabblingBrook namespace
            if (typeof BabblingBrook !== 'object') {
                BabblingBrook = {};
            }
            if (typeof BabblingBrook.Client !== 'object') {
                BabblingBrook.Client = {};
            }
            BabblingBrook.Client.User = <?php $this->renderPartial('/layouts/client_user_data'); ?>
        </script>
    <?php } ?>

    <link rel="icon" type="image/png" href="/themes/store/images/brookbank-fav.png?v=1">

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

</head>

<body>

<div id="page">

    <?php if ($this->id=='site' && $this->action->id=='password') { ?>
        <div id="top_warning">
            <img src="/images/ui/domain-warning-arrow.png" />
            Always check that the domain name is same as the start of your username when you enter your password!
        </div>
    <?php } else { ?>
        <nav id="top_nav" class="alt-text">
            <?php $this->renderPartial('/layouts/store_top_nav'); ?>
        </nav>
    <?php } ?>

    <header id="header">
        <h1><a href='/' title="Brook Bank"><img src="/themes/store/images/ui/brookbank.png" /></a></h1>
    </header>

    <article id="content">
        <?php echo $content; ?>
    </article>

    <div class="footer">
        &#169; Babbling Brook Network Ltd
    </div>

</div><!-- page -->

<?php //These are html templates that are required by the default javascript classes. ?>
<?php //These are html templates that are required by the default javascript classes. ?>
<div id="templates" class="hide">

    <?php // Used to display the list of subscribed streams to the user. ?>
    <div id="stream_nav_template">
        <ul>
            <li class="more"><a>more...</a>
                <ul id="streams_more" class="hide">
                    <li class="edit-stream-subscriptions">
                        <a href="/user/streamsubscriptions">Edit&nbsp;Stream&nbsp;Subscriptions</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <?php // Used to display posts in streams. Also the default template for the DisplayPost class. ?>
    <div id="post_stream_template">
        <div class="post stream-post">
            <div class="top-value">
                <div class="field-2 field"></div>
            </div>
            <div class="title">
                <a class="field-1 field block-loading" data-post-link="true"></a>
            </div>
            <div class="info">
                <span class="username-intro">Made by</span> <a class="username"></a>.
                <time class="time-ago"></time>.
                <span class="kindred-intro">
                    Your kindred rating with <a class="username"></a> is <span class="kindred-score"></span>.
                </span>
                <span class="sort-score-intro">The sort score is <span class="sort-score"></span>.</span>
            </div>
            <div class="actions">
                <a class="link-to-post">comments</a>
                <span class="hide-post link">hide</span>
                <span class="delete link">delete</span>
                <span class="deleted hide">This post has been deleted</span>
                <span class="delete-confirm hide">
                    Confirm deletion
                    <span class="delete-confirmed link">Yes</span> /
                    <span class="delete-canceled link">No</span>
                </span>
                <span
                    class="cooldown hide"
                    title="This post will not be made public until the cooldown has finished">
                    cooldown
                </span>
                <span class="cooldown-time"></span>

                <span class="revision hide">
                    <span class="revision-title hide">Revision</span>
                    <span class="revision-content hide"></span>
                </span>
                <span class="update link hide">
                    <span class="show-update">show update</span>
                </span>
                <span class="post-loading hide"></span>
            </div>
            <div class="post-error error">
                <span class="post-error"></span>
            </div>
            <div class="new-posts-link hidden">
                <span class="link"><img src="/images/ui/add-fat.svg" />show new posts</span>
            </div>
        </div>
    </div>

    <?php // The html involved in displaying value widgets. ?>
    <div id="value_widget_templates">
        <div id="up_down_value_template">
            <div class="updown take">
                <span class="up-arrow up-waiting"></span>
                <span class="down-arrow down-waiting"></span>
            </div>
        </div>
        <div id="textbox_value_template">
            <div class="textbox take">
                <input type="text" class="text-value untaken" value="">
                <span class="error"></span>
            </div>
        </div>
        <div id="button_value_template">
            <div class="button take">
                <span class="button-value untaken">Take</span>
            </div>
        </div>
        <div id="linear_slider_value_template">
            <div class="slider take">
                <div class="linear untaken"></div>
            </div>
        </div>
        <div id="logarithmic_slider_value_template">
            <div class="slider take">
                <div class="logarithmic untaken"></div>
                <input type="text" class="value hide" value="" />
            </div>
        </div>
        <div id="stars_value_template">
            <div class="stars take">
                <div class="stars-list untaken">
                    <span class="star"></span>
                </div>
                <input type="text" class="star-value hide" value="">
            </div>
        </div>
        <div id="list_value_template">
            <div class="value-list take field">
            </div>
        </div>
        <div id="list_value_item_template">
            <input data-value="" type="checkbox" />
            <label class="list-item"></label>
        </div>
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

    <?php // Reply to an post template. ?>
    <div id="post_reply_template">
        <div class="reply-post">
            <div class="reply-content"></div>
        </div>
    </div>

    <?php // Lines in a child stream menu. Used in posts to show the list of child streams when reply is clicked ?>
    <div id="reply_line_template">
        <li class="moderation-submenu-item link"></li>
    </div>

    <?php // Template for the private section of a new post. ?>
    <div id="private_post_template">
        <div class="private-post">
            <div class="private-post-check">
                <input type="checkbox" name="private-post" value="true">
                <label>Make this post private.</label>
            </div>
            <div class="private-post-title hide">
                Send this private post to :
            </div>
            <div class="private-post-to hide"></div>
            <div class="private-post-error error hide">
                One of the users this post is being sent to is not valid.
            </div>
            <div class="private-post-waiting error hide">
                Validating usernames. Please wait.
            </div>
            <div class="private-post-none-selected error hide">
                No usernames have been entered to send the post to.
            </div>
        </div>
    </div>

    <?php // Template for the 'to' address in the private section of a new post. ?>
    <div id="private_post_to_template">
        <div class="private-post-to-line">
            <div class="delete"></div>
            <input type="text" class="address empty" value ="" />
            <span class="tick hide" title="Username is valid"></span>
            <span class="label-loading hide"></span>
            <div class="address-error error hide"></div>
        </div>
    </div>

    <?php // Content message for first private message address. ?>
    <span id="private_post_first_message">Enter the username@domain to send this post to</span>

    <?php // Content message for additional private message address. ?>
    <span id="private_post_additional_message">Enter another username@domain to send this post to</span>

    <?php // Error message if the username is not found. ?>
    <span id="private_post_username_error">Username not found</span>

    <?php // Content message for additional private message address. ?>
    <span id="private_post_domain_error">
        The Domain in this username is not a BabblingBrook domain or is not responding
    </span>

    <?php // Template for suggested addresses in the private section of a new post. ?>
    <ul class="private-post-suggestions"></ul>

    <?php // Template for a suggested addresses line in the private section of a new post. ?>
    <li class="private-post-suggestions-line"></li>

    <?php // Template for a suggested addresses line in the private section of a new post. ?>
    <div id="line_loading_template">
        <div class='line-loading'></div>
    </div>

    <?php // Template for the pop up bug post form. ?>
    <div id="bug_post_template">
        <div id="bug_post_container">
            <div id="bug_post_form" class="block-loading"></div>
        </div>
    </div>

    <?php // Template for the content to display once the bug has been submitted. ?>
    <div id="bug_submitted_template">
        Thank you for submitting a bug, view it <a id="submitted_bug_link" href="">here</a>.
    </div>

    <?php // Template for cropping the message box text. ?>
    <div id="message_crop_template">
        <div id="message_crop"></div>
    </div>

    <?php // Template for displaying snippet posts in the message box ?>
    <div id="snippet_template">
        <div class="post snippet-post">
            <div class="top-value">
                <div class="field-2 field"></div>
            </div>
            <div class="title">
                <span class="field-1 field textbox-field" data-post-link="false"></span>
            </div>
        </div>
    </div>

    <?php // Used to display a full post in the message box. ?>
    <div id="snippet_full_template">
        <div class="post child-post">
            <div class="top-value child-arrows">
                <div class="field-2 field no-label"></div>
            </div>
            <div class="info">
                <span class="username-intro">Made by </span><a class="username"></a>
            </div>
            <div class="title">
                <span class="field-1 field" data-post-link="false"></span>
            </div>
            <div class="post-content-container">
                <span class="post-content" data-show-label="true"></span>
            </div>
            <div class="actions">
                <a class="link-to-post">link</a>
            </div>
        </div>
    </div>

    <?php // Used to display a a stream suggestion in the message box. ?>
    <div id="suggestion_stream_message_template">
        <div>
            A new stream has been suggested :
            <a class="suggestion-message" href="" title=""></a>
        </div>
    </div>

    <?php // Used to display a a filter suggestion in the message box. ?>
    <div id="suggestion_filter_message_template">
        <div>
            A new filter has been suggested for the <a href="" title="" class="stream-name"></a> stream :
            <a class="suggestion-message" href="" title=""></a>
        </div>
    </div>

    <?php // Used to display a moderation ring suggestion in the message box. ?>
    <div id="suggestion_moderation_ring_message_template">
        <div>
            A new moderation ring has been suggested for the <a href="" title="" class="stream-name"></a> stream :
            <a class="suggestion-message" href="" title=""></a>
        </div>
    </div>

    <?php // Used to display a user suggestion in the message box. ?>
    <div id="suggestion_user_message_template">
        <div>
            A new user has been suggested for you :
            <a class="suggestion-message" href="" title=""></a>
        </div>
    </div>

    <?php // Used to display a full posts in the message box. ?>
    <div id="no_more_suggestions_message_template">
        <div>
            There are no more suggestions to show at the moment.
        </div>
    </div>


    <?php // Template for the pop up bug post form. ?>
    <div id="no_messages_template">
        There are no messages to display.
    </div>

    <?php // A template  for displaying cascades of posts. ?>
    <div id="cascade_template">
        <div class="cascade">
            <div class="cascade-loading">
                Loading posts...
            </div>
            <div class="cascade-new-top hide hidden">
                <span class="link">
                    <img src="/images/ui/add-fat.svg">
                    show new posts
                </span>
            </div>
            <div class="cascade-body"></div>
            <div class="cascade-no-posts hide">
                There are no posts available to display.
            </div>
            <div class="cascade-no-more-posts hide">
                There are no more posts to display.
            </div>
        </div>
    </div>

    <?php // A template dummy cascade posts. ?>
    <?php // Used as a place holder to maintain display order whilst the post data is loading. ?>
    <div id="cascade_dummy_post_template">
        <div class="cascade-dummy-post block-loading hide"></div>
    </div>

    <?php // A template for the container to display open list suggestions. ?>
    <div id="open_list_suggestions_template">
        <div class="suggestion-hints-container">
            <ul class="open-list-selected"></ul>
            <div class="open-list-multi-container">
                <ul class="open-list-suggestions hide"></ul>
                <input class="open-list-visible-input" type="text"/>
            </div>
        </div>
    </div>

    <?php // A template for a line for displaying an open list suggestion. ?>
    <div id="open_list_suggestions_line_template">
        <li>
        </li>
    </div>

    <?php // A template for a line for displaying an open list suggestion. ?>
    <div id="open_list_suggestions_selected_line_template">
        <li>
        </li>
    </div>

</div>
</body>
</html>