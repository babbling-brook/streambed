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
 *
 * @fileOverview Handles the making of new posts.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}



/**
 * @namespace Handles the making of new posts.
 *
 * The post is sent to multiple domains that have an interest in it.
 * Public posts
 * -------------
 * The first is the domain of the stream that the post resides in. This is the main home of the post
 * and the place where it is usually fetched from.
 * The second is the domain of the user who made this posts parent post. This is because every reply to an
 * existing post is treated as a message sent to that user and even if the message is deleted at the senders end
 * it is not deleted from the receivers domus domain.
 * The third is this submitters domus, so that the submitter always has a record even if the stream goes offline.
 * Finally, if this is an edit to an post that anyone can edit, then it is sent to all domains for the users
 * who have made an edit. If any of these domains fails to repsond correctly, the post is submitted anyway.
 * Private posts
 * --------------
 * Are sent to the domains of both the sender and recipients.
 *
 * Posts are only recorded once per domain, even if the domain is the home of all the domains involved.
 *
 * The posts are stored transactionaly. If any process fails then the domains are asked to removeany records they
 * have made. This must be done before the protocol defined cooldown has expired.
 *
 * The process:
 * ------------
 * A list of all domains that need to receive the post are generated, secrets are then requested from all
 * domains that are not the local one.
 *
 * Once all secrets have returned (guaranteeing that all servers are repsonding) the post is sent.
 *
 * For public posts, it is first sent to the stream domain, so that the revision number can be returned
 * for post edits; this prevents overlaps if multiple users are submitting edits simultaneously.
 *
 * If there is an error at any point then all domains are requested to delete the post.
 *
 * @param {object} post The post to make. See BabblingBrook.Models.post for details.
 * @param {function} successCallback Called with the success data to return data to the client.
 * @param {function} errorCallback Called if there is an error to pass the error to the client.
 * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
 *
 * @package JS_Domus
 *
 * @return void
 */
