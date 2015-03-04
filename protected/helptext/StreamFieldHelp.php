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
 * Returns help text for Stream views.
 *
 * @package PHP_Help
 */
class StreamFieldHelp extends Help
{

    /**
     * Title help.
     *
     * @return string Help tags.
     */
    public function title() {
        $title = "Title";
        $description = "
            <p>
                This is the main field for an post. It can either be a title or a link.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Main value help.
     *
     * @return string Help tags.
     */
    public function mainValue() {
        $title = "Main Value";
        $description = "
            <p>This is default value field that all posts have.</p>
            <p>Its primary purpose is to indicate if a user likes or dislikes a post.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Textbox help.
     *
     * @return string Help tags.
     */
    public function textbox() {
        $title = "Textbox";
        $description = "
            <p>A place for any additional text to be diplayed, such a detailed description.</p>
            <p>
                A textbox or textarea is shown depending on the maximum length.
                Under eighty and a text box is shown
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Link help.
     *
     * @return string Help tags.
     */
    public function link() {
        $title = "Link";
        $description = "<p>Allows a user to add a link to an post.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Checkbox help.
     *
     * @return string Help tags.
     */
    public function checkbox() {
        $title = "Checkbox";
        $description = "<p>Useful for simple yes/no questions.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Value help.
     *
     * @return string Help tags.
     */
    public function value() {
        $title = "Value";
        $description = "<p>Extra values that can be attatched to the main one.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * List help.
     *
     * @return string Help tags.
     */
    public function listField() {
        $title = "List";
        $description = "<p>Allows an post to be placed into groups.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Openlist help.
     *
     * @return string Help tags.
     */
    public function openlist() {
        $title = "Open List";
        $description = "
            <p>Allows the user making the post to enter any value into a list.</p>
            <p>Might be used for tags or for poll questions that are used by child posts.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Maximum value.
     *
     * @return string Help tags.
     */
    public function valueScale() {
        $title = "Type of Value";
        $description = "
            <p>The describes how this value field will be presented to the user.</p>
            <p>
                <strong>Arrows</strong> allow the user to vote an item up or down.
                If the value options allow it then the
                arrows can be clicked multiple times to increase and decrease the take value.
            </p>
            <p>
                A <strong>linear scale</strong> is a slide bar which the user can slide between the
                minimum and maximum values to quickly select between a large range.
            </p>
            <p>
                A <strong>logarthmic scale</strong> is a similar to a linear scale, only the values go up faster the
                further along the bar from zero the user drags the slider.
            </p>
            <p>The <strong>textbox</strong> simply allows the user to type in a value.</p>
            <p>
                <strong>Stars</strong> present five stars that the user can click on to score an post.
                Normally a score of one to five is set. If Min and Max values are defined then the range
                is divided between the stars. EG if min is -5 and max is 5 then 0 stars is -5,
                1 star is -3, 2 stars is -2, 3 stars is 1,4 stars is 3 and five stars = 5
            </p>
            <p>
                A <strong>Button</strong> value field takes the post for a single value defined in the value options.
            </p>
            <p>
                A <strong>List</strong> Enables the user to select values based on items in a list.
            </p>            ";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Value Options.
     *
     * @return string Help tags.
     */
    public function valueOptions() {
        $title = "Value Options";
        $description = "<p>This defines the value of a take on a value field.</p>
                        <p>The simplest option is to let the <strong>user enter any value</strong> they wish.
                        <p>
                            <strong>Maximum and minimum constraints. Defined here</strong>
                            , allows you to define limits on the values that the user can enter.
                        </p>
                        <p>
                            <strong>Maximum and minimum constraints. Defined on the post</strong>
                            , enables the user who is submitting the post to define the limits.
                        </p>
                        <p>
                            <strong>Rhythm constraints. Defined here</strong>
                            , allows you to define who can take the post based on a take rhythm.
                        </p>
                        <p>
                            <strong>Rhythm constraints. Defined on the post</strong>
                            , enables the user who is submitting the post to define who can accept
                            the post based on a take rhythm.
                        </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Maximum value.
     *
     * @return string Help tags.
     */
    public function valueMax() {
        $title = "Maximum permited value";
        $description = "<p>The maximum value that a user is allowed to place against a post</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Minimum value.
     *
     * @return string Help tags.
     */
    public function valueMin() {
        $title = "Minimum permited value";
        $description = "<p>The minimum value that a user is allowed to place against a post</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Value rhythm check url help.
     *
     * @return string Help tags.
     */
    public function valueUrl() {
        $title = "Rhythm Check Url";
        $description = "
            <p>
                The URL of a take rhythm that is used to verfiy that the user wanting to take
                up a post is permitted to do so.
            </p>
            <p>If this is left blank then this value can be taken by anyone. This is the default</p>
            <p>
                However, if a take rhythm is entered then the users request to take the post is
                put on hold until the user making the post logs in, at which time it is automaticaly
                processed by this take rhythm.
                The requestor is then notified if they have been allowed to take it or not.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Value rhythm check url help.
     *
     * @return string Help tags.
     */
    public function valueList() {
        $title = "Value List";
        $description = "
            <p>
                Add items to the list, they are automatically given values.
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Value rhythm check url help.
     *
     * @return string Help tags.
     */
    public function textType() {
        $title = "Text Type";
        $description = "
            <p>
                Defines what kind of text is allowed in the text field.
            </p>
            <p>
                This defines what is happening behind the scenes. The client website may provide a text editor
                in order aid the user in entering html.
            </p>
            <p>
                <strong>Just test</strong> : Only pure text. Any links, html, css or javascript
                code will be coverted to text.
            </p>
            <p>
                <strong>Text with links</strong> : The same 'Just text', only text that looks like links
                will be converted into links. html A tags are also allowed and displayed as links.
            </p>
            <p>
                <strong>Text with links</strong> : The following HTML tags are allowed and will
                be displayed as html:
                A, STRONG, EM, OL, UL, BLOCKQUOTE, PRE, STRIKETHROUGH, SUP, SUB
            </p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Filter selection help.
     *
     * @return string Help tags.
     */
    public function filter() {
        $title = "Filter";
        $description = "
            <p>
                Enables you to restrict the kind of text that can be entered in the textbox.
            </p>
            <p>
                Can only be used if the textbox type is set to 'Just text'.
            </p>
            <p>
                Select a filter from the drop down list or select custom (Requires knowledge or regular expressions.)
            </p>";
        return $this->dialogue->inline($title, $description);
    }

}

?>
