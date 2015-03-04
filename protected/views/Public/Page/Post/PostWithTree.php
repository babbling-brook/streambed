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
 * View for the viewing of posts.
 *
 * @fixme need to sort out cross domain urls for streams and profiles.
 */

$post = PostMulti::getFullPost($_GET['post_id']);
if ($post === 'Post not found') {
    throw new CHttpException(404, 'Page not found.');
    return;
}

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Post/PostWithTree'));

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Library'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ModalLogin'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Post'));

$stream_username = $post['stream']['domain'] . '/' . $post['stream']['username'];

// Hack together a stream name object as if it is fetched using models.
$stream_version = Version::splitPartialVersionString($post['stream']['version']);
$site = (object)array(
    'domain' => $post['post']['stream_domain'],
);
$user = (object)array(
    'site' => $site,
    'username' => $post['stream']['username'],
);
$extra = (object)array(
    'version' => (object)$stream_version,
);
$stream = (object)array(
    'name' => $post['stream']['name'],
    'user' => $user,
    'extra' => $extra,
);

$this->renderPartial(
    '/Shared/Page/Stream/_side_description',
    array(
        'owned_by' => 'Owned by : ' . $stream_username,
        'title' => $post['stream']['name'],
        'description' => $post['stream']['description'],
        'stream' => $stream,
    )
);

?>
<ul id="tree_root" class="content-indent">

    <?php
    $this->renderPartial(
        '/Public/Page/Post/_nestedpost',
        array(
            'type' => 'main',
            'post' => $post['post'],
            'stream' => $post['stream'],
            'child_posts' => $child_posts,
            'child_streams' => $post['child_streams'],
        )
    );
    ?>

</ul>
