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
 * Main site actions.
 *
 * @package PHP_Controllers
 */
class SiteController extends Controller
{
    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters
     */
    public function filters() {
        return array(
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
                'allow',
                'actions' => array(
                    'Index',
                    'Setup',
                    'StoreExists',
                    'Error',
                    'Signup',
                    'JSError',
                    'Login',
                    'ModalLogin',
                    'LoginCheckUsername',
                    'LoginCheckSignupCode',
                    'LoginRedirect',
                    'Password',
                    'PasswordReset',
                    'PasswordResetLink',
                    'PasswordCheck',
                    'LoggedIn',
                    'Logout',
                    'LocalLogout',
                    'LogoutAll',
                    'About',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Setsup the DB for a new installation.
     *
     * @return void
     */
    public function actionSetup() {
        $this->skip_log = true;
        $db_found = true;
        try {
            $connection = Yii::app()->db;
            $exists = Setup::doDbsExist();
            if ($exists === false) {
                $db_found = false;
            }
        } catch (Exception $e) {
            $db_found = false;
        }

        if ($db_found === true) {
            throw new CHttpException(400, 'Bad Request. Databases allready exist.');
        }

        Setup::go();

        $this->render('/Public/Page/Site/Setup');
    }

    /**
     * Public page containing general information and links
     *
     * @return void
     */
    public function actionAbout() {
        $this->render('/Shared/Page/Site/About');
    }

    /**
     * Displays the home page as a stream for unauthenticatedusers.
     *
     * @return void
     */
    private function displayPublicHomePageAsStream() {
        $stream = Yii::app()->params['home_page_stream'];
        $controller = new StreamController('stream');
        $controller->actionPosts(
            $stream['username'],
            $stream['name'],
            $stream['version']['major'],
            $stream['version']['minor'],
            $stream['version']['patch'],
            '1',
            $stream['rhythm']['domain'],
            $stream['rhythm']['username'],
            $stream['rhythm']['name'],
            $stream['rhythm']['version']['major'],
            $stream['rhythm']['version']['minor'],
            $stream['rhythm']['version']['patch']
        );
    }

    /**
     * Displays the home page as a stream for unauthenticatedusers.
     *
     * @return void
     */
    private function displayPublicHomePageAsPostWithTree() {
        $post_location = Yii::app()->params['home_page_post'];
        $controller = new PostController('post');
        $controller->actionPostWithTree($post_location['post_id'], $post_location['domain']);
    }

    /**
     * Displays the home page as a stream for unauthenticatedusers.
     *
     * @return void
     */
    private function displayPublicHomePageAsPost() {
        $post_location = Yii::app()->params['home_page_post'];
        $controller = new PostController('post');
        $controller->actionPost($post_location['post_id'], $post_location['domain']);
    }

    /**
     * Site home page.
     *
     * @return void
     */
    public function actionIndex() {
        if (Yii::app()->user->isGuest === true) {
            switch (Yii::app()->params['home_page_type']) {
                case 'stream':
                    $this->displayPublicHomePageAsStream();
                    break;

                case 'post_with_tree':
                    $this->displayPublicHomePageAsPostWithTree();
                    break;

                case 'post':
                    $this->displayPublicHomePageAsPost();
                    break;

                default:
                    throw new CHttpException(500, 'home_page_type is invalid. Please set it in the config');
            }

        } else {
            $this->render('/Client/Page/Home');
        }
    }

    /**
     * This is the action to handle external exceptions.
     *
     * @return void
     */
    public function actionError() {
        $error = Yii::app()->errorHandler->error;
        if ($error !== false) {
            if (Yii::app()->request->isAjaxRequest === true && isset($_POST['ajaxurl']) === false) {
                echo $error['message'];
            } else {
                $this->render('/Shared/Page/Site/Error', $error);
            }
        }
    }

    /**
     * This is the action to log javascript errors.
     *
     * Request varaibles:
     * $_POST['type'] The type of error. See lookup table jserror.type
     * $_POST['data'] A string of JSON data.
     * $_POST['message'] The error message
     * $_POST['location'] Is this the scientia, domus or client domain?
     *
     * @return void
     */
    public function actionJSError() {
        $model = new JsError;
        if (isset($_POST['type']) === true) {
            $model->type = $_POST['type'];
        }
        if (isset($_POST['data']) === true) {
            $model->data = $_POST['data'];
        }
        if (isset($_POST['message']) === true) {
            $model->message = $_POST['message'];
        }
        if (isset($_POST['location']) === true) {
            $model->location_name = $_POST['location'];
        }

        $error = '';
        if ($model->save() === false) {
            $error = $model->getErrors();
        }
        echo JSON::encode(array('error' => $error));
    }

    /**
     *
     * @param type $url
     */
    private function signupRedirect($url) {
        if (isset($url) === false) {
            $url = 'http://' . Yii::app()->params['host'];
        }
        if (substr($url, 0, 8) === 'https://') {
            $url = 'http://' . substr($url, 0, 9);
        }
        $this->redirect($url);
    }

    /**
     * Signup form for a new user.
     *
     * @return void
     */
    public function actionSignup($g_client=null) {
        $this->redirectToSecure();
//        if (Yii::app()->user->isGuest === false) {
//            //$this->signupRedirect($g_client);
//        }
        $user_signup_form = new UserSignupForm;

        if (isset($_POST['UserSignupForm']) === true) {
            $user_post =  Yii::app()->request->getPost('UserSignupForm');
            $user_signup_form->username = $user_post['username'];
            $user_signup_form->password = $user_post['password'];
            $user_signup_form->verify_password = $user_post['verify_password'];
            $user_signup_form->email = $user_post['email'];
            $user_signup_form->test_ok = $user_post['test_ok'];
            if (Yii::app()->params['use_signup_codes'] === true) {
                $user_signup_form->signup_code = $user_post['signup_code'];
            }
            if ($user_signup_form->validate() === true) {
                $user_model = new User;
                $user_model->username = $user_post['username'];
                $user_model->password = $user_post['password'];
                $user_model->setScenario('new');
                // allready validated as part of $user_signup_form->validate()
                $user_ident = new UserIdentity(
                    $user_post['username'],
                    $user_post['password']
                );
                $user_ident->signUp($user_post['email']);
                SignupCode::markUsed($user_signup_form->signup_code, Yii::app()->user->getId());
                $this->signupRedirect($g_client);
            }
        }

        $this->render(
            '/Public/Page/Site/Signup',
            array(
                'model' => $user_signup_form,
            )
        );
    }

    /**
     * When a user recieves a password reset email, it links to here to enable the reset.
     *
     * @param string $g_username The username of the user who is requesting a password reset.
     * @param string $g_reset_secret The secret in the email to confirm that this user is who they say they are.
     * @parma string $g_client_domain The domain of the client website that initiated a login request.
     * @param string $g_secret The secret from the client domain that allows this site to validate that
     *  a login request came from a particular client website.
     * @param string $p_new_password The new password for this user.
     * @param string $p_verify_new_password Confirmation for the new password for this user.
     *
     * return void
     */
    public function actionPasswordResetLink($g_username, $g_reset_secret, $g_client_domain=null, $g_secret=null,
        $p_new_password=null, $p_verify_new_password=null
    ) {
        $secret_valid = User::checkResetSecret($g_username, $g_reset_secret);
        if ($secret_valid === false) {
            throw new CHttpException(400, 'Bad Request. Secret is incorrect or has expired.');
        }

        $error = false;
        if (isset($p_new_password) === true) {
            if ($p_new_password !== $p_verify_new_password) {
                $error = 'Passwords do not match';
            } else if (empty($p_new_password) === true) {
                $error = 'Password must not be empty';
            } else {
                $user_ident = new UserIdentity($g_username, $p_new_password);
                $user_ident->updatePassword();
                $login_querystring = '?username=' . $g_username;
                if (isset($g_secret) === true) {
                    $login_querystring .= '&secret=' . $g_secret;
                }
                if (isset($g_client_domain) === true) {
                    $login_querystring .= '&client_domain=' . $g_client_domain;
                }
                $this->render('/Public/Page/Site/PasswordResetDone', array('login_querystring' => $login_querystring));
                return;
            }
        }

        if (isset($p_new_password) === false) {
            $p_new_password = '';
        }
        if (isset($p_verify_new_password) === false) {
            $p_verify_new_password = '';
        }

        $this->render(
            '/Public/Page/Site/PasswordResetLink',
            array(
                'error' => $error,
                'new_password' => $p_new_password,
                'verify_new_password' => $p_verify_new_password,
            )
        );
    }

    /**
     * Enables the user to send a password reset email to themselves.
     *
     * @param string $g_username The username of the user who is requesting a password reset.
     * @parma string $g_client_domain The domain of the client website that initiated a login request.
     * @param string $g_secret The secret from the client domain that allows this site to validate that
     *  a login request came from a particular client website.
     * @param string $g_reset Has the user confirmed the password reset.
     *
     * return void
     */
    public function actionPasswordReset($g_username, $g_client_domain=null, $g_secret=null, $g_reset=null) {
        $this->redirectToSecure();
        $login_querystring = '?username=' . $g_username;
        if (isset($g_secret) === true) {
            $login_querystring .= '&secret=' . $g_secret;
        }
        if (isset($g_client_domain) === true) {
            $login_querystring .= '&client_domain=' . $g_client_domain;
        }

        $email = User::getEmail($g_username);
        if ($email === false) {
            $this->render('/Public/Page/Site/NoResetEmail', array('login_querystring' => $login_querystring));
            return;
        }

        if ($g_reset === 'true') {
            $subject = 'Password reset request for ' . Yii::app()->params['host'];
            $headers="From: noreply@" . Yii::app()->params["host"] . "\r\n".
                "Reply-To: noreply@" . Yii::app()->params["host"] . "\r\n".
                "MIME-Version: 1.0\r\n".
                "Content-Type: text/plain; charset=UTF-8";
            $reset_secret = CryptoHelper::makeGuid();
            User::storeResetSecret($g_username, $reset_secret);
            $body = "Click the link below to reset the password for username: " . $g_username . "\r\n\r\n" .
                "If you did not reqeust a password reset then you can ignore this email.\r\n\r\n" .
                "https://" . Yii::app()->params["host"] . "/site/passwordresetlink" .$login_querystring
                . "&reset_secret=" . $reset_secret;

            mail($email, $subject, $body, $headers);

             $this->render('/Public/Page/Site/PasswordResetSent', array('login_querystring' => $login_querystring));
        } else {
            $this->render('/Public/Page/Site/PasswordReset', array('login_querystring' => $login_querystring));
        }
    }

    /**
     * Redirects a the user to a secure version of this page if they are on an insecure connection.
     *
     * @return void
     */
    private function redirectToSecure() {
        if (Yii::app()->getRequest()->isSecureConnection === false) {
            $url = 'https://'
                . Yii::app()->getRequest()->serverName
                . Yii::app()->getRequest()->requestUri;
            Yii::app()->request->redirect($url);
            return;
        }
    }

    /**
     * User login.
     *
     * The login process is complex as any client site can log in via a users data store hosted elswhere
     * and yet that same site may have its own data store for other users.
     *
     * If the username is not local then the users data store is queried to see if it is valid.
     * If the username is valid then an ok is sent to the browser.
     * The browser then redirects to the LoginRedirect action using https so that a secret can be stored
     * and the browser can be redirected to the data store action.
     * The data store then validates the password and redirects the secret back
     * to the client with https using action actionLoggedIn.
     * The client then checks the secret and logs the user on localy and redirects the browser to the return location.
     *
     * @return void
     */
    public function actionLogin() {
        $this->redirectToSecure();
        // Attatch the js here so that it does not get downloaded for modal requests.
        $cs = Yii::app()->getClientScript();
        //$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Public/Login.js');
        $this->render('/Public/Page/Site/Login');
    }

    /**
     * Starts the login and signup process from the modal login popup.
     *
     * @return void
     */
    public function actionModalLogin() {

        $this->layout='blank';
       // $cs = Yii::app()->getClientScript();
      //  $cs->registerCssFile(Yii::app()->baseUrl . '/css/screen.css');
        $this->render('/Public/Page/Site/Login');
    }

    /**
     * Checks the username of a user is valid.
     *
     * Also checks the signup_code if it is applicable.
     *
     * @param string $p_username  The full username.
     * @param string $p_signup_code The signup code to activate this username.
     *
     * @return void
     */
    public function actionLoginCheckSignupCode($p_full_username, $p_signup_code) {
        $user = UserIdentity::standardiseUser($p_full_username);
        if (SignupCode::isValid($p_signup_code) === true) {
            $valid = true;
            SignupCode::hold($user['domain'], $user['username'], $p_signup_code);
        } else {
            $valid = false;
        }
        $json = array('valid' => $valid);
        echo JSON::encode($json);
    }

    /**
     * Checks the username of a user is valid.
     *
     * Also checks the signup_code if it is applicable.
     *
     * @param string $p_username  The full username.
     *
     * @return void
     */
    public function actionLoginCheckUsername($p_full_username) {
        $user = UserIdentity::standardiseUser($p_full_username);
        $signup_code_status = true;
        $error_message = false;

        $site_id = SiteMulti::getSiteID($user['domain'], true, true);

        if ($site_id === false) {
            $exists = false;
            $error_message = '<Strong>' . $user['domain'] . '</strong> is not  a valid Babbling Brook domain';
        } else {
            $user_multi = new UserMulti($site_id);
            $exists = $user_multi->userExists($user['username'], true, $user['domain']);

            if (Yii::app()->params['use_signup_codes'] === true) {
                $user_id = User::getIDFromUsernameAndDomain($user['username'], $user['domain']);
                if ($user_id === false) {
                    $signup_code_status = false;
                } else if (SignupCode::hasUserAlreadySignedUp($user_id) === false) {
                    $signup_code_status = false;
                }
            }

//            if ($exists === false && $user['domain'] !== Yii::app()->params['host']) {
//                // Don't bother if we know the code is invalid.
//                if (Yii::app()->params['use_signup_codes'] === false || $signup_code_status === true) {
//                    // check remote domain for username otherDomainHelper.
//                    // ...
//                }
//            }
        }

        $json = array(
            'signup_code_status' => $signup_code_status,
            'success' => true,
            'exists' => $exists,
            'error_message' => $error_message,
        );

        echo JSON::encode($json);
    }

    /**
     * Generates a secret to validate return request and then redirects to the data store.
     *
     * Once a username is validated the users browser is redirected here using https so that a secret can be
     * generated, stored and then sent to the clients domain. This secret is passed back after logging in
     * to allow the user to be logged on at the clients site.
     *
     *
     * @param string $p_username The username in short, url or email form.
     * @param string $p_return_location The location to return a user to after loggging in.
     *
     * @retuirn void
     */
    public function actionLoginRedirect($g_username, $g_return_location) {

        $user = UserIdentity::standardiseUser($g_username);
        $secret = CryptoHelper::makeGuid();

        if (empty($g_return_location) === true) {
            $g_return_location = Yii::app()->params['default_after_login_location'];
        }

        // Store the secret in the database
        Login::setRow($user['username'], $user['domain'], $secret, $g_return_location);

       // Redirect to the users data store for password entry.
        header(
            'Location: https://' . $user['domain']
                . '/site/password?username=' . $user['username']
                . '&secret=' . $secret
                . '&client_domain=' . Yii::app()->params['host']
        );
    }


    /**
     * This is a data store action. The user is redirected here from the client when they login.
     *
     * After enetering the correct password they are redirected back to the client.
     * See the PasswordCheck action for password validation.
     *
     * @param string $g_username The short version of the username of the user who is logging in.
     * @param string $g_secret The secret that client domain has sent. This needs sending back to the client
     *                         When the client has logged in. It confirmed that they have logged into their
     *                         data store and can be logged into the client.
     *                         Documented here but only used in javascript
     * @param string $g_client_domain The domain of the client that is requesting to be logged in.
     *                                Documented here but only used in javascript
     * @param string $p_password The password that is entered into the password form.
     * @param string $p_remember_me Has the user checked the remember me box.
     *
     * @return void
     */
    public function actionPassword($g_username, $g_secret, $g_client_domain, $p_password=null, $p_remember_me=null) {
        $this->redirectToSecure();
        $view_data = array('username' => $g_username);
        if (isset($p_password) === true) {
            $view_data = $this->passwordCheck($g_username, $p_password, $p_remember_me, $g_client_domain, $g_secret);
            if ($view_data === false) {
                return;
            }
        }

        $user_multi = new UserMulti;
        $exists = $user_multi->userExists($g_username);
        if ($exists === false) {
            throw new CHttpException(400, 'Bad Request. User does not exist');
        }

        $this->render('/Public/Page/Site/Password', $view_data);
    }

    /**
     * Processes a data store login request.
     *
     * This will also login the client if it is local.
     * To log in a client from a remote data store.
     *
     * @param string $p_username The short version of the username of the user who is logging in.
     * @param string $p_password The password of the user who is logging in.
     * @param string $p_remember_me Whether to remember this user or not on this domain.
     * @param string $p_client_domain The domain that is logging on.
     * @param string $g_secret The secret that client domain has sent. This needs sending back to the client
     *                         When the client has logged in. It confirmed that they have logged into their
     *                         data store and can be logged into the client.
     *                         Documented here but only used in javascript
     *
     * @return void
     */
    private function passwordCheck($p_username, $p_password, $p_remember_me, $p_client_domain, $g_secret) {

        $user_multi = new UserMulti;
        $exists = $user_multi->userExists($p_username);
        if ($exists === false) {
            throw new CHttpException(400, 'Bad Request. User does not exist');
        }

        $login_form = new LoginForm;
        $login_form->username = $p_username;
        $login_form->password = $p_password;
        $login_form->remember_me = $p_remember_me === 'yes' ? true : false;
        $login_form->domain = $p_client_domain;
        $identity = new UserIdentity($p_username, $p_password);
        $login_form->setIdentity($identity);
        $error = false;

        if ($p_remember_me === 'yes') {
            $remember_time = time() + Yii::app()->params['remember_timeout'];
        } else {
            $remember_time = 0;
        }

        if ($login_form->validate() === true) {
            $identity->loginUser($p_client_domain, $remember_time);
        } else {
            $error = $login_form->getError('password');
        }
        $view_data = array(
            'username' => $p_username,
            'success' => ($error === false) ? true : false,
            'remember_me' => $p_remember_me,
            'remember_time' => $remember_time,
            'error' => $error,
            'client_domain' => $p_client_domain,
            'secret' => $g_secret,
        );

        if ($error !== false) {
            return $view_data;
        }

        // Need to redirect from javascript so that the remember me cookie is saved.
        $this->layout='blank';
        $this->render('/Client/Page/Site/RedirectToClient', $view_data);
        return false;
    }


    /**
     * Return url after logging in to a users data store.
     *
     * The secret passed from the data store is checked with the one stored in the session to see if they match.
     * If the user is not local then they are logged in.
     * Local users are already logged in.
     *
     * @param {string} $g_secret The secret that this client sent to the data store with the loggin request.
     *                           It was stored in session and now needs to be compared with the origional to
     *                           log the user on here.
     * @param {string} $g_remember_time Time in seconds to remember this user as logged in if session ends.
     *
     * @return void
     */
    public function actionLoggedIn($g_secret, $g_remember_time) {

        if (ctype_digit($g_remember_time) === false) {
            throw new CHttpException(400, 'Bad data. Remember time is not a unix timestamp.');
        }

        $login_row = Login::getRowBySecret($g_secret);
        if ($login_row === false) {
            throw new CHttpException(400, 'Bad data or login timout.');
        }
        // Check that the user signup code is active
        if (Yii::app()->params['use_signup_codes'] === true) {
            $user_id = User::getIDFromUsernameAndDomain($login_row['username'], $login_row['domain']);
            if (SignupCode::hasUserAlreadySignedUp($user_id) === false) {
                $code = SignupCode::getOnHold($login_row['username'], $login_row['domain']);
                if ($code !== false) {
                    SignupCode::markUsed($code, $user_id);
                } else {
                     throw new CHttpException(400, 'Bad data. Signup code is no longer marked as available.');
                }
            }
        }

        Login::deleteRowByUsername($login_row['username'], $login_row['domain']);

        $identity = new UserIdentity($login_row['username'], null, $login_row['domain']);
        $identity->loginClientUser($g_remember_time);

        // This may be the first time the user has logged into this site.
        // Check that they have their default subscriptions.
        $defaults_length = count(Yii::app()->params['default_subscriptions']);
        if ($defaults_length > 0) {
            $user_id = Yii::app()->user->getId();
            if (UserStreamSubscription::getFirstId($user_id) === false) {
                UserStreamSubscription::insertDefaults($user_id);
            }
        }

        $this->redirect($login_row['return_location']);
    }


    /**
     * This is the first part of the logout process.
     *
     * Returns a view containing javascript that then logs the user out locally and then sends a message to the
     * data store to logout all other windows/tabs and logs out the data store.
     *
     * The user is not logged out in this initial request because the data store needs to load so that it can
     * be sent the logout message.
     *
     * @return void
     */
    public function actionLogout() {

        if (Yii::app()->user->isGuest === true) {
            $this->redirect('/?loggedout=true');
        }

        $this->render('/Client/Page/Site/Logout');
    }

    /**
     * Logs any logged in user out of this local client site.
     *
     * @return void
     */
    public function actionLocalLogout() {

        // The client may also be the data store, so be sure to logout of site_access
        // as the user will already be logged out when they try to from the data store.
        if (Yii::app()->user->getSiteId() === Yii::app()->params['site_id']) {
            SiteAccess::removeClient(Yii::app()->user->getId(), Yii::app()->params['site_id']);
        }

        Yii::app()->user->logout();

        if (Yii::app()->user->isGuest === true) {
            $success = true;
        } else {
            $success = false;
        }

        $json = array('success' => $success);
        echo JSON::encode($json);
    }

}

?>