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
 * Extends CActiveForm
 * Please check {@link CActiveForm} for detailed information
 *
 * @package PHP_ExtendedYii
 */
class ActiveForm extends CActiveForm
{

    /**
     * Extends parent to add help option.
     *
     * Renders an HTML label for a model attribute.
     * This method is a wrapper of {@link CHtml::activeLabel}.
     * Please check {@link CHtml::activeLabel} for detailed information.
     * about the parameters for this method.
     *
     * @param CModel $model The data model.
     * @param string $attribute The attribute.
     * @param array $htmlOptions Additional HTML attributes.
     * @param string $help Help tags.
     *
     * @return string the generated label tag.
     */
    public function label($model, $attribute, $htmlOptions=array(), $help=null) {
        return Html::activeLabel($model, $attribute, $htmlOptions, $help);
    }

    /**
     * Extends parent to add help option.
     *
     * Renders an HTML label for a model attribute.
     * This method is a wrapper of {@link CHtml::activeLabelEx}.
     * Please check {@link CHtml::activeLabelEx} for detailed information.
     * about the parameters for this method.
     *
     * @param CModel $model The data model.
     * @param string $attribute The attribute.
     * @param array $htmlOptions Additional HTML attributes.
     * @param string $help Help tags.
     * @param boolean $loading Include a loading icon.
     *
     * @return string the generated label tag.
     */
    public function labelEx($model, $attribute, $htmlOptions=array(), $help=null, $loading=false) {
        return Html::activeLabelEx($model, $attribute, $htmlOptions, $help, $loading);
    }

}

?>