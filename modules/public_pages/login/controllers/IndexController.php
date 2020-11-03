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

    public function __construct($module=null,$view)
    {
        parent::__construct($module=null,$view);

        // Form based login
        if (isset($_POST['username'])) {
            $this->username = $_POST['username'];
        }

        // Or we might be using LDAP, etc.
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->username = $_SERVER['PHP_AUTH_USER'];
        }
    }

    public function index()
    {

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
        $this->view->set('layout', 'loginpage');

        // don't show login form for non-interactive logins
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');
        if (! $authentication->interactive()) {
            $this->login();
            exit();
        }
    }

    public function login()
    {
        $injector = $this->_injector;
        $authentication = $injector->Instantiate('LoginHandler');

        $logger = uzLogger::Instance();
        // set log 'channel' for authentication error/audit messages
        $logger = $logger->withName('uzerp_authentication');

        $flash = Flash::Instance();

        if ($authentication->interactive()) {
            if (! isset($this->username) || ! isset($_POST['password'])) {
                $flash->addError("Please enter a username and password");
                sendTo();
            }
        }

        if (isset($_POST['rememberUser']) && $_POST['rememberUser'] == 'true') {
            $params = session_get_cookie_params();
            setcookie("username", $this->username, time() + 3600, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        }

        // Set a device ID cookie for use with VAT MTD
        // Fraud protection
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_X500, $this->username . '@' . $_SERVER['REMOTE_ADDR']);
        setcookie("uzerpdevice", $uuid->toString(), time() + 31556952, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));

        $available = SystemCompanySettings::Get('access_enabled');

        if ($available == 'NONE') {
            $flash->addError('The system is unavailable at present');
        } elseif ($authentication->doLogin() !== FALSE) {

            $user = DataObjectFactory::Factory('User');
            $user->load($this->username);

            if ($user->access_enabled == 't') {

                setLoggedIn();

                $_SESSION['username'] = $this->username;

                $user->update($_SESSION['username'], 'last_login', date('Y-m-d H:i:s'));

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

                $controller = (! empty($_POST['controller'])) ? $_POST['controller'] : '';
                $module = (! empty($_POST['module'])) ? $_POST['module'] : '';

                if (! empty($_POST['submodule'])) {
                    $module = array(
                        $module,
                        $_POST['submodule']
                    );
                }

                $action = (! empty($_POST['action']) && $_POST['action'] != 'login') ? $_POST['action'] : '';

                unset($_POST['controller']);
                unset($_POST['module']);
                unset($_POST['action']);
                unset($_POST['username']);
                unset($_POST['password']);
                unset($_POST['rememberUser']);
                unset($_POST['csrf_token']);

                // before we send away, lets cleanup the users tmp directory
                // deletes any file older than 'yesturday', just to keep the file size down

                clean_tmp_directory(DATA_USERS_ROOT . $_SESSION['username'] . '/TMP/');

                if (AUDIT || get_config('AUDIT_LOGIN')) {
                    $logger->info('SUCCESSFUL LOGIN', array('username' => $this->username));
                }

                sendTo($controller, $action, $module, $_POST);
            } else {
                $flash->addError('Incorrect username or password');
                $logger->warning('FAILED LOGIN, account disabled', array('username' => $this->username));
                if (! $authentication->interactive()) {
                    $this->view->display($this->getTemplateName('logout'));
                    exit();
                }
            }
        } else {
            if (! $authentication->interactive()) {
                $flash->addError('Incorrect username or password');
                $logger->warning('FAILED LOGIN, either the username was not found in the database or system access is disabled', array('username' => $this->username));
                $this->view->display($this->getTemplateName('logout'));
                exit();
            }
            $flash->addError('Incorrect username or password');
            $logger->warning('FAILED LOGIN, Incorrect username or password', array('username' => $this->username));
        }
        $this->index();
        $this->_templateName = $this->getTemplateName('index');
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
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));

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
