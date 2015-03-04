<?php
/**
 *
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
 * Manages the creation of signup codes.
 *
 * @package PHP_Controllers
 */
class SignupCodesController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            array(
                'application.filters.IsClientFilter
                    +
                    Create,
                    Index,
                    CreateCodes'
            ),
            'accessControl',
        );
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array(
                'allow',    // Public access
                'actions' => array(''),
                'users' => array('*'),
            ),
            array(
                'allow', // Admin access
                'actions' => array(
                    'Index',
                    'Create',
                    'CreateCodes',
                ),
                'expression' => 'Yii::app()->user->isadmin()',
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Presents a form for an admin to create access codes.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Client/Admin/SignupCodes/Index');
    }

    /**
     * Presents a form for an admin to create access codes.
     *
     * @return void
     */
    public function actionCreate() {
        $this->render('/Client/Admin/SignupCodes/Create');
    }


    /**
     * Presents a form for an admin to create access codes.
     *
     * @param string $p_primary_category The primary category to tag the codes with.
     * @param string $p_secondary_category The secondary category to tag the codes with.
     * @param string $p_qty The quantity of codes to create.
     *
     * @return void
     */
    public function actionCreateCodes($p_primary_category, $p_secondary_category, $p_qty) {
        $signup_codes_form = new CreateSignupCodesForm;
        $signup_codes_form->primary_category = $p_primary_category;
        $signup_codes_form->secondary_category = $p_secondary_category;
        $signup_codes_form->qty = $p_qty;
        if ($signup_codes_form->validate() === false) {
            $json = JSONHelper::convertYiiModelError($signup_codes_form->getErrors());
        } else {
            $signup_codes_form->createCodes();
            $json = array('success' => true);
        }
        echo JSON::encode($json);
    }

}

?>