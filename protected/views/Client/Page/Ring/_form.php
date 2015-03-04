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
 * View for an insert/update form template for ring views.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/Admin'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Ring/Admin'));

$help = new RingHelp();
$admin_type_true = isset($model->admin_type) && empty($model->admin_type) === false;
if ($admin_type_true === true) {
    $admin_type_value = LookupHelper::getValue($model->admin_type);
} else {
    $admin_type_value = "";
}
?>
<?php if ($admin_type_true === true && $admin_type_value === "invitation" && isset($ring_id) === true) { ?>
    <?php $admins = UserRing::getAdmins($ring_id); ?>
    <div id="current_admins">
        <h3 class="no-top">Current administrators: </h3>
        <ul>
            <?php foreach($admins as $admin) { ?>
                <li>
                    <a href='http://<?php echo $admin['domain'] . "/" . $admin['username']; ?>/profile'>
                        <?php echo $admin['domain'] . "/" . $admin['username']; ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php
}
?>


<div class="form">
    <?php
    $form = $this->beginWidget(
        'ActiveForm',
        array(
            'id' => 'create_ring',
            'enableAjaxValidation' => false,
        )
    );
    ?>

    <div class="row">
        <?php
        $disabled = '';
        if ($model->new === false) {
            $disabled = "disabled";
        }
        ?>
        <label for="ring_name" class="block-label">Name *</label>
        <input type="text" id="ring_name" <?php echo $disabled ?> value="<?php echo $model->name; ?>" />
        <?php echo $help->name(); ?>
        <div class="error" id="name_error"></div>
    </div>

    <div class="row">
        <label for="membership_type" class="block-label">Membership *</label>
        <div class="select-container">
            <?php
            echo CHtml::dropDownList(
                'membership_type',
                LookupHelper::getValue($model->membership),
                LookupHelper::getDropDown('ring.membership_type'),
                array('prompt' => 'Choose how new members are admited')
            );
            ?>
            <?php echo $help->membership(); ?>
        </div>
        <div class="error" id="membership_error"></div>
    </div>

    <?php
    $membership_rhythm_hide = 'hide';
    if (isset($model->membership) === true && LookupHelper::getValue($model->membership) === 'rhythm') {
        $membership_rhythm_hide ='';
    }
    ?>
    <div id="membership_rhythm_textfield" class="row extra-height <?php echo $membership_rhythm_hide; ?>">
        <label for="membership_rhythm" class="block-label">Membership Rhythm</label>
        <input type="text" id="membership_rhythm" value="<?php echo $model->membership_rhythm; ?>" />
        <?php echo $help->membershipRhythm(); ?>
        <span class="search"><a id="membership_rhythm_search">search</a></span>
        <div class="error" id="membership_rhythm_error"></div>
    </div>
    <div id="membership_rhythm_selector" class="hide maxwide"></div>

    <?php
    $membership_super_ring_hide = 'hide';
    if (isset($model->membership) === true && LookupHelper::getValue($model->membership) === 'super_ring') {
        $membership_super_ring_hide ='';
    }
    ?>
    <div id="membership_super_ring_textfield" class="row  extra-height <?php echo $membership_super_ring_hide; ?>">
        <label for="membership_super_ring" class="block-label">Membership Super Ring *</label>
        <input type="text" id="membership_super_ring" value="<?php echo $model->membership_super_ring; ?>" />
        <?php echo $help->membershipSuperRing(); ?>
        <span class="search"><a id="membership_super_ring_search">search</a></span>
        <div class="error" id="membership_super_ring_error"></div>
    </div>
    <div id="membership_super_ring_selector" class="hide maxwide"></div>


    <div class="row">
        <label for="admin_type" class="block-label">Admin Type *</label>
        <div class="select-container">
            <?php
            echo CHtml::dropDownList(
                'admin_type',
                LookupHelper::getValue($model->admin_type),
                LookupHelper::getDropDown('ring.admin_type'),
                array('prompt' => 'Choose who has administrator access')
            );
            ?>
            <?php echo $help->adminType(); ?>
        </div>
        <div class="error" id="admin_type_error"></div>

    </div>

    <?php
    $admin_super_ring_hide = 'hide';
    if ($admin_type_value === "super_ring") {
        $admin_super_ring_hide ='';
    }
    ?>
    <div id="admin_super_ring_textfield" class="row  extra-height <?php echo $admin_super_ring_hide; ?>">
        <label for="admin_super_ring" class="block-label">Admin Super Ring *</label>
        <input type="text" id="admin_super_ring" value="<?php echo $model->admin_super_ring; ?>" />
        <?php echo $help->adminSuperRing(); ?>
        <span class="search"><a id="admin_super_ring_search">search</a></span>
        <div class="error" id="admin_super_ring_error"></div>
    </div>
    <div id="admin_super_ring_selector" class="hide maxwide"></div>

    <div id="ring_rhythm_textfield" class="row extra-height">
        <label for="ring_rhythm" class="block-label">Ring Rhythm</label>
        <input type="text" id="ring_rhythm" value="<?php echo $model->ring_rhythm; ?>" />
        <?php echo $help->ringRhythm(); ?>
        <span class="search"><a id="ring_rhythm_search">search</a></span>
        <div class="error" id="ring_rhythm_error"></div>
        <?php if ($model->new === true) { ?>
            <div class="hint">You can leave this blank and select the Rhythm once the Ring has been created</div>
        <?php } ?>
    </div>
    <div id="ring_rhythm_selector" class="hide maxwide"></div>

    <div class="row buttons">

        <?php
        $update_hide = '';
        $create_hide = 'hide';
        if ($model->new === true) {
            $update_hide = 'hide';
            $create_hide = '';
        }
        ?>
        <input type="button" class="standard-button <?php echo $create_hide;?>" id="create_ring_submit" value="Create"/>
        <input type="button" class="standard-button <?php echo $update_hide;?>" id="update_ring_submit" value="Update"/>
    </div>

    <?php $this->endWidget(); ?>

</div><!-- form -->

<div class="hide">
    <div id="on_ring_update_server_error_template">
        There was an error sending your request to update/create a ring to the server.
    </div>

    <div id="update_title_template">
        <?php echo Yii::app()->name . ' - Update Ring'; ?>
    </div>
</div>