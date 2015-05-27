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
/*
 * Templates for the tutorial messages.
 */
?>

<ul id="suggestions_top_nav_template">
    <li id="suggestions_link">
        <a href="/">
            Suggestions
        <span id="suggestion_count" class=""></span>
        </a>
    </li>
</ul>

<ul id="bugs_top_nav_template">
    <li id="small_bug">
        <span href="/">
            Report Bug
        </span>
    </li>
</ul>

<ul id="posts_top_nav_template">
    <li id="posts_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>/post">
            Posts
            <span class="" id="message_count"></span>
        </a>
    </li>
</ul>

<ul id="profile_top_nav_template">
    <li id="profile_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>">Profile</a>
    </li>
</ul>

<ul id="streams_top_nav_template">
    <li id="streams_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>/streams">Streams</a>
    </li>
</ul>

<ul id="rings_top_nav_template">
    <li id="rings_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>/ring/index">Rings</a>
    </li>
</ul>

<ul id="rhythms_top_nav_template">
    <li id="rhythms_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>/rhythms">Rhythms</a>
    </li>
</ul>


<ul id="settings_top_nav_template">
    <li id="settings_top_nav_link">
        <a href="/<?php echo Yii::app()->user->getName(); ?>/settings">Settings</a>
    </li>
</ul>

<div id="level_0_title_template">
    Welcome to Cobalt Cascade
</div>

<div id="level_0_template">
    <div>
        <p>
            Would you like to take the tutorial?
        </p>
        <button class='standard-button' id='yes_tutorial'>Yes, lead the way</button>
        <button class='standard-button' id='no_tutorial'>No, I'm good</button>
    </div>
</div>

<div id="tutorial_location_template">
    <div>
        <p>

        </p>
        <button class='standard-button' id='yes_tutorial'>Yes, lead the way</button>
        <button class='standard-button' id='no_tutorial'>No, I'm good</button>
    </div>
</div>


<div id="turn_off_tutorials_button_template">
    <button class='standard-button extra-margin-top' id='turn_off_tutorials'>Turn the tutorial off</button>
</div>

<div id="turn_off_title_template">
    Disabling Tutorials
</div>

<div id="turn_off_template">
    <div>
        <p>
            You can change your mind at any point by clicking on the tutorials link at the top of the page.
        </p>
        <button class='standard-button extra-bottom-margin' id='close_tutorial'>Ok, got it.</button>
        <button class='standard-button' id='restart_tutorial'>I've changed my mind. Turn it back on</button>
    </div>
</div>

<div id="level_completed_title_template">
    Well done!
</div>

<div id="level_completed_details_template">
    <div>
        <p>
            You have completed your quest.
        </p>
        <button class='standard-button extra-bottom-margin' id='next_quest'>Take me to the next quest</button>
        <button class='standard-button' id='wait_here'>I'll start the next quest later</button>
    </div>
</div>

<div id="wait_here_title_template">
    Take your time
</div>

<div id="wait_here_details_template">
    <div>
        <p>
            When you are ready, click on the tutorial link at the top of the page to continue.
        </p>
        <button class='standard-button extra-bottom-margin' id='wait_here_ok'>OK</button>
        <button class='standard-button extra-bottom-margin' id='turn_off_tutorials'>Turn tutorials off</button>
    </div>
</div>


<div id="level_READ_POSTS_title_template">
    Reading Posts Tutorial
</div>

<div id="level_READ_POSTS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            This is the first in a series of quests that will help you find your way.
            If you loose track of where you are, you can click on the 'Tutorials' link at the top of the page
            to be reminded.
        </p>
        <p>
            Clicking the thumbnail will show you a larger image.
        </p>
        <p>
            Clicking the title will take you to the original.
        </p>
        <p>
            Your task is to click on at least three photo thumbnails or titles to take a closer look.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_VOTE_POSTS_title_template">
    Voting On Posts Tutorial
</div>

