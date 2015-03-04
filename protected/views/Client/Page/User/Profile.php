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
 * View for a users profile. Details are populated via ajax.
 */

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'User/Profile'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'User/Profile'));

$this->pageTitle=Yii::app()->name . ' - ' . $username . ' Profile';

$profile_help = new ProfileHelp();

if ($is_ring === true) {
    $type = 'ring';
} else {
    $type = 'user';
}
$this->menu_title = null;
$this->menu = array(
    array(
        'label' => 'Edit Your Profile',
        'url' => '/' . $username . '/editprofile',
        'itemOptions' => array(
            'id' => 'edit_link',
        ),
        'visible' => Yii::app()->user->getName() ===  $username,
    ),
    array(
        'label' => 'Edit This Ring Profile',
        'url' => '/' . $username . '/editprofile',
        'itemOptions' => array(
            'id' => 'edit_ring_profile',
            'class' => 'hide',
        ),
    ),
    array(
        'label' => 'Ring Members Area',
        'url' => '/' . $username . '/ring/members',
        'itemOptions' => array(
            'id' => 'ring_members',
            'class' => 'hide',
        ),
    ),
    array(
        'label' => 'Join Ring',
        'url' => '#',
        'itemOptions' => array(
            'id' => 'join_ring',
            'class' => 'hide',
        ),
    ),
    array(
        'label' => 'Request Membership',
        'url' => '#',
        'itemOptions' => array(
            'id' => 'request_ring_membership',
            'class' => 'hide',
        ),
    ),
    array(
        'label' => 'Download Your Data',
        'url' => '/' . $username . '/download',
        'visible' => false, //Yii::app()->user->getName() ===  $username,
    ),
);
?>


<div id="username" class="hide">
    <?php echo $username; ?>
</div>
<div id="domain" class="hide">
    <?php echo $domain; ?>
</div>
<div id="is_ring" class="hide">
    <?php echo $is_ring; ?>
</div>

<h2>
    <?php
    if ($is_ring === true) {
        echo "Ring profile ";
    } else {
        echo "Profile ";
    }
    ?>
    for <em><?php echo $username; ?></em>
</h2>