BabblingBrook.Domus.MakePost = function (post, successCallback, errorCallback, onFormCreated, timeout) {
    'use strict';

    /**
     * An array of post objects. One for each revision of the post being edited.
     *
     * @type {object[]}
     */
    var revision_posts = [];

    /**
     * Is this a private post.
     *
     * @type {boolean}
     */
    var private_post;

    /**
     * An array of domains that the post will be sent to.
     *
     * If these domains do not respond correctly then an error is  thrown and the post is NOT inserted.
     *
     * @type {string[]}
     */
    var domains_to_send_post_to = [];

    /**
     * An array of extra domains that the post will be sent to.
     *
     * If these domains do not respond correctly then an error is not thrown and the post IS inserted.
     *
     * @type {string[]}
     */
    var extra_domains_to_send_post_to = [];

    /**
     * An object containing the secrets to pass the post to domains. Indexed by domain.
     *
     * @type {object}
     */
    var domain_secrets = {};

    /**
     * A count of how many domains the post has been successfully sent to.
     *
     * Starts at one because the stream domain is fetched seperatly.
     *
     * @type {number}
     */
    var returned_main_post_count = 1;

    /**
     * A count of how many domains the private post has been successfully sent to.
     *
     * @type {number}
     */
    var returned_private_post_count = 0;

    /**
     * Set to true once the make post process has started. Prevents duplicate posts from being created.
     *
     * @type {boolean}
     */
    var already_making_post = false;

    /**
     * Contains the made post returned from the server.
     *
     * For public posts this comes from the stream domain.
     * For private posts this comes from the makers domain.
     *
     * @type {object}
     */
    var made_post;

    /**
     * The usernames to send an post to in each domain a private post is being posted to.
     *
     * The domain is used as a key. Each domain holds an array of usernames.
     *
     * @type {string[]} private_usernames
     */
    var private_usernames = {};

    /**
     * If this is an edit to an post, have we already started the edit process because the stream said it was ok.
     *
     * The origional post and stream are downloaded to check. Both process might give the goahead but we only want
     * to create one new edit.
     *
     * @type {boolean}
     */
    var stream_ok_to_edit;

    /**
     * If this is an edit to an post, have we already started the edit process because the post said it was ok.
     *
     * The origional post and stream are downloaded to check. Both process might give the goahead but we only want
     * to create one new edit.
     *
     * @type {boolean}
     */
    var post_ok_to_edit;

    /**
     * The domain of the parent post to this one. Set to false if there is no parent.
     *
     * @type {string|boolean}
     */
    var parent_domain;

    /**
     * Attempt Deletion of an post that has just been made.
     *
     * Done because there is a problem with storing the post at one of the other domains.
     * This will only work if done before the domains timeout for deleting posts.
     *
     * @param {string} domain The domain to delete the post from.
     * @param {number} post_id The local id of the post to delete.
     * @param {string} secret The secret that was used to create the post. Not needed for the users domus.
     */
    var deletePost = function(domain, post_id, secret) {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            domain,
            'DeleteNewPost',
            {
                post_id : post_id,
                secret : secret
            },
            true,
            errorCallback('MakePost_failed'),
            errorCallback('MakePost_delete_failed'),
            timeout
        );
    };

    /**
     * If there are any errors then attempt to delete what has been done and report the error to the client.
     *
     * @param {string} [message] An error message.
     *
     * @return void
     */
    var error = function (message) {
        console.error('make post error.')
        if (typeof message === 'undefined') {
            message = 'An unknown error occured whils trying to make an post.';
        }

        // Send a request to delete any posts that have already been made.
        if (typeof made_post !== 'undefined') {
            jQuery.each(domains_to_send_post_to, function(index, domain) {
                if (typeof domain_secrets[domain] !== 'undefined') {
                    deletePost(domain, domain_object.post_id, domain_secrets[domain]);
                }
            });
        }

        errorCallback('MakePost_failed');
    };

    /**
     * Test an post object after it has been returned from the server.
     *
     * @param {object} post The post object. See BabblingBrook.Models.posts with sinlge 'extension'.
     *
     * @return boolean
     * @refactor extensions in models should be called scenarios.
     * @refactor All tests should be moved into a test object in each domain. The correct tests are then called
     *      depending on which action was called.
     */
    var testMadePost = function (post) {
        post = BabblingBrook.Models.posts([post], 'Posts test.', ['single']);
        if (post === false) {
            return false;
        }
        return true;
    };


    /**
     * Callback to send the post to a revision domain.
     *
     * @param {string} domain The domain to send the post to.
     * @param {object} secret_data Contains the requested secret.
     * @param {string} secret_data.secret The secret for communicating with this domain.
     *
     * @return {void}
     */
    var onGeneratedRevisionSecret = function (domain, secret_data) {
        if (typeof secret_data !== 'object' || typeof secret_data.secret !== 'string') {
            return; // Fail silently
        }

        // Extend the post because we don't want to record the secret in the post object
        // because when it is passed to the users domain it will cause an error.
        var tmp_post = {};
        jQuery.extend(true, tmp_post, post);
        tmp_post.secret = secret_data.secret;
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            extra_domains_to_send_post_to[count],    // The stream domain is always the first in the array.
            'MakePost',
            {
                post : tmp_post
            },
            true,
            function() {},
            function() {}, // Fail silently as these domains are not required.
            timeout
        );
    }

    /**
     * Fetch the secrets for all the domains that have revisions of the post by a user at their doman.
     *
     * @return void
     */
    var fetchRevisionPostSecrets = function() {
        var qty_of_domains = extra_domains_to_send_post_to.length;
        for (var count = 0; count < qty_of_domains; count++) {
            generateSecret(
                extra_domains_to_send_post_to[count],
                onGeneratedRevisionSecret.bind(null, extra_domains_to_send_post_to[count])
            );
        }
    };

    /**
     * Called when the post has been made without error and the made post is ready to be sent back to the client.
     *
     * @return void
     */
    var finishPost = function () {
        var new_post = {
            post : made_post
        };
        BabblingBrook.Domus.SortedStreamResults.prependPost(made_post.stream_name, made_post.top_parent_id);
        successCallback(new_post);

        // Each revision editor of a post is also sent a version of the post, but the success of
        // their domains recording it is not dependant on the success of this latest edit
        // so they are sent after the make post process has been finished.
        fetchRevisionPostSecrets();
    };

    /**
     * Generate and return the secrets for storing this post.
     *
     * The domain that owns the stream the post is placed in, and the domain of the user who made the parent post
     * need to know that this post is genuinely being made by the user who is claiming it.
     * In order to do this, this users domus generates secrets that are passed to the other domains. These domains then
     * verify the secret dirrectly with this users domus in order to be sure that the user is not spoofed.
     *
     * @param {string} domain The domain that this secret is being generated for.
     * @param {function} onSecretGenerated Callback to run once the secret has been generated.
     *
     * @return void
     */
    var generateSecret = function (domain, onSecretGenerated) {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            BabblingBrook.Domus.User.domain,
            'GenerateSecret',
            {
                username : BabblingBrook.Domus.User.username,
            },
            true,
            onSecretGenerated.bind(null, domain),
            error,
            timeout
        );
    };

    /**
     * A private post has been made, mark it as done.
     *
     * @param {object} stream_post The post object after it has been made in the stream domain.
     *
     * @return void
     */
    var madePrivatePost = function (private_post) {
        if (testMadePost(private_post.post) === false) {
            error();
            return;
        }

        // The first post back will be the one from the makers domus, so use that.
        if(typeof made_post === 'undefined') {
            made_post = private_post.post;
        }
        returned_private_post_count++;
        // If all the domains have returned then finish the make post request.
        if (returned_private_post_count === domains_to_send_post_to.length) {
            finishPost();
        }
    };

    /**
     * Send all the private posts.
     *
     * @return {void}
     */
    var sendPrivatePosts = function () {
        jQuery.each(domains_to_send_post_to, function(index, domain){
            var private_post = {};
            jQuery.extend(true, private_post, post);
            private_post.secret = domain_secrets[domain];
            private_post.private_addresses = private_usernames[domain];

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain,
                'MakePost',
                {
                    post : private_post
                },
                true,
                madePrivatePost,
                error,
                timeout
            );
        });
    };

    /**
     * Ascertain that all secrets have been generated and then make the private posts.
     *
     * @param {string} domain The name of the domain that the secret is for.
     * @param {object} secret_data Contains the requested secret.
     * @param {string} secret_data.secret The secret.
     */
    var onPrivatePostSecretFetched = function (domain, secret_data) {
        if (typeof secret_data !== 'object' || typeof secret_data.secret !== 'string') {
            error();
            return;
        }
        domain_secrets[domain] = secret_data.secret;
        // When all the secrets have returned, make the stream post.
        if (Object.keys(domain_secrets).length === domains_to_send_post_to.length) {
            sendPrivatePosts();
        }
    };

    /**
     * Send a private post to each of the recipents domains and to the senders domain.
     *
     * First split the addresses by domain then fetch secrets for and send a single post to each domain.
     *
     * @return void
     * @tests both processes in this class need testing on multiple server setups.
     */
    var makePrivatePosts = function () {
        jQuery.each(post.private_addresses, function(i, full_username) {
            var domain = BabblingBrook.Library.extractDomainFromFullUsername(full_username);
            domains_to_send_post_to.push(domain);

            var username = BabblingBrook.Library.extractUsernameFromFullUsername(full_username);
            if (typeof private_usernames[domain] === 'undefined') {
                private_usernames[domain] = [];
            }
            private_usernames[domain].push(domain + '/' + username);
            return true;    // Continue with the .each
        });

        jQuery.each(domains_to_send_post_to, function(index, domain){
            if (domain === post.submitting_user.domain) {
                onPrivatePostSecretFetched(domain, {secret : 'false'});
            } else {
                generateSecret(domain, onPrivatePostSecretFetched.bind(null, domain));
            }
        });
    };

    /**
     * Callback for when an post has been sent to another domain.
     *
     * @param {object} domain_post The post object after it has been made in the remote domain.
     *
     * @return void
     */
    var onMadeDomainPost = function (domain_post) {
        if (testMadePost(domain_post.post) === false) {
            error();
            return;
        }
        returned_main_post_count++;
        // If all the domains have returned then finish the make post request.
        if (returned_main_post_count === domains_to_send_post_to.length) {
            finishPost();
        }
    };

    /**
     * Send the post to all domains that require it.
     *
     * @return void
     */
    var makeMainPosts = function () {
        var qty_of_domains = domains_to_send_post_to.length;
        // Skip the first domain, as that is the stream domain and it has already been submitted.
        for (var count = 1; count < qty_of_domains; count++) {
            // Extend the post because we don't want to record the secret in the post object
            // because when it is passed to the users domain it will cause an error.
            var tmp_post = {};
            jQuery.extend(true, tmp_post, post);
            tmp_post.secret = domain_secrets[domains_to_send_post_to[count]];
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domains_to_send_post_to[count],    // The stream domain is always the first in the array.
                'MakePost',
                {
                    post : tmp_post
                },
                true,
                onMadeDomainPost,
                error,
                timeout
            );
        }
    };

    /**
     * The stream post has been made. Set the rest of them in motion.
     *
     * @param {object} stream_post The post object after it has been made in the stream domain.
     *
     * @return void
     */
    var onMadeStreamPost = function (stream_post) {
        if (testMadePost(stream_post.post) === false) {
            error();
            return;
        }

        made_post = stream_post.post;

        post.revision = stream_post.revision;

        // If there is only one domanin then we are done.
        if (returned_main_post_count === domains_to_send_post_to.length) {
            finishPost();

        } else {
            // Start the process of storing the post in all the other domains that require it.
            makeMainPosts();
        }
    };

    /**
     * Make the post in the domain of the stream that the post is in.
     *
     * This is always submitted first as it returns the revision number used in submitions to other streams
     * so that they can be sure to all use the same revision (Two users may be simultaneously submitting edits).
     *
     * @param {string} stream_domain The domain for the stream that the post is being sent to.
     *
     * @return void
     */
    var makeStreamPost = function () {
        // Extend the post because we don't want to record the secret in the post object
        // because when it is passed to the users domain it will cause an error.
        var stream_post = {};
        jQuery.extend(true, stream_post, post);
        stream_post.secret = domain_secrets[domains_to_send_post_to[0]];
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            domains_to_send_post_to[0],    // The stream domain is always the first in the array.
            'MakePost',
            {
                post : stream_post
            },
            true,
            onMadeStreamPost,
            error,
            timeout
        );
    };

    /**
     * Recieves the request secret for a remote domain and sends the post to it.
     *
     * @param {string} domain The domain to send the post to.
     * @param {object} secret_data Contains the requested secret.
     * @param {string} secret_data.secret The secret for communicating with this domain.
     *
     * @return {void}
     */
    var storeSecret = function (domain, secret_data) {
        if (secret_data !== false && typeof secret_data !== 'object' || typeof secret_data.secret !== 'string') {
            error();
            return;
        }
        domain_secrets[domain] = secret_data.secret;

        // when all the secrets have returned, make the stream post.
        if (Object.keys(domain_secrets).length === domains_to_send_post_to.length) {
            makeStreamPost();
        }
    };

    /**
     * Fetch all the secrets for the domains that the new post is being pushed to.
     *
     * @return {void}
     */
    var fetchMainSecrets = function () {
        jQuery.each(domains_to_send_post_to, function (index, domain) {
            if (domain === post.submitting_user.domain) {
                storeSecret(domain, {secret : 'false'});
            } else {
                generateSecret(domain, storeSecret.bind(null, domain));
            }
        });
    }

    /**
     * Starts the process for making a public post.
     *
     * Adds all the domains to send the post to, to domains_to_send_post_to.
     *
     * @return {void}
     */
    var makePublicPost = function() {
        // The post is always sent to the stream domain.
        domains_to_send_post_to.push(post.stream.domain);

        // The post is also always sent to the user domain.
        if (domains_to_send_post_to.indexOf(BabblingBrook.Domus.User.domain) === -1) {
            domains_to_send_post_to.push(BabblingBrook.Domus.User.domain);
        }

        // If there is a parent domain, the post is sent to that.
        if (parent_domain !== false && domains_to_send_post_to.indexOf(parent_domain) === -1) {
            domains_to_send_post_to.push(parent_domain);
        }

        // If there are any revisions of an post with a different domain then they need
        // to be sent the post, but the post is still inserted if this process fails.
        jQuery.each(revision_posts, function(index, revision) {
            if (domains_to_send_post_to.indexOf(revision.domain) === -1) {
                if (extra_domains_to_send_post_to.indexOf(revision.domain) === -1) {
                    extra_domains_to_send_post_to.push(revision.domain);
                }
            }
        });
        fetchMainSecrets();
    };


    /**
     * Starts the process to create or edit an post.
     *
     * Called from each process until the neccessary data has been fetched.
     *
     * @return {void}
     */
    var makePost = function() {
        // If htis is a public post then we always need to know the parent domain.
        // If this is a top level post then parent_domain will be set to false.
        if (typeof parent_domain === 'undefined' && private_post === false) {
            return;
        }

        // If this is an edit then wait until we have checked that an post is editable.
        if (typeof post.post_id !== 'undefined') {
            if (post_ok_to_edit === false && stream_ok_to_edit === false) {
                error();
                return;
            }
            if (typeof post_ok_to_edit === 'undefined' && typeof stream_ok_to_edit === 'undefined') {
                return;
            }

        }

        // Ensure only one post is made.
        if (already_making_post === true) {
            return;
        }
        already_making_post = true;

        // If there are private addressses then this is a private post and it is not stored
        // in the stream domain, only the recciepents and the submitters domains.
        if (private_post === true) {
            makePrivatePosts();

        } else {
            makePublicPost();
        }
    };

    /**
     * All revisions of an post have been fetched.
     *
     * Check the domain of each one to see if it needs a copy of the post.
     *
     * @param {object[]} revisions An array of post objects, each a revision of the first post in the array.
     *
     * @return {void}
     */
    var onRevisionsFetched = function (revisions) {
        if (revisions.length < 1) {
            error();
            return;
        }
        revision_posts = revisions;

        if (revisions[0].username === post.submitting_user.username
            && revisions[0].domain === post.submitting_user.domain
        ) {
            post_ok_to_edit = true;
        }
        makePost();
    };

    /**
     * Fetches all the post revisions, so that the domain of each can be sent a copy of the post.
     *
     * @return {void}
     */
    var getAllRevisions = function () {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            post.stream.domain,
            'FetchData',
            {
                url : post.stream.domain + '/post/' + post.stream.domain + '/' + post.post_id + '/revisions',
                data : {},
                client_domain : BabblingBrook.Domus.User.domain
            },
            false,
            onRevisionsFetched,
            error,
            timeout
        );
    };


    /**
     * Stream details for the post that is being edited.
     *
     * @pram {object} stream See BabblingBrook.Models.stream for full definition.
     *
     * @return void
     */
    var onPostStreamFetched = function (stream) {
        if (stream.edit_mode === 'anyone') {
            stream_ok_to_edit = true;
        }
        makePost();
    };

    /**
     * Fetch the stream details for the post that has been submitted.
     *
     * @return void
     */
    var getPostStream = function () {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            post.stream.domain,
            'FetchData',
            {
                url : BabblingBrook.Library.makeStreamUrl(post.stream, 'json'),
                data : {},
                client_domain : BabblingBrook.Domus.User.domain
            },
            false,
            onPostStreamFetched,
            error,
            timeout
        );
    };

    /**
     * Checks if an post can be edited and then edits the post.
     *
     * An post is editable if the post is owned by the current user or the stream permits anyone to edit it.
     *
     * @return void
     */
    var verifyPostIsEditable = function() {
        getAllRevisions();
        getPostStream();
    };

    /**
     * Recieves the parent post and decides if the new post needs submittting to its domain.
     *
     * @param {object} parent_post The parent post for the post being submitted.
     *
     * @return void
     */
    var fetchedParentPost = function (parent_post) {
        if (testMadePost(parent_post) === false) {
            error();
            return;
        }

        parent_domain = parent_post.domain;
        makePost();
    };


    /**
     * Fetch the domain of the user who submitted the parent post.
     *
     * This is retrieved from the stream that hosts the post by fetching its post object.
     * The result is passed to makeParentPost via a callback.
     * Assumes that there is a parent secret and that this needs to be called.
     *
     * @return void
     */
    var getParentPostUserDomain = function () {
        if (typeof post.parent_id === 'undefined') {
            parent_domain = false;
            makePost();
            return;
        }

        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            post.stream.domain,
            'GetPost',
            {
                post_id : post.parent_id
            },
            false,
            fetchedParentPost,
            error,
            timeout
        );
    };

    /**
     * Constructor that starts the process of making the post.
     *
     * The function divides the process into one of two streams depending on if the post is private or not.
     *
     * @return void
     */
    var setup = function() {
        // Set revision to be undefined to ensure it is not spoofed in the data passed in from the client.
        post.revision = undefined;

        // Add the logged on user to the post object.
        post.submitting_user = {
            username : BabblingBrook.Domus.User.username,
            domain : BabblingBrook.Domus.User.domain
        };

        if (typeof post.private_addresses !== 'undefined' && post.private_addresses.length > 0) {
            private_post = true;
        } else {
            private_post = false;
            // Only public posts need to fetch the parent post domain.
            getParentPostUserDomain();
        }

        // If this is an edit then the stream and origional post needs to be fetched so that we can be sure
        // that this post is allowed to be edited.
        if (typeof post.post_id !== 'undefined') {
            verifyPostIsEditable();
        }

        makePost();
    };

    // Call the constructor.
    setup();

};