<div id="level_VOTE_POSTS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            You have unlocked the power to vote on what you do and don't like.
        </p>
        <p>
            Upvote at least three photos that you find beautiful, downvote at least one that you don't.
        </p>
        <p>
            Every vote you make creates a connection between you,
            the user who made the post and other users who upvoted it.
            This in turn effects the posts that you will see in the future.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_STREAM_NAV_title_template">
    Stream Navigation Tutorial
</div>

<div id="level_STREAM_NAV_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            You have unlocked the ability to view different streams.
        </p>
        <p>
            There are many different streams of posts. The streams you are subscribed to are listed in the paler blue
            bar at the top of the stream.
        </p>
        <p>
            Visit one of the other streams in the stream bar to progress.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_READ_COMMENTS_title_template">
    Reading Comments Tutorial
</div>

<div id="level_READ_COMMENTS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            You have unlocked the power to read comments.
        </p>
        <p>
            Each post now has a comments link. Click on a comments link and check out what people think of it.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_VOTE_COMMENTS_title_template">
    Voting On Comments Tutorial
</div>

<div id="level_VOTE_COMMENTS_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            Comments can also be voted on to create connections with the users who made the comments.
        </p>
        <p>
            Vote on three comments.
        </p>
        <button class='standard-button' id='close_tutorial'>OK, let me at 'em.</button>
    </div>
</div>

<div id="level_MAKE_COMMENT_title_template">
    Making A Comment Tutorial
</div>

<div id="level_MAKE_COMMENT_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            You can now make your own comments.
        </p>
        <p>
            Say something nice, or not.
        </p>
        <p>
            Alternatively you could reply to someone else's comment.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_EDIT_COMMENT_title_template">
    Editing A Comment Tutorial
</div>

<div id="level_EDIT_COMMENT_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            Now you have a comment it can be edited or deleted.
        </p>
        <p>
            Your comment isn't published until the cooldown has finished. If you delete it before the cooldown has
            finished then no one will ever see your comment.
        </p>
        <p>
            If you delete it after the cooldown then it will become invisible to most people.
            However it will remain visible
            to people who have commented or voted on it.
            Think of comments and posts like letters or emails. You can't make
            someone unsee what you have shown them, but you can stop showing new people.
        </p>
        <p>
            Your next quest is to edit a comment you have made.
        </p>
        <button class='standard-button' id='close_tutorial'>OK, let me at 'em.</button>
    </div>
</div>

<div id="level_LINK_COMMENTS_title_template">
    Linking To A Comment Tutorial
</div>

<div id="level_LINK_COMMENTS_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            All comments are also posts with their own page.
            You can click on the 'link' button under the comment to go there.
        </p>
        <p>
            This allows you to link to any comment.
        </p>
        <p>
            Your next quest is to follow a comment link.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_MAKE_SELF_POST_title_template">
    Making A Post In A Stream Tutorial
</div>

<div id="level_MAKE_SELF_POST_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/favourite+places/latest/latest/latest"></div>
        <p>
            You can make your own posts in a stream.
        </p>
        <p>
            Simply click in the textbox at the top of a stream and start typing.
        </p>
        <p>
            Your quest is to write a post in the favourite places stream.
        </p>
        <p>
            A hill you enjoy walking on. A city you treasure. A pub you relax in.
        </p>
        <p>
            Wherever it is, write a few words about a place you really love.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_STREAM_SORT_title_template">
    Sorting Streams Tutorial
</div>

<div id="level_STREAM_SORT_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            So far everything you have seen can be found on other social networks. It is time to start introducing
            some new features.
        </p>
        <p>
            When you load a stream or comments page, the results are sorted for you by an algorithm.
            On most social networks these are fixed in place and you have little control over them.
            In Babbling Brook websites like Cobalt Cascade, you can choose from many algorithms and if
            you can code then you can write your own in JavaScript.
        </p>
        <p>
            In Babbling Brook these algorithms are called rhythms. There are many different kinds of rhythm but today
            we are going to look at filter rhythms.
            These rhythms decide on the order that posts should be displayed.
        </p>
        <p>
            The <em>beautiful</em> stream is currently sorted randomly.
            A new dropdown menu has appeared to the right of the stream.
            Your quest is to select a different Sort Rhythm.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_SUGGESTION_MESSAGES_title_template">
    Suggestions Tutorial
