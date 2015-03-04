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

Yii::import('zii.widgets.grid.CGridView');

/**
 * Extends Yii class {@link CGridveiw}
 *
 * @package PHP_ExtendedYii
 */
class GridView extends CGridView
{

    /**
     * Key => Column names to have a help icon.  Content => the help content.
     *
     * @var array
     */
    public $filter_help = array();


    /**
     * See {@link CGridView::initColumns}.
     *
     * Overidden so that {@link CDataColumn} calls extended {@link DataColumn}
     * Creates column objects and initializes them.
     *
     * @return void
     */
    protected function initColumns() {
        if ($this->columns === array()) {
            if ($this->dataProvider instanceof CActiveDataProvider) {
                $this->columns=$this->dataProvider->model->attributeNames();
            } else if ($this->dataProvider instanceof IDataProvider) {
                // use the keys of the first row of data as the default columns
                $data=$this->dataProvider->getData();
                if (isset($data[0]) === true && is_array($data[0]) === true) {
                    $this->columns=array_keys($data[0]);
                }
            }
        }
        $id=$this->getId();
        foreach ($this->columns as $i => $column) {
            if (is_string($column) === true) {
                $column=$this->createDataColumn($column);
            } else {
                if (isset($column['class']) === false) {
                    $column['class']='DataColumn';
                }
                $column=Yii::createComponent($column, $this);
            }

            if (array_key_exists($column->header, $this->filter_help) === true) {
                $column->setupHelp($this->filter_help[$column->header]);
            }

            if ($column->visible === false) {
                unset($this->columns[$i]);
                continue;
            }
            if ($column->id===null) {
                $column->id=$id.'_c'.$i;
            }
            $this->columns[$i]=$column;
        }

        foreach ($this->columns as $column) {
            $column->init();
        }
    }


}

?>