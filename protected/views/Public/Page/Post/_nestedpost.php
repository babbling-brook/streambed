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
 * A view to inlude on public pages that need to display a post.
 * Used in public pages to prompt a login.
 *
 * Paramaters that are required to be in scope:
 * @param string $type The type of post to display. Valid values are 'main' and 'tree'
 * @param object $post An post_object.
 * @param object $stream The stream that the post was made with.
 * @param object $child_posts The child posts for this post.
 */

$stream_url = "/" . $stream['username'] . '/stream/posts/'
    . $stream['name'] . "/" . $stream['version'];
$post_username = $post['domain'] . '/' . $post['username'];
$post_url = '/postwithtree/' . $post['domain'] . "/" . $post['post_id'];
$post_time = date('l \t\h\e jS \o\f F Y \a\t H:i:s \G\M\T', $post['timestamp']);
$post_time_ago = DateHelper::timeSince($post['timestamp']);
$title = "";
$title_link = $post_url;
$show_thumbnail = false;
if (isset($post['title']) === true) {
    $title = $post['title'];
} else if (empty($post['content']) === false && isset($post['content'][1]['text']) === true) {
    $title = $post['content'][1]['text'];
} else if (empty($post['content']) === false && isset($post['content'][1]['link_title']) === true) {
    $title = $post['content'][1]['link_title'];
    $title_link = $post['content'][1]['link'];
    $show_thumbnail = true;
}

