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
 * Stream view for users who are not logged in and bots.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'User/StreamSubscriptions'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'User/StreamSubscriptions'));
?>
<h2> Edit your stream subscriptions </h1>
<div class="content-indent larger">
    Select the details link of the subscription you wish to edit or select streams to subscribe to.
</div>

<ul id="edit_streams">

</ul>
<div></div>
<div id="select_streams" class="content-indent">
    <ul id="choose_stream">
        <li id="stream_suggestions_opener" class="closed">

        </li>
        <li class="closed" id="stream_search_opener">
            <a class="search"></a>
            <div id="stream_search_container" class="hide"></div>
        </li>
    </ul>
</div>

<?php //These are html templates that are required by the default javascript classes. ?>
<div id="edit_streams_templates" class="hide">

    <div id="stream_search_opener_message_template">
        Search for more streams
    </div>

    <div id="stream_search_closer_message_template">
        Close stream search
    </div>

    <div id="stream_suggestions_opener_message_template">
        Open stream suggestions
    </div>

    <div id="stream_suggestions_closer_message_template">
        Close stream suggestions
    </div>

    <div id="no_suggestions_template" class="hide">
        <div>
            No suggestions are available.
        </div>
    </div>

    <table id="search_stream_description_template">
        <tbody>
            <tr class="search-stream-description-row">
                <td colspan="100">
                    <div class="search-stream-description hide"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <table id="search_rhythm_description_template">
        <tbody>
            <tr class="search-rhythm-description-row">
                <td colspan="100">
                    <div class="search-rhythm-description hide"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <ul id="stream_subscription_unsubscribe_filter_template">
        <li data-filter-id="" title="">
            <span class="filter-name"></span>
            <a class="delete filter-delete">unsubscribe</a>
            <a class="details filter-details">details</a>
        </li>
    </ul>

    <div id="subscription_filter_details_template">
        <div class="full-name">
            <strong>Full name: </strong>
            <span class="full-name"></span>
        </div>
        <div>
            <strong>Description: </strong>
            <span class="filter-description"></span>
        </div>
        <div class="change-version-row filter-version-row">
            <strong>Version: </strong>
            <span class="version-description"></span>
            <span class="filter-version-selectors hide">
                <select class="select-new-version filter-versions major-version">
                </select> /
                <select class="select-new-version filter-versions minor-version">
                </select> /
                <select class="select-new-version filter-versions patch-version">
                </select>
            </span>
            <a title="Change Version" class="change-version">change version</a>
            <a title="Cancel" class="cancel-version-change hide">cancel change version</a>
        </div>
    </div>

    <table id="subscription_filter_suggestion_table_template">
        <tbody>
            <tr data-filter-name="" data-filter-domain="" data-filter-username="" data-filter-version="">
                <td class="rhythm" title=""></td>
                <td class="add">
                    <a href="" class="add-suggestion-filter">Add</a>
                </td>
                <td class="view">
                    <a href="" class="view-suggestion-filter">View</a>
                </td>
            </tr>
        </tbody>
    </table>

    <div id="subscription_filter_suggestion_template">
        <div class="filter-suggestions">
            <table class="filter-suggestions-table selector"></table>
        </div>
    </div>

    <ul id="moderation_line_template">
        <li title="">
            <span class="ring-name"></span>
            <a class="delete">remove</a>
            <a class="details" href="">view profile</a>
        </li>
    </ul>

    <table id="moderation_ring_suggetstion_line_template">
        <tbody>
            <tr data-ring-name="" data-ring-domain="">
                <td class="ring" title=""></td>
                <td class="add ">
                    <a href="" class="add-suggested-moderation-ring">Subscribe</a>
                </td>
                <td class="view">
                    <a class="view-suggested-moderation-ring" href="">View</a>
                </td>
            </tr>
        </tbody>
    </table>

    <div id="moderation_ring_suggestion_container_template">
        <div class="moderation-suggestions hide">
            <table class="selector"></table>
        </div>
    </div>

    <div id="stream_details_line_template">
        <div class="full-name">
            <strong>Full name: </strong>
            <span class="full-name"></span>
        </div>
        <div>
            <strong>Description: </strong><span class="stream-description"></span>
        </div>
        <div class="change-version-row stream-version-row">
            <strong>Version: </strong>
            <span class="version-description"></span>
            <span class="stream-version-selectors hide">
                <select class="select-new-version stream-versions major-version">
                </select> /
                <select class="select-new-version stream-versions minor-version">
                </select> /
                <select class="select-new-version stream-versions patch-version">
                </select>
            </span>
            <a title="Change Version" class="change-version">change version</a>
            <a title="Cancel" class="cancel-version-change hide">cancel change version</a>
        </div>
        <div>
            <strong>Filter Rhythms: </strong>
            <ul class="filters"></ul>
            <ul class="filter-options">
                <li class="filter-suggest-opener">
                    <a class="suggest-new-filter">Show rhythm suggestions</a>
                </li>
                <li class="filter-search-opener">
                    <a class="search-new-filter">Search for a new rhythm</a>
                </li>
            </ul>
        </div>
        <div class="moderation-block">
            <strong>Moderation Rings: </strong>
            <ul class="moderation-rings">
            </ul>
            <ul class="moderation-rings-options">
                <li class="suggestion-moderation-rings-opener">
                    <a class="suggest-new-moderation-ring" href="">
                        Show moderation ring suggestions</a>
                </li>
                <li class="search-moderation-rings-opener">
                    <a class="search-new-moderation-ring" href="#">
                        Search for moderation rings</a>
                </li>
            </ul>
        </div>
    </div>

    <ul id="subscribed_line_template">
        <li data-subscription-id="">
            <span class="stream-name"></span>
            <a class="delete" href="">unsubscribe</a>
            <a class="details" href="">details</a>
        </li>
    </ul>

    <table id="stream_suggestion_row_template">
        <tr data-stream-name="">
            <td class="add">
                <a class="add-suggestion-stream">Add</a>
            </td>
            <td class="view">
                <a class="view-suggestion-stream">View</a>
            </td>
            <td title="" class="rhythm">
            </td>
        </tr>
    </table>

    <select id="Page_StreamSubscriptions_select_version_loading_template">
        <option class="option-loading" selected disabled>Loading ...</option>
    </select>
</div>
