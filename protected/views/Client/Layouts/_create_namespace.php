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

$rings = UserRing::getConfig(Yii::app()->user->getId());
$level_data = UserLevel::getLevel(Yii::app()->user->id);
?>

<script type="text/javascript">
    if (typeof BabblingBrook !== 'object') {
        BabblingBrook = {};
    }
    if (typeof BabblingBrook.Client !== 'object') {
        BabblingBrook.Client = {};
    }
    if (typeof BabblingBrook.Client.Component !== 'object') {
        BabblingBrook.Client.Component = {};
    }
    if (typeof BabblingBrook.Client.Core !== 'object') {
        BabblingBrook.Client.Core = {};
    }
    if (typeof BabblingBrook.Client.BackboneModel !== 'object') {
        BabblingBrook.Client.BackboneModel = {};
    }
    if (typeof BabblingBrook.Client.Object !== 'object') {
        BabblingBrook.Backbone = {};
    }
    if (typeof BabblingBrook.Client.Page !== 'object') {
        BabblingBrook.Client.Page = {};
    }
    if (typeof BabblingBrook.Client.UX !== 'object') {
        BabblingBrook.Client.UX = {};
    }
    BabblingBrook.Client.User = {
        username : '<?php echo Yii::app()->user->getName(); ?>',
        domain : '<?php echo Yii::app()->user->getDomain(); ?>',
        tutorial_set : '<?php echo $level_data['tutorial_set']; ?>',
        tutorial_level_name : '<?php echo $level_data['level_name']; ?>',
        Rings : <?php  echo CJSON::encode($rings); ?>,
        RingTakes : {},
        Config : {}
    };
    BabblingBrook.Client.DefaultConfig = <?php echo CJSON::encode(UserConfigDefault::getForUse()) ?>;
    BabblingBrook.Client.ClientConfig = <?php $this->renderPartial('/Client/Layouts/_client_config'); ?>;
    BabblingBrook.Client.CustomConfig = <?php $this->renderPartial('/Client/Layouts/_custom_config'); ?>;
    BabblingBrook.Settings = <?php $this->renderPartial('/Client/Layouts/_settings'); ?>;
    BabblingBrook.csfr_token = '<?php echo Yii::app()->user->getCSFRToken(); ?>';
</script>