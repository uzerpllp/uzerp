<?php

/**
 * system uzERP system loader
 *
 * @version $Revision: 1.126 $
 * @package uzerp
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

require 'vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;

class system
{

    protected $version = '$Revision: 1.126 $';

    const DAY_START_HOURS = '9';

    const DAY_START_MINUTES = '0';

    const DAY_LENGTH = '8';

    const _THEME = 'default';

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

        $extensions = array(
            'json'
        );

        foreach ($extensions as $ext) {

            if (! extension_loaded($ext)) {
                die('Missing extension: ' . $ext);
            }
        }

        // **************************
        // check for PHP extensions

        $check_extensions = array(
            'apc',
            'memcache',
            'memcached'
        );

        foreach ($check_extensions as $extension) {

            if (extension_loaded($extension)) {
                define('HAS_' . strtoupper($extension), TRUE);
            } else {
                define('HAS_' . strtoupper($extension), FALSE);
            }
        }

        // **************************
        // check for packages

        $check_packages = array(
            'fop',
            'pdftk',
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

        // *********************
        // REQUIRED DIRECTORIES

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

        return TRUE;
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
        $start = gettimeofday(TRUE);

        // ATTN: check system always returns true?
        if (! $this->check_system()) {

            $this->login_required = FALSE;

            // if (!defined('SETUP'))
            // {
            // define('SETUP', TRUE);
            // }

            if (! defined('MODULE')) {
                define('MODULE', 'system_admin');
            }

            if (! defined('CONTROLLER')) {
                define('CONTROLLER', 'SystemsController');
            }
        }

        $this->load_essential();

        debug('system::display session data:' . print_r($_SESSION, TRUE));

        $this->user = FALSE;

        if (isLoggedIn()) {
            // Sets the global constants EGS_USERNAME and EGS_COMPANY_ID
            setupLoggedInUser();

            $this->user = getCurrentUser();

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

        $csrf = new \Riimu\Kit\CSRF\CSRFHandler();

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

        // output standard arrays to smarty
        $this->view->set("module_css", $this->get_css());
        $this->view->set("module_js", $this->get_js());
        $this->view->set('current_user', $this->user);

        $action = $this->action;
        $controller = $this->controller;

        $theme = '';

        if (defined('EGS_COMPANY_ID') && EGS_COMPANY_ID !== 'null' && EGS_COMPANY_ID > 0) {

            $sc = DataObjectFactory::Factory('Systemcompany');
            $sc->load(EGS_COMPANY_ID);

            if ($sc->isLoaded()) {

                define('SYSTEM_COMPANY', $sc->company);
                define('COMPANY_ID', $sc->company_id);

                $theme = $sc->theme;
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

        if (defined('LOGIN_PAGE_THEME') && $this->modules['module'] == 'login') {
            $theme = LOGIN_PAGE_THEME;
        }

        if (! empty($theme)) {
            define('THEME', $theme);
        } else {
            define('THEME', 'default');
        }

        $this->view->set('theme', THEME);

        if (! defined('EGS_CURRENCY')) {
            define('EGS_CURRENCY', 'GBP');
        }

        if (class_exists('Currency')) {

            $currency = DataObjectFactory::Factory('Currency');
            $currency->loadBy('currency', EGS_CURRENCY);

            if ($currency) {
                define('EGS_CURRENCY_SYMBOL', utf8_decode($currency->symbol));
            }
        }

        if (! defined('EGS_CURRENCY_SYMBOL')) {
            define('EGS_CURRENCY_SYMBOL', utf8_decode('£'));
        }

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

            $controller->checkRequest($this->request, $action)->$action();

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

        showtime('pre-display');
        // echo 'System::display end '.(gettimeofday(TRUE)-$start).'<br>';
        // echo 'system::display (3),'.microtime(TRUE).'<br>';
        $this->view->display('index_page.tpl', $cache_key);
        // echo 'system::display (4),'.microtime(TRUE).'<br>';

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
                    'logout'
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

    public function get_uzlet_resources($type)
    {
        $cache = Cache::Instance();
        $files = $cache->get(array(
            'uzlet_' . $type
        ));

        $extentions = array(
            '.uzlet.' . $type
        );

        // we need to include less if the type is css
        if ($type === 'css') {
            $extentions[] = '.uzlet.less';
        }

        // go and fetch the data and populate the cache
        if ($files === FALSE) {

            // if the cache result was false, make double sure
            // we're working with an array form this point one

            $files = array();

            if (file_exists(PUBLIC_MODULES)) {

                $it = new RecursiveDirectoryIterator(PUBLIC_MODULES);

                // loop through each file found
                foreach (new RecursiveIteratorIterator($it) as $file) {

                    foreach ($extentions as $extention) {

                        // but only include it if the file ends with .uzlet.$type
                        if (substr($file, - strlen($extention)) === $extention) {
                            $files[] = str_replace(FILE_ROOT, '', $file);
                        }
                    }
                }

                $cache->add(array(
                    'uzlet_' . $type
                ), $files);
            }
        }

        return $files;
    }

    public function get_css()
    {
        $cache_id = array(
            'resources',
            'css',
            $this->modules['module']
        );

        $cache = Cache::Instance();
        $files = $cache->get($cache_id);

        // go and fetch the data and populate the cache
        if ($files === FALSE) {

            // START FETCHING FILES

            $files = array();
            $dirs = array();

            $dirs['shared'] = self::findModulePath(PUBLIC_MODULES, 'shared', FALSE);
            $dirs['module'] = self::findModulePath(PUBLIC_MODULES, $this->modules['module'], FALSE);
            $dirs['module'] .= DIRECTORY_SEPARATOR . 'resources/css';

            foreach ($dirs as $dir) {

                if (file_exists($dir)) {

                    $it = new RecursiveDirectoryIterator($dir);

                    // loop through each file found
                    foreach (new RecursiveIteratorIterator($it) as $file) {

                        if (is_css($file)) {
                            $files[] = str_replace(FILE_ROOT, '', $file);
                        }
                    }
                }
            }

            // loop through discovered files, remove sibling less => css files
            foreach ($files as $key => $css) {

                if (get_file_extension($css) === 'less') {

                    // build the 'wanted' path and search in the array for it
                    $find_path = str_replace('.less', '.css', $css);
                    $matching_key = array_search($find_path, $files);

                    // if a key is found, delete it
                    if ($matching_key !== FALSE) {
                        unset($files[$matching_key]);
                    }
                }
            }

            // END

            $cache->add($cache_id, $files);
        }

        return $files;
    }

    public function get_js()
    {
        $cache_id = array(
            'resources',
            'js',
            $this->modules['module']
        );

        $cache = Cache::Instance();
        $files = $cache->get($cache_id);

        // go and fetch the data and populate the cache
        if ($files === FALSE) {

            // START FETCHING FILES

            $files = array();
            $dirs = array();

            $dirs['module'] = self::findModulePath(PUBLIC_MODULES, $this->modules['module'], FALSE);
            $dirs['module'] .= DIRECTORY_SEPARATOR . 'resources/js';

            foreach ($dirs as $dir) {

                if (file_exists($dir)) {

                    $it = new RecursiveDirectoryIterator($dir);

                    // loop through each file found
                    foreach (new RecursiveIteratorIterator($it) as $file) {

                        // but only include it's a js file and isn't the uzlet file
                        if (is_js($file) && substr($file, - 9) !== '.uzlet.js') {
                            $files[] = str_replace(FILE_ROOT, '', $file);
                        }
                    }
                }
            }

            // END

            $cache->add($cache_id, $files);
        }

        return $files;
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
        define('TEMPLATES_NAME', 'templates' . DIRECTORY_SEPARATOR);
        define('CONTROLLERS_NAME', 'controllers' . DIRECTORY_SEPARATOR);
        define('EGLETS_NAME', 'eglets' . DIRECTORY_SEPARATOR);
        define('MODELS_NAME', 'models' . DIRECTORY_SEPARATOR);
        define('REPORTS_NAME', 'reports' . DIRECTORY_SEPARATOR);
        define('STANDARD_TPL_ROOT', COMMON_MODULES . TEMPLATES_NAME);
        define('STANDARD_EGLET_TPL_ROOT', COMMON_MODULES . TEMPLATES_NAME . EGLETS_NAME);
        define('SHARED_TPL_ROOT', PUBLIC_MODULES . 'shared' . DIRECTORY_SEPARATOR . TEMPLATES_NAME);
        define('THEME_ROOT', FILE_ROOT . 'themes' . DIRECTORY_SEPARATOR);
        define('THEME_URL', 'themes' . DIRECTORY_SEPARATOR);
        define('USER_ROOT', FILE_ROOT);
        define('PRINT_ROOT', PLUGINS_ROOT . 'printIPP' . DIRECTORY_SEPARATOR);
        define('PDF_ROOT', PLUGINS_ROOT . 'ezpdf' . DIRECTORY_SEPARATOR);
        define('SMARTY_CUSTOM_PLUGINS', PLUGINS_ROOT . 'smarty' . DIRECTORY_SEPARATOR . 'custom_plugins' . DIRECTORY_SEPARATOR);
        define('JS_ROOT', FILE_ROOT . 'lib/js' . DIRECTORY_SEPARATOR);
        define('JS_LIB_ROOT', JS_ROOT . 'lib' . DIRECTORY_SEPARATOR);
        define('JS_JQUERY_ROOT', JS_ROOT . 'jquery' . DIRECTORY_SEPARATOR);
        define('JS_JQUERY_CORE', JS_JQUERY_ROOT . 'core' . DIRECTORY_SEPARATOR);
        define('JS_JQUERY_SCRIPTS', JS_JQUERY_ROOT . 'scripts' . DIRECTORY_SEPARATOR);
        define('JS_JQUERY_PLUGINS', JS_JQUERY_ROOT . 'plugins' . DIRECTORY_SEPARATOR);
    }

    public function getResources($theme = 'default')
    {

        // javascript resources
        $jsfiles = array();

        // JavaScript console fix
        $jsfiles[] = JS_JQUERY_SCRIPTS . "console.js";

        // jQuery core
        $jsfiles[] = JS_JQUERY_CORE . "jquery-1.7.1.min.js";
        $jsfiles[] = JS_JQUERY_CORE . "jquery-ui-1.8.16.custom.min.js";

        // jQuery functions
        $jsfiles[] = JS_JQUERY_SCRIPTS . "functions.js";
        $jsfiles[] = JS_JQUERY_SCRIPTS . "ajax.js";
        $jsfiles[] = JS_JQUERY_SCRIPTS . "print_dialog.js";
        $jsfiles[] = JS_JQUERY_SCRIPTS . "rules.js";

        // jQuery plugins
        $jsfiles[] = JS_JQUERY_PLUGINS . "uiBlock" . DIRECTORY_SEPARATOR . "uiBlock.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "superfish-1.4.8" . DIRECTORY_SEPARATOR . "hoverIntent.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "superfish-1.4.8" . DIRECTORY_SEPARATOR . "superfish.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "jquery.multiSelect-1.2.2" . DIRECTORY_SEPARATOR . "jquery.bgiframe.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "jquery.multiSelect-1.2.2" . DIRECTORY_SEPARATOR . "jquery.multiSelect.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "jquery.watermark-3.1.1" . DIRECTORY_SEPARATOR . "jquery.watermark.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "jquery.uz-grid.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "jquery.uz-validation.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "jquery.uz-autocomplete.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "jquery.uz-constrains.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "charts" . DIRECTORY_SEPARATOR . "uz-chart.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "charts" . DIRECTORY_SEPARATOR . "jquery.uz-pie-chart.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "charts" . DIRECTORY_SEPARATOR . "jquery.uz-line-chart.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "uz-collection" . DIRECTORY_SEPARATOR . "charts" . DIRECTORY_SEPARATOR . "jquery.uz-bar-chart.js";
        // $jsfiles[] = JS_JQUERY_PLUGINS . "fullcalendar" . DIRECTORY_SEPARATOR . "fullcalendar.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "fullcalendar" . DIRECTORY_SEPARATOR . "fullcalendar.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "fullcalendar" . DIRECTORY_SEPARATOR . "gcal.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "contextMenu" . DIRECTORY_SEPARATOR . "jquery.contextMenu.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "contextMenu" . DIRECTORY_SEPARATOR . "jquery.ui.position.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "collapsibleCheckboxTree" . DIRECTORY_SEPARATOR . "jquery.collapsibleCheckboxTree.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "tabby" . DIRECTORY_SEPARATOR . "tabby.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "scrollTo" . DIRECTORY_SEPARATOR . "jquery.scrollTo-1.4.2-min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "tinysort" . DIRECTORY_SEPARATOR . "jquery.tinysort.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "jqPagination" . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . "jqPagination.jquery.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "nestedSortable" . DIRECTORY_SEPARATOR . "jquery.ui.nestedSortable.js";
        // the glob.js file should be first... but it won't work in that configuration
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "external" . DIRECTORY_SEPARATOR . "raphael.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "jquery.wijmo.wijchartcore.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "jquery.wijmo.wijpiechart.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "jquery.wijmo.wijlinechart.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "jquery.wijmo.wijbarchart.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "wijmo" . DIRECTORY_SEPARATOR . "external" . DIRECTORY_SEPARATOR . "jquery.glob.min.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "jquery.tableScroll" . DIRECTORY_SEPARATOR . "jquery.tablescroll.js";
        $jsfiles[] = JS_JQUERY_PLUGINS . "strengthify" . DIRECTORY_SEPARATOR . "jquery.strengthify.js";

        // css resources
        $cssfiles = array();

        // Standard CSS files
        $cssfiles[] = THEME_ROOT . $theme . "/css/reset.css";
        $cssfiles[] = THEME_ROOT . $theme . "/css/screen.less";
        $cssfiles[] = THEME_ROOT . $theme . "/css/forms.less";
        $cssfiles[] = THEME_ROOT . $theme . "/css/print.less";
        $cssfiles[] = THEME_ROOT . $theme . "/css/calendar.less";

        // jQuery CSS files
        $cssfiles[] = THEME_ROOT . $theme . "/lib/jquery.watermark-3.1.1/jquery.watermark.css";
        $cssfiles[] = THEME_ROOT . $theme . "/lib/jquery-ui/jquery-ui-1.8.custom.less";
        $cssfiles[] = THEME_ROOT . $theme . "/lib/fullcalendar/fullcalendar.css";
        $cssfiles[] = THEME_ROOT . $theme . "/lib/formalize/custom_formalize.css";
        $cssfiles[] = JS_JQUERY_PLUGINS . "collapsibleCheckboxTree" . DIRECTORY_SEPARATOR . "jquery.collapsibleCheckboxTree.css";
        $cssfiles[] = JS_JQUERY_PLUGINS . "jqPagination" . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "style.css";
        // $cssfiles[] = JS_JQUERY_PLUGINS . "jquery.tableScroll" . DIRECTORY_SEPARATOR . "jquery.tablescroll.css";
        $cssfiles[] = JS_JQUERY_PLUGINS . "contextMenu" . DIRECTORY_SEPARATOR . "jquery.contextMenu.css";
        $cssfiles[] = JS_JQUERY_PLUGINS . "strengthify" . DIRECTORY_SEPARATOR . "strengthify.css";

        // return resources
        return array(
            'css' => $cssfiles,
            'js' => $jsfiles
        );
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
        $safe_methods = ['get', 'head', 'options', 'trace'];
        $request_method = strtolower($this->request->getMethod());

        // test for valid CSRF token on all unsafe requests
        if(!in_array($request_method, $safe_methods)) {
            try {
                $csrf = new \Riimu\Kit\CSRF\CSRFHandler();
                $csrf->validateRequest(true);
            } catch (\Riimu\Kit\CSRF\InvalidCSRFTokenException $ex) {
                error_log('Bad or missing CSRF token: ' . $this->request->getURI());
                header('HTTP/1.0 400 Bad Request');
                exit('Bad CSRF Token');
            }
        }

        return TRUE;
    }
}
?>
