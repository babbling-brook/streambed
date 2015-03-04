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
 * @fileOverview Code relating to the create and update forms for Rings.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}

/**
 *  @namespace Javascript singleton that works with the ring admin page.
 */
BabblingBrook.Client.Page.Ring.Admin = (function () {
    'use strict';

    /**
     * Shows form errors
     *
     * @param object errors An error object with fields indexed by their form names.
     *
     * @returns {undefined}
     */
    var showErrors = function (errors) {
        jQuery.each(errors, function (error_name, error) {
            jQuery('#' + error_name + '_error')
                .text(error)
                .removeClass('hide');
        });
    };

    /**
     * Callback for when the server responds to updating a ring.
     *
     * @param {object} response_data
     *
     * @returns {undefined}
     */
    var onRingUpdated = function (response_data) {
        jQuery('#update_ring_submit').removeClass('button-loading');
        jQuery('#create_ring_submit').removeClass('button-loading');
        if (typeof response_data.errors !== 'undefined') {
            showErrors(response_data.errors);
        }

        jQuery.each(BabblingBrook.Client.User.Rings, function(i, ring) {
            if (ring.domain === window.location.hostname && ring.name === jQuery('#ring_name').val()) {
                BabblingBrook.Client.User.Rings[i] = response_data.updated_ring;
                return false;   // exit the .each.
            }
            return true;        // continue the .each.
        });

        if (response_data.updated_ring.member_type === 'admin_invitation') {
            jQuery('#ring_member_invite_menu').removeClass('hide');
        } else {
            jQuery('#ring_member_invite_menu').addClass('hide');
        }

        if (response_data.updated_ring.admin_type === 'invitation') {
            jQuery('#ring_admin_invite_menu').removeClass('hide');
            jQuery('#ring_resign_menu').removeClass('hide');
        } else {
            jQuery('#ring_admin_invite_menu').addClass('hide');
            jQuery('#ring_resign_menu').addClass('hide');
        }
    };

    /**
     * Callback for when the server responds to creating a ring.
     *
     * @param {object} response_data
     *
     * @returns {undefined}
     */
    var onRingCreated = function (response_data) {
        jQuery('#create_ring_submit').removeClass('button-loading');
        if (typeof response_data.errors !== 'undefined') {
            showErrors(response_data.errors);
        } else {
            jQuery('#update_ring_submit').removeClass('hide');
            jQuery('#create_ring_submit').addClass('hide');
            jQuery('#ring_name').attr("disabled", "disabled");
            var protocol = window.location.protocol;
            var domain = window.location.host;
            var url = protocol + '//' + domain + '/' + jQuery('#ring_name').val() + '/ring/update';
            var title = jQuery('#update_title_template').text();
            BabblingBrook.Client.User.Rings.push(response_data.new_ring);
            BabblingBrook.Client.Core.Ajaxurl.redirect(url);
        }
    };

    /**
     * Callback for when the server responds to creating a ring.
     *
     * @param {object} response_data
     *
     * @returns {undefined}
     */
    var onRingServerError = function () {
        jQuery('#invitations_loading').addClass('hide');
        jQuery('#update_ring_submit').removeClass('button-loading');
        jQuery('#create_ring_submit').removeClass('button-loading');
        var message = jQuery('#on_ring_update_server_error_template').text();
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });
    };

    /**
     * Collects the forms field details.
     *
     * @returns {undefined}
     */
    var collectFormFields = function () {
        var form = {
            name : jQuery('#ring_name').val(),
            membership : jQuery('#membership_type').val(),
            membership_rhythm : jQuery('#membership_rhythm').val(),
            membership_super_ring : jQuery('#membership_super_ring').val(),
            admin_type : jQuery('#admin_type').val(),
            admin_super_ring : jQuery('#admin_super_ring').val(),
            ring_rhythm : jQuery('#ring_rhythm').val(),
        }
        return form;
    };

    /**
     * Callback for when the update button is pressed.
     *
     * @returns {undefined}
     */
    var onUpdateRingClicked = function () {
        var form = collectFormFields();
        jQuery('#update_ring_submit').addClass('button-loading');
        jQuery('.error').addClass('hide');
        BabblingBrook.Library.post(
            '/' + jQuery('#ring_name').val() + '/ring/updatejson',
            form,
            onRingUpdated,
            onRingServerError
        )
    };

    /**
     * Callback for when the create button is clicked.
     *
     * @returns {undefined}
     */
    var onCreateRingClicked = function () {
        var form = collectFormFields();
        jQuery('#create_ring_submit').addClass('button-loading');
        jQuery('.error').addClass('hide');
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/ring/createjson',
            form,
            onRingCreated,
            onRingServerError

        )
    };

    return {

        construct : function() {

            jQuery('#update_ring_submit').click(onUpdateRingClicked);
            jQuery('#create_ring_submit').click(onCreateRingClicked);

            // Show membership hidden options.
            jQuery('#membership_type').change(function () {
                jQuery('#membership_rhythm_textfield').addClass('hide');
                jQuery('#membership_super_ring_textfield').addClass('hide');
                jQuery('#membership_rhythm_search').text('search');
                jQuery('#membership_super_ring_search').text('search');
                var option = jQuery('option:selected', this).val();
                jQuery('#membership_rhythm_textfield').addClass('hide').css('display','');
                jQuery('#membership_super_ring_selector').addClass('hide').css('display','');
                jQuery('#membership_rhythm_selector').addClass('hide').css('display','');
                jQuery('#membership_super_ring_textfield').addClass('hide').css('display','');
                if (option === '56') {
                    jQuery('#membership_rhythm_textfield').removeClass('hide');

                } else if (option === '57') {
                    jQuery('#membership_super_ring_textfield').removeClass('hide');
                }
            });
            if (jQuery('#membership_type option:selected').val() === '56') {
                jQuery('#membership_rhythm_textfield').removeClass('hide');
            }
            if (jQuery('#membership_type option:selected').val() === '57') {        // super_ring.
                jQuery('#membership_super_ring_textfield').removeClass('hide');
            }

            // Show Admin hidden options.
            var admin_value;
            jQuery('#admin_type')
                .click(function () {
                    admin_value = jQuery(this).val();
                })
                .change(function () {
                    // Warning for admin drop down change.
                    var option = jQuery('option:selected', this).val();
                    var success = true;
                    if (option === '60') {        // super_ring.
                        success = confirm('If you select an option here that locks you out then you will no '
                            + 'longer be able to edit these options. Please confirm.');
                    }
                    jQuery('#admin_super_ring_textfield').addClass('hide');
                    jQuery('#admin_super_ring_selector').hide().addClass('hide');
                    jQuery('#admin_super_ring_search').text('search');

                    if (success === false) {
                        jQuery('#admin_type').val(admin_value);
                    }
                    if (option === '60' && success === true) {        // super_ring.
                        jQuery('#admin_super_ring_textfield').removeClass('hide');
                    } else {
                        jQuery('#admin_super_ring_textfield').addClass('hide');
                    }
                });
            // Open hidden fields if required on page reload.
            if (jQuery('#admin_type option:selected').val() === '60') {        // super_ring.
                jQuery('#admin_super_ring_textfield').removeClass('hide');
            }

            // Show a rhythm selector when the membership Rhythm selector is selected.
            var jq_rhythm_search = jQuery('#membership_rhythm_selector');
            jQuery('#membership_rhythm_search').click(function () {

                var jq_search = jQuery(this);

                if (jq_rhythm_search.is(':visible')) {
                    jq_rhythm_search.slideUp(250, function () {
                        jq_search.text("search");
                        jq_rhythm_search.empty();
                    });
                    return false;
                }

                var actions = [
                {
                    name : 'Select',
                    onClick : function (event, jq_row, row) {
                        event.preventDefault();
                        var rhythm_url = BabblingBrook.Library.makeRhythmUrl(
                            {
                                domain : row.domain,
                                username : row.username,
                                name : row.name,
                                version : row.version
                            }
                        );
                        jQuery('#membership_rhythm').val(rhythm_url);
                        jq_rhythm_search.slideUp(250, function (){
                            jq_search.text("search");
                            jq_rhythm_search.empty();
                        });
                    }
                }
                ];
                var rhythm_search_table = new BabblingBrook.Client.Component.Selector(
                    'rhythm',
                    'member_rhythm',
                    jq_rhythm_search,
                    actions,
                    {
                        show_fields : {
                            domain : false
                        },
                        initial_values : {
                            rhythm_category : 'ring'
                        }
                    }
                );

                jq_search.text("close search");
                jq_rhythm_search
                    .slideDown(250)
                    .removeClass('hide');
                return false;
            });

            // Show an Rhythm selector when the ring Rhythm selector is selected.
            var jq_rhythm_ring_search = jQuery('#ring_rhythm_selector');
            jQuery('#ring_rhythm_search').click(function () {

                var jq_search = jQuery(this);

                if (jq_rhythm_ring_search.is(':visible')) {
                    jq_rhythm_ring_search.slideUp(250, function () {
                        jq_search.text("search");
                        jq_rhythm_ring_search.empty();
                    });
                    return false;
                }

                var actions = [
                {
                    name : 'Select',
                    onClick : function (event, jq_row, row) {
                        event.preventDefault();
                        var rhythm_url = BabblingBrook.Library.makeRhythmUrl(
                            {
                                domain : row.domain,
                                username : row.username,
                                name : row.name,
                                version : row.version
                            }
                        );
                        jQuery('#ring_rhythm').val(rhythm_url);
                        jq_rhythm_ring_search.slideUp(250, function () {
                            jq_search.text("search");
                            jq_rhythm_ring_search.empty();
                        });
                    }
                }
                ];
                var rhythm_ring_search_table = new BabblingBrook.Client.Component.Selector(
                    'rhythm',
                    'ring_rhythm',
                    jq_rhythm_ring_search,
                    actions,
                    {
                        show_fields : {
                            domain : false
                        },
                        initial_values : {
                            rhythm_category : 'ring'
                        }
                    }
                );

                jq_search.text("close search");
                jq_rhythm_ring_search
                    .slideDown(250)
                    .removeClass('hide');
                return false;
            });

            // Show a user selector when the membership super ring selector is selected.
            var jq_member_user_search = jQuery('#membership_super_ring_selector');
            jQuery('#membership_super_ring_search').click(function () {

                var jq_search = jQuery(this);

                if (jq_member_user_search.is(':visible')) {
                    jq_member_user_search.slideUp(250, function () {
                        jq_search.text("search");
                        jq_member_user_search.empty();
                    });
                    return false;
                }

                var actions = [
                {
                    name : 'Select',
                    onClick : function (event, jq_row, row) {
                        event.preventDefault();
                        jQuery('#membership_super_ring').val(row.username);
                        jq_member_user_search.slideUp(250, function () {
                            jq_search.text("search");
                            jq_member_user_search.empty();
                        });
                    }
                }
                ];
                var member_user_search_table = new BabblingBrook.Client.Component.Selector(
                    'user',
                    'member_ring_user',
                    jq_member_user_search,
                    actions,
                    {
                        user_type : 'ring',
                        show_fields : {
                            domain : false
                        },
                        initial_values : {
                            domain : window.location.hostname
                        },
                        exact_match : {
                            domain : true
                        }
                    }
                );

                jq_search.text("close search");
                jq_member_user_search
                    .slideDown(250)
                    .removeClass('hide');
                return false;

            });

            // Show a user selector when the super ring selector is selected.
            var jq_user_search = jQuery('#admin_super_ring_selector');
            jQuery('#admin_super_ring_search').click(function () {

                var jq_search = jQuery(this);

                if (jq_user_search.is(':visible')) {
                    jq_user_search.slideUp(250, function () {
                        jq_search.text("search");
                        jq_user_search.empty();
                    });
                    return false;
                }

                var actions = [
                {
                    name : 'Select',
                    onClick : function (event, jq_row, row) {
                        event.preventDefault();
                        jQuery('#admin_super_ring').val(row.username);
                        jq_user_search.slideUp(250, function () {
                            jq_search.text("search");
                            jq_user_search.empty();
                        });
                    }
                }
                ];
                var user_search_table = new BabblingBrook.Client.Component.Selector(
                    'user',
                    'admin_ring_user',
                    jq_user_search,
                    actions,
                    {
                        user_type : 'ring',
                        show_fields : {
                            domain : false
                        },
                        initial_values : {
                            domain : window.location.hostname
                        },
                        exact_match : {
                            domain : true
                        }
                    }
                );

                jq_search.text("close search");
                jq_user_search
                    .slideDown(250)
                    .removeClass('hide');
                return false;

            });

            BabblingBrook.Client.Core.Loaded.setRingAdminLoaded();

        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.Admin.construct();
});