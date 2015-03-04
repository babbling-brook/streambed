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
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 *
 * @package PHP_ExtendedYii
 */
class Controller extends CController
{
    /**
     * The default layout for the controller view.
     *
     * Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     *
     * @var string
     */
    public $layout;

    /**
     * @var boolean set this to true to skip the logging of this call.
     */
    public $skip_log = false;

    /**
     * Only actions that have this set to true (in the isFilterFilter filter). Can be accessed on the filter subdomain.
     *
     * @var boolean
     */
    public $is_filter = false;

    /**
     * Only actions that have this set to true (in the isSuggestion filter) can be accessed on the suggestion subdomain.
     *
     * @var boolean
     */
    public $is_suggestion = false;

    /**
     * Only actions that have this set to true (in the isRing filter) can be accessed on the ring subdomain.
     *
     * @var boolean
     */
    public $is_ring = false;

    /**
     * Only actions that have this set to true (in the isKindred filter) can be accessed on the ring subdomain.
     *
     * @var boolean
     */
    public $is_kindred = false;

    /**
     * Only actions that have this set to true (in the isScientia filter) can be accessed on the ring subdomain.
     *
     * @var boolean
     */
    public $is_scientia = false;

    /**
     * Only actions that have this set to true (in the IsDomusFilter filter) can be accessed on the domus subdomain.
     *
     * @var boolean
     */
    public $is_domus = false;

    /**
     * Only action that have this set to to true (in the isClientFilter filter) can be accessed on the main domain.
     *
     * @var boolean
     */
    public $is_client = false;

    /**
     * The id of this subdomain in db log.subdomain
     *
     * @var integer
     */
    public $subdomain_id = 1;

    /**
     * Context menu items. This property will be assigned to {@link CMenu::items}.
     *
     * @var array
     */
    public $menu=array();

    /**
     * The title for the operations menu.
     *
     * @var string
     */
    public $menu_title = 'Operations';

    /**
     * Any extra html to display on the side menu.
     *
     * @var boolean
     */
    public $menu_extra = '';

    /**
     * Default string to be appeded to url redirects if ajaxurl is enabled.
     *
     * @var string
     */
    public $ajaxurl = "";

    /**
     * Append this to iframe javascript files if a test user is requesting them.
     *
     * It will force fresh versions to be loaded.
     *
     * @var string
     */
    public $js_version_number = "";

    /**
     * @var boolean A flag to indicate that test data is in use.
     */
    public $testing = false;

    /**
     * A timestamp for when execution of this action started.
     *
     * @var string In the format given by microtime().
     */
    private $execution_start_time;

    /**
     * An array of paramaters and their content for this action.
     *
     * @var array
     */
    private $params = array();

    /**
     * Part of a dirty hack to ensure that the sidebar displays in the correct place on public stream views.
     * @var type
     */
    public $public_stream_view = false;

    /**
     * Override the constructor to set the $ajaxurl value.
     *
     * @param integer $id The page id.
     * @param object $module The module.
     */
    public function __construct($id, $module=null) {
        if (Yii::app()->user->isGuest === true) {
            $this->layout = '/Public/Layouts/ClientType/' . CLIENT_TYPE . '/Main';
        } else {
            $this->layout = '/Client/Layouts/ClientType/' . CLIENT_TYPE . '/Main';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Yii::app()->user->isGuest === false) {
            if (isset($_POST['csfr_token']) === false) {
                throw new CHttpException(400, 'Must include a valid CRFS token.');
            } else if ($_POST['csfr_token'] !== Yii::app()->user->getCSFRToken()) {
                throw new CHttpException(400, 'CRFS token is invalid.');
            }
        }

        if ((isset($_POST['testing']) === true && $_POST['testing'] === 'true')
            || (isset($_GET['testing']) === true && $_GET['testing'] === 'true')
        ) {
            $this->testing = true;
        }

        parent::__construct($id, $module);
        $this->execution_start_time = microtime(true);
        ob_start();

        // If this is a local environment then pause to simulate being on a hosted environment.
        if (Yii::app()->params['local_host_delay'] > 0) {
            usleep(Yii::app()->params['local_host_delay'] * 1000);
        }

        // Create the default ajaxurl queryline amendment for redirects
        $this->ajaxurl = isset($_POST['ajaxurl']) === true ? "?ajaxurl=true&time=" . time() : "";

        // Include the fake firbug.console file?
        if (Yii::app()->params['fake_firebug'] === true) {
            Yii::app()->getClientScript()->registerScriptFile(Yii::app()->baseUrl . "/js/firebug/fake.js");
        }

        // If this is the test user then over ride some settings to make testing easier.
        $this->js_version_number = "?rnd=" . Yii::app()->params['javascript_version'];
    }

