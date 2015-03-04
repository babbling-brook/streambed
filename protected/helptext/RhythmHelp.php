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
class RhythmHelp extends Help
{

    /**
     * Name help.
     *
     * @return string Help tags.
     */
    public function name() {
        $title = "Name";
        $description = "
            <p>A rhythm name is required and needs to be unique amongst your rhythms.</p>
            <p>The name must be lowercase, as it is used as part of the url for its location.</p>
            <p>
                It also needs to be alpha numeric and start and end with a letter,
                single spaces are allowed.
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
            <p>This should describe the rhythm in plain english.</p>";
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Description help.
     *
     * @return string Help tags.
     */
    public function category() {
        $title = 'Category';
        $description = '
            <p>
                The category of an rhythm describes its purpose. For example <em>kindred_rhythm</em>s describe
                rhythms that help people work out how connected they are with other users.
            </p>
            <p>
                The structure of rhythms anonymous function differs depending on the category of the rhythm.
            </p>
            <p>
                See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">Babbling Brook</a>
                documentation for more information.
            </p>

        ';
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Description help.
     *
     * @return string Help tags.
     */
    public function clientParameters() {
        $title = 'Client Parameters';
        $description = '
            <p>
                Client parameters enable a client website to customize the results of a rhythm.
            </p>
            <p>
                The client website can either provide values for the client paramaters behind the scenes,
                or it can present the user with the option of providing the parameters.
            </p>
            <p>
                They make it possible for rhythms to be much more extensibly customisable, by for example
                enabling a user to limit results to a particular tag or search term.
            </p>
            <p>
                The hint column is used to provide the user with additional details about the nature of the paramater
                if the is not descriptive enough.
            </p>
            <p>
                See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">Babbling Brook</a>
                documentation for more information.
            </p>

        ';
        return $this->dialogue->inline($title, $description);
    }

    /**
     * Description help.
     *
     * @return string Help tags.
     */
    public function javascript() {
        $title = 'Rhythm JavaScript';
        $description = '
            <p>This is your Javascript code for this rhythm. </p>
            <p>Rhythms must be defined in an anonymous function.</p>
            <p>
                The structure of rhythms anonymous function differs depending on the category of the rhythm but a
                general outline is:
                <code>
function () {
    var init = function () {

    };

    var main = function () {

    };

    var final = function () {

    };
    return {
        init : init,
        main : main,
        final : final
    };
}
                </code>
            </p>
            <p>
                See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">Babbling Brook</a>
                documentation for more information.
            </p>';
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
            <p>The status of a rhythm defines who can access the rhythm.</p>
            <p><strong>Private</strong> rhythms are only available for you to use.</p>
            <p><strong>Public</strong> rhythms are available for anyone to use.</p>
            <p>
                <strong>Deprecated</strong> rhythms are ones you have discontinued,
                and no longer appear in searches, however they are still available for people to use if
                accessed directly.
            </p>
            <p>
                It is only possible to <strong>delete</strong> rhythms that are private;
                once made public they are licensed with a
                <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank">
                Creative Commons CC-BY-SA-3.0 license</a> and any users using them are considered to
                have made a copy of the origional rhythm.
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
                Rhythms cannot be edited once they have been made public.
            </p>
            <p>
                If you need to edit a rhythm, then create a new version.
                This enables people to continue using the old version if they wish to.
            </p>
            <p>
                The first number represents a major change, such as a change in functionality.
            </p>
            <p>
                The middle number represents a minor change, such as a small additional feature.
            </p>
            <p>
                The final number represents a patch, such as a bug fix.
            </p>";
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
            <p>Results can be sorted by clicking on most column headings.</p>";
        return $this->dialogue->inline($title, $description);
    }


    /**
     * Filter help.
     *
     * @return string Help tags.
     */
    public function duplicate() {
        $title = "Duplicate a Rhythm";
        $description = "
            <p>Enter a new name to duplicate the current rhythm.</p>";
        return $this->dialogue->inline($title, $description);
    }
}

?>
