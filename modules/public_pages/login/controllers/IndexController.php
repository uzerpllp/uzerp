<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Ramsey\Uuid\Uuid;

/**
 * IndexController Allows users to login to uzERP
 *
 * @version $Revision: 1.17 $
 * @package login
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class IndexController extends Controller
{

    protected $username = '';

    public function __construct($module=null, $view)
    {
        parent::__construct($module, $view);

        $this->logger = uzLogger::Instance();
        // set log 'channel' for authentication error/audit messages
        $this->logger = $this->logger->withName('uzerp_authentication');
        
        // Form based login
        if (isset($_POST['username'])) {
            $this->username = $_POST['username'];
        }

        // Or we might be using LDAP, etc.
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->username = $_SERVER['PHP_AUTH_USER'];
        }
    }

    public function index($collection=null, $sh = '', &$c_query = null)
    {

        if (!isset($_SESSION['post_login_page']) || empty($_SESSION['post_login_page'])){
            $_SESSION['post_login_page'] = $this->_data;
        }

        // if we're not yet secure, check to see if we could be
        if (SERVER_SECURE === FALSE) {

            // build secure link to this instance
            $check_domain = 'https://' . $_SERVER['SERVER_NAME'];

            // check if the is_logged_in script is available
            // we use this as index.php or system.php may exist on other systems

            if (is_domain_availible($check_domain . '/lib/scripts/is_logged_in.php')) {
                header('Location: ' . $check_domain);
            }
        }

        $this->view->set('ajax', isset($this->_data['ajax']));
        $this->view->set('action', 'login');
        $this->view->set('layout', 'loginpage');

        // don't show login form for non-interactive logins
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');
        $require_mfa = false;
        if (method_exists($authentication, 'require_factor')) {
            $require_mfa = $authentication->require_factor();
        }

        $user = DataObjectFactory::Factory('User');
        $user->load($_SESSION['username']);

        // Enroll user for MFA
        if ($require_mfa === true && (isset($_SESSION['mfa_status']) && $_SESSION['mfa_status'] == 'enrolling')) {
            unset($_SESSION['mfa_status']);
            $mfa_errors = [];
    
            if (!isset($_SESSION['mfa_pack'])) {
                $pack = $authentication->validator->Enroll($user, $mfa_errors);
                if (count($mfa_errors) > 0) {
                    $this->logger->error('MFA Error on starting enrollment', ['username' => $_SESSION['username'], 'errors' => $mfa_errors]);
                }
                $_SESSION['mfa_pack'] = $pack;
            } else {
                $pack = $_SESSION['mfa_pack'];
            }
            $qrcode_png = base64_encode($pack['qrCode']);
            $this->view->set('qrcode', "data:image/png;base64, {$qrcode_png}");
            $this->view->set('secret', $pack['secret']);
            $this->view->set('action', 'mfaenroll');
            $this->_templateName = $this->getTemplateName('mfaenroll');
        }

        // Allow login without MFA and re-enable
        if ($require_mfa === true && $user->mfa_enrolled === 't' && $user->mfa_enabled === 'f') {
            $user->update($user->username, 'mfa_enabled', 't');
            unset($_SESSION['mfa_status']);
            $this->completeLogin($user);
        }

        // Request MFA token
        if ($require_mfa === true && (isset($_SESSION['mfa_status']) && $_SESSION['mfa_status'] == 'validate')) {
            $this->view->set('action', 'mfavalidate');
            $this->_templateName = $this->getTemplateName('mfavalidate');
        }

        if (! $authentication->interactive()) {
            $this->login();
            exit();
        }
    }


    /**
     * Process user MFA enrollment
     *
     * @return void
     */
    public function mfaenroll()
    {
        if ($this->_injector->getRequest()->getMethod() !== 'POST'){
            sendTo('index');
        }

        $flash = Flash::Instance();
        $mfa_errors = [];
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');
        
        $user = DataObjectFactory::Factory('User');
        $user->load($_SESSION['username']);

        $pack = $_SESSION['mfa_pack'];
        $code = $_POST['authcode'];

        $enrolled = false;
        $enrolled = $authentication->validator->VerifyEnroll($user, $pack, $code, $mfa_errors);

        if ($enrolled === true) {
            $user->update($user->username,
                ['mfa_sid', 'mfa_enrolled', 'mfa_enabled'],
                [$pack['sid'], 't', 't']
            );
            unset($_SESSION['mfa_pack']);
            $flash->addMessage('Enrollment successful');
            $this->completeLogin($user);
        }
        if (count($mfa_errors) > 0) {
            if (array_key_exists(404, $mfa_errors) ||
                array_key_exists(60311, $mfa_errors))
            {
                // The factor is no longer available to be validated or too many retries, remove it from the session.
                unset($_SESSION['mfa_pack']);
                $flash->addError('Factor expired, please log in to continue');
                sendTo('index');
            }
            $this->logger->error('MFA Error on verifying enrollment', ['username' => $_SESSION['username'], 'errors' => $mfa_errors]);
        }
        $flash->addError('Enrollment failed');
        sendTo('index');
    }


    /**
     * Validate user MFA token
     *
     * @return void
     */
    public function mfavalidate()
    {
        if ($this->_injector->getRequest()->getMethod() !== 'POST'){
            sendTo('index');
        }

        // The user can cancel the token validation dialog
        if (isset($this->_data['cancel'])) {
            unset($_SESSION['mfa_status']);
            sendTo('index');
        }

        $flash = Flash::Instance();
        $mfa_errors = [];
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');
        
        $user = DataObjectFactory::Factory('User');
        $user->load($_SESSION['username']);

        $code = $_POST['authcode'];

        $valid = false;
        $valid = $authentication->validator->ValidateToken($user, $code, $mfa_errors);
        if (count($mfa_errors) > 0) {
            $this->logger->error('MFA Error on token validation', ['username' => $_SESSION['username'], 'errors' => $mfa_errors]);
        }
        if ($valid === true) {
            unset($_SESSION['mfa_status']);
            $this->completeLogin($user);
        } else {
            $flash->addError('Code not Valid');
            sendBack();
        }
        unset($_SESSION['mfa_status']);
        sendTo('index');
    }

    /**
     * Process the user login
     *
     * @return void
     */
    public function login()
    {
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');

        $require_mfa = false;
        if (method_exists($authentication, 'require_factor')) {
            $require_mfa = $authentication->require_factor();
        }

        $flash = Flash::Instance();

        if ($authentication->interactive()) {
            if (! isset($this->username) || ! isset($_POST['password'])) {
                $flash->addError("Please enter a username and password");
                sendTo();
            }
        }

        if (isset($_POST['rememberUser']) && $_POST['rememberUser'] == 'true') {
            addCookie("username", $this->username, time() + $_ENV['USER_SESSION_MAX_AGE_SECS']);
        }

        // Set a device ID cookie for use with VAT MTD
        // Fraud protection
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_X500, $this->username . '@' . $_SERVER['REMOTE_ADDR']);
        addCookie("uzerpdevice", $uuid->toString(), time() + 31556952);

        $available = SystemCompanySettings::Get('access_enabled');

        if ($available == 'NONE') {
            $flash->addError('The system is unavailable at present');
        } elseif ($authentication->doLogin() !== FALSE) {

            $user = DataObjectFactory::Factory('User');
            $user->load($this->username);

            if ($require_mfa === true && $user->mfa_enrolled !== 't') {
                $_SESSION['username'] = $this->username;
                $_SESSION['mfa_status'] = 'enrolling';
                // User needs a uuid, make sure they have one
                $user_id = $user->uuid;
                if (empty($user_id)) {
                    $uuid = Uuid::uuid4();
                    $result = $user->update($user->username, 'uuid', $uuid);
                }
                sendTo('index');
                exit();
            }

            if ($require_mfa === true && $user->mfa_enrolled === 't') {
                $_SESSION['username'] = $this->username;
                $_SESSION['mfa_status'] = 'validate';
                sendTo('index');
                exit();
            }

            if ($user->access_enabled == 't') {
                $this->completeLogin($user);
            } else {
                $flash->addError('Incorrect username or password');
                $this->logger->warning('FAILED LOGIN, account disabled', array('username' => $this->username));
                if (! $authentication->interactive()) {
                    $this->view->display($this->getTemplateName('logout'));
                    exit();
                }
            }
        } else {
            if (! $authentication->interactive()) {
                $flash->addError('Incorrect username or password');
                $this->logger->warning('FAILED LOGIN, either the username was not found in the database or system access is disabled', array('username' => $this->username));
                $this->view->display($this->getTemplateName('logout'));
                exit();
            }
            $flash->addError('Incorrect username or password');
            $this->logger->warning('FAILED LOGIN, Incorrect username or password', array('username' => $this->username));
        }
        $this->index();
        $this->_templateName = $this->getTemplateName('index');
    }

    /**
     * Complete the login process for an authenticated user
     *
     * @param User $user
     * @return void
     */
    private function completeLogin(User $user)
    {
        setLoggedIn();

        $_SESSION['username'] = $user->username;

        $user->update($user->username, 'last_login', date('Y-m-d H:i:s'));

        if (isset($_POST['ajax'])) {

            // If login due to timeout prior to ajax request
            // need to override ajax request to display full

            unset($_POST['ajax']);

            if (isset($_SERVER['HTTP_REFERER'])) {

                // If browser agent supports http_referer
                // use this address instead of ajax request
                $url = parse_url($_SERVER['HTTP_REFERER']);

                unset($_POST);

                $components = explode('&', $url['query']);

                foreach ($components as $component) {
                    list ($key, $value) = explode('=', $component);
                    $_POST[$key] = $value;
                }
            }
        }
        $controller = (! empty($_SESSION['post_login_page']['controller'])) ? $_SESSION['post_login_page']['controller'] : '';
        $module = (! empty($_SESSION['post_login_page']['module'])) ? $_SESSION['post_login_page']['module'] : '';

        if (! empty($_SESSION['post_login_page']['submodule'])) {
            $module = array(
                $module,
                $_SESSION['post_login_page']['submodule']
            );
        }

        $action = (! empty($_SESSION['post_login_page']['action']) && $_SESSION['post_login_page']['action'] != 'login') ? $_SESSION['post_login_page']['action'] : '';

        unset($_POST['controller']);
        unset($_POST['module']);
        unset($_POST['action']);
        unset($_POST['username']);
        unset($_POST['password']);
        unset($_POST['rememberUser']);
        unset($_POST['csrf_token']);
        unset($_POST['authcode']);
        unset($_SESSION['post_login_page']);

        // before we send away, lets cleanup the users tmp directory
        // deletes any file older than 'yesturday', just to keep the file size down

        clean_tmp_directory(DATA_USERS_ROOT . $_SESSION['username'] . '/TMP/');

        if (AUDIT || get_config('AUDIT_LOGIN')) {
            $this->logger->info('SUCCESSFUL LOGIN', array('username' => $_SESSION['username']));
        }

        session_regenerate_id(true);
        sendTo($controller, $action, $module, $_POST);
        exit();
    }

    public function password()
    {
        $this->view->set('layout', 'loginpage');
    }

    public function requestpassword()
    {
        $flash = Flash::Instance();

        $user = DataObjectFactory::Factory('User');
        $user->load($this->username);

        if ($user->isLoaded()) {

            $db = DB::Instance();
            $email = $user->email;

            if (empty($email) && ! is_null($user->person_id)) {

                $person = DataObjectFactory::Factory('Person');
                $person->load($user->person_id, true);

                if ($person->isLoaded()) {
                    $email = $person->email->contactmethod;
                }
            }

            if (! empty($email)) {

                $characters = array(
                    'a',
                    'b',
                    'c',
                    'd',
                    'e',
                    'f',
                    'g',
                    'h',
                    'i',
                    'j',
                    'k',
                    'l',
                    'm',
                    'n',
                    'o',
                    'p',
                    'q',
                    'r',
                    's',
                    't',
                    'u',
                    'v',
                    'w',
                    'x',
                    'y',
                    'z',
                    '0',
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    '7',
                    '8',
                    '9'
                );
                $passwd = '';

                for ($i = 0; $i <= mt_rand(6, 8); $i ++) {
                    $passwd .= $characters[mt_rand(0, count($characters) - 1)];
                }

                $user->update($user->username, 'password', password_hash($passwd, PASSWORD_DEFAULT));
                $flash->addMessage('Your new password will be emailed to you shortly.');

                $message = "You have modified your password for " . SERVER_ROOT . "\n" . "Your username is {$user->username}\n" . "Your password is {$passwd}\n" . "Thank you";

                $subject = 'New Password';
                $to = $email;

                if ($to != '') {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->setFrom(get_config('ADMIN_EMAIL'));
                        $mail->addAddress($to);
                        $mail->Subject = $subject;
                        $mail->Body = $message;
                        $mail->send();
                    } catch (Exception $e) {
                        $flash->addError("New password email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                    }
                }

                $this->index();
                $this->_templateName = $this->getTemplateName('index');
            } else {
                $flash->addError('Unable to retrieve your email address, please contact the system administrator');
                $this->index();
                $this->_templateName = $this->getTemplateName('index');
            }
        } else {
            $flash->addError('Invalid username specified');
            $this->index();
            $this->_templateName = $this->getTemplateName('index');
        }
    }

    function __call($func, $args)
    {
        $this->_templateName = $this->getTemplateName('index');
        return $this->index();
    }

    function logout()
    {

        if (AUDIT || get_config('AUDIT_LOGIN')) {
            $audit = Audit::Instance();
            $audit->write('logout', TRUE, (microtime(TRUE) - START_TIME));
            $audit->update();
        }

        session_destroy();
        session_unset();

        //remove session cookie
        addCookie(session_name(), '', 0);

        // don't show the login form for non-interactive logins
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');
        if (! $authentication->interactive()) {
            $this->view->display($this->getTemplateName('logout'));
            exit();
        }

        header("Location: /");
        exit();
    }
}
?>