<div id="profile" class="content-indent">

    <div class="profile-photo">
        <?php
        $profile_username = str_replace(' ', '-', $_GET['user']);
        $profile_domain = HOST;
        $profile_image = '/images/user/' . $profile_domain . '/' . $profile_username . '/profile/large/profile.jpg';
        $image_path = realpath(Yii::app()->basePath . "/../") . $profile_image;
        if (file_exists($image_path) === false) {
            $profile_image = '/images/default_user_large.png';
        }
        ?>
        <img class="profile-photo" src='<?php echo $profile_image ?>' />
    </div>

    <?php if ($is_ring === false) { ?>
    <h3>
        Name:
    </h3>
    <div class="end line">
        <span id="name_content" class="block-loading something"></span>
    </div>
    <?php } ?>

    <h3>
        <?php if ($is_ring === true) { ?>
            About this ring:
        <?php } else { ?>
            About Me:
        <?php } ?>
    </h3>
    <div class="end line">
        <span id="about_content" class="block-loading something"></span>
    </div>

    <h3 class="end">
        <span>
            Kindred score:
        </span><strong><span id="kindred_score" class="something block-loading"></span></strong>
        <span class="smaller-extra"><?php echo $profile_help->kindredScore(); ?></span>
    </h3>

    <div class="content-block-2 larger end clear">
        <a id="conversation" href="#" class="text-loading">Conversation about this <?php echo $type; ?></a>
    </div>

    <div id="ratings">

        <div class="content-block-2 block-loading end">
            <h3 class="clear">Your tags for this <?php echo $type; ?></h3>
            <div id="users_tags_for_profile" class="clear"></div>
            <div id="users_tags_for_profile_page" class="page-links"></div>
            <div id="users_tags_for_profile_none" class="hide">
                You have not made any tags for this <?php echo $type; ?>
            </div>
        </div>

        <div id="rate_user_options" class="content-block-2 end">
            <h3>Tag this <?php echo $type; ?></h3>
            <div id="users_popular_tags" class="clear">
                <a id="users_popular_tags_off" class="larger">List the tags you use the most</a>
                <a id="users_popular_tags_on" class="larger end hide">Close popular tags</a>
                <div id="users_popular_tags_list"></div>
                <div id="users_popular_tags_none" class="hide">You have not tagged any <?php echo $type; ?>s.</div>
                <div id="users_popular_tags_used" class="hide">You have already applied all of your popular tags.</div>
            </div>

            <div id="suggest_user_tags" class="clear">
                <a id="suggest_user_tags_on" class="larger">Suggest <?php echo $type; ?> tags</a>
                <a id="suggest_user_tags_off" class="larger hide end">Close tag suggestions</a>
                <div id="suggest_user_tags_list"></div>
                <div id="suggest_user_tags_none" class="hide">No suggestions available</div>
            </div>

            <div id="search_tags" class="clear">
                <a id="search_tags_on" class="larger">Search for <?php echo $type; ?> tags</a>
                <a id="search_tags_off" class="larger hide end">Close tag search</a>
                <div id="search_tags_list"></div>
            </div>

            <div id="make_new_tag" class="clear">
                <a class="larger">Create a new <?php echo $type; ?> tag</a>
            </div>
            <div id="make_new_tag_form" class="content-block-3 form hide">
                <a id="make_new_tag_form_off" class="larger end">Close create new tag form</a>
                <div>Have you <a id="search_tags_hint">searched</a> for a tag?</div>
                <div class="extra-margin-top">
                    <label class="block-label" for="new_tag_name">
                        Name <span class="required">*</span>
                        <?php $stream_help = new StreamHelp(); ?>
                        <?php echo $stream_help->tagName(); ?>
                    </label>
                    <input type="text" value="" id="new_tag_name" maxlength="128" size="60">
                    <div id="stream_name_error" class="error hide"></div>
                </div>
                <div class="extra-margin-top">
                    <label class="block-label" for="new_tag_description">
                        Description
                        <?php echo $stream_help->description(); ?>
                    </label>
                    <textarea id="new_tag_description" cols="50" rows="6"></textarea>
                    <div id="stream_description_error" class="error hide"></div>
                </div>
                <div class="extra-margin-top buttons">
                    <input type="button" value="Create" id="create_tag_stream" class="standard-button">
                </div>
                <div id="make_new_tag_success" class="hide">
                    <span id="the_new_tag"></span>
                    Your new tag has been created.
                </div>
            </div>

        </div>

        <div class="content-block-2 block-loading end">
            <h3 id="kindred_title" class="clear">Kindred tags for this <?php echo $type; ?></h3>
            <div id="kindred_tag_list" class="clear"></div>
                <div id="kindred_tag_list_none" class="hide">No kindred tags have been made</div>
        </div>

        <div class="content-block-2 block-loading end">
            <h3 id="global_title" class="clear">Global tags for this <?php echo $type; ?></h3>
            <div id="global_tag_list" class="clear"></div>
                <div id="global_tag_list_none" class="hide">No global tags have been made</div>
        </div>
    </div>


    <div id="make_ring_invite" class="content-block-2 end hide">
        <h3 id="invite_title">Invite this <?php echo $type; ?> to a ring you belong to</h3>
        <div class="end">
        </div>
    </div>

</div>



<div class="templates hide">

    <div id="make_ring_invite_line_template">
        <div>
            <a href="">Invite this <?php echo $type; ?> to the <em>*ring_name*</em> ring</a>
        </div>
    </div>

    <div id="profile_post_template">
        <div class="post short-post">
            <div class="top-value">
                <div class="field-2 field"></div>
            </div>
            <div class="title">
                <a class="stream"></a>
            </div>
            <div class="info">
                <span class="sort-score-intro">The sort score is <span class="sort-score"></span>.</span>
                <a class="link-to-post">
                    comments
                    (<span class="child-count"></span>)
                </a>
            </div>
        </div>
    </div>

    <div id="tag_template">
        <div class="user-tag">
            <span class="tag-score hide" title="!username! has been tagged !qty! times with this tag "></span>
            <a class="tag-name hide"></a>
            <span class="tag-name-no-link"></span>
            <img class="tag-icon hide" title="Tag the user with this" src="/images/ui/tag.png" />
            <img class="untag-icon hide" title="Remove this tag from the user" src="/images/ui/delete.png" />
        </div>
    </div>

    <div id="new_tag_default_description_template">
        A stream for tagging <?php echo $type; ?>s.
    </div>

    <div id="kindred_tag_extra_title_template">
        by your kindred
    </div>

    <div id="on_request_ring_membership_error_template">
        There was an error when requesting ring membership for the <span class="ring-name"></span> ring.
    </div>

    <div id="on_request_ring_membership_success_template">
        Your application to join the <span class="ring-name"></span> ring has been sent. You will receive a private
        message when a decision has been made.
    </div>

</div>