</div>

<div id="level_SUGGESTION_MESSAGES_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            There are two ways in which you find new content in the Babbling Brook network.
        </p>
        <p>
            The first, and the subject of this tutorial is through <em>suggestions</em>.
            (The second is via searching and will be explored in a later tutorial.)
        </p>
        <p>
            Suggestions are another kind of rhythm, only rather than sort posts for you, they seek out new content
            that you might be interested in. There are many different kinds of suggestion.
            They can introduce new streams, users, filter rhythms,
            other websites in the Babbling Brook network and much more.
        </p>
        <p>
            Today we are going to look at a suggestion for a new stream that you might be interested in.
        </p>
        <p>
            A new 'Suggestions' link has appeared at the top of the page. The number after the link indicates the
            number of suggestions that are waiting for you to check out.
        </p>
        <p>
            Your task is to click on the suggestions link, view the suggested stream and then accept the
            suggestion.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_SUGGESTION_MESSAGES_stream_nav_template">
    <li>
        <a title="" href=""></a>
    </li>
</div>

<div id="level_SUGGESTION_MESSAGES_declined_template">
    Normally clicking on the 'No Thanks' button would cause the suggestion to go away. However you need to click the
    'Subscribe' button to finish the tutorial so we'll leave it here.
    <br />
    <button id="suggestion_tutorial_ok" class="standard-button extra-margin-top">OK</button>
</div>

<div id="level_SUGGESTION_MESSAGES_notnow_template">
    Normally clicking on the 'Not Now' button would cause the suggestion to go away for a while so you can look at it
    again later. However you need to click the
    'Subscribe' button to finish the tutorial so we'll leave it here.
    <br />
    <button id="suggestion_tutorial_ok" class="standard-button extra-margin-top">OK</button>
</div>

<div id="level_SUBSCRIBE_LINK_title_template">
    Subscribe From The Sidebar
</div>

<div id="level_SUBSCRIBE_LINK_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/babbling+brook/latest/latest/latest"></div>
        <p>
           It is also possible to subscribe and unsubscribe from a stream in the sidebar.
        </p>
        <p>
           This stream is for discussing Babbling Brook.
        </p>
        <p>
           Your task is to click the 'subscribe' link.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_EDIT_SUBSCRIPTIONS_LINK_title_template">
    Edit Subscriptions
</div>

<div id="level_EDIT_SUBSCRIPTIONS_LINK_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/babbling+brook/latest/latest/latest"></div>
        <p>
           Your real stream subscriptions have now been turned on.
        </p>
        <p>
           You can manage your stream subscriptions simply by following suggestions and the subscribe/unsubscribe link
           on the sidebar.
        </p>
        <p>
            If you want more control then you need to go to your subscriptions page.
        </p>
        <p>
           Your task is to click on the 'more...' link on the right hand side of the list your stream subscriptions
           at the top of the page. Then click on the 'Edit Stream Subscriptions' link that will appear below it.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_FIND_SEARCH_STREAMS_title_template">
    About Stream Subscriptions
</div>

<div id="level_FIND_SEARCH_STREAMS_template">
    <div>
        <div class="tutorial-location hide" data-location="/user/streamsubscriptions"></div>
        <p>
           At the top if this page are a list of your current stream subscriptions. Ignore this for now,
           as we will look at it in a future tutorial.
        </p>
        <p>
           Below this you will see a link that suggests streams you may be interested in.
           This works in a similar manner to the tutorial on suggestions that you recently completed so it will not
           be covered again here.
        </p>
        <p>
            If however you want to find something specific, then you need to search for it.
            At the bottom of the page is a link that lets you search for streams. Click on that link.
        </p>
        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>



<div id="level_SEARCH_STREAMS_title_template">
    Searching For A Stream
</div>

