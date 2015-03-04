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
 * View for administrating categories.
 */

$this->menu=array(
    array('label' => 'List Cat', 'url' => array('index')),
    array('label' => 'Create Cat', 'url' => array('create')),
);

Yii::app()->clientScript->registerScript(
    'search',
    "
    $('.search-button').click(function() {
        $('.search-form').toggle();
        return false;
    });
    $('.search-form form').submit(function() {
        $.fn.yiiGridView.update('cat-grid', {
            data: $(this).serialize()
        });
        return false;
    });
    "
);
?>

<h1>Manage Cats</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search', '#', array('class' => 'search-button')); ?>
<div class="search-form" style="display:none">
<?php
$this->renderPartial(
    '/Client/Admin/Cat/_search',
    array(
        'model' => $model,
    )
);
?>
</div><!-- search-form -->

<?php
$this->widget(
    'zii.widgets.grid.CGridView',
    array(
        'id' => 'cat-grid',
        'dataProvider' => $model->search(),
        'filter' => $model,
        'columns' => array(
            'cat_id',
            'name',
            array(
                'class' => 'CButtonColumn',
            ),
        ),
    )
);
?>
