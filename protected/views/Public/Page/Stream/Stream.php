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
 * View for post pages.
 */

if (isset($posts[0]['link']) === true) {

}

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Stream/Stream'));

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Library'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Stream'));
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(
    Yii::app()->baseUrl . '/js/Minified/resources/jquery.justifiedGallery.min.js' . $this->js_version_number
);
$cs->registerCssFile(Yii::app()->baseUrl . '/css/Minified/Libraries/justifiedGallery.min.css');

$this->renderPartial(
    '/Shared/Page/Stream/_side_description',
    array(
        'owned_by' => 'Owned by : ' . $stream->user->site->domain . '/' . $stream->user->username,
        'title' => $stream->name,
        'description' => $stream->extra->description,
        'rhythms' => $rhythms,
        'current_rhythm_extra_id' => $current_rhythm_extra_id,
        'stream' => $stream,
        'selected_rhythm' => $selected_rhythm,
        'full_stream_version' => $full_stream_version,
    )
);

?>

<div class="content-indent">

    <?php if (isset($_GET['loggedout']) === true && $_GET['loggedout'] === 'true') { ?>
    <div class="content-block-2 error"><span class="larger">You have logged out</span></div>
    <?php } ?>


    <?php $hidden_posts_class = ""; ?>
    <?php if (isset($posts[0]['link']) === true) { ?>
        <?php $hidden_posts_class = "hidden"; ?>
        <input type="hidden" value="true" id="image_stream" />
    <?php } else { ?>
        <input type="hidden" value="false" id="image_stream" />
    <?php } ?>


    <div id="make_post" class="content-block-2">
        <input class="make-post" type="text" value="Write something..." />
    </div>

        <?php if (empty($posts) === true) { ?>
            <div id="no_posts" class="ba">No posts to display.</div>
        <?php } else { ?>

            <div id="posts" class="<?php echo $hidden_posts_class; ?>">

            <?php if (isset($posts[0]['link']) === true) { ?>
                <script type="text/javascript" src="/js/Minified/resources/jquery.min.js"></script>
                <script type="text/javascript" src="/js/Minified/resources/jquery.justifiedGallery.min.js"></script>
                <link rel="stylesheet" type="text/css" href="/css/Minified/Libraries/justifiedGallery.min.css" />


                <?php foreach ($posts as $post) { ?>
                    <?php
                    $thumbnail = '/images/user/' . HOST . '/' . $post['username'] . '/post/thumbnails/large-proportional/' . $post['post_id'] . '/1.png';
                    if (file_exists(realpath(Yii::app()->basePath . "/../") . $thumbnail) === false) {
                        //$thumbnail = '';
                        continue;
                    };
                    ?>
                    <div>
                        <a href="<?php echo $post['link']; ?>">
                            <img class="stream-image" title="<?php echo $post['link_title']; ?>" src="<?php echo $thumbnail; ?>" />
                        </a>
                        <div class=caption stream-image-links">

                            <div class="top-value">
                                <div class="field-2 field updown take">
                                    <span class="up-arrow up-untaken"></span>
                                    <span class="down-arrow down-untaken"></span>
                                </div>
                            </div>

                            <div class="title">
                                <?php
                                $link = "/postwithtree/" . $stream->user->site->domain . "/" . $post['post_id'];
                                $comment_link = $link;
                                if ($post['link'] !== null) {
                                    $link = $post['link'];
                                }
                                ?>
                                <a class="field-1 fiels textbox-field" href="<?php echo $link; ?>">
                                    <?php
                                    if ($post['text'] !== null) {
                                        echo $post['text'];
                                    } else if ($post['link_title'] !== null) {
                                        echo $post['link_title'];
                                    }
                                    ?>
                                </a>
                            </div>
                        <div class="info">
                            <?php $full_username = $post['user_domain'] . "/" . $post['username']; ?>
                            Posted by
                            <?php $made_by = 'Made by :' . $post['user_domain'] . "/" . $post['username']; ?>
                            <a class="username" href="/<?php echo $post['username'];?>" title="<?php echo $made_by; ?>">
                                <?php echo $post['username']; ?>
                            </a>
                            <?php
                            $timestamp = strtotime($post['date_created']);
                            $post_time_ago = DateHelper::timeSince($timestamp);
                            $full_date = date('l \t\h\e jS \o\f F Y \a\t H:i:s \G\M\T', $timestamp)
                            ?>
                            <time class="time-ago" title="Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)">
                                <?php echo $post_time_ago; ?> ago.
                            </time>
                            <a class="link-to-post" href="<?php echo $comment_link; ?>">
                                comments&nbsp;(<?php echo $post['child_count']; ?>)
                            </a>
                        </div>

                        </div>
                    </div>
                <?php } ?>

            <?php } else { ?>

                <?php $size = 'large'; ?>
                <?php $large_thumbnail = 'large-thumbnail'; ?>
                <?php foreach ($posts as $post) { ?>
                    <?php
                    $thumbnail = '/images/user/' . HOST . '/' . $post['username'] . '/post/thumbnails/' . $size . '/' . $post['post_id'] . '/1.png';
                    $size = 'small';
                    if (file_exists(realpath(Yii::app()->basePath . "/../") . $thumbnail) === false) {
                        $thumbnail = '';
                    };
                    ?>


                    <div class="post stream-post" data-post-id="<?php echo $post['post_id']; ?>">
                        <div class="top-value">
                            <div class="field-2 field updown take">
                                <span class="up-arrow up-untaken"></span>
                                <span class="down-arrow down-untaken"></span>
                            </div>
                        </div>
                        <?php if (empty($thumbnail) === false) { ?>
                            <a href="<?php echo $post['link']; ?>" target="_blank" class="post-thumbnail-container <?php echo $large_thumbnail; ?>">
                                <img src="http://<?php echo HOST . $thumbnail; ?>" class="post-thumbnail">
                            </a>
                            <?php $large_thumbnail= '';?>
                        <?php } ?>
                        <div class="title">
                            <?php
                            $link = "/postwithtree/" . $stream->user->site->domain . "/" . $post['post_id'];
                            $comment_link = $link;
                            if ($post['link'] !== null) {
                                $link = $post['link'];
                            }
                            ?>
                            <a class="field-1 fiels textbox-field" href="<?php echo $link; ?>">
                                <?php
                                if ($post['text'] !== null) {
                                    echo $post['text'];
                                } else if ($post['link_title'] !== null) {
                                    echo $post['link_title'];
                                }
                                ?>
                            </a>
                        </div>
                        <div class="info">
                            <?php $full_username = $post['user_domain'] . "/" . $post['username']; ?>
                            Made by
                            <?php $made_by = 'Made by :' . $post['user_domain'] . "/" . $post['username']; ?>
                            <a class="username" href="/<?php echo $post['username'];?>" title="<?php echo $made_by; ?>">
                                <?php echo $post['username']; ?>
                            </a>
                            <?php
                            $timestamp = strtotime($post['date_created']);
                            $post_time_ago = DateHelper::timeSince($timestamp);
                            $full_date = date('l \t\h\e jS \o\f F Y \a\t H:i:s \G\M\T', $timestamp)
                            ?>
                            <time class="time-ago" title="Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)">
                                <?php echo $post_time_ago; ?> ago
                            </time>
                        </div>
                        <div class="actions">
                            <a class="link-to-post" href="<?php echo $comment_link; ?>">
                                comments (<?php echo $post['child_count']; ?>)
                            </a>
                        </div>
                    </div>
                <?php } ?>

            </div>

        <?php } ?>

    <?php } ?>
</div>

