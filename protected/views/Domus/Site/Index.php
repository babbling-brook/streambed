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
 * View for the domus subdomain.
 */

$this->layout='blank';


if (Yii::app()->user->isGuest === false) {

    $cs = Yii::app()->getClientScript();
    if (Yii::app()->params['minify'] === true) {
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Minified/domus.js' . $this->js_version_number);
    } else {
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jquery.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/json2.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Library.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Test.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/TestErrors.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Models.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/LocalStorage.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/SortedStreamResults.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/SharedRhythm.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Controller.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/FeatureUsage.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Filter.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/FilterController.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/ScientiaController.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Interact.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/kindred_data.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Kindred.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/KindredController.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Loaded.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/MakePost.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/DeletePost.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Ring.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/RingController.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/SendToScientiaFrame.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/Suggestion.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/SuggestionController.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/ManageTakes.js' . $this->js_version_number);
        $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Domus/ready.js' . $this->js_version_number);
    }
    ?>
    <script>
        <?php
        // Fetch rhythm usage
        $rhythm = Rhythm::getUserKindredRhythm(Yii::app()->user->getId());

        $site_access_rows = SiteAccess::getAll();
        $site_access = array();

        foreach ($site_access_rows as $row) {
            array_push($site_access, $row['domain']);
        }

        $admin_rings = UserRing::getRingDetailsForDomus(Yii::app()->user->getId(), 'admin');
        $member_rings = UserRing::getRingDetailsForDomus(Yii::app()->user->getId(), 'member');

        $ary = array(
            "username" => Yii::app()->user->getName(),
            "domain" => Yii::app()->user->getDomain(),
            "site_access" => $site_access,
            "kindred_rhythm" => $rhythm,
            "max_takes_from_server" => Yii::app()->params['takes_to_process'],
            "initial_wait_before_processing_takes" => Yii::app()->params['initial_wait_before_processing_takes'],
            "short_wait_before_processing_takes" => Yii::app()->params['short_wait_before_processing_takes'],
            "long_wait_before_processing_takes" => Yii::app()->params['long_wait_before_processing_takes'],
            "ring_pause" => Yii::app()->params['ring_pause'],
            "member_rings" => $member_rings,
            "admin_rings" => $admin_rings,
            "max_ring_member_data_length" => Yii::app()->params['max_ring_member_data_length'],
            "max_admin_member_data_length" => Yii::app()->params['max_admin_member_data_length'],
            "stream_expiry" => Yii::app()->params['stream_expiry'],
        );
        $user_json = CJSON::encode($ary);
        $user_multi = new UserMulti();
        $site_access_data = $user_multi->getSiteAccess(Yii::app()->user->getId(), false);
        $site_access_json = CJSON::encode($site_access_data);
        ?>
        if (typeof BabblingBrook !== "object") {
            BabblingBrook = {};
        }
        if (typeof BabblingBrook.Domus !== "object") {
            BabblingBrook.Domus = {};
        }
        if (typeof BabblingBrook.Domus.Loaded !== "object") {
            BabblingBrook.Domus.Loaded = {};
        }
        <?php
        /**
         * @type {object} BabblingBrook.Domus.User Global object containing the following paramaters once loaded
         * @type {string} BabblingBrook.Domus.User.site_access A list of domains that
         *      currently have access to the domus.
         * @type {string} BabblingBrook.Domus.User.username username of the user
         * @type {string} BabblingBrook.Domus.User.domain domain name of the user
         * @type {number} BabblingBrook.Domus.User.max_takes_from_server The number of takes to fetch at a time
         *                                                        for processing by kindred rhythms
         * @type {number} BabblingBrook.Domus.User.initial_wait_before_processing_takes
         *      How long to wait before processing takes
         * @type {number} BabblingBrook.Domus.User.short_wait_before_processing_takes
         *      How long to wait between batches of takes
         * @type {number} BabblingBrook.Domus.User.long_wait_before_processing_takes How long to wait if no
         *                                                                    takes are left for processing
         * @type {string} BabblingBrook.Domus.User.stream_rhythm_filter_suggestion_url
         * @type {string} BabblingBrook.Domus.User.stream_rhythm_moderation_ring_suggestion_url
         * @type {string} BabblingBrook.Domus.User.stream_rhythm_suggestion_url
         * @type {number} BabblingBrook.Domus.User.stream_rhythm_user_rate
         */
        ?>
        BabblingBrook.Domus.User = <?php echo $user_json ;?>;
        BabblingBrook.DomusSiteAccess = <?php echo $site_access_json ;?>;
        BabblingBrook.Settings = <?php $this->renderPartial('/Client/Layouts/_settings'); ?>;
        BabblingBrook.csfr_token = '<?php echo Yii::app()->user->getCSFRToken(); ?>';
    </script>
<?php } ?>