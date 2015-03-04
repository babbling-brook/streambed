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
 * View for updating Rhythms.
 */

$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageRhythm/Update'));

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/salt.jquery.ba-bbq.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/salt.jquery.yiiactiveform.js');
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageRhythm/Update'));

// @todo minify bbq css
$cs->registerCssFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.css');

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/edit/matchbrackets.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/comment/continuecomment.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/mode/javascript/javascript.js');


$this->menu = $this->operationsMenu("update");
$this->menu_drop_down = '<h4 id="side_bar_switch_versions">Switch Versions</h4>';
$this->menu_drop_down .= Version::switchVersions("update", $model->extra->version_id, 'rhythm');
$help = new RhythmHelp();
?>
<h2>Update Rhythm</h2>

<div class="form content-indent">

    <p>
        See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">Babbling Brook</a>
        documentation for details on how rhythms are constructed.
    </p>

    <?php $form=$this->beginWidget('ActiveForm', array('id' => 'rhythm_form')); ?>

    <div class="row">
        <span class="inline-label">Name:</span> <span id="rhythm_name"><?php echo $model->name; ?></span>
    </div>

    <div class="row">
        <span class="inline-label">Version:</span> <?php echo $this->version_string ?>
    </div>

    <div class="row">
        <span class="inline-label">Date Created:</span> <?php echo $model->extra->date_created ?>
    </div>

    <div class="row">
        <span class="inline-label">Status:</span>
        <span id="rhythm_status"><?php echo StatusHelper::getDescription($model->extra->status_id); ?></span>
    </div>


    <h3 class="form">Actions</h3>

    <?php
    $error_string = "";
    if ($version_error === true) {
        $error_string = "error";
    }
    ?>

    <?php // this is required for the status update process. Until it moved into the protocol. ?>
    <input type="hidden" id="RhythmExtra_rhythm_extra_id" value="<?php echo $model->extra->rhythm_extra_id;?>" />

    <div class="row new-version">
        <?php
        echo CHtml::label(
            'Create New Version ' . $help->versions(),
            'version',
            array('class' => 'inline-label')
        );
        ?>
        <div class="select-container">
        <?php
            echo CHtml::dropDownList(
                'version',
                '',
                $versions
            );
        ?>
        </div>
        <?php
        echo CHtml::Button(
            'New Version',
            array(
                "id" => "new_version",
                "name" => "new_version",
                "class" => "standard-button",
            )
        );
        echo CHtml::Tag('div', array('class' => 'error', 'id' => 'new_version_error'), "");
        echo CHtml::Tag('div', array('class' => 'success', 'id' => 'new_version_success'), "");
        ?>
        <?php if ($version_error === true) { ?>
            <div class="error">
                Please select a new version number.
            </div>
        <?php } ?>
    </div>

    <div id="rhythm_status_actions" class="row">
        <?php
            $update_url = Yii::app()->params['site_root'] . '/' . $this->username . "/rhythms/ajaxpost";
            echo CHtml::HiddenField('ajax_url', $update_url);
        ?>
        <label class="inline-label">
            Change Status
            <?php echo ($help->status()); ?>
        </label>
        <?php
        $status = StatusHelper::getValue($model->extra->status_id);
        $deprecate = $publish = $delete = "";
        if ($status === "public") {
            $publish = "hide";
        }
        if ($status === "public" || $status === "deprecated" ) {
            $delete = "hide";
        }
        if ($status === "private" || $status === "deprecated" ) {
            $deprecate = "hide";
        }
        ?>
        <img
            alt="Publish"
            src="/images/ui/publish.png"
            class="button publish <?php echo $publish; ?>"
            title="Publish">
        <img
            alt="Delete"
            src="/images/ui/delete.png"
            class="button delete <?php echo $delete; ?>"
            title="Delete">
        <img
            alt="Deprecate"
            src="/images/ui/deprecate.png"
            class="button deprecate <?php echo $deprecate; ?>"
            title="Deprecate">
    </div>

    <div class="row row-duplicate">
        <?php
        echo Html::label('Duplicate: New Name', 'duplicate_name', array('class' => 'inline-label'), $help->duplicate());
        echo CHtml::textField('duplicate_name', $model->name, array('size' => 60, 'maxlength' => 128));
        echo CHtml::Button(
            'Duplicate',
            array(
                "id" => "duplicate",
                "name" => "duplicate",
                "class" => "standard-button",
            )
        );
        echo CHtml::Tag('span', array('class' => 'label-loading hide', 'id' => 'duplicate_loading'), "");
        echo CHtml::Tag('div', array('class' => 'error', 'id' => 'duplicate_error'), "");
        echo CHtml::Tag('div', array('class' => 'success', 'id' => 'duplicate_success'), "");
        ?>
    </div>

    <h3 class="form">Edit Rhythm</h3>

    <?php
    // If the status is not edit then disable these fields
    $hide = "";
    $show = "hide";
    if ($status === "private") {
        $hide = "hide";
        $show = "";
    }
    $view_link = '/' . $model->user->username . '/rhythm/' . $model->name . '/' . $model->extra->version->major .
        '/' . $model->extra->version->minor . '/' . $model->extra->version->patch;
    ?>

    <div id="no_editing" class="<?php echo $show; ?> row">
        <p>
            Create a new version or duplicate to edit. Only private Rhythms can be edited.
        </p>
        <p>
            <a href="<?php echo $view_link; ?>">View</a> the rhythm if you want to inspect the code.
        </p>
    </div>

    <div class="row only-private <?php echo $show; ?>">
        <label for="rhythm_description" class="inline-label required">
            Description <span class="required">*</span>
            <?php echo $help->description(); ?>
         </label>
        <textarea id="rhythm_description" cols="70" rows="4"><?php echo $model->extra->description; ?></textarea>
        <div id="rhythm_description_error" class="error internal-row hide"></div>
    </div>

    <div class="row only-private <?php echo $show; ?>">
        <label for="rhythm_category" class="inline-label required">
            Category
            <?php echo $help->category(); ?>
         </label>
        <?php echo Rhythm::getRhythmCatDropDownList($model->extra->rhythm_cat_id); ?>
        <div id="rhythm_category_error" class="error internal-row hide"></div>
    </div>

    <div class="row only-private <?php echo $show; ?>">
        <label for="rhythm_cats" class="inline-label">
            Client Parameters
            <?php echo $help->clientParameters(); ?>
        </label>

        <div class="content-block-3">

            <?php $rhythm_params = RhythmParam::getForRhythm($model->extra->rhythm_extra_id);
            $no_rhythms_hide = '';
            $rhythms_table_hide = 'hide';
            if (empty($rhythm_params) === false) {
                $no_rhythms_hide = 'hide';
                $rhythms_table_hide = '';
            }
            ?>

            <div id="no_rhythm_parameters" class="bottom-margin <?php echo $no_rhythms_hide; ?>">
                There are no parameters for this rhythm.
            </div>

            <table id="rhythm_parameters" class="<?php echo $rhythms_table_hide; ?>">
                <thead>
                    <tr>
                        <td class="rhythm-param-original-name hide"></td>
                        <td class="rhythm-param-name">Name</td>
                        <td class="rhythm-param-hint">Hint</td>
                        <td class="rhythm-param-action">&nbsp;</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rhythm_params as $param) { ?>
                        <tr>
                            <td class="rhythm-param-original-name hide">
                                <input type="hidden" value="<?php echo $param['name'];?>" />
                            </td>
                            <td class="rhythm-param-name">
                                <input type="text" value="<?php echo $param['name'];?>" />
                            </td>
                            <td class="rhythm-param-hint"><textarea><?php echo $param['hint'];?></textarea></td>
                            <td class="rhythm-param-action">
                                <input type="button" class="remove-parameter standard-button" value="Remove" />
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="rhythm_original_name hide"></td>
                        <td class="rhythm-param-name"><input id="new_param_name" type="text" /></td>
                        <td class="rhythm-param-hint"><textarea id="new_param_hint"></textarea></td>
                        <td class="rhythm-param-action">
                            <input id="add_new_parameter" type="button" class="standard-button" value="Add" />
                        </td>
                    </tr>
                    <tr id="add_parameter_error" class="hide">
                        <td colspan="3" class="error"></td>
                    </tr>
                </tfoot>
            </table>
            <div>
                <input id="open_new_parameter"
                    class="standard-button <?php echo $no_rhythms_hide; ?>" type="button" value="Add a parameter" />
            </div>
        </div>
    </div>

    <div class="alpha-note content-indent only-private <?php echo $show; ?>">
        <p>Code mirror - the plugin used to display the JavaScript code - is not as stable as it should be.</p>
        <p>Reloading the page resolves problems.</p>
        <p>Recomend editing elsewhere and copy/pasting for now.</p>
    </div>

    <div class="row only-private <?php echo $show; ?>">
        <label for="rhythm_javascript" class="inline-label required">
            JavaScript code for the Rhythm <span class="required">*</span>
            <?php echo $help->javascript(); ?>
         </label>
        <textarea id="rhythm_javascript" cols="70" rows="20"><?php echo $model->extra->full; ?></textarea>
        <div id="rhythm_javascript_error" class="error internal-row hide"></div>
    </div>

    <div class="row buttons only-private <?php echo $show; ?>">
        <input type="button" value="Save" id="save_rhythm" class="standard-button">
    </div>

<?php $this->endWidget(); ?>
</div>


<?php //These are html templates that are required by the default javascript classes. ?>
<div id="update_rhythm_templates" class="hide">

    <?php // Used to display new paramater rows ?>
    <table id="parameter_row_template">
        <tbody>
            <tr>
                <td class="rhythm-param-original-name hide">
                    <input type="hidden" value="" />
                </td>
                <td class="rhythm-param-name">
                    <input type="text" value="" />
                </td>
                <td class="rhythm-param-hint"><textarea></textarea></td>
                <td class="rhythm-param-action">
                    <input type="button" class="remove-parameter standard-button" value="Remove" />
                </td>
                <div>something here, it needs to be. just about here.</div>
            </tr>
        </tbod>
    </table>

    <?php // Used to display new paramater rows ?>
    <table id="parameter_row_error_template">
        <tbody>
            <tr class="parameter-row-error">
                <td colspan="4" class="error bottom-padding"></td>
            </tr>
        </tbod>
    </table>

    <div id="rhythm_deleted_template">
         Rhythm <em class="deleted-rhythm-name"></em> has been deleted.
    </div>

    <div id="rhythm_duplicated_template">
         Rhythm duplicated. Edit it <a class="duplicated-rhythm-url" href="">here</a>.
    </div>

    <div id="new_rhythm_version_template">
         New version created. Edit it <a class="new-rhythm-version-url" href="">here</a>.
    </div>


</div>