<?php

/**
 * system uzERP system loader
 *
 * @version $Revision: 1.126 $
 * @package uzerp
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

class system
{

    protected $version = '$Revision: 1.126 $';

    const DAY_START_HOURS = '9';

    const DAY_START_MINUTES = '0';

    const DAY_LENGTH = '8';

    /*
     * The AccessObject object for this user request
     */
    public $access;

    /*
     * The requested action
     */
    public $action;

    /*
     * The requested controller
     */
    public $controller;

    /*
     * The injector object for this request
     */
    public $injector;

    /*
     * Array of $_GET parameters where parameter name contains module
     */
    public $modules = array();

    /*
     * The code module (as distinct from the menu module)
     */
    public $module;

    /*
     * The permission id
     */
    public $pid;

    /*
     * The router object for this request
     */
    public $router;

    /*
     * smarty template locations for the current module
     */
    public $templates = array();

    /*
     * The view object
     */
    public $view;

    public $flash = array();

    protected $ajax = FALSE;

    protected $audit;

    protected $available;

    protected $debug = FALSE;

    protected $jsfiles = array();

    protected $cssfiles = array();

    protected $json = FALSE;

    protected $login_required = TRUE;

    protected $user;

    // http request object;
    protected $request;

    /*
     * The permissions context for this request
     * key = permission, value = data for this permission id
     */
    private $module_context;

    private function __construct()
    {

        // set path constants
        $this->setPathBase();

        // set the http request object
        $this->request = Request::createFromGlobals();
    }

    public static function &Instance()
    {
        static $system;

        if ($system == null) {
            $system = new system();
        }

        return $system;
    }

    function checkPermission()
    {
        $controllername = get_class($this->controller);

        $continue = FALSE;

        if (! $this->access->hasPermission($this->modules, $controllername, $this->action, $this->pid) && ! $continue) {
            $flash = Flash::Instance();
            // $flash->clear();
            $this->access->save();
            $flash->addError("You do not have access to the requested action.");
            $flash->save();
            $count = count($this->modules);

            if (strtolower($this->action) == 'index') {

                if ($controllername !== 'IndexController') {
                    sendTo('', 'index', $this->modules);
                } else {

                    if ($count <= 1) {
                        sendTo('', 'index', 'dashboard');
                    }

                    // The x = 1; $x < $count is not a coding error is is to get all modulues but the last one
                    $mod = $this->modules;
                    array_pop($mod);
                    sendTo('', 'index', $mod);
                }
            } else {
                sendTo(str_replace('controller', '', strtolower($controllername)), 'index', $this->modules);
            }
        }
    }

    public function check_system()
    {
        static $checked;

        // we only want to call checked system
        if ($checked !== NULL) {
            return $checked;
        }

        // OS installed packages required for PDF previews on output dialog
        $check_packages = array(
            'convert',
            'pdfinfo'
        );

        foreach ($check_packages as $package) {

            // set a few vars
            $output = array();
            $location = '';

            // check if the package has a location
            // we use whereis instead parsing the response from the vanila command so if
            // we (for some strange reason) searched for the package 'shutdown -h 0' it
            // wouldn't kill the server... belt and braces

            // we also need to remove any characters which could cause our system harm
            // a valid unix command 'fop && rm -fR *' would completely wipe the os
            // removing spaces isn't good enough on it's own either

            $package = current(preg_replace("/[^A-Za-z0-9_-]+/i", "", explode(' ', $package)));

            // execute the whereis command, catch the result
            exec("whereis " . $package, $output);

            // rip the response apart and prepare (trim) the location part
            $array = explode(":", $output[0]);
            $location = trim(end($array));

            // if the location is empty, the package doesn't exist
            if (empty($location)) {
                define('HAS_' . strtoupper($package), FALSE);
            } else {
                define('HAS_' . strtoupper($package), TRUE);
            }
        }

        // an array of directories that must exist
        $required_directories = array(
            CACHE_ROOT
        );

        foreach ($required_directories as $directory) {

            // if the directory doesn't exist...
            if (! is_dir($directory)) {

                // attempt to create it
                if (! mkdir($directory, 0777)) {
                    trigger_error("Cannot create cache directory (" . $directory . ")", E_USER_ERROR);
                }
            }
        }

        // set to true to avoid running more than once per request
        $checked = true;
        return $checked;
    }

    /**
     * A single function to load (in order) all the essential foundations to the uzERP framework
     *
     * @param bool $_disable_cache
     */
    public function load_essential($_disable_cache = FALSE)
    {
        static $loaded;

        // we only want to call this function once
        if ($loaded !== NULL) {
            return $loaded;
        }

        // lib.php includes some very important helper functions
        // include it now, before it's too late!

        require LIB_ROOT . 'lib.php';
        require LIB_ROOT . 'classes/utils/Cache.php';
        require LIB_ROOT . 'classes/utils/Config.php';

        $this->setPathNames();

        $this->set_autoloader($_disable_cache);

        // **************
        // PRELOAD CACHE

        // we want to preload the cache so we can be prepared for disabled caching
        // hitting this now will expose (bool) MEMCACHED_ENABLED for the system
        // as we've yet to get to autoload we must include the path ourselves

        Cache::Instance();

        // set the path names before checking the system
        // otherwise we won't have access to setting functions

        // set the loaded flag to true
        $loaded = TRUE;
    }


    /*
     * Main control function to set up environment, set route (module, controller, action) and call controller action
     */
    public function display()
    {

        $this->load_essential();

        debug('system::display session data:' . print_r($_SESSION, TRUE));

        $this->user = FALSE;

        if (isLoggedIn()) {
            // Sets the global constants EGS_USERNAME and EGS_COMPANY_ID
            setupLoggedInUser();

            $this->user = getCurrentUser();

            if(isset($_ENV['UZERP_MANAGE_USER_SESSIONS']) && strtolower($_ENV['UZERP_MANAGE_USER_SESSIONS']) === 'on') {
                if(($_SERVER['REQUEST_TIME'] > $_SESSION['last_active'] + $_ENV['USER_ACTIVITY_TIMEOUT_SECS'])
                || ($_SERVER['REQUEST_TIME'] > $_SESSION['started'] + $_ENV['USER_SESSION_MAX_AGE_SECS'])){
                    session_destroy();
                    session_unset();
                    //remove session cookie
                    addCookie(session_name(), '', 0);
                    sendTo(
                        $_GET['controller'],
                        $_GET['action'],
                        $_GET['module']
                    );
                }
            }
            $_SESSION['last_active'] = $_SERVER['REQUEST_TIME'];
            
            $this->access = AccessObject::Instance($_SESSION['username']);
        } else {

            define('EGS_COMPANY_ID', - 1);
            define('EGS_USERNAME', $_SESSION['username']);

            $this->access = AccessObject::Instance();
        }

        $this->setView();

        $this->view->set("accessTree", $this->access->tree);
        $this->view->set('access', $this->access);

        $this->setController();

        $this->setTemplates();

        $this->setAction();

        $cookie_params = session_get_cookie_params();
        $csrf = new \Riimu\Kit\CSRF\CSRFHandler();
        // Use CSRF cookie storage and set the secure flag
        // to the current value of session.cookie_secure in php.ini
        $storage = new \Riimu\Kit\CSRF\Storage\CookieStorage(
            $name = 'csrf_token',
            $expire = 0,
            $path = $cookie_params['path'],
            $domain = $cookie_params['domain'],
            $secure = $cookie_params['secure']
        );
        $csrf->setStorage($storage);

        // check that the csrf token is valid
        if (!$this->csrfValid()) {
            sendBack();
        }
        $csrf_token = $csrf->getToken();
        // make csrf token available to smarty templates
        $this->view->set('csrf_token', $csrf_token);


        if (isLoggedIn()) {
            $this->checkPermission();
        }

        // Find css/js
        $css = glob('dist/css/main*.css');

        if($this->module == 'login') {
            $logincss = glob('dist/css/login-*.css');
            $this->view->set('login_css', $logincss[0]);
        }

        $jsdir = self::findModulePath(PUBLIC_MODULES, $this->modules['module'], FALSE);
        $jsdir .= DIRECTORY_SEPARATOR . 'resources/js';
        if (!strpos($jsdir, 'user/modules')) {
            $jsdir = str_replace(FILE_ROOT . 'modules/public_pages' , 'dist/js/modules', $jsdir);
        } else {
            // Serve user module js from module directory
            $jsdir = str_replace(FILE_ROOT , '', $jsdir);
        }
        $modulejs = glob("{$jsdir}/*.js");

        $js = glob('dist/js/scripts*.js');

        // output standard arrays to smarty
        $this->view->set('current_user', $this->user);
        $this->view->set('main_css', $css[0]);
        if (file_exists('user') && is_dir('user') && count(glob('user/theme*.css')) >= 1) {
            $user_css = glob('user/theme*.css');
            $this->view->set('user_css', $user_css[0]);
        }
        $this->view->set('main_js', $js[0]);
        $this->view->set('module_js', $modulejs[0]);

        $action = $this->action;
        $controller = $this->controller;

        if (defined('EGS_COMPANY_ID') && EGS_COMPANY_ID !== 'null' && EGS_COMPANY_ID > 0) {

            $sc = DataObjectFactory::Factory('Systemcompany');
            $sc->load(EGS_COMPANY_ID);

            if ($sc->isLoaded()) {

                define('SYSTEM_COMPANY', $sc->company);
                define('COMPANY_ID', $sc->company_id);

                $this->available = ($sc->access_enabled == 'NONE') ? FALSE : TRUE;
                $this->audit = ($sc->audit_enabled == 't' ? TRUE : FALSE);
                $this->debug = ($sc->debug_enabled == 't' ? TRUE : FALSE);
                $this->view->set('info_message', $sc->info_message);
                $this->view->set('systemcompany', $sc);
            }
        }

        $policy = DataObjectFactory::Factory('SystemObjectPolicy');

        if ($policy->getCount() > 0) {
            define('SYSTEM_POLICIES_ENABLED', TRUE);
        } else {
            define('SYSTEM_POLICIES_ENABLED', FALSE);
        }

        if (! defined('SYSTEM_COMPANY')) {
            define('SYSTEM_COMPANY', '');
        }

        if (! defined('COMPANY_ID')) {
            define('COMPANY_ID', '');
        }

        // Set auditing/debugging for logged in user
        if ($this->user) {
            $this->audit = $this->audit ? $this->audit : ($this->user->audit_enabled == 't' ? TRUE : FALSE);
            $this->debug = $this->debug ? $this->debug : ($this->user->debug_enabled == 't' ? TRUE : FALSE);
            $this->available = $this->available ? ($this->user->access_enabled == 't' ? TRUE : FALSE) : $this->available;
        }

        if (! $this->available && isLoggedIn()) {
            $_SESSION['loggedin'] = FALSE;
            $_SESSION['username'] = null;
            $flash = Flash::Instance();
            $flash->addError('The system is unavailable at present');
            $flash->save();
            sendto('');
        }

        define('AUDIT', $this->audit);
        define('DEBUG', $this->debug);

        $db = DB::Instance();
        $db->debug(DEBUG);

        if (! defined('EGS_CURRENCY')) {
            define('EGS_CURRENCY', 'GBP');
        }

        if (class_exists('Currency')) {

            $currency = DataObjectFactory::Factory('Currency');
            $currency->loadBy('currency', EGS_CURRENCY);

            if ($currency) {
                define('EGS_CURRENCY_SYMBOL', mb_convert_encoding($currency->symbol, 'UTF-8'));
            }
        }

        if (! defined('EGS_CURRENCY_SYMBOL')) {
            define('EGS_CURRENCY_SYMBOL', mb_convert_encoding('£', 'UTF-8'));
        }

        /**
         * Get job messages
         */
        $messages = uzJobMessages::Factory(EGS_USERNAME, EGS_COMPANY_ID);
        $messages->displayJobMessages();

        /**
         * *BEGIN CACHE CHECK*****
         */
        if (! defined('EGS_COMPANY_ID')) {
            define('EGS_COMPANY_ID', '');
        }

        if (DEBUG) {
            $this->writeDebug();
        }

        $cache_key = md5($_SERVER['REQUEST_URI'] . EGS_COMPANY_ID . EGS_USERNAME);

        if (TRUE || ! $smarty->isCached('index.tpl', $cache_key)) {

            $flash = Flash::Instance();
            $config = Config::Instance();

            // output all the variables to smarty
            // this replaces $smarty.const.setting_name

            $this->view->assign('config', $config->get_all());

            setRefererPage();

            debug('system::display Calling function ' . get_class($controller) . '::' . $action);
            // echo 'system::display (1),'.microtime(TRUE).'<br>';

            $controller->$action();

            // echo 'system::display (2),'.microtime(TRUE).'<br>';

            $flash->save();

            // Save any flash messages for audit purposes
            $this->flash['errors'] = $flash->getMessages('errors');
            $this->flash['warnings'] = $flash->getMessages('warnings');
            $this->flash['messages'] = $flash->getMessages('messages');

            if (isLoggedIn()) {
                $this->access->save();
            }

            // assign stuff to smarty
            $controller->assignModels();

            // this code fires $controller->index() if (perhaps) getPrintActions doesn't exist,
            // thus overwriting the sidebar. Only fire if subclass of printController
            if (is_subclass_of($controller, 'printController') && $action != 'printDialog') {
                $this->view->assign('printaction', $controller->getPrintActions());
            }

            $controllername = str_replace('Controller', '', get_class($controller));
            $this->pid = $this->access->getPermission($this->modules, $controllername, $action);
            $self = array();

            if (! empty($this->pid)) {
                $self['pid'] = $this->pid;
            }

            $self['modules'] = $this->modules;

            // $self['controller']=$controllername;
            // $self['action']=$action;

            $qstring = $_GET;

            foreach ($qstring as $qname => $qvalue) {

                if (! in_array($qname, array(
                    'orderby',
                    'page'
                ))) {
                    $self[$qname] = $qvalue;
                }
            }

            $this->view->assign('self', $self);

            if (isset($this->user)) {
                $this->view->assign('current_user', $this->user);
            }

            // Session timed out on input form so save the form data while the user logs back in
            // See system::setController for where the form data is read after logging back in

            if ($this->modules['module'] == 'login' && ! empty($_POST)) {
                $_SESSION['data'] = $_POST;
            }

            $echo = $controller->view->get('echo');

            if (($this->ajax || $this->json) && $echo !== FALSE) {
                echo $controller->view->get('echo');
                exit();
            } elseif ($this->modules['module'] == 'login') {

                $current = getParamsArray($_SERVER['QUERY_STRING']);
                $referer['modules'] = $current['modules'];
                $referer['controller'] = 'Index';
                $referer['action'] = 'index';

                unset($referer['other']);
                $_SESSION['referer'][setParamsString($current)] = setParamsString($referer);
            } elseif (! isset($_GET['ajax'])) {

                $referer = '';

                if (! empty($_POST)) {
                    // This is a save form so set the referer to be the referer's referer!
                    $referer = (isset($_SESSION['refererPage'])) ? $_SESSION['refererPage'] : '';
                }

                setReferer($referer);

                $current = getParamsArray($_SERVER['QUERY_STRING']);
                $flash = Flash::Instance();

                $current += array(
                    'messages' => $flash->getMessages('messages'),
                    'warnings' => $flash->getMessages('warnings'),
                    'errors' => $flash->getMessages('errors')
                );

                $_SESSION['submit_token']['current'] = $current;
            }
        }

        // Set the user's 'home' link
        if (!isset($_SESSION['user_home']) 
            && isLoggedIn())
        {
            
            $prefs = UserPreferences::Instance(EGS_USERNAME);
            $default_page = $prefs->getPreferenceValue('default_page', 'shared');
            if ($default_page == "") {
                $home = link_to(['module' => 'dashboard'], false, false);
            } else {
                $home = link_to(['module' => explode(',', $default_page)[1]], false, false);
            }
            $_SESSION['user_home'] = $home;
        }
        $home = $_SESSION['user_home'];
        $this->view->set('user_home', $home);

        showtime('pre-display');
        $this->view->display('index_page.tpl', $cache_key);
        showtime('post-display');
    }

    /*
     * Gets the fields for the supplied tablename
     */
    public static function getFields($tablename, $cache_results = TRUE)
    {
        $cached_fields = FALSE;
        $cache_id = array(
            'table_fields',
            $tablename
        );

        // we might not want to hit the cache
        if (MEMCACHED_ENABLED && $cache_results === TRUE) {

            // instanciate the cache and get the cache value
            $cache = Cache::Instance();
            $cached_fields = $cache->get($cache_id);
        }

        // go and fetch the data and populate the cache
        if ($cached_fields === FALSE) {

            $fields = Fields::getFields_static($tablename);
            $return = array();

            if (is_array($fields)) {

                foreach ($fields as $field) {
                    $return[$field->name] = clone $field;
                }
            } else {
                $return = FALSE;
            }

            if (MEMCACHED_ENABLED && $return !== FALSE && $cache_results === TRUE) {
                $cache->add($cache_id, $return);
            }
        } else {
            $return = $cached_fields;
        }

        return $return;
    }

    public static function scanDirectories($rootDir, $module, $require = FALSE)
    {
        if (! empty($module)) {
            $start = self::findModulePath($rootDir, $module);
        }

        if (empty($start)) {
            $start = $rootDir;
        }

        $allData = array();
        $allData = array_merge($allData, self::getDirectories($start, 'down', '', $require));

        if ($rootDir != $start) {
            $allData = array_merge($allData, self::getDirectories($start, 'up', $rootDir, $require));
        }

        return $allData;
    }

    public static function getDirectories($start, $direction, $stop = '', $require = FALSE)
    {

        // echo 'system::getDirectories - '.$direction.' '.$start.'<br>';
        $allData = array();
        $dirContent = scandir($start);

        foreach ($dirContent as $key => $content) {

            if ($content != '.' && $content != '..' && $content != 'CVS') {

                if (substr($start, - 1) == DIRECTORY_SEPARATOR) {
                    $path = $start . $content;
                } else {
                    $path = $start . DIRECTORY_SEPARATOR . $content;
                }

                if (substr($content, - 4) == '.php' && is_file($path) && is_readable($path)) {

                    if ($require) {
                        require $path;
                    } else {
                        $allData[strtolower(substr_replace($content, '', strrpos($content, '.')))] = $path;
                    }
                } else {

                    if (is_dir($path) && is_readable($path)) {

                        // $allData[]=$path.'/';
                        // echo 'system::getDirectories - '.$direction.' '.$start.' adding path '.$path.'<br>';

                        // recursive callback to open new directory
                        if ($direction == 'down') {
                            $allData = array_merge($allData, self::getDirectories($path, $direction, $stop, $require));
                        }
                    }
                }
            }
        }

        if (! empty($stop)) {

            if (substr($stop, - 1) == DIRECTORY_SEPARATOR) {
                $stop = substr($stop, 0, strrpos($stop, DIRECTORY_SEPARATOR));
            }

            if ($direction == 'up') {

                $path = substr($start, 0, strrpos($start, DIRECTORY_SEPARATOR));

                if ($path != $stop) {
                    $allData = array_merge($allData, self::getDirectories($path, $direction, $stop, $require));
                }
            }
        }

        return $allData;
    }

    public static function findModulePath($directory, $module = '')
    {
        if (! empty($module)) {

            $cache_id = array(
                'module_dir_path',
                strtolower($module)
            );

            $cache = Cache::Instance();
            $module_path = $cache->get($cache_id);

            // go and fetch the data and populate the cache
            if ($module_path === FALSE) {

                $moduleobject = DataObjectFactory::Factory('ModuleObject');
                $moduleobject->loadBy('name', $module);

                if ($moduleobject->isLoaded()) {

                    $cache->add($cache_id, FILE_ROOT . $moduleobject->location);

                    return FILE_ROOT . $moduleobject->location;
                } else {

                    $dirContent = scandir(realpath($directory));

                    if (is_array($dirContent)) {

                        foreach ($dirContent as $content) {

                            if (substr($content, 0, 1) != ".") {

                                if (substr($directory, - 1) == DIRECTORY_SEPARATOR) {
                                    $path = $directory . $content;
                                } else {
                                    $path = $directory . DIRECTORY_SEPARATOR . $content;
                                }

                                if ($content == $module) {
                                    return $path;
                                } elseif (file_exists($path) && is_dir($path) && is_readable($path)) {

                                    $path = self::findModulePath($path, $module);

                                    if (! empty($path)) {
                                        return $path;
                                    }
                                } else {
                                    $path = '';
                                }
                            }
                        }
                    }
                }
            } else {
                return $module_path;
            }
        }

        return '';
    }

    public function setAction()
    {
        showtime("---");
        // action

        if (empty($this->action)) {

            if ($this->router->Dispatch('action') !== null) {
                $this->view->assign('action', strtolower($this->router->Dispatch('action')));
            }

            $this->action = ActionFactory::Factory($this->controller);

            if ($this->modules['module'] == 'login') {

                $actions = array(
                    'index',
                    'password',
                    'requestpassword',
                    'login',
                    'logout',
                    'mfaenroll',
                    'mfavalidate'
                );

                if (! in_array(strtolower($this->action), $actions)) {
                    $this->action = 'index';
                }
            }
        }

        $this->controller->setTemplateName($this->action);
    }

    public function setController()
    {
        showtime('pre-controller-new');

        $autoloader = &AutoLoader::Instance();

        // controller
        $controller = ControllerFactory::Factory($this->login_required, $autoloader->paths);

        $this->controller = new $controller($this->module, $this->view);
        $this->controller->setInjector($this);
        $this->controller->setData($this->router->Dispatch());

        // If session timed out on input form, get the saved form data
        // after the user has logged back in
        // see system::display for saving of form data

        if (isset($_SESSION['data']) && $this->modules['module'] != 'login') {
            $this->controller->setData($_SESSION['data']);
            unset($_SESSION['data']);
        }

        $this->controller->setData($_GET);
        $this->controller->setData($_POST);
    }


    /**
     * Return the http request object
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest() {
        return $this->request;
    }

    function instantiate($interface, $type = 'SY')
    {
        if (! defined('EGS_COMPANY_ID')) {
            $usercompanyid = - 1;
        } else {
            $usercompanyid = EGS_COMPANY_ID;
        }

        if (! isset($_SESSION['injectorclass'][$usercompanyid][$interface])) {

            $cc = new ConstraintChain();
            $cc->add(new Constraint('name', '=', $interface));
            $cc->add(new Constraint('category', '=', $type));
            $cc1 = new ConstraintChain();
            $cc1->add(new Constraint('usercompanyid', '=', $usercompanyid));

            if ($usercompanyid > 0) {
                $cc1->add(new Constraint('usercompanyid', '=', - 1), 'OR');
            }

            $cc2 = new ConstraintChain();
            $cc2->add($cc1);
            $cc2->add($cc);
            $query = "select * from injector_classes where " . $cc2->__toString() . " order by usercompanyid";
            $db = &DB::Instance();
            $result = $db->GetRow($query);

            if (empty($result)) {
                return FALSE;
            }

            $_SESSION['injectorclass'][$usercompanyid][$interface] = $result['class_name'];
        }

        $class_name = $_SESSION['injectorclass'][$usercompanyid][$interface];
        $dependencies = self::instantiateDependencies(new ReflectionClass($class_name));

        return call_user_func_array(array(
            new ReflectionClass($class_name),
            'newInstance'
        ), $dependencies);
    }

    private function instantiateDependencies($reflection, $supplied = '')
    {
        $dependencies = array();

        if ($constructor = $reflection->getConstructor()) {

            foreach ($constructor->getParameters() as $parameter) {

                if ($interface = $parameter->getClass()) {
                    $dependencies[] = self::instantiate($interface->getName());
                } elseif ($dependency = array_shift($supplied)) {
                    $dependencies[] = $dependency;
                }
            }
        }

        return $dependencies;
    }

    public function get_lib_files($_disable_cache = FALSE)
    {
        $cache_id = array(
            'resources',
            'lib_root'
        );

        $cache = Cache::Instance();

        if ($_disable_cache) {
            $files = FALSE;
        } else {
            $files = $cache->get($cache_id);
        }

        if ($files === FALSE) {

            $files = self::scanDirectories(LIB_ROOT, '', FALSE);

            // attempt to set the cache value
            $cache->add($cache_id, $files);
        }

        return $files;
    }

    public function set_autoloader($_disable_cache = FALSE)
    {

        // include autoloader
        require LIB_ROOT . 'classes' . DIRECTORY_SEPARATOR . 'AutoLoader.php';

        $autoloader_paths = $this->get_lib_files($_disable_cache);

        $autoloader = &AutoLoader::Instance();
        $autoloader->addPath($autoloader_paths);
        if (file_exists(SITE_CLASSES)) {
            $autoloader->addPath(self::scanDirectories(SITE_CLASSES, '', FALSE));

        }
        $moduleobject = DataObjectFactory::Factory('ModuleObject');
        $moduleobject->loadBy('name', 'common');

        $scan_dirs = array();

        if ($moduleobject->isLoaded()) {
            $scan_dirs = $moduleobject->getComponentLocations();
        }

        if (empty($scan_dirs)) {
            $scan_dirs = self::scanDirectories(COMMON_MODULES, '', FALSE);
        }

        $autoloader->addPath($scan_dirs);
    }

    // public function set_plugins()
    public function setPathNames()
    {

        // Need way of registering plugins
        require PRINT_ROOT . 'PrintIPP.php';
        require PRINT_ROOT . 'ExtendedPrintIPP.php';
        require PRINT_ROOT . 'CupsPrintIPP.php';

        $this->injector = $this;
    }

    public function setPathBase()
    {

        // we only want to set the paths once...
        // if they've already been set, return
        if (defined('PATHS_SET')) {
            return;
        }

        // mark the paths as have been set
        define('PATHS_SET', TRUE);

        // only set server root if http post item exists
        // if it doesn't, chances are the request is from the PHP CLI

        if (isset($_SERVER['HTTP_HOST'])) {
            define('SERVER_SECURE', (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'));
            define('SERVER_PROTOCOL', (SERVER_SECURE ? 'https://' : 'http://'));
            define('SERVER_ROOT', SERVER_PROTOCOL . $_SERVER['HTTP_HOST']);
        }

        if (substr($_SERVER['DOCUMENT_ROOT'], - 1) != DIRECTORY_SEPARATOR) {
            define('FILE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
        } else {
            define('FILE_ROOT', $_SERVER['DOCUMENT_ROOT']);
        }

        define('PLUGINS_ROOT', FILE_ROOT . 'plugins' . DIRECTORY_SEPARATOR);
        define('LIB_ROOT', FILE_ROOT . 'lib' . DIRECTORY_SEPARATOR);
        define('DATA_ROOT', FILE_ROOT . 'data' . DIRECTORY_SEPARATOR);
        define('CACHE_ROOT', DATA_ROOT . 'cache' . DIRECTORY_SEPARATOR);
        define('DATA_URL', 'data' . DIRECTORY_SEPARATOR);
        define('DATA_USERS_ROOT', DATA_ROOT . 'users' . DIRECTORY_SEPARATOR);
        define('DATA_USERS_URL', DATA_URL . 'users' . DIRECTORY_SEPARATOR);
        define('COMMON_MODULES', FILE_ROOT . 'modules' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR);
        define('PUBLIC_MODULES', FILE_ROOT . 'modules' . DIRECTORY_SEPARATOR . 'public_pages' . DIRECTORY_SEPARATOR);
        define('USER_MODULES', FILE_ROOT . 'user' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR);
        define('TEMPLATES_NAME', 'templates' . DIRECTORY_SEPARATOR);
        define('CONTROLLERS_NAME', 'controllers' . DIRECTORY_SEPARATOR);
        define('EGLETS_NAME', 'eglets' . DIRECTORY_SEPARATOR);
        define('MODELS_NAME', 'models' . DIRECTORY_SEPARATOR);
        define('REPORTS_NAME', 'reports' . DIRECTORY_SEPARATOR);
        define('STANDARD_TPL_ROOT', COMMON_MODULES . TEMPLATES_NAME);
        define('STANDARD_EGLET_TPL_ROOT', COMMON_MODULES . TEMPLATES_NAME . EGLETS_NAME);
        define('SHARED_TPL_ROOT', PUBLIC_MODULES . 'shared' . DIRECTORY_SEPARATOR . TEMPLATES_NAME);
        define('BASE_TPL_ROOT', COMMON_MODULES . TEMPLATES_NAME . 'base' . DIRECTORY_SEPARATOR);
        define('USER_ROOT', FILE_ROOT);
        define('PRINT_ROOT', PLUGINS_ROOT . 'printIPP' . DIRECTORY_SEPARATOR);
        define('PDF_ROOT', PLUGINS_ROOT . 'ezpdf' . DIRECTORY_SEPARATOR);
        define('SMARTY_CUSTOM_PLUGINS', PLUGINS_ROOT . 'smarty' . DIRECTORY_SEPARATOR . 'custom_plugins' . DIRECTORY_SEPARATOR);
        define('SITE_CLASSES', FILE_ROOT . 'user' . DIRECTORY_SEPARATOR . 'classes');
    }

    /*
     * Get Context - return the group/module permissions for the current pid
     */
    public function getContext()
    {
        // echo 'System::getContext<pre>'.print_r($this->module_context, true).'</pre><br>';
        return $this->module_context;
    }

    public function setContext()
    {
        // Sets the current permissions context
        // and gets the code module for the permission id
        $autoloader = &AutoLoader::Instance();

        // ATTN: there should be a specific function for this, make it easier to centrally cache
        $scan_dirs = array();

        $moduleobject = DataObjectFactory::Factory('ModuleObject');
        $moduleobject->loadBy('name', 'shared');

        if ($moduleobject->isLoaded()) {
            $scan_dirs = $moduleobject->getComponentLocations();
        }

        if (empty($scan_dirs)) {
            $scan_dirs = self::scanDirectories(PUBLIC_MODULES . 'shared' . DIRECTORY_SEPARATOR, '', FALSE);
        }

        $autoloader->addPath($scan_dirs);

        // modules contains the url parameters (logical modules)
        // which may differ from the module id in the permissions
        if (count($this->modules) > 0) {
            $this->module = end($this->modules);
        } else {
            $this->module = '';
        }

        $scan_dirs = array();

        $context_module = $this->module;

        // TODO: pid should be set here; problem is that this is called from setView
        // which is called before setController
        // need to look more closely at the process path here; does this have to
        // be called from setView?
        if (! is_null($this->pid)) {
            $context = $this->access->permissions[$this->pid];

            if (! empty($context['permission'])) {
                $this->module_context[$context['permission']] = $context;
            }

            if (! empty($context['module_id'])) {
                $context_module = $context['module_id'];
            }

            while (! empty($context['parent_id'])) {

                $context = $this->access->permissions[$context['parent_id']];

                $this->module_context[$context['permission']] = $context;
            }
        }

        $moduleobject = DataObjectFactory::Factory('ModuleObject');

        if (! empty($context_module)) {
            $moduleobject->loadBy('name', $this->module);
        }

        if ($moduleobject->isLoaded()) {
            $this->module = $moduleobject->name;
            $scan_dirs = $moduleobject->getComponentLocations();
        }

        if (empty($scan_dirs)) {
            $scan_dirs = self::scanDirectories(PUBLIC_MODULES, $context_module, FALSE);
        }

        $autoloader->addPath($scan_dirs);
    }

    public function setTemplates()
    {

        // Load searchable template directories
        // $this->templates[]=TEMPLATE_DIR_ROOT.$this->modules[0].TEMPLATE_DIR_NAME;
        // Load searchable template directories for the specified module
        $module = $this->module;
        $controllername = strtolower(str_replace('Controller', '', get_class($this->controller)));
        $module_path = self::findModulePath(PUBLIC_MODULES, $module, FALSE);

        // Module/controller template overrides.
        $override_dir = FILE_ROOT . 'user' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $controllername;
        if (file_exists($override_dir)) {
            // Add the override directory to the array of searchable template directories, if it exists
            $this->templates['moduleoverrides'] = $override_dir;
        }

        if (! empty($module_path)) {

            $template_path = $module_path . DIRECTORY_SEPARATOR . TEMPLATES_NAME . $controllername . DIRECTORY_SEPARATOR;

            if (is_dir($template_path)) {
                $this->templates[$module] = $template_path;
            }
        }

        if (count($this->templates) === 0) {
            $template_path = $module_path . DIRECTORY_SEPARATOR . TEMPLATES_NAME;
            $this->templates[$module] = $template_path;
        }

        $this->templates['shared'] = SHARED_TPL_ROOT;
        $this->templates['standard'] = STANDARD_TPL_ROOT;
        $this->templates['elements'] = STANDARD_TPL_ROOT . 'elements' . DIRECTORY_SEPARATOR;
        $this->templates['uzlets'] = STANDARD_TPL_ROOT . EGLETS_NAME;
        $this->templates['smarty'] = STANDARD_TPL_ROOT . 'smarty' . DIRECTORY_SEPARATOR;

        $this->view->setTemplateDir($this->templates);

        debug('system::setTemplates ' . print_r($this->templates, TRUE));
    }

    public function setView()
    {
        $this->router = RouteParser::Instance();

        $this->router->ParseRoute(isset($_GET['url']) ? $_GET['url'] : '');

        if (! isset($this->login_required)) {
            $this->login_required = FALSE;
        }

        $this->modules = ModuleFactory::Factory(null, $this->login_required);

        $this->pid = $this->router->Dispatch('pid');

        $this->view = new View();

        $this->setContext();

        $this->view->set('help_link', $this->access->setHelpContext($this->pid));

        $this->view->set('modules', $this->modules);

        if (count($this->modules) > 0) {

            $modtype = 'module';

            foreach ($this->modules as $module) {
                $this->view->set($modtype, strtolower($module));
                $modtype = 'sub' . $modtype;
            }
        }

        if (isset($_GET['ajax'])) {
            $this->ajax = TRUE;
        }

        if (isset($_GET['json'])) {
            $this->json = TRUE;
        }

        // echo 'system::setView modules=<pre>'.print_r($this->modules,TRUE).'</pre><br>';
    }

    private function writeDebug()
    {
        $db = DB::Instance();
        $db->debug = FALSE;
        $audit = Debug::Instance();
        $autoloader = &AutoLoader::Instance();

        $audit->write('system:autoloader_paths ' . print_r($autoloader->paths, TRUE));
        $audit->write('system:template_paths ' . print_r($this->templates, TRUE));

        foreach ($this->modules as $module) {
            $audit->write('system:url_info Module : ' . $module);
        }

        $audit->write('system:url_info Controller : ' . get_class($this->controller));
        $audit->write('system:url_info Action : ' . $this->action);
        $audit->write('system:url_info ' . print_r($this->controller->_data, TRUE));
        $audit->write('system:url_info EGS_COMPANY_ID : ' . EGS_COMPANY_ID);
        $audit->write('system:url_info EGS_USERNAME : ' . EGS_USERNAME);

        $db->debug(DEBUG);
    }

    public static function references($module = null, $type = 'controller', $controller = null)
    {
        if (empty($module)) {
            return;
        }

        $system = System::Instance();
        $scan_dirs = self::scanDirectories(PUBLIC_MODULES, $module, FALSE);

        switch (strtolower($type)) {

            case 'controller':
                $match = CONTROLLERS_NAME;
                break;

            case 'eglet':
                $match = 'eglets' . DIRECTORY_SEPARATOR;
                break;

            case 'model':
                $match = MODELS_NAME;
                break;

            case 'template':

                $match = 'templates' . DIRECTORY_SEPARATOR;

                foreach ($scan_dirs as $path) {
                    if (strpos($path, $module . DIRECTORY_SEPARATOR . TEMPLATES_NAME . $controller) !== FALSE) {
                        $system->templates[] = $path;
                    }
                }

                return;

            default:
                return;
        }

        $autoloader = &AutoLoader::Instance();

        foreach ($scan_dirs as $key => $path) {

            if (strpos($path, $match) !== FALSE) {
                $autoloader->addPath(array(
                    $key => $path
                ));
            }
        }
    }

    public function xmlrpcServer()
    {
        if (! $this->check_system()) {
            return;
        }

        $this->load_essential();

        if (! defined('EGS_USERNAME')) {

            $config = Config::Instance();

            define('EGS_USERNAME', $config->get('TICKET_USER'));
        }

        include PLUGINS_ROOT . 'xmlrpc/xmlrpc.inc';
        include PLUGINS_ROOT . 'xmlrpc/xmlrpcs.inc';

        $this->references('ticketing', 'model');

        // Need to load definitions for introspection purposes
        // TODO: register in database and retrieve from there

        $newOther_sig = array(
            array(
                'struct',
                'subject' => 'string',
                'string',
                'string'
            )
        );
        $newOther_doc = 'Another new uzERP xml rpc function';
        $newOther = array(
            'function' => 'newOther',
            'signature' => $newOther_sig,
            'docstring' => $newOther_doc
        );

        // $ticketRequest_sig = array(array('struct', 'subject'=>'string', 'from_email'=>'string', 'request'=>'string', 'to_email'=>'string'));
        $ticketRequest_sig = array(
            array(
                'struct',
                'string',
                'string',
                'struct',
                'string'
            )
        );
        $ticketRequest_doc = 'Submit uzERP ticket';
        $ticketRequest = array(
            'function' => 'xmlrpcTicket::request',
            'signature' => $ticketRequest_sig,
            'docstring' => $ticketRequest_doc
        );

        new xmlrpc_server(array(
            'uzerp.newOther' => $newOther,
            'support.request' => $ticketRequest
        ));
    }

    /**
     * Validate the CSRF token for all unsafe request methods
     *
     * @return boolean
     */
    private function csrfValid()
    {
        $flash = Flash::Instance();

        $safe_methods = ['get', 'head', 'options', 'trace'];
        $request_method = strtolower($this->request->getMethod());

        // test for valid CSRF token on all unsafe requests
        if(!in_array($request_method, $safe_methods)) {
            try {
                $csrf = new \Riimu\Kit\CSRF\CSRFHandler();
                $csrf->validateRequest(true);
            } catch (\Riimu\Kit\CSRF\InvalidCSRFTokenException $ex) {
                error_log('Bad or missing CSRF token: ' . $this->request->getURI());
                $flash->addError('Action cancelled: invalid or missing CSRF Token<br/><em>You may have a problem with your network or internet connection</em>');
                if ($this->request->headers->get('x-requested-with') != 'XMLHttpRequest') {
                    // uzERP's ajax can't handle this, only set for html form responses
                    http_response_code (400);
                }
                return false;
            }
        }

        return true;
    }
}
?>