    /**
     * Overrides the CController beforeAction method.
     *
     * Called before the action and after filters.
     *
     * @param CInlineAction $action The action that is about to run.
     *
     * @return boolean
     */
    public function beforeAction($action) {
        // We don't want to hide a filter error with one of the errors below. As the errors below
        // will erroneously trigger if a different filter errors: the tests below are based on filters running.

        // Checks if this request is on on the correct subdomain.
        if ($_SERVER['HTTP_HOST'] === "domus." . Yii::app()->params['host']) {
            $this->subdomain_id = 8;
//            if ($this->is_domus === false) {
//                //throw new Exception("This controller/action is not part of the domus subdomain.");
//            }
        } else if ($_SERVER['HTTP_HOST'] === "filter." . Yii::app()->params['host']) {
            $this->subdomain_id = 7;
//            if ($this->is_filter === false) {
//                //throw new Exception("This controller/action is not part of the filter subdomain.");
//            }
        } else if ($_SERVER['HTTP_HOST'] === "kindred." . Yii::app()->params['host']) {
            $this->subdomain_id = 6;
//            if ($this->is_kindred === false) {
//                //throw new Exception("This controller/action is not part of the kindred subdomain.");
//            }
        } else if ($_SERVER['HTTP_HOST'] === "suggestion." . Yii::app()->params['host']) {
            $this->subdomain_id = 5;
//            if ($this->is_suggestion === false) {
//                //throw new Exception("This controller/action is not part of the suggetsion subdomain.");
//            }
        } else if ($_SERVER['HTTP_HOST'] === "ring." . Yii::app()->params['host']) {
            $this->subdomain_id = 4;
//            if ($this->is_ring === false) {
//                //throw new Exception("This controller/action is not part of the ring subdomain.");
//            }
        } else if ($_SERVER['HTTP_HOST'] === "scientia." . Yii::app()->params['host']) {
            $this->subdomain_id = 3;
//            if ($this->is_scientia === false) {
//                //throw new Exception("This controller/action is not part of the scientia subdomain.");
//            }
        } else {
            $this->subdomain_id = 2;
//            if ($this->is_client === false) {
//                //throw new Exception("This controller/action is not part of the client domain.");
//            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Render the view.
     *
     * @param string $view The view to render.
     * @param array $data Data that is passed to the view.
     * @param boolean $return Shall we return output or echo it to screen.
     *
     * @return string|null The output or not returned.
     */
    public function render($view, $data=null, $return=false) {
        $cs = Yii::app()->getClientScript();
        // Remove auto generated base files.
        // These must now be set manually on any page that needs them,
        // and the name must be altered or they will still be blocked
        $cs->scriptMap = array(
            'jquery.ba-bbq.js' => false,
            'jquery.js' => false,
            'jquery.yii.js' => false,
            'jquery-ui.min.js' => false,
            'jquery.yiiactiveform.js' => false,
            'jquery.yiilistview.js' => false,
        );

        $output = $this->renderPartial($view, $data, true);
        if (isset($_REQUEST['ajaxurl']) === true) {

            // If ajaxurl is set on the url then it needs converting to an array
            if (isset($_GET['ajaxurl']) === true && $_GET['ajaxurl'] !== "true") {
                $ajaxURL = CJSON::decode($_GET['ajaxurl'], true);
            }

            // Render content and place in elements array
            $elements = array();

            $elements['#sidebar_container']
                = $this->renderPartial('/Client/Layouts/ClientType/' . CLIENT_TYPE . '/_sidebar', null, true);
            $elements['#content_page'] = $output;

            $raw_css = $cs->getCSSfiles();
            $raw_script = $cs->getScriptfiles();
            $inline_script = $cs->getScripts();

            // Process css and javascript into flat arrays
            // Assumes all are screen. Would need adapting for other media.
            $css = array();
            foreach (array_keys($raw_css) as $item) {
                array_push($css, $item);
            }
            $script = array();
            foreach ($raw_script as $row) {
                foreach ($row as $item) {
                    // Some assest files still get inlucded for some reason. Remove them here.
                    if (substr($item, 0, 8) !== "/assets/") {
                        array_push($script, $item);
                    }
                }
            }

            // Create the url for the Javascript history object; strip off any ajax url references
            $url = $_SERVER['REQUEST_URI'];
            if (strpos($url, "ajaxurl=") > 0) {
                $url = substr($url, 0, strpos($url, "ajaxurl="));
            }
            if ($url[strlen($url) - 1] === "?") {       // Is the last character in url == ?
                $url = substr($url, 0, strlen($url)-1);
            }

            //array_pop($script);

            // Construct the JSON array
            $json_array = array(
                "title" => $this->pageTitle,
                "css" => $css,
                "script" => $script,
                "inlineScript" => $inline_script,
                "url" => $url,
                "elements" => $elements,
            );

            echo (JSON::encode($json_array));
            Yii::app()->end();
        } else if (($layoutFile=$this->getLayoutFile($this->layout))!== false) {
            $output=$this->renderFile($layoutFile, array('content' => $output), true);
        }
        $processed_output=$this->processOutput($output);

        if ($return === true) {
            return $processed_output;
        } else {
            echo $processed_output;
        }
    }

    /**
     * Override the redirect function so that the ajaxurl parameter can be apended if it is present.
     *
     * @param type $url The URL to be redirected to.
     *                  If the parameter is an array, the first element must be a route to a
     *                  controller action and the rest are GET parameters in name-value pairs.
     * @param type $terminate Whether to terminate the current application after calling this method.
     * @param type $statusCode The HTTP status code.
     *
     * @return void
     */
    public function redirect($url, $terminate=true, $statusCode=302) {

        if (is_array($url) === false) {
            if (isset($_REQUEST['ajaxurl']) === true) {
                if (strpos($url, '?') === false) {
                    $url .= '?ajaxurl=true';
                } else {
                    $url .= '&ajaxurl=true';
                }
            }
        }

        parent::redirect($url, $terminate, $statusCode);
    }

    /**
     * Override the method for passing paramaters to actions.
     *
     * Include POST as well as GET by prepending with p_ for all post paramaters and g_for all get paramaters.
     *
     * @return array
     */
    public function getActionParams() {
        foreach ($_GET as $key => $value) {
            $this->params['g_' . $key] = $value;
        }
        foreach ($_POST as $key => $value) {
            $this->params['p_' . $key] = $value;
        }

        return $this->params;
    }

    /**
     * Improve the default error message for missing action paramaters.
     *
     * Detect and display a list of paramaters that are missing.
     *
     * @param CAction $action The action that is being called with the wrong paramaters.
     *
     * @return void
     */
    public function invalidActionParams($action) {

        // Fetch the paramaters that have been passed in.
        $available_params = $this->actionParams;
        $action_name = 'action' . ucfirst($action->id);

        // Fetch the paramaters that are detailed in the action that is reporting an error.
        $method = new ReflectionMethod($action->controller, $action_name);
        $params = $method->getParameters();
        $missing_params = array();
        foreach ($params as $param) {
            if (array_key_exists($param->name, $available_params) === false) {
                $type = "";
                if (substr($param->name, 0, 2) === "g_") {
                    $type = '$_GET["';
                } else if (substr($param->name, 0, 2) === "p_") {
                    $type = '$_POST["';
                }
                array_push($missing_params, $type . substr($param->name, 2) . '"]');
            }
        }

        // List each of the params
        throw new CHttpException(
            400,
            Yii::t('yii', 'Your request is invalid. Action requires paramater(s) : ' . implode(', ', $missing_params))
        );
    }

    /**
     * Throws an error if the action is not accessed via https
     *
     * @return void
     */
    public function ensureSSL() {
        if (intval($_SERVER['HTTPS']) === 1) /* Apache */ {
            return;
        } else if ($_SERVER['HTTPS'] === 'on') /* IIS */ {
            return;
        } else if (intval($_SERVER['SERVER_PORT']) === 443) /* others */ {
            return;
        } else {
            throw new CHttpException(403, "This url must be accessed over SSL");
        }
    }

    /**
     * Runs after all actions so that we can log the that the action has been used.
     */
    protected function afterAction($action) {
        if ($this->skip_log === true) {
            return;
        }

        $response_time_float = microtime(true) - $this->execution_start_time;
        $response_time_full = $response_time_float * 1000;
        $response_time = ceil($response_time_full);

        $response_size = ob_get_length();
        ob_end_flush();

        $action_name = $action->id;
        $controller_name = $action->controller->id;
        ActionLog::logAction(
            $controller_name,
            $action_name,
            $this->params,
            $response_size,
            $response_time,
            $this->subdomain_id,
            Yii::app()->user->getId()
        );
    }
}

?>