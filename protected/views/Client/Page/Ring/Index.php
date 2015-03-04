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
 * View for index page for a users rings.
 */
$this->pageTitle=Yii::app()->name . ' - Your Rings';
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Ring/Menu'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/Menu'));

// @fixme rename this page to menu (js file already renamed.)
?>

<h2>Rings</h2>

<?php if (isset($leave_ring) === true) { ?>
    <div class="content-block-2 content-indent">
        You have resigned your membership of the <em><?php echo $leave_ring; ?></em> ring.
    </div>
<?php } ?>

<div id="member_rings" class="content-indent ring-menu">
    <h3>Ring membership</h3>
    <div id="member_rings_loading" class="block-loading line-loading"></div>
    <table class="ring-table hide">
        <tbody>

        </tbody>
    </table>
    <div id="no_membership_rings">You are not a member of any rings</div>
    <a id="join_rings" href="">Search for rings</a>
    <div id="search_rings" class="hide"></div>
</div>

<div id="admin_rings" class="content-indent ring-menu">
    <h3>Rings you administer</h3>
    <div id="admin_rings_loading" class="block-loading line-loading"></div>
    <table class="ring-table hide">
        <tbody>

        </tbody>
    </table>
    <div id="no_admin_rings">You do not administrate any rings</div>
    <a href="create">Create a new ring</a>
</div>

<div id="invitations" class="content-indent ring-menu">
    <h3>Invitations to join rings</h3>
    <div id="invitations_loading" class="block-loading line-loading"></div>
    <div id="invitations_none" class="hide">There are no invitations</div>
    <table id="invitations_table" class="ring-table hide">
        <tbody>
        </tbody>
    </table>
</div>

<div id="ring_templates" class="hide">
    <table id="admin_row_template">
        <tbody>
            <tr>
                <td>
                    <span class="ring-name"></span>
                </td>
                <td>
                    <a class="member-invitation hide" href="">send a member invitation</a>
                    <a class="admin-invitation hide" href="">send an admin invitation</a>
                </td>
                <td>
                    <a href="" class="profile-page">profile</a>
                </td>
                <td>
                    <a href="" class="edit-profile">edit profile</a>
                </td>
                <td>
                    <a href="" class="vet-users">
                        <span class="vet-users-content hide text-loading">
                            vet users (<span class="vet-users-qty"></span>)
                        </span>
                    </a>
                </td>
                <td>
                    <a href="" class="admin-page">admin</a>
                </td>
            </tr>
        </tbody>
    </table>

    <table id="member_row_template">
        <tbody>
            <tr>
                <td>
                    <span class="ring-name"></span>
                </td>
                <td>
                    <a class="member-invitation hide" href="">send a member invitation</a>
                </td>
                <td>
                    <a href="" class="profile-page">profile</a>
                </td>
                <td>
                    <a href="" class="members-area">members area</a>
                </td>
            </tr>
        </tbody>
    </table>

    <table id="invites_row_template">
        <tbody>
            <tr>
                <td class="invite-details">

                </td>

                <td>
                    <a href="" class="invite-join">join</a>
                </td>
            </tr>
        </tbody>
    </table>

    <div id="join_as_member_invitation_template">
        <div>
            Join the <span class="ring-name"></span> ring as a member.
            Invitation sent by <span class="from-user"></span>
        </div>
    </div>

    <div id="join_as_admin_invitation_template">
        <div>
            Join the <span class="ring-name"></span> ring as an administrator.
            Invitation sent by <span class="from-user"></span>
        </div>
    </div>

    <div id="on_invite_accepted_generic_error_template">
        There was an error when accepting your invitation to join a ring.
    </div>

    <div id="on_fetching_invitations_error_template">
        There was an error fetching your ring invitations.
    </div>

    <div id="on_fetching_ring_users_waiting_to_be_vetted_error_template">
        There was an error fetching the number of membership requests waiting for the
        <span class="waiting-to-be-vetted-ring"></span> ring.
    </div>

</div>