?>
<li class="post">
    <?php if ($type === 'main') { ?>
        <?php if ($show_thumbnail === true) { ?>
            <?php $top_post_content_class = 'top-post-content'; ?>
            <div class="top-post-image">
                <img src="/images/user/<?php echo $post['domain']; ?>/<?php echo $post['username']; ?>/post/thumbnails/large-proportional/<?php echo $post['post_id']; ?>/1.png" />
            </div>
        <?php } else {
            $top_post_content_class = '';
        }?>
        <div class="<?php echo $top_post_content_class; ?> content-block-2">
            <div class="top-value">
                <div class="field-2 field updown take">
                    <span class="up-arrow up-untaken"></span>
                    <span class="down-arrow down-untaken"></span>
                </div>
            </div>
            <?php $title_class='title'; ?>
            <?php if ((int)$stream['fields'][1]['max_size'] > 200) { ?>
                <?php $title_class = 'readable-text'; ?>
            <?php }?>
            <div class="first-row <?php echo $title_class; ?>">
                <?php  if ($title_class === 'title') { ?>
                <a href="<?php echo $title_link; ?>">
                <?php } ?>
                    <?php echo $title; ?>
                <?php  if ($title_class === 'title') { ?>
                </a>
                <?php } ?>
            </div>
            <div class="info">
                <?php // @fixme $post_username is only for users on this site. needs converting to work cross domain. ?>
                Made by <a title="<?php echo $post_username; ?>" href="/<?php echo $post['username']; ?>" class="user">
                    <?php echo $post['username']; ?>
                </a>
                <span class="time" title="<?php echo $post_time; ?>"><?php echo $post_time_ago; ?> ago</span>
            </div>
        <?php // the top-post-content div is closed below. ?>
    <?php } else if ($type === 'tree') { ?>
        <div class="top-value">
            <div class="field-2 field updown take">
                <span class="up-arrow up-untaken"></span>
                <span class="down-arrow down-untaken"></span>
            </div>
        </div>
        <div class="info">
            <span class="switch"></span>
            Made by <a title="<?php echo $post_username; ?>" href="/<?php echo $post['username']; ?>" class="user">
                <?php echo $post['username']; ?>
            </a>
            <span class="time" title="<?php echo $post_time; ?>"><?php echo $post_time_ago; ?> ago</span>
        </div>
        <div class="title">
            <span class="field-1 field textbox-field" href="<?php echo $title_link; ?>">
                <?php echo $title; ?>
            </span>
        </div>
    <?php } ?>

    <div class="post-content-container">
        <?php
        foreach ($stream['fields'] as $key => $field) {

            // The key is one based rather than zero, and the first row is null.
            // The first text field row is already taken care of above
            // The first value field row is already taken care of above
            if ($key === 0 || $key === 1 || $key === 2) {
                continue;
            }

            $content_row = array();
            if (isset($post['content'][$key]) === true) {
                $content_row = $post['content'][$key];
            }
            $content = '';
            $field_class = "";
            $label = $field['label'];

            switch($field['type']) {
                case "textbox":
                    $field_class = 'textbox-field';
                    $content = $content_row['text'];
                    $content = nl2br($content);
                    break;

                case "link":
                    $field_class = 'field-type-link';
                    $content = '<a class="post-field-link" href="' . $content_row['link'] . '">'
                        . $content_row['link_title'] . '</a>';
                    break;

                case "checkbox":
                    $field_class = 'field-type-checkbox';
                    $content = ($content_row['checked'] === true) ? 'Yes' : 'No';
                    break;

                case "list":
                    $field_class = 'field-type-list';
                    $content = '<ul class="post-field-list">';
                    foreach ($content_row['selected'] as $list_item) {
                        $content .= '<li>' . $list_item . '</li>';
                    }
                    $content .= '</ul>';
                    break;

                case "openlist":
                    $field_class = 'field-type-openlist';
                    $content = '<ul class="post-field-list">';
                    foreach ($content_row['selected'] as $list_item) {
                        $content .= '<li>' . $list_item . '</li>';
                    }
                    $content .= '</ul>';
                    break;

                case "value":
                    switch($field['value_type']) {
                        case "textbox":    // textbox with link to signup/login
                            $field_class = 'textbox take';
                            $content = '<input class="text-value untaken" type="text" value="0">';
                            break;

                        case "updown":
                            $content = '<div field_id="8" class="updown take" title="0">'
                                . '<span class="up-arrow up-untaken"></span>'
                                . '<span class="down-arrow down-untaken"></span>'
                                . '</div>';
                            break;

                        case "linear":
                            $content = '<div class="take">'
                                . ' <div class="linear untaken ui-slider ui-slider-horizontal ui-widget'
                                . 'ui-widget-content ui-corner-all">'
                                . '<a href="#" class="ui-slider-handle ui-state-default ui-corner-all"></a>'
                                . '</div>'
                                . '</div>';
                            break;

                        case "logarithmic":
                            $content = '<div class="take">'
                                . ' <div class="linear untaken ui-slider ui-slider-horizontal ui-widget'
                                . 'ui-widget-content ui-corner-all">'
                                . '<a href="#" class="ui-slider-handle ui-state-default ui-corner-all"></a>'
                                . '</div>'
                                . '</div>';
                            break;

                        case "list":
                            $content = '<div>Login to view status</div>';
                            // @task See http://cobaltcascade.net/postwithtree/cobaltcascade.net/10219
        //                    $content = '<div class="value-list take field untaken ui-buttonset">';
        //                    foreach($field['value_list'] as $list_item) {
        //                        $content .= '<div>' . $list_item['name'] . '</div>';
        //                    }
        //                    $content .= '</div>';
                            break;

                        case "button":
                            $content = '<div class="take"><span class="button-value untaken">Take</span></div>';
                            break;

                        case "stars":
                            $content = '<div class="stars untaken">';
                            $star_qty = $field['value_max'];
                            // Get the post defined max value if it exists.
                            if (isset($post['content'][$key]) === true
                                && isset($post['content'][$key]['value_max']) === true
                            ) {
                                $star_qty = $post['content'][$key]['value_max'];
                            }
                            for ($i = 0; $i < $star_qty; $i++) {
                                $content .= '<span class="star" star_id="' . $i . '"></span>';
                            }
                            $content .= '</div>';
                            break;

                        default:
                            throw new Exception("A value type in a post field is not found : " . $field['value_type']);
                    }
                    break;

                default:
                    throw new Exception("Post field type not found : " . $field['type']);
            }
            if ($field['type'] === 'textbox' && $content === '') {
                continue;
            }

            $field_div = '<span class="field-label field-label-' . $key . '">' . $label . '</span>';
            $field_div .= '<div class="field-' . $key . ' field ' . $field_class . '">' . $content . '</div>';

            echo $field_div;
        }
        ?>
    </div>

    <div class="actions">
        <a class="link-to-post" href="<?php echo $title_link; ?>">link</a>
        <?php
        if (isset($parent_post) === true) {
            $parent_url = '/postwithtree/' . $parent_post['domain'] . "/" . $parent_post['post_id'];
            ?>
            <a class="link" href="<?php echo $parent_url; ?>">parent</a>
            <?php
        }
        ?>
        <a class="reply link">reply</a>
    </div>

    <?php if ($type === 'main') { ?>
        <?php // closes the top-post-content div ?>
        </div>
    <?php } ?>

    <ul class="children post">
        <?php
        foreach ($child_posts as $child_post) {

            foreach ($child_streams as $child_stream) {
                if ($child_stream['local_id'] === $child_post['stream_extra_id']) {
                    $stream = $child_stream;
                }
            }
            if (isset($stream) === false) {
                throw new Exception("Child stream not found.");
            }

            $this->renderPartial(
                '/Public/Page/Post/_nestedpost',
                array(
                    'type' => 'tree',
                    'post' => $child_post,
                    'stream' => $stream,
                    'child_posts' => $child_post['children'],
                    'child_streams' => $child_streams,
                    'parent_post' => array(
                        'domain' => $post['domain'],
                        'post_id' => $post['post_id'],
                    )
                )
            );
            echo ('</li>');
        }
        ?>
    </ul>

</li>