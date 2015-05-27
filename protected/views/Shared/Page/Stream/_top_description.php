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
 * View for the side nav name and description of a stream.
 *
 * The following variables can be set before calling this, but they are not required.
 * $owned_by Used in the title of the stream name.
 * $title The stream title.
 * $description The stream description.
 *
 * $post_data
 * $owned_by
 * $title
 * $description
 * $rhythms
 * $current_rhythm_extra_id
 * $stream
 */

// These are only set if viewing a public page.
// Logged in users fetch this data via javascript
if (isset($owned_by) === false) {
    $owned_by = '';
}
if (isset($title) === false) {
    $title = '';
}
if (isset($description) === false) {
    $description = '';
}

$subscribe_html = "";
if (Yii::app()->user->isGuest === true) {
    if (isset($selected_rhythm['name']) === true) {
        $selected_rhythm_name = $selected_rhythm['name'];
    } else {
        $selected_rhythm_name = "popular in last hour";
    }
    $rhythm_links = '';
    $stream_major = $stream->extra->version->major;
    $stream_minor = $stream->extra->version->minor;
    $stream_patch = $stream->extra->version->patch;
    if (isset($full_stream_version) === true) {
        $stream_major = $full_stream_version['major'];
        $stream_minor = $full_stream_version['minor'];
        $stream_patch = $full_stream_version['patch'];
    }

    $selected_rhythm_description = '';
    if (isset($rhythms) === true) {
        foreach ($rhythms as $rhythm) {
            if (empty($rhythm['name']) === false) {
                $rhythm_url = '/' . $stream->user->username . '/stream/' . $stream->name . '/'
                    .$stream_major . '/' . $stream_minor . '/' . $stream_patch . '/rhythm/' .
                    $rhythm['domain'] . '/' . $rhythm['username'] . '/' . $rhythm['name'] . '/' . $rhythm['version'];
                $link = '
                     <dd>
                        <a href="' . $rhythm_url . '">' . $rhythm['name'] . '</a>
                     </dd>';
                $rhythm_links .= $link;
                if (empty($rhythm_name) === true) {
                    $rhythm_name = $rhythm['name'];
                    //$rhythm_description = $rhythm['description'];
                }
                if (intval($rhythm['rhythm_extra_id']) === $current_rhythm_extra_id) {
                    $selected_rhythm_name = $rhythm['name'];
                  //  $selected_rhythm_description = $rhythm['description'];
                }
            }
        }
    }
    $subscribe_html = '<li class="subscribe sidebar-link hide"><a href="">Subscribe</a></li>';
    $description_loading_html = '';
    $title_loading_html = '';
    $sort_bar = ''
        . '<dl id="sort_bar">'
        . ' <dt class="sorted" value="" id="sort_bar_title">' . $selected_rhythm_name . '</dt>'
        . '     <div id="sort_bar_options" class="hide">' . $rhythm_links . '</div>'
        . '</dl>';
    $meta_html = '';
} else {
    $subscribe_html = '<li class="subscribe sidebar-link hide"><a href="">Subscribe</a></li>';
    $title_loading_html = '<li class="block-loading" id="sidebar_loading_title">loading...</li>';
    $description_loading_html = '<li class="block-loading" id="sidebar_loading_description">loading...</li>';
    $sort_bar = ''
        . '<dl id="sort_bar">'
        . ' <dt class="unsorted block-loading" value="" title="Sort Rhythm">Waiting for results</dt>'
        . '</dl>';
    $meta_html = '<li class="meta-url hide sidebar-link"><a href="">Meta</a></li>';
}

$hide_title = 'hide';
if (Yii::app()->user->isGuest === true) {
    $hide_title = '';
}

$this->menu_extra = '
    <div id="sidebar" class="sidebar-top">
        <ul id="sidebar_extra">
            <li class="title ' . $hide_title . '">
                <h3 title="' . $owned_by . '"><a href="">' . $title . '</a></h3>
            </li>
            ' . $title_loading_html . '
            ' . $sort_bar . '
            ' . $description_loading_html . '
            <div id="sidebar_description_wrapper">
                <div id="sidebar_open" class="standard-button openable-button" title="Open the sidebar"></div>
                <li class="description">
                    ' . $description . '
                </li>
            </div>
            <li class="filter-details hide">
                <span class="filter-name">' . $selected_rhythm_name . '</span>
                <span class="filter-description">' . $selected_rhythm_description . '</span>
            </li>
            <li class="restricted hide">
                Submitting posts to this stream is restricted.
            </li>
            <li class="client-params hide"></li>
            ' . $subscribe_html . '
            ' . $meta_html . '
        </ul>
    </div>
';

?>