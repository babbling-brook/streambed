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
 * Contains information about the site.
 */
$this->pageTitle = 'About ' . Yii::app()->name;
?>

<h2>About <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h2>

<div class="content-indent blocktext">
    <p>
        Cobalt Cascade is an alpha implementation of the exciting new
        <a href="http://www.babblingbrook.net">Babbling Brook</a> protocol.
    </p>
    <p>
        Babbling Brook is a new social networking protocol that decentralises
        your data and is designed from the ground up to enable an emergently complex social order.
        Visit <a href="http://www.babblingbrook.net">babblingbrook.net</a>
        to read more about the platform and how it works.
    </p>
    <p>
        Cobalt Cascade is a testing ground for the protocol. It is designed as a tree based forum and
        link aggregation platform with some built in task management features to facilitate the
        development of the protocol.
    </p>

    <p>
        The best way to learn about the site is to sign up and follow the tutorials
        but if you want an overview, then read on.
    </p>

    <h4>Suggestions</h4>

    <p>
        Central to Babbling Brook are suggestions. These are features of the network that the system thinks
        you might be interested in.
    </p>
    <p>
        You will see suggestions for all aspects of the Babbling Brook network and are the main method
        for finding new content and users.
    </p>
    <p>
        The algorithms (computer programs) that derive the suggestions are fully controllable by the user
        (See the rhythms section below).
    </p>

    <h4>Streams</h4>

    <p>
        All social data in Babbling Brook is organised into streams.
    </p>
    <p>
        Streams contain the posts that users have made.
    </p>

    <h4>Posts</h4>

    <p>
        Users make content by creating posts.
    </p>
    <p>
        The format of the post is defined by the stream it is posted in. It might be a link, a comment, an image
        or something more complex such as a survey questionnaire.
    </p>

    <h4>Taking</h4>

    <p>
        All posts in the Babbling Brook network can be taken. On Cobalt Cascade this is represented
        primarily by voting using the up and down arrows next to a post title.
    </p>
    <p>
        There are additional ways that posts can be taken, such as stars, buttons and
        textfields. The ability to take a post can even be made conditional on an algorithm
        (see the rhythms section below).
    </p>
    <p>
        Takes lie at the heart of the Babbling Brook network. they are used to calculate relationships
        between the users, which in turn effects the content that users see.
    </p>

    <h4>Rhythms</h4>
    <p>
        Rhythms control what data you see when browsing a Babbling Brook website.
        For example, by changing a streams sort rhythm, the posts you see will be prioritised
        by that rhythm.
    </p>
    <p>
        Another important rhythm is your kindred rhythm. It decides which other users are close
        to you in the Babbling Brook network and is used by further rhythms to decide what posts
        you see in streams, which features to suggest to you and even if you are
        allowed to make or take posts in a particular stream.
    </p>
    <p>
        Rhythms will be suggested to you, but they can also be edited on
        the 'Settings' page.
    </p>
    <p>
        Anyone who can write in JavaScript can create a new rhythm,
        click on the <a href="/site/rhythms">Rhythms</a> link in the top nav bar.
        See the <a href="http://www.babblingbrook.net/page/docs">Babbling Brook documentation</a>
        for details.
    </p>

    <h4>Datastores and Users</h4>

    <p>
        Everything in the Babbling Brook network has a home domain. The domain for this site
        is cobaltcascade.net.
    </p>
    <p>
        All content in the Babbling Brook network is owned by a user and all users have a home store domain.
    </p>
    <p>
        When you are viewing some content, you can click on the creators name to go their profile.
    </p>
    <p>
        The profile page is a combination of information submitted by the user and a report of what your
        network thinks of this user.
    </p>

    <h4>Rings</h4>
    <p>
        Rings allow users to group together for specific purposes.
    </p>
    <p>
        Rings are also users and can do everything that a user can do.
    </p>
    <p>
        An important use of rings is as a moderation filter for streams. For example they are
        used as a spam filter.
    <p>
        An user can create a ring and they can be connected together in hierarchies to allow for complex relationships.
    </p>
    <p>
        Membership of rings is not always open to everyone, and you may need to apply for membership,
        however subscription to a rings filters is always open.
    </p>

    <h4>Private Posts</h4>
    <p>
        Babbling Brook is both a public and a private network. You can make private posts to other users.
    </p>
</div>