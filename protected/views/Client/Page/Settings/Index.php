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
 * View for the users config page.
 */

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Settings/Settings'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Settings/Settings'));
$json_settings = CJSON::encode(Yii::app()->params['user_editable_settings']);
?>

<script>
    if (typeof BabblingBrook.Client.Settings === 'undefined') {
        BabblingBrook.Client.Settings = {};
    }

    BabblingBrook.Client.Settings.settings_to_show = <?php echo $json_settings; ?>;
</script>

<h2>User Settings</h2>

<div class="content-indent" id="config_content">
    <div class="larger readable-text">
        <p>Use these options to change your experience of Cobalt Cascade.</p>
        <p class="alpha-note">
            In the production version of Babbling Brook it should not be necessary to visit this page,
            but in the meantime you will probably become familiar with it.
        </p>
    </div>

    <div class="end error text larger readable-text">
        Warning : changing some of these options can cause the site to malfunction.
        Read the help text to be sure you are changing the correct option and if it all goes wrong then you can
        always reset your settings</a>. Settings you have edited are in bold.
        Individual options can be reset by setting them to be blank.
    </div>

    <div id="config_options" class="form"></div>
</div>

<?php //These are html templates that are required by Config.js. ?>
<div id="config_templates" class="hide">
    <div id="config_row_template">
        <div class="row">
        </div>
    </div>

    <div id="config_row_rhythm_template">
        <label></label>
        <input class="config-value" type="text" />
        <span class="help-icon"></span>
        <span class="help-title hide"></span>
        <span class="help-content hide"></span>
        <span class="search">
            <a>search</a>
        </span>
        <div class="config-row-error error hide"></div>
        <div class="rhythm-selector hide config-selector maxwide"></div>
        <input class="config-code" type="hidden" />
    </div>

    <div id="config_row_stream_template">
        <label></label>
        <input class="config-value" type="text" />
        <span class="help-icon"></span>
        <span class="help-title hide"></span>
        <span class="help-content hide"></span>
        <span class="search">
            <a>search</a>
        </span>
        <div class="config-row-error error hide"></div>
        <div class="stream-selector hide config-selector maxwide"></div>
        <input class="config-code" type="hidden" />
    </div>

    <div id="config_row_uint_template">
        <label></label>
        <input class="config-value" type="text" />
        <span class="help-icon"></span>
        <span class="help-title hide"></span>
        <span class="help-content hide"></span>
        <div class="config-row-error error hide"></div>
        <input class="config-code" type="hidden" />
    </div>

    <div id="config_row_string_template">
        <label></label>
        <input class="config-value" type="text" />
        <span class="help-icon"></span>
        <span class="help-title hide"></span>
        <span class="help-content hide"></span>
        <div class="config-row-error error hide"></div>
        <input class="config-code" type="hidden" />
    </div>

    <div id="config_reset_confirm_template">
        Are you sure you want to reset your settings to the defaults? This cannot be undone.
    </div>

    <div id="config_page_reloading_template">
        <div class="larger">
            Your settings are being reset, please wait...
        </div>
    </div>
</div>