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
 * @fileOverview Represents sorted results in a tree structure.
 * @author Sky Wickenden
 */

/**
 * Represents sorted results in a tree structure.
 * Used by DisplayTree To show a tree of posts.
 */
BabblingBrook.Client.Core.Tree = (function () {
    'use strict';
    /**
     * @type {object} trees Different sets of tree nodes.
     * @type {object} trees[domain] The domain for the parent post.
     * @type {object} trees[domain][post_id] The parent post id.
     * @type {object} trees[domain][post_id][filter_url] The url of the filter used to create this tree.
     * @type {object} trees[domain][post_id][filter_url].nodes The nodes object for this tree.
     */
    var trees = [];

    /**
     * @type {object} current Holds location data about the current tree. Used to look up current tree in trees.
     * @type {string} current.post_id
     * @type {string} current.post_domain
     * @type {string} current.filter_url
     */
    var current = {};

    /**
     * @type {object} nodes Holds all the nodes in a flat format.
     * @type {object} nodes.data The data that is held in this node.
     * @type {object} nodes.parent Points to the parent object of this node.
     * @type {object[]} nodes.children An array of the child nodes of this node.
     * @type {boolean} nodes.shown Has this node been displayed.
     */
    var nodes = {};

    /**
     * @type {object} current_root_node The current root node.
     */
    var root_node;

    /**
     * A sort function to sort the nodes by the data.sort value in each node.
     * @param {number} a The first node to compare.
     * @param {number} b The second node to compare.
     * @return {boolean} Swap these nodes or not.
     */
    var comparer = function (a, b) {
        return a.data.sort < b.data.sort ? 1 : 0;
    };

    /**
     * Sorts all the nodes so that they are in the correct order according to each nodes data.sort value.
     * @param {object} node A reference to the node in the nodes object.
     */
    var sortRecursive = function (node) {
        node.children.sort(comparer);
        var len = node.children.length;
        var i;
        for (i = 0; i < len; i++) {
            sortRecursive(node.children[i]);
        }
    };

    /**
     * Create a new node with the passed in data.
     * @param {object} data The data that is associated with this node.
     */
    var create_node = function (data) {
        var node = {
            data : data,
            parent : null,
            children : []
        };
        return node;
    };


    /**
     * Walk through the nodes in nested and then sorted order, calling the passed in callback for each node.
     *
     * @param {function} callback A callback function to call for each node.
     * @param {boolean} recursive Should the walkback be recursive, or just fetch the top level results.
     * @param {Object|Undefined} node The node that is currently being walked.
     *                                Ommit this value and the root node will be used.
     * @param {Number|Undefined} depth The current depth in the iteration. Undefined defaults to 1.
     * @param {boolean} update Is this an update walk. Defaults to false.
     *
     * @return void
     */
    var walk = function (callback, recursive, node, depth, update) {
        if (typeof node === 'undefined') {
            node = root_node;
        }

        if (typeof depth === 'undefined') {
            depth = 0;
        } else {
            ++depth;
        }

        if (typeof update === 'undefined') {
            update = false;
        }
        var i, len;
        for (i = 0, len = node.children.length; i < len; i++) {
            var child = node.children[i];
            child.data.depth = depth;
            // Only callback if not an update or not shown before.
            if (!update || (typeof child.shown === 'undefined' || child.shown === false)) {
                var has_children = false;
                if (typeof child.children !== 'undefined' && child.children.length > 0) {
                    has_children = true;
                }
                callback(child.data, has_children);
            }

            child.shown = true;
            if (recursive) {
                walk(callback, recursive, child, depth, update);
            }
        }
    };

    /**
     * Switch to a different tree.
     * @param {number} top_parent_post_id The top parent post id of the tree.
     * @param {string} post_domain The domain of the top parent post id of the tree.
     * @param {string} filter_url The filter url that was used to sort the tree.
     */
    var switchTree = function (top_parent_post_id, post_domain, filter_url) {

        // If the new tree is the current one then exit.
        if (current.post_id === top_parent_post_id
            && current.post_domain === post_domain
            && current.filter_url === filter_url
        ) {
            return;
        }

        // If this is the first tree then set it as current and exit.
        if (typeof current.post_id === 'undefined') {
            current = {
                post_domain : post_domain,
                post_id : top_parent_post_id,
                filter_url : filter_url
            };
            return;
        }

        // Put the old current data into the tree.
        BabblingBrook.Library.createNestedObjects(trees, [current.post_domain, current.post_id, current.filter_url]);
        trees[current.post_domain][current.post_id][current.filter_url] = nodes;

        // Change the current data.
        current = {
            post_domain : post_domain,
            post_id : top_parent_post_id,
            filter_url : filter_url
        };

        // If the new tree exists then make it current. else make a new tree.
        if (BabblingBrook.Library.doesNestedObjectExist(trees, [post_domain, top_parent_post_id, filter_url])) {
            nodes = trees[post_domain][top_parent_post_id][filter_url];
        } else {
            nodes = {};
        }

        // Store the root node and switch to the new one.
        root_node = nodes[top_parent_post_id];

    };

    return {

        /**
         * Create a new tree of data.
         *
         * @param {object[]} data An array of data objects to transorm into a tree.
         * @param {number} data[].post_id The id of this node.
         * @param {number} data[].parent_id The parent id of this node.
         * @param {number} top_parent_post_id The top parent post id of the tree.
         * @param {string} post_domain The domain of the top parent post id of the tree.
         * @param {string} filter_url The filter url that was used to sort the tree.
         *
         * @return void
         */
        create : function (data, top_parent_post_id, post_domain, filter_url) {

            // Store any tree data that is currently in use.
            switchTree(top_parent_post_id, post_domain, filter_url);
            var i;
            var len = data.length;

            // Create an empty root node.
            nodes[top_parent_post_id] = create_node({});
            root_node = nodes[top_parent_post_id];

            // Make node objects for each item.
            for (i = 0; i < len; i++) {
                if (typeof data[i].sort !== 'undefined') {
                    nodes[data[i].post_id] = create_node(data[i]);
                }
            }

            // Link all TreeNode objects.
            for (i = 0; i < len; i++) {
                var node = nodes[data[i].post_id];
                node.parent = nodes[node.data.parent_id];
                // If the parent is not found then skip it.
                if (typeof node.parent === 'undefined') {
                    console.log('Parent not found for post_id:' + data[i].post_id);
                    continue;
                }
                node.parent.children.push(node);
            }
        },

        /**
         * Update the given tree with this new data.
         *
         * @param {object[]} data An array of data objects to transform into a tree.
         * @param {number} data.post_id The id of this node.
         * @param {number} data.parent_id The parent id of this node.
         * @param {number} top_parent_post_id The top parent post id of the tree.
         * @param {string} post_domain The domain of the top parent post id of the tree.
         * @param {string} filter_url The filter url that was used to sort the tree.
         *
         * @return void
         */
        update : function (data, top_parent_post_id, post_domain, filter_url) {
            // Store current tree data.
            switchTree(top_parent_post_id, post_domain, filter_url);
            var i;
            var len = data.length;

            // Make node objects for each item.
            for (i = 0; i < len; i++) {
            //    if (typeof nodes[ data[i].post_id ] !== 'undefined')

                if (typeof data[i].sort !== 'undefined' && typeof nodes[data[i].post_id] === 'undefined') {
                    nodes[data[i].post_id] = create_node(data[i]);
                }
            }

            // Link all TreeNode objects.
            for (i = 0; i < len; i++) {
                var node = nodes[data[i].post_id];
                node.parent = nodes[node.data.parent_id];
                // If the parent is not found then skip it.
                if (typeof node.parent === 'undefined') {
                    console.log('Parent not found for post_id:' + data[i].post_id);
                    continue;
                }
                var depth = nodes[node.data.parent_id].data.depth;
                //if (typeof depth === 'undefined')
                //    depth = 0;
                node.data.depth = depth + 1;
                var found = false;
                var child;
                for (child in node.parent.children) {
                    if (node.parent.children.hasOwnProperty(child)) {
                        if (node.parent.children[child].data.post_id === data[i].post_id) {
                            found = true;
                            // If this is an update then mark it so that it will be passed in for display.
                            if (node.parent.children[child].data.revision !== data[i].revision) {
                                node.parent.children[child].shown = false;
                                node.data = data[i];
                            }
                            break;
                        }
                    }
                }
                if (!found) {
                    node.parent.children.push(node);
                }
            }
        },

        /**
         * Display a tree of post data.
         *
         * @param {number} top_parent_post_id The top parent post id of the tree.
         * @param {string} post_domain The domain of the top parent post id of the tree.
         * @param {string} filter_url The filter url that was used to sort the tree.
         * @param {string} element The ID of the html element that the tree is to be placed in.
         * @param {function} callback Function to display a sub post.
         *
         * @return void
         */
        displayTree : function (top_parent_post_id, post_domain, filter_url, callback) {
            switchTree(top_parent_post_id, post_domain, filter_url);

            // Sort each branch by sort order.
            sortRecursive(nodes[current.post_id]);
            // Walk through the list of nodes in the correct order and perform the callback function on each line.
            walk(callback, true, nodes[current.post_id]);

        },

        displayTreeUpdate : function (top_parent_post_id, post_domain, filter_url, callback) {

            switchTree(top_parent_post_id, post_domain, filter_url);

            // Sort each branch by sort order.
            sortRecursive(nodes[current.post_id]);

            // Walk through the list of nodes in the correct order and perform this function on each new line.
            walk(callback, true, undefined, undefined, true);

        }

    };
}());