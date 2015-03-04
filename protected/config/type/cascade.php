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
 * config/type/cascade.php
 *
 * This is the supplementary configuration for cascade type websites. Any writable
 * CWebApplication properties can be configured here.
 */

return
    array(
        'theme' => 'cobaltcascade',

        'params' => array(

            // A list of all components that are active on this website.
            // if a component is present called 'all' then all available components are activated.
            // Component names should be in Title case, exactly as they appear in the css or js folder but without
            // the extension.
            // for items in subfolders include the folder name, eg 'foldername/filename'
            'active_components' => array(
                'all',
            ),

            // This is the stream that appears when the site root page is accessed.
            'home_page_stream' => array(
                'domain' => HOST,
                'username' => 'sky',
                'name' => 'news',
                'version' => array(
                    'major' => 'latest',
                    'minor' => 'latest',
                    'patch' => 'latest',
                ),
                'rhythm' => array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'skys priority',
                    'version' => array(
                        'major' => '0',
                        'minor' => '0',
                        'patch' => '0',
                    ),
                ),
            ),

            // The username domain and name of the stream that users can submit bugs to.
            'bug_stream_username' => 'sky',
            'bug_stream_domain' => HOST,
            'bug_stream_name' => 'bugs',

            'results_per_stream_page' => 25,

            'default_public_tree_filter_rhythm_id' => 10017,

            // The default filters that are appended to a stream subscription.
            // The numbers represent the rhythm_extra_id, version_type and display_order
            'defult_filter_subscriptions' => array(
                array(10002, 32, 1),
                array(10000, 32, 2),
            ),

            // These streams and filters are automatically subscribed to new users
            // when they sign up or log in for the first time.
            // The display order of the streams and filters reflects the order in the array.
            // See the commented out example for the structure of each subscription
            'default_subscriptions' => array(
            //  array(
            //      'domain' => 'The domain name of the stream',
            //      'username' => 'The username of the stream'
            //      'name' => 'The name of the stream',
            //      'version' => 'The version numbers of the stream',
            //      'filters' => array(
            //          array(
            //              'domain' => 'The domain name of a filter',
            //              'username' => 'The username of a filter',
            //              'name' => 'The name of a filter',
            //              'version' => 'The version string of a filter',
            //              'locked' => true|false, // Is user able to unsubscribe this filter
            //          ),
            //          ...
            //      ),
            //      'locked' => true|false, // Is the user able to unsubscribe this stream.
            //  ),
            //  ...
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'news',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'skys priority',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => true,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => true,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'oldest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => true,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'chat',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular recently',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),

                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'feedback',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular recently',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'tasks',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'all',
                        'patch' => 'all',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'skys task priority',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular in last hour',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular in last day',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular in last week',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'bugs',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'all',
                        'patch' => 'all',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'skys open bugs',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'skys closed bugs',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'feature requests',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'all',
                        'patch' => 'all',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular recently',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'babbling brook',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'all',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular recently',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'cobalt meta',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'all',
                    ),
                    'filters' => array(
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'newest',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                        array(
                            'domain' => HOST,
                            'username' => 'sky',
                            'name' => 'popular recently',
                            'version' => array(
                                'major' => 'latest',
                                'minor' => 'latest',
                                'patch' => 'latest',
                            ),
                            'locked' => false,
                        ),
                    ),
                    'locked' => false,
                ),
                // reinstate this when created on the local site.
//                array(
//                    'domain' => HOST,
//                    'username' => 'sky',
//                    'name' => 'help',
//                    'version' => array(
//                        'major' => 'latest',
//                        'minor' => 'latest',
//                        'patch' => 'latest',
//                    ),
//                    'filters' => array(
//                        array(
//                            'domain' => HOST,
//                            'username' => 'sky',
//                            'name' => 'newest',
//                            'version' => array(
//                                'major' => 'latest',
//                                'minor' => 'latest',
//                                'patch' => 'latest',
//                            ),
//                            'locked' => false,
//                        ),
//                        array(
//                            'domain' => HOST,
//                            'username' => 'sky',
//                            'name' => 'popular in last week',
//                            'version' => array(
//                                'major' => 'latest',
//                                'minor' => 'latest',
//                                'patch' => 'latest',
//                            ),
//                            'locked' => false,
//                        ),
//                    ),
//                    'locked' => false,
//                ),
            ),

            'default_child_stream_id' => '10003',   // comments

            // If this is set to true then users will not be able to signup or log in if they have not
            // entered a sign up code.
            'use_signup_codes' => false,

            'default_after_login_location' => 'http://' . HOST . '/sky/stream/news/0/0/0',

            'invitation_message' => 'You have been invited to join the *ring_name* by *ring_member*'
                . ' visit your <a href="*users_ring_index*">ring page</a> to join.',

            'default_sort_filters' => array(
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'newest',
                    'priority' => 10,
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'oldest',
                    'priority' => 10,
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                ),
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'skys+priority',
                    'priority' => 10,
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                ),
            ),

            'default_moderation_rings' => array(
                array(
                    'domain' => HOST,
                    'username' => 'user spam',
                ),
            ),

            'default_child_streams' => array(
                array(
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'comments',
                    'version' => array(
                        'major' => 'latest',
                        'minor' => 'latest',
                        'patch' => 'latest',
                    ),
                ),
            ),

            'public_stream_rhythms' => array(
                array(
                    'rhythm_extra_id' => '10017',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'popular recently',
                    'version' => '0/0/0',
                ),
                array(
                    'rhythm_extra_id' => '10013',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'popular in last week',
                    'version' => '0/0/0',
                ),
                array(
                    'rhythm_extra_id' => '10012',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'popular in last day',
                    'version' => '0/0/0',
                ),
                array(
                    'rhythm_extra_id' => '10011',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'popular in last hour',
                    'version' => '0/0/0',
                ),
                array(
                    'rhythm_extra_id' => '10008',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'skys priority',
                    'version' => '0/0/0',
                ),
                array(
                    'rhythm_extra_id' => '10002',
                    'domain' => HOST,
                    'username' => 'sky',
                    'name' => 'newest',
                    'version' => '0/0/0',
                ),
            ),

            // These config settings are made available to edit by the user on the settings page.
            'user_editable_settings' => array(
                'stream_rhythm_suggestion_url',
                'stream_filter_rhythm_suggestion_url',
                'stream_expiry',
                'iframe_timeout',
                'filter_timeout',
                'settimeout_timeout',
                'action_timeout',
                'user_stream_rhythm_suggestion_url',
                'stream_ring_rhythm_suggestion_url',
                'user_rhythm_suggestion_url',
                'meta_rhythm_suggestion_url',
                'kindred_rhythm_suggestion_url',
                'ajax_timeout',
                'snippets_to_store',
                'message_box_lines',
                'kindred_rhythm_url',
                'ring_rhythm_suggestion_url',
                'suggestion_message_rate',
                'private_message_stream',
                'domus_timeout',
                'home_page_stream',
                'default_private_filter',
                'default_private_filter_priority',
                'override_stream_update_frequency',
                'rich_text_editor_adapter',
            ),
        )
    );
