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
 * Returns help text for Stream views
 *
 * @package PHP_Help
 */
class StreamHelp extends Help
{

    /**
     * Name help.
     *
     * @return string Help tags.
     */
    public function name() {
        $title = "Name";
        $description = "
            <p>Your stream name is required and needs to be unique amongst your streams.</p>
            <p>The name must be lowercase, as it is used as part of the url for its location.</p>
            <p>
                It also needs to be alpha numeric and start and end with a letter,
                single spaces are also allowed, but not at the start and end of the name.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Name help for tag (user) streams.
     *
     * @return string Help tags.
     */
    public function tagName() {
        $title = "Tag Name";
        $description = "
            <p>
                The tag must be unique to you.
                It must be lowercase, as it is used as part of the url for its location.
            </p>
            <p>
                It also needs to be alpha numeric and start and end with a letter,
                single spaces are also allowed, but not at the start and end of the name.
            </p>
            <p>
                Tags are a special kind of stream. Once you have created it you can edit it from your streams
                menu.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Description help.
     *
     * @return string Help tags.
     */
    public function description() {
        $title = "Description";
        $description = "
            <p>This should describe the purpose of your stream in plain english.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Presentation type help.
     *
     * @return string Help tags.
     */
    public function presentationType() {
        $title = "Presentation Type";
        $description = "
            <p>
                This selects the default presentation type for this stream. Client sites do not have to obay this
                option. It provides an indication of how the posts are expected to be displayed.
            </p>
            <ul>
                <li><strong>list</strong>. A simple list of posts.</li>
                <li><strong>photowall</strong>. A wall of images.</li>
            </ul>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Kind help.
     *
     * @return string Help tags.
     */
    public function kind() {
        $title = "Kind";
        $description = "
            <p>Some streams have special features, these are selected here.</p>
            <p>Standard should be selected for most streams.</p>
            <p>User should be selected if this stream is used to rate users.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Description help.
     *
     * @return string Help tags.
     */
    public function tags() {
        $title = "Tags";
        $description = "
            <p>
                Tags have two purposes. Firstly, they aid in searching and secondly they allow
                delta rhythms to group different streams together.
            </p>
            <p>Popular suggestions will appear when you start typing.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Status help.
     *
     * @return string Help tags.
     */
    public function status() {
        $title = 'Status';
        $description = '
            <p>The status of a stream defines who can access the stream.</p>
            <p><strong>Private</strong> streams are only available for you to use.</p>
            <p><strong>Public</strong> Streams are available for anyone to use.</p>
            <p>
                <strong>Deprecated</strong> Streams are ones you have discontinued,
                however they are still available for people to use.
            </p>
            <p>
                It is only possible to <strong>delete</strong> Streams that are private;
                once made public they are licensed with a
                <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank">
                Creative Commons CC-BY-SA-3.0 license</a> and any posts made in them are considered to
                have made a copy of the origional stream.
                See the <a href="http://www.babblingbrook.net/page/docs/copyleft">
                Babbling Brook documention</a> for more information.
            </p>';
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Versions help.
     *
     * @return string Help tags.
     */
    public function versions() {
        $title = "Versions";
        $description = "
            <p>
                Streams cannot be edited once they have been made public.
            </p>
            <p>
                If you need to edit a stream, then create a new version.
                This enables people to continue using the old version if they wish to.
            </p>
            <p>
                The first number represents a major change, such as a change in functionality or the removal of
                fields.
            </p>
            <p>
                The middle number represents a minor change, such as adding new fields to the stream.
            </p>
            <p>
                The final number represents a patch, such as a change to the description.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Versions help.
     *
     * @return string Help tags.
     */
    public function postMode() {
        $title = "Post Mode";
        $description = "
            <p>
                Use this to decide who can submit posts to this stream.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Versions help.
     *
     * @return string Help tags.
     */
    public function duplicate() {
        $title = "Duplicate";
        $description = "
            <p>Enables you to make a copy of a streams structure without making a new version.</p>
            <p>If you are just making an update, then it is better to create a new version.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Versions help.
     *
     * @return string Help tags.
     */
    public function defaultSortRhythms() {
        $title = "Default Filter Rhythms";
        $description = "
            <p>The default filter rhythms that a user is subscribed to when they subscribe to this stream.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Filter help.
     *
     * @return string Help tags.
     */
    public function filter() {
        $title = "Filter your results";
        $description = "
            <p>
                To filter the results: type in a textbox and press return.
                Multiple filters can be used at the same time.
            </p>
            <p>
                In the version filter replace any of 'major', 'minor' or 'patch'
                with a version number to narrow down the search.
            </p>
            <p>Results can be sorted by clicking on most column headings.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Children help.
     *
     * @return string Help tags.
     */
    public function children() {
        $title = "Children";
        $description = "
            <p>
                The main use of child streams is to enable commenting on a post in this stream.
            <p>
            <p>
                In order for tree comments to be enabled, the child stream must reference itself as a child.
            </p>
            <p>Copy the url of a child stream into the textbox, or alternativly click on 'Open Search'.</p>
            <p>The default setting is for a standard comment.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Moderation Rings help.
     *
     * @return string Help tags.
     */
    public function defaultModerationRings() {
        $title = "Default Moderation Rings";
        $description = "
            <p>A list of the rings that are used for moderation of this stream.</p>
            <p>Default spam rings can also be included here.</p>
            <p>The user can override these settings.</p>";
        return $this->dialogue->inline($title, $description);
    }
}

?>