<div id="level_SEARCH_STREAMS_template">
    <div>
        <div class="tutorial-location hide" data-location="/user/streamsubscriptions"></div>
        <p>
           Finding the stream you want can be a little complex due to Babbling Brook being used on many websites.
           Any user, on any Babbling Brook website can create a stream and the streams name only has to be unique
           to that user. This means that there can be multiple different streams with the same name because they
           are created by different users.
        </p>
        <p>
           There are three textboxes at the top of the table which let you narrow down your search.
           The domain textbox has already been filled out with this websites name so you are only seeing streams that
           have been made here.
        </p>
        <p>
            Your task is to find a new stream to subscribe to. Try using the filters to narrow down your search.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_CHANGE_STREAM_SORT_RHTYHM_title_template">
    Add A New Sort Rhythm To A Stream
</div>

<div id="level_CHANGE_STREAM_SORT_RHTYHM_template">
    <div>
        <div class="tutorial-location hide" data-location="/user/streamsubscriptions"></div>
        <p>
           A while back there was a tutorial to show you how to change the sorting rhythm for a stream. This made the
           posts appear in a different order.
        </p>
        <p>
           Rhythms can be written by users. When they are published then it becomes possible for you to use them to.
           This makes it possible for the posts you look at in a stream to become highly customised to your needs.
        </p>
        <p>
            As with streams, you will receive suggestions for new filter rhythms and you don't have to manually
            select them.
            However, if you want to, then this tutorial will show you how.
        </p>
        <p>
            Start by clicking on the 'details' link on one of your stream subscriptions. A new panel will open up.
            click on the next link titled 'Search for a new rhythm'.
        </p>
       <p>
            A search box looking very similar to the one in the last tutorial opens up.
        </p>
        <p>
            Your task is to choose a new rhythm to subscribe to. Try using the filters to narrow down your search.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_CHANGE_STREAM_MODERATION_RING_title_template">
    Add A New Moderation Ring To A Stream
</div>

<div id="level_CHANGE_STREAM_MODERATION_RING_template">
    <div>
        <div class="tutorial-location hide" data-location="/user/streamsubscriptions"></div>
        <p>
           Time to introduce another novel feature.
        </p>
        <p>
           On most social networking sites the content is moderated. Spam and inappropriate content is removed.
           This is an essential service that prevents social networks from drowning in an endless torrent of
           rubbish.
        </p>
        <p>
            This is normally achieved in two ways. Firstly by having an algorithm (A computer program)
            that automatically removes spam. Secondly by assigning a group of human moderators.
        </p>
        <p>
            To a certain extent this works well, but it also produces its own problems. It concentrates power in the
            hands of the moderators and it enables them to shape the experience of the social network. On websites that
            use the Babbling Brook protocol this power is given back to you by letting you choose which algorithms
            and people you want to moderate your content.
            You do this by assigning <em>moderation rings</em> to your stream subscriptions.
        </p>
        <p>
            In Babbling Brook a <em>ring</em> is a group of users working together. So a moderation ring is a group of
            users working together to moderate content.
        </p>
        <p>
            We will look at rings in more detail later. For now lets
            just look at how you can change who moderates your stream subscriptions.
            Most of the time the defaults will work for you and you won't want to change them. Often when it needs
            to change then it will be suggested to you. But occasionally you may want to lead the charge and change them
            manually.
        </p>
       <p>
            This is done in the same way as changing filter rhythms. Click on the 'details' link of one of
            your subscriptions and you will see that it has a new section titled 'Moderation Rings'.
        </p>
        <p>
            Your task is to choose a new moderation ring for one of your subscriptions.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_BUGS_title_template">
    Handling Bugs
</div>

