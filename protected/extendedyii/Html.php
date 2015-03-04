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
 * Extends Chtml
 * Please check {@link CHtml} for detailed information.
 *
 * @package PHP_ExtendedYii
 */
class Html extends CHtml
{

    /**
     * Adds help tag to original.
     *
     * Please check {@link CHtml::activeLabelEx} for detailed information.
     *
     * @param CModel $model The data model.
     * @param string $attribute The attribute.
     * @param array $htmlOptions Additional HTML attributes.
     * @param string $help Help tags.
     * @param boolean $loading Include a loading icon
     *
     * @return string the generated label tag.
     */
    public static function activeLabelEx($model, $attribute, $htmlOptions=array(), $help=null, $loading=false) {
        $realAttribute = $attribute;
        self::resolveName($model, $attribute); // strip off square brackets if any
        $htmlOptions['required'] = $model->isAttributeRequired($attribute);
        return self::activeLabel($model, $realAttribute, $htmlOptions, $help, $loading);
    }

    /**
     * Adds help tag to original.
     *
     * Please check {@link CHtml::activeLabel} for detailed information.
     *
     * @param CModel $model The data model.
     * @param string $attribute The attribute.
     * @param array $htmlOptions Additional HTML attributes.
     * @param string $help Help tags.
     * @param boolean $loading Include a loading icon.
     *
     * @return string the generated label tag.
     */
    public static function activeLabel($model, $attribute, $htmlOptions=array(), $help=null, $loading=false) {
        if (isset($htmlOptions['for']) === true) {
            $for = $htmlOptions['for'];
            unset ($htmlOptions['for']);
        } else {
            $for = self::getIdByName(self::resolveName($model, $attribute));
        }
        if (isset($htmlOptions['label']) === true) {
            if (($label = $htmlOptions['label']) === false) {
                return '';
            }
            unset ($htmlOptions['label']);
        } else {
            $label = $model->getAttributeLabel($attribute);
        }
        if ($model->hasErrors($attribute) === true) {
            self::addErrorCss($htmlOptions);
        }

        return self::label($label, $for, $htmlOptions, $help, $loading);
    }

    /**
     * Adds help tag to original.
     *
     * Please check {@link CHtml::label} for detailed information.
     *
     * @param CModel $label The label.
     * @param string $for The form element that the label is for.
     * @param array $htmlOptions Additional HTML attributes.
     * @param string $help Help tags.
     * @param boolean $loading Include a loading icon.
     *
     * @return string the generated label tag.
     */
    public static function label($label, $for, $htmlOptions=array(), $help=null, $loading=false) {
        if ($for === false) {
            unset($htmlOptions['for']);
        } else {
            $htmlOptions['for'] = $for;
        }
        if (isset($htmlOptions['required']) === true) {
            if ($htmlOptions['required'] === true) {
                if (isset($htmlOptions['class']) === true) {
                    $htmlOptions['class'] .= ' ' . self::$requiredCss;
                } else {
                    $htmlOptions['class'] = self::$requiredCss;
                }
                $label = self::$beforeRequiredLabel . $label . self::$afterRequiredLabel;
            }
            unset ($htmlOptions['required']);
        }

        $loading_tag = '';
        if ($loading === true) {
            $loading_tag = self::tag('span', array('class' => 'ajax-loading-inline hidden'));
        }

        return self::tag('label', $htmlOptions, $label . $help . $loading_tag);
    }

    /**
     * Add an ajaxLink function.
     *
     * @param string $text The link text.
     * @param string $url The link url.
     * @param array $ajaxOptions Ajax options.
     * @param array $htmlOptions HTML options.
     *
     * @return string
     */
    public static function ajaxLink($text, $url, $ajaxOptions=array(), $htmlOptions=array()) {
        $ajaxOptions['url']=$url;
        $htmlOptions['ajax']=$ajaxOptions;
        self::clientChange('click', $htmlOptions);
        return self::tag('a', $htmlOptions, $text);
    }
}

?>