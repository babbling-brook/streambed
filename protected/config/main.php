<?php
/**
 *
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
 * config/main.php
 *
 * This is the main Web application configuration. Any writable
 * CWebApplication properties can be configured here.
 */

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local', 'path/to/local-folder');

return
    array(
        'id' => 'babbling_brook',
        'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
        'name' => 'Cobalt Cascade',

        // autoloading model and component classes
        'import' => array(
            'application.models.*',
            'application.models.setup.*',
            'application.models.forms.*',
            'application.models.multi.*',
            'application.models.transactions.*',
            'application.models.babblingbrook.*',
            'application.models.log.*',
            'application.helpers.*',
            'application.helptext.*',
            'application.extendedyii.*',
            'application.filters.*',
            'application.libraries.*',
            'application.controllers.*',
            'application.controllers.' . $sub_domain . '.*',
        ),

        'controllerNamespace' => $sub_domain,

        // application components
        'components' => array(
            'clientScript' => array(
                'class' => 'ClientScript',
            ),
            'html' => array(
                'class' => 'Html',
            ),
            'user' => array(
                // enable cookie-based authentication
                'allowAutoLogin' => true,
                'class' => 'WebUser',
            ),
            // clientdomain/feature/domain/user/name/version_major/version_minor/version_patch/action/data
            'urlManager' => array(
                'urlFormat' => 'path',
                'caseSensitive' => true,
                'rules' => array(
                    //'/' => '/test/stream/posts/test stream/0/0/0',                 // Default post page.

                    'gii' => 'gii',
                    'gii/<controller:\w+>' => 'gii/<controller>',
                    'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',

                    'site/tests/<action>' => 'Tests/<action>',                             // Test controller.

                    'page/<view>' => 'Site/Page',                                     // Static pages.

                    'site/<action>' => 'Site/<action>',                               // Site controller.
                    'site/tag/<action>' => 'Tag/<action>',                            // Tag actions.
                    // If a generic version is used for these, then gridview
                    // pagers for other controllers try to use them.
                    'site/admin/cat' => 'Cat/index',                                  // Site admin controller.
                    'site/admin/cat/<action>' => 'Cat/<action>',                      // Site admin controller.
                    'site/admin/streamregex' => 'Cat/Index',                          // Site admin controller.
                    'site/admin/streamregex/<action>' => 'Cat/<action>',              // Site admin controller.
                    // Site admin controller for managing signup codes.
                    'site/admin/<action>' => 'Admin/<action>',


                    'user/<action>' => 'GenericUser/<action>',                        // Generic logged on user actions.
                    'elsewhere/<domain>/<user>/<action>' => 'User/<action>',          // Stuff from other data stores.

                                                                        // The take value by a single user on an post.
                    'post/<domain>/<post_id>/usertake/<user_domain>/<username>' => 'Post/UserTake',
                    'post/<domain>/<post_id>/<action>' => 'Post/<action>',            // Any action on an post.
                    'post/<domain>/<post_id>' => 'Post/Post',                         // View an post.
                    'postwithtree/<post_domain>/<post_id>' => 'Post/PostWithTree',         // View an post.

                    'cache/<action>/<type>/<site>/<user>/<name>/'
                        . '<version_major:\d+|latest>/<version_minor:\d+|latest>/<version_patch:\d+|latest>' =>
                              'cache/<action>',                                         // Retrieve cached Rhythm.
                    'cache/<action>/<type>/<site>/<user>/<name>/'
                        . '<version_major:\d+|latest>/<version_minor:\d+|latest>' =>
                              'cache/<action>',                                         // Retrieve cached Rhythm.
                    'cache/<action>/<type>/<site>/<user>/<name>/<version_major:\d+|latest>' =>
                        'cache/<action>',                                               // Retrieve cached Rhythm.
                    'cache/<action>/<type>/<site>/<user>/<name>' => 'cache/<action>',   // Retrieve cached Rhythm.

                    'data/<action>' => 'UserData/<action>',                             // Any JSON user data requests.

                    '<username>/clientdata/<action>' => 'UserClientData/<action>',      // All client data requersts.

                    '<user>/data/<action>' => 'UserData/<action>',                      // Any JSON user data requests.

                    '<user>/post' => 'Mail/Index',                      // Messaging inbox.
                    '<user>/post/<action>' => 'Mail/<action>',               // Client messaging actions.

                    '<user>/ring/<action>' => 'Ring/<action>',                          // All ring actions.
                    '<user>/ring/<action>/<name>' => 'Ring/<action>',                   // All ring actions.

                    '<user>/streamsubscription/<action>' => 'StreamSubscription/<action>',

                    '<user>/stream/<stream>/<major:\d+|latest|all>/<minor:\d+|latest|all>/<patch:\d+|latest|all>'
                        . '/rhythm/<rhythm_domain>/<rhythm_user>/<rhythm_name>/'
                        . '<rhythm_major:\d+|latest>/<rhythm_minor:\d+|latest>/<rhythm_patch:\d+|latest>' =>
                              'Stream/posts',
                    '<user>/stream/<stream>/'
                        . '<major:\d+|latest|all>/<minor:\d+|latest|all>/<patch:\d+|latest|all>/<action>/<post_id>' =>
                              'Stream/<action>',
                    '<user>/stream/<stream>/<major:\d+|latest|all>/<minor:\d+|latest|all>/<patch:\d+|latest|all>' =>
                        'Stream/posts',
                    '<user>/stream/<stream>/'
                        . '<major:\d+|latest|all>/<minor:\d+|latest|all>/<patch:\d+|latest|all>/<action>' =>
                              'Stream/<action>',

                    '<user>/streams/<action>' => 'Stream/<action>',
                    '<user>/streams' => 'Stream/Index',

                    '<user>/streamfield/<action>' => 'StreamField/<action>',

                    '<user>/rhythm/<rhythm>/<major:\d+|latest>/<minor:\d+|latest>/<patch:\d+|latest>/<action>' =>
                        'Rhythm/<action>',    // Rhythm actions <rhythm> is the urlencoded name or table id
                    '<user>/rhythm/<rhythm>/<major:\d+|latest>/<minor:\d+|latest>/<patch:\d+|latest>' =>
                        'Rhythm/view',    // Rhythm actions <rhythm> is the urlencoded name or table id
                    '<user>/rhythm/<rhythm>/<major:\d+|latest>/<minor:\d+|latest>/<action>' =>
                        'Rhythm/<action>',    // Rhythm actions <rhythm> is the urlencoded name or table id
                    '<user>/rhythm/<rhythm>/<major:\d+|latest>/<action>' =>
                        'Rhythm/<action>',    // Rhythm actions <rhythm> is the urlencoded name or table id
                    '<user>/rhythm/<rhythm>/<action>' =>
                        'Rhythm/<action>',    // Rhythm actions <rhythm> is the urlencoded name or table id
                    '<user>/rhythm/<action>' => 'Rhythm/<action>',           // Rhythm actions

                    '<user>/rhythms/<action>' => 'Rhythm/<action>',
                    '<user>/rhythms' => 'Rhythm/Index',

                    '<user>/logout/<return>/<name>' => 'User/Logout',    // usernames logout with embeded return link
                    '<user>/<action>' => 'User/<action>',                // usernames actions
                    '<user>' => 'User/Profile',                    // usernames index redirects to profile
                ),
                'showScriptName' => false,
            ),
        ),

        // application-level parameters that can be accessed
        // using Yii::app()->params['paramName']
        //   A not at the bottom of params should point the user  to them.
        //   This is because this file is loaded with every request and few need that data.
        //   Need to be aware when doing this that test config settings are overloaded in controller.php
        'params' => array(
            'host' => HOST,

            // Is this a website a datastore (i.e. can users sign up for accounts here.)
            'babbling_store' => true,

            'show_tutorial' => false,

            'db_type' => 'MySql',

            // Time to remember that a user has logged in. In seconds 60 * 60 * 24 * 30 = 30 days
            'remember_timeout' => '2592000',

            // Time in seconds before a login request return to the client is out of date.
            // 60 seconds * 5 minutes = 300.
            'login_timout' => '300',

            'site_root' => 'http://' . HOST,

            // The db table site.site_id for this site. May need manually entering into the DB
            'site_id' => '10000',

            // This user is used to control assets that are owned by the system.
            // This user does not need user_config settings as it is never used as a client user.
            'system_user_id' => 10000, // @fixme this data needs to be auto created with a startupscript.

            // The id of the meta stream used by the system to enter posts containing meta data about
            // user submitted streams.
            'meta_stream_extra_id' => 10002,

            // The id of the meta stream used by the system to enter posts containing meta data about
            // user submitted Rhythms.
            'meta_rhythm_extra_id' => 10009,

            // The id of the meta stream used by the system to enter posts containing meta data about users and rings.
            'meta_user_id' => 10004,

            // the number of takes to fetch for processing at a time.
            'takes_to_process' => 50,

            // Milliseconds to wait before js starts to process takes
            'initial_wait_before_processing_takes' => 500000,

            // Milliseconds to wait before js process more takes
            'short_wait_before_processing_takes' => 500000,

            // Milliseconds to wait before js process more takes, when none are left to process
            'long_wait_before_processing_takes' => 1000000,

            // Maximum value of a take (minimum is this in negative)
            'max_value' => 1000000000,

            // The number of post messages to fetch at a time.
            'private_post_page_qty' => 100,

            //The maximum number of public posts to return for a user at a time (page size).
            'public_post_page_qty' => 100,

            //The maximum number of public posts to return for a user at a time (page size).
            'search_post_page_qty' => 100,

            // The number of seconds that an post has to have been created before it is publicly available.
            // @protocol This needs to be a standard.
            'post_cooldown' => 120,

            // Seconds to wait since last post take grouping before grouping again.
            'time_until_next_take_group' => 60,

            // Seconds to wait before a user secret has timed out.
            'secret_timeout' => 600,    // Ten minutes.

            // The maximum number of latest user takes to fetch. See actionGetLatestTakes
            'user_takes_qty' => 1000,

            // How long to wait in milliseconds before processing the next ring Rhythm.
            'ring_pause' => 5000,

            // Seconds until new data is generated. Used by post and take streams to cache data
            'refresh_frequency' => 180,

            // Delete error logs that are older than this time (seconds before now). 1209600 = 14 days.
            'delete_logs_time' => 1209600,

            // How long is the data generated by a ring rhythm running on a members account allowed to be.
            'max_ring_member_data_length' => 1000,

            // How long is the data generated by a ring rhythm running on an admin account allowed to be.
            'max_admin_member_data_length' => 10000,

            //
            // Default moderation rings for any stream. Array of ring_id
            'moderation_ring_defaults' => array(
                10000
            ),

            // Stream to send private invitations to join new rings.
            // This must be a local stream.
            'invitation_stream' => array(
                'domain' => HOST,
                'username' => 'sky',
                'name' => 'invitations',
                'version' => '0/0/0',
            ),

            'accept_ring_membership_messsage' => "Your invitation to the *ring-name* ring has been accepted.",

            'decline_ring_membership_messsage' => "Your invitation to the *ring-name* ring has been declined.",

            'default_max_stream_text_field_size' => '200',
        ),
    );