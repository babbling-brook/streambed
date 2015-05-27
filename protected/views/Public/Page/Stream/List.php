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

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Stream/Stream'));

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Library'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Stream'));

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

    <input type="hidden" value="false" id="image_stream" />

    <div id="make_post" class="content-block-2">
        <input class="make-post" type="text" value="Write something..." />
    </div>

        <?php if (empty($posts) === true) { ?>
            <div id="no_posts" class="ba">No posts to display.</div>
        <?php } else { ?>
        <div id="stream_container">
                <?php foreach ($posts as $post) { ?>
                    <div class="post stream-post" data-post-id="<?php echo $post['post_id']; ?>">
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
</div>