<div id="level_BUGS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/bugs/latest/latest/latest"></div>
        <p>
            Cobalt Cascade is a website designed to test the Babbling Brook protocol and it is currently
            in an alpha status. Meaning it will have plenty of bugs.
        </p>
        <p>
            Your help is needed to improve it.
        </p>
        <p>
            Sometimes something will go wrong in the code and a message box will pop open at the top of the
            page with an error message.
        </p>
        <p>
            Your first task is to <a href='' id='turtorial_error'>click here to create an error</a>.
        </p>
        <p>
            To the right of the error message is a ladybird. If you click it then a form will pop up
            with details of the bug already filled in. If you submit the form then it will be posted to the bugs stream
            where it will be looked at and dealt with.
        </p>
        <p>
            If something goes wrong but no error appears then you can submit a bug report by clicking on the
            'report bug' link at the top right of the page. If you want to give some feedback that is not a bug then use
            the <a href='/sky/stream/feedback/latest/latest/latest'>feeback</a> stream.
            Both the bug and feedback streams are included in your default subscriptions and should be visible in
            your list of stream subscriptions at the top of the page. If you find a bug particularly annoying
            or there is a feature you really want
            then you can vote for it to be fixed in these streams.
        </p>
        <p>
            Your task is to submit a new bug report. ( Don't worry it wont
            actually be submitted this time.)
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_KINDRED_SCORE_title_template">
    Kindred relationships
</div>

<div id="level_KINDRED_SCORE_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            As you start to use Cobalt Cascade, or any Babbling Brook website, relationships start being
            generated between you and other users. These relationships are created from the posts that you vote
            on, the streams you subscribe to and many other things. They are created by another algorithm called
            a <em>kindred rhythm</em>.
        </p>
        <p>
            As with filter rhythms, a new kindred rhythm can be suggested to you, making it possible for how you
            relate to other users to be customised to your needs. It puts you in control.
        </p>
        <p>
            Your kindred relationships effect almost every aspect of your experience on a Babbling Brook website.
            They help filter rhythms decide which posts to show you and in what order they should appear. They
            help suggestion rhythms decide what new features they should let you know about. They also help you
            find users and friends who share your interests.
        </p>
        <p>
            Posts can show your kindred
            score for the user who made it. And if you click on the username who made a post you will be taken
            to that users profile page where you can find out all about them.
        </p>
        <p>
            Your task is to visit the profile of one of the users on this page.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_VIEW_PROFILE_title_template">
    Tag a user
</div>

<div id="level_VIEW_PROFILE_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky"></div>
        <p>
            A users profile page can be customised to the website you are using. The Cobalt Cascade profile
            has two main features.
        </p>
        <p>
            You can read what others have to say about a user or make a comment yourself by
            following the conversation link
        </p>
        <p>
            You can also tag the user. Tagging users lets other users quickly know about them.
            Perhaps they talk a lot, so they may have been tagged as chatty. Or they are very
            helpful at answering questions and have been tagged as helpful.
        </p>
        <p>
            Babbling Brook is a distributed network. There is no central repository of tags. Instead, as with all other
            Babbling Brook data they are stored in the accounts of the user who made them. This means that there can
            be multiple tags with the same name, each made by a different user. They can have subtly different meanings.
            To find out more about a tag, click on it and you will be taken to a page where you can read its description
            and discuss it.
        </p>
        <p>
            Your task for this tutorial is to click <em>search for user tags</em>, find a
            tag called 'the tutorial made me do it' and apply it to this user.
            Once you have found the tag, you can apply it by clicking on the little tag icon.
            To remove a tag click on the cross next to it.
        </p>
        <p>
            Hint: Use the 'name' filter to find the tag.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_EDIT_PROFILE_title_template">
    Edit your profile
</div>

<div id="level_EDIT_PROFILE_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            To view your own profile, click on the profile link that has appeared on the top navigation bar.
        </p>
        <p>
            Once you are on your profile page, there is a link at the top that lets you edit it. Click it.
        </p>
        <p>
            Here you can upload a profile picture, include your name (or remain anonymous if you like),
            or say something about yourself.
        </p>
        <p>
            Your task is to say something about yourself in the About textbox. It doesn't have to be anything personal.
            Maybe you like flapjacks, or you are ticklish, or you have eight eyes. Say whatever you want - you can
            always edit it after you've finished the tutorial.
        </p>
        <p>
            (Just click anywhere outside of the textbox to update it.)
        </p>
        <p>
            Note: This section is currently underdeveloped. This will be much more customisable soon.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_PRIVATE_POSTS_title_template">
    Private Posts
</div>

