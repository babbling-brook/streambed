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

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Extends Yii class {@link CDataColumn}.
 *
 * @package PHP_ExtendedYii
 */
class DataColumn Extends CDataColumn
{

    /**
     * Holds a popup help object.
     *
     * @var HelpDialogue
     */
    private $help_dialogue = "";

    /**
     * Renders the filter cell with additional help icon if set.
     *
     * @return void
     */
    public function renderFilterCell() {
        echo "<td>";
        $this->renderFilterCellContent();
        echo $this->help_dialogue;
        echo "</td>";
    }

    /**
     * Setup the help dialogue.
     *
     * @param HelpDialogue $help_dialogue Holds the popup help object.
     *
     * @return void
     */
    public function setupHelp($help_dialogue) {
        $this->help_dialogue = $help_dialogue;
    }


    /**
     * Renders the filter cell content.
     *
     * This method will render the {@link filter} as is if it is a string.
     * If {@link filter} is an array, it is assumed to be a list of options, and a dropdown selector will be rendered.
     * Otherwise if {@link filter} is not false, a text field is rendered.
     *
     * @return void
     */
    protected function renderFilterCellContent() {
        if ($this->filter !== false && $this->grid->filter !== null && $this->name !== null) {
            if (is_array($this->filter) === true) {
                echo CHtml::activeDropDownList(
                    $this->grid->filter,
                    $this->name,
                    $this->filter, array('id' => false, 'prompt' => '')
                );
            } else if ($this->filter === null) {
                echo CHtml::activeTextField($this->grid->filter, $this->name, array('id' => false));
            } else {
                echo $this->filter;
            }
        } else {
            parent::renderFilterCellContent();
        }
    }
}

?>