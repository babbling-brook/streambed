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
 * View for listing Rhythms.
 */

$this->menu = $this->operationsMenu("index");
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageRhythm/List'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageRhythm/List'));
?>

<h2>List of Rhythms by <?php echo ($this->username); ?></h2>

<?php if ($message === true) { ?>

    <div class="success_message">
        <?php echo $message; ?>
    </div>

<?php } ?>

<input type="hidden" id="users_domain" value="" />
<input type="hidden" id="" value="" />

<div id="users_rhythm_search_container"
     data-users-domain="<?php echo Yii::app()->params['host']; ?>"
     data-users-username="<?php echo $this->username; ?>"
     class="content-indent">
</div>


<div id="rhythm_list_publish_button_template" class="hide">
    <img src="/images/ui/publish.png" class="publish grid-button" title="Publish">
</div>
<div id="rhythm_list_deprecate_button_template" class="hide">
    <img src="/images/ui/deprecate.png" class="deprecate grid-button" title="Deprecate">
</div>
<div id="rhythm_list_delete_button_template" class="hide">
    <img src="/images/ui/delete.png" class="delete hidden grid-button" title="Delete">
</div>
<div id="rhythm_list_revert_button_template" class="hide">
    <img src="/images/ui/revert.png" class="revert hidden grid-button" title="Revert to private status">
</div>
<div id="rhythm_list_delete_button_confirm_template" class="hide">
    Are you sure? Deleted Rhythms are not recoverable.
</div>