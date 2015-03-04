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
?>
<div id="selector_template">
    <table class="selector" class="block-loading">
        <thead>
            <tr class="titles">
                <td class="name">
                    Name<span class="column-sort"></span>
                </td>
                <td class="domain">
                    Domain<span class="column-sort"></span>
                </td>
                <td class="username">
                    User<span class="column-sort"></span>
                </td>
                <td class="version">
                    Version<span class="column-sort"></span>
                </td>
                <td class="status">
                    Status<span class="column-sort"></span>
                </td>
                <td class="stream-kind">
                    Kind<span class="column-sort"></span>
                </td>
                <td class="user-type">
                    Type<span class="column-sort"></span>
                </td>
                <td class="ring-ban">
                    Banned<span class="column-sort"></span>
                </td>
                <td class="action">
                    Actions
                </td>
            </tr>
            <tr class="search">
                <td class="name">
                    <input type="text"/>
                </td>
                <td class="domain">
                    <input type="text" />
                </td>
                <td class="username">
                    <input type="text" />
                </td>
                <td class="version">
                    <input type="text" />
                </td>
                <td class="status">
                    <select>
                        <option value="" selected>All</option>
                        <?php
                        $values = StatusHelper::getValueDescriptions();
                        foreach ($values as $value => $description) { ?>
                            <option value="<?php echo $value;?>"><?php echo $description;?></option>
                        <?php } ?>
                    </select>
                </td>
                <td class="stream-kind">
                    <select>
                        <option value="" selected>All</option>
                        <?php
                        $kind_values =  LookupHelper::getValues('stream.kind');
                        foreach ($kind_values as $value => $id) { ?>
                            <option value="<?php echo $value;?>"><?php echo ucfirst($value);?></option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
                <td class="user-type">
                    <input type="text" />
                </td>
                <td class="ring-ban">
                    <select>
                        <option id="all" selected>all</option>
                        <option id="banned">banned</option>
                        <option id="members">members</option>
                    </select>
                </td>
                <td class="action">
                    <div title="Click for search help" class="help-icon">
                        <span class="help-title hide">Filter your results</span>
                        <span class="help-content hide">
                            <p>
                                To filter the results: type in a textbox and press return.
                                Multiple filters can be used at the same time.
                            </p>
                            <p>Results can be sorted by clicking on most column headings.</p>
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="error-row">
                <td colspan="100" class="error">

                </td>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="100">
                    Page <span class="page-number">&nbsp;</span>
                    <a href="#" class="first pale">First</a>
                    <a href="#" class="last pale">Previous</a>
                    <a href="#" class="next">Next</a>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<table id="selector_row_template">
    <tbody>
        <tr>
            <td class="name">
            </td>
            <td class="domain">
            </td>
            <td class="username">
            </td>
            <td class="version">
            </td>
            <td class="status">
            </td>
            <td class="stream-kind">
            </td>
            <td class="user-type">
            </td>
            <td class="ring-ban">
            </td>
            <td class="action">
            </td>
        </tr>
    </tbody>
</table>

<div id="version_error_template" class="hide">
    The version filter must have three parts. Each
    separated with a forward slash. E.G. 'major/minor/patch'
    Each part must be the word 'major', 'minor' or 'patch' to
    search for all versions in that part of the version, or a specific version number.
</div>
<div id="version_error_part_template" class="hide">
    The version filter must have three parts separated with a forward slash. E.G. 'major/minor/patch'
</div>

<div id="selector_error_template" class="hide">
    An error has occurred whilst searching. Please try again.
</div>

<div id="selector_error_domain_template" class="hide">
    The domain is not responding or is not a Babbling Brook domain name.
</div>