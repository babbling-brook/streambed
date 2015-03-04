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
 * WebApplication Extends the Yii CWebApplication because I can't workout how to get controllerNamespace to work
 * correctly. With this updated namespace, the correct controller subfolder is selected when
 * controllerNamespace => $subdomain is set in the main config file.
 *
 * @package PHP_ExtendedYii
 */
class WebApplication extends CWebApplication
{

    /**
     * See CWebApplication for documentation.
     *
     * @param string $route
     * @param CWebModule $owner
     *
     * @return array
     */
    public function createController($route, $owner=null) {
        if ($owner===null) {
            $owner=$this;
        }
        if (($route=trim($route, '/')) === '') {
            $route=$owner->defaultController;
        }
        $caseSensitive=$this->getUrlManager()->caseSensitive;

        $route.='/';
        while (($pos=strpos($route, '/')) !== false) {
            $id=substr($route, 0, $pos);
            if (preg_match('/^\w+$/', $id) === false) {
                return null;
            }
            if ($caseSensitive === false) {
                $id=strtolower($id);
            }
			$route=(string)substr($route,$pos+1);
			if(isset($basePath) === false) {
				if(isset($owner->controllerMap[$id]) === true) {
                    return array(
                        Yii::createComponent($owner->controllerMap[$id], $id, $owner===$this?null:$owner),
                        $this->parseActionParams($route),
                    );
                }

                if (($module=$owner->getModule($id)) !== null) {
                    return $this->createController($route, $module);
                }

                $basePath=$owner->getControllerPath();
                $controllerID='';
            } else {
                $controllerID.='/';
            }

            $className=ucfirst($id).'Controller';

//            if ($owner->controllerNamespace!==null)
//                $className=$owner->controllerNamespace.'\\'.$className;

            $classFile=$basePath.DIRECTORY_SEPARATOR.$owner->controllerNamespace.DIRECTORY_SEPARATOR.$className.'.php';

            if (is_file($classFile) === true) {
                if (class_exists($className, false) === false) {
                    require ($classFile);
                }

                if (class_exists($className, false) === true && is_subclass_of($className, 'CController') === true) {
                    $id[0]=strtolower($id[0]);
                    return array(
                        new $className($controllerID.$id,$owner===$this?null:$owner),
                        $this->parseActionParams($route),
                    );
                }
                return null;
            }
            $controllerID .= $id;
            $basePath .= DIRECTORY_SEPARATOR.$id;
        }
    }
}

?>