<div id="level_PRIVATE_POSTS_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            You can make private as well as public posts.
        </p>
        <p>
           Click any of the 'reply' buttons on this page and type 'I'm talking to myself' into the textbox that
           appears.
        </p>
        <p>
            Now, tick the box that says 'Make this post private'.
        </p>
        <p>
            You need to enter the username the post is going to be sent to.
        </p>
        <p>
            Enter your full username ( <span id='tutorial_private_posts_username'></span> )
            into the textbox so that you can send a private message to yourself.
        </p>
        <p>
            Then click the 'Make post' button.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_READ_PRIVATE_POSTS_title_template">
    Read private posts
</div>

<div id="level_READ_PRIVATE_POSTS_template">
    <div>
        <div class="tutorial-location hide"
             data-location="/postwithtree/<?php echo HOST; ?>/<?php echo Yii::app()->params['tutorial_post_id']; ?>"></div>
        <p>
            You can read your private posts by clicking on the 'Posts' link at the top of the page.
        </p>
        <p>
           The number of unread messages will be displayed after the link.
           (It doesn't update immediately).
        </p>
        <p>
            There are two inboxes. The local inbox holds all your mail from other users on this website
            as well as all public posts that have been made in response to posts that you made.
            The global inbox lists all your mail in the whole Babbling Brook network. Since there is
            only this site at the moment it is essentially a duplicate or the local one for now.
        </p>
        <p>
            You can also review your sent items, which includes all your public posts and compose a private
            message.
        </p>
        <p>
            Your task is to click on the 'Posts' link and have a look.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_META_LINKS_title_template">
    Meta Discussion
</div>
<div id="level_META_LINKS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            Congratulations!
        </p>
        <p>
           You now know what you need in order to you to start using Babbling Brook websites as a consumer.
           However there is a lot more to Babbling Brook than just consuming content. Everything in Babbling Brook
           can be modified by the users. It is up to users to create the streams, rhythms and rings that make
           Babbling Brook possible.
        </p>
        <p>
            The rest of these tutorials will briefly introduce you to these features. However
            now might be a good time to take a break. You can always turn the tutorials back on by clicking the
            'tutorials' link in the top navigation bar.
        </p>
        <p>
            This tutorial is the first on making and managing streams. Streams can be about anything. You could
            have one about pet rocks, or another about sunsets. A stream can be a blog,
            or the comments on the blog. It can be for surveys or reviewing products. It could even be a shop, with
            products being the posts and can 'purchased' by voting on them (Restricted 'votes' are coming soon.)
            Streams can be used for many different purposes. The first step  in creating your own stream is to
            know about the meta conversation.
        </p>
        <p>
            On the side bar of every stream is a 'meta' link. This takes you to a place
            where public discussion about the stream can take place.
        </p>
        <p>
            Your task is to visit this streams meta conversation.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_MAKE_STREAMS_title_template">
    Creating Streams
</div>
<div id="level_MAKE_STREAMS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            You can create your own streams by clicking on the 'streams' link in the top navigation bar and
            then clicking create on the sidebar.
        </p>
        <p>
           Once you have created a stream there are many options. You can change the child stream that is used to
           make comments. You can change the default rings for moderating posts. You can change the fields in
           the stream - perhaps add a new value field to ask people to rate something with stars, or make a list
           for users to choose from.
        </p>
        <p>
            An in depth stream tutorial will be made soon, but in the meantime there are just a couple of things that
            you need to be aware of.
            Firstly, no one can see your stream until you publish it (The globe icon).
            Secondly, once you have published a stream and another user has made a post in it,
            then you can't edit or delete it.
            This is to ensure that users can always see the posts that they have made.
            If you want to edit a stream after it has been used then you will need to make a new version.
            There is an option to do that on the edit page.
        </p>
        <p>
            Your task is to create a new stream. Don't worry about thinking of a topic, just have a look around.
            No one will see it unless you publish it.
        </p>
        <p>
            If you need help, then click on the buttons like this one <span class="fake-help-icon"></span>.
            If you are still stuck then head over to the
            <a href="/sky/stream/help/latest/latest/latest">help stream</a>.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>



<div id="level_RING_MEMBERSHIP_title_template">
    Ring Membership
</div>
<div id="level_RING_MEMBERSHIP_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest"></div>
        <p>
            In an earlier tutorial you learned how to subscribe to moderation rings. In this and the next tutorial
            you will learn how to become a member of a ring and help with moderation. (Rings are simply groups of
            people working together.)
        </p>
        <p>
           A new link called 'Rings' has appeared on the top navigation bar. Click it and you will be taken to your
           Ring page.
        </p>
        <p>
            This page lists any rings that you are a member of, administrate or have been invited to join.
        </p>
        <p>
            Rings can have different kinds of membership. Some you need to be invited to join, others are open to the
            public. Today we are going to join a public ring.
        </p>
        <p>
            Underneath 'Ring membership' there is a link titled 'search for rings'. It will open a search box that
            should be now be becoming familiar. Search for a ring called 'tutorial spam'. This is a ring that is open
            to anyone to join and is used to mark content as spam. Click on the view button and you will be taken
            to the rings profile page.
        </p>
        <p>
            At the top of the profile page is a link to join the ring. Click it to become a member.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_MODERATING_POSTS_title_template">
    Moderating posts
</div>
<div id="level_MODERATING_POSTS_template">
    <div>
        <div class="tutorial-location hide" data-location="/<?php echo Yii::app()->user->getName(); ?>/ring/index">
        </div>
        <p>
            Now that you have joined a ring, you can begin moderating. But before that, notice that the ring now appears
            on your ring membership list. If you want to resign from the ring for any reason
            then head over to the members area.
        </p>
        <p>
            Click on one of your stream subscriptions towards the top of the page. Any will do.
        </p>
        <p>
            After the comments link of every post there is now a ring link. Click one of these links
            and a little menu will pop up. This menu will list all the moderation actions you can make for all the
            rings you are a member of.
        </p>
        <p>
            Your task is to mark a post as tutorial spam on the ring menu. (Don't worry, the tutorial spam ring is
            only used for this tutorial, you wont really effect anything.)
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>

<div id="level_MAKING_RINGS_title_template">
    Making Rings
</div>
<div id="level_MAKING_RINGS_template">
    <div>
        <div class="tutorial-location hide" data-location="/<?php echo Yii::app()->user->getName(); ?>/ring/index">
        </div>
        <p>
            You can also make your own rings. Simply click the 'create a new ring' under the Rings you
            administer heading.
        </p>
        <p>
            We are not going into details in this tutorial. Head over to the
            <a href="/sky/stream/help/latest/latest/latest">help stream</a> if you need it.
        </p>
        <p>
            Your task is to click the 'create a new ring' link and have a look around.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_MAKE_RHYTHMS_title_template">
    Making Rhythms
</div>
<div id="level_MAKE_RHYTHMS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest">
        </div>
        <p>
            If you know how to code JavaScript, then you can also create rhythms. Don't worry if you can't,
            you can select rhythms from all those that other users publish.
        </p>
        <p>
            Documentation will be live soon. In the meantime. Checkout the
            <a href='/sky/rhythms'> rhythms in Skys account</a> to get an idea of how they work.
        </p>
        <p>
            Click on the rhythms link on the top nav bar to continue.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_SETTINGS_title_template">
    The Settings Page
</div>
<div id="level_SETTINGS_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/beautiful/latest/latest/latest">
        </div>
        <p>
            Finally, for the ultimate power user there is the settings page.
        </p>
        <p>
            Simply click the link at the top of the page.
        </p>
        <p>
            Click the settings link to finish the tutorial.
        </p>

        <button class='standard-button' id='close_tutorial'>OK</button>
    </div>
</div>


<div id="level_FINISHED_title_template">
    Congratulations!
</div>
<div id="level_FINISHED_template">
    <div>
        <div class="tutorial-location hide" data-location="/sky/stream/feedback/latest/latest/latest">
        </div>
        <p>
            You have finished the tutorial.
        </p>
        <p>
            How did you find it? Feedback is always welcome.
        </p>
        <button class='standard-button' id='go_to_start_tutorial'>Restart the tutorial from the start</button>
    </div>
</div>
