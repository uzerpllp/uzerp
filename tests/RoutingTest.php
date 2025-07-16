<?php

// -- Test Setup: Define Dummy Classes to satisfy dependencies --

if (!class_exists('AccessObject')) {
    class AccessObject {
        public $tree = [];
        public static function Instance() { return new self(); }
        public function hasPermission($m, $c, $a, $p) { return true; }
        public function save() {}
        public function getPermission($m, $c, $a) { return 1; }
        public function setHelpContext($p) { return 'help.html'; }
    }
}

if (!class_exists('Flash')) {
    class Flash {
        public static function Instance() { return new self(); }
        public function addError($msg) {}
        public function save() {}
        public function getMessages($type) { return []; }
    }
}

if (!class_exists('DB')) {
    class DB {
        public static function Instance() { return new self(); }
        public function debug($bool) {}
    }
}

if (!class_exists('DataObjectFactory')) {
    class DataObjectFactory {
        public static function Factory($name) {
            $mock = new stdClass();
            $mock->load = function() {};
            $mock->isLoaded = function() { return true; };
            $mock->getCount = function() { return 0; };
            $mock->company = 'Test Company';
            $mock->company_id = 1;
            $mock->access_enabled = 't';
            $mock->audit_enabled = 'f';
            $mock->debug_enabled = 'f';
            $mock->info_message = '';
            return $mock;
        }
    }
}

if (!class_exists('Config')) {
    class Config {
        public static function Instance() { return new self(); }
        public function get($key) { return 'development'; }
        public function get_all() { return []; }
    }
}

if (!class_exists('uzJobMessages')) {
    class uzJobMessages {
        public static function Factory($user, $company) { return new self(); }
        public function displayJobMessages() {}
    }
}

if (!class_exists('ControllerFactory')) {
    class ControllerFactory {
        public static function Factory() {
            if (isset($_GET['controller']) && $_GET['controller'] === 'sales') {
                return 'SalesController';
            }
            return 'IndexController';
        }
    }
}

if (!class_exists('ActionFactory')) {
    class ActionFactory {
        public static function Factory() {
             if (isset($_GET['action'])) {
                return $_GET['action'];
            }
            return 'index';
        }
    }
}

if (!class_exists('View')) {
    class View {
        public function set($name, $value) {}
        public function setTemplateDir($dirs) {}
        public function assign($name, $value) {}
    }
}

if (!class_exists('Controller')) {
    abstract class Controller {
        public $view;
        public function __construct($module, &$view) { $this->view = $view; }
        public function setInjector($i) {}
        public function setData($d) {}
        public function setTemplateName($name) {}
        public function assignModels() {}
    }
}

if (!class_exists('IndexController')) {
    class IndexController extends Controller {}
}

if (!class_exists('SalesController')) {
    class SalesController extends Controller {}
}

// -- Global test helpers --

beforeEach(function () {
    // Reset the RouteParser singleton to ensure clean state
    RouteParser::resetInstance();

    // Reset globals for each test
    $_GET = [];
    $_SESSION = [
        'loggedin' => true, // Set session to logged in
        'username' => 'testuser'
    ];  
    
    // Define constants that are normally set during login
    if (!defined('EGS_USERNAME')) define('EGS_USERNAME', 'testuser');
    if (!defined('EGS_COMPANY_ID')) define('EGS_COMPANY_ID', 1);

    // Manually initialize the access object on the system singleton
    $system = system::Instance();
    $system->access = AccessObject::Instance();
    $system->modules = [];
    $system->controller = null;
    $system->action = null;
    $system->router = null; // Reset the router to force re-parsing of $_GET
});


// -- Tests --

test('correctly parses a simple module route', function () {
    // Simulate a URL like index.php?module=dashboard
    $_GET['module'] = 'dashboard';

    $system = system::Instance();
    
    // Manually trigger the parts of the display() method that handle routing
    $system->setView();
    $system->setController();
    $system->setAction();

    // Assert that the module is correctly identified
    expect($system->modules['module'])->toBe('dashboard');
    // Assert that controller and action fall back to defaults
    expect($system->controller)->toBeAnInstanceOf(IndexController::class);
    expect($system->action)->toBe('index');
});

test('correctly parses a module and controller route', function () {
    // Simulate a URL like index.php?module=reports&controller=sales
    $_GET['module'] = 'reports';
    $_GET['controller'] = 'sales';
    
    $system = system::Instance();
    $system->setView();
    $system->setController();
    $system->setAction();

    expect($system->modules['module'])->toBe('reports');
    expect($system->controller)->toBeAnInstanceOf(SalesController::class);
    expect($system->action)->toBe('index');
});

test('correctly parses a full module, controller, and action route', function () {
    // Simulate a URL like index.php?module=reports&controller=sales&action=export
    $_GET['module'] = 'reports';
    $_GET['controller'] = 'sales';
    $_GET['action'] = 'export';

    $system = system::Instance();
    $system->setView();
    $system->setController();
    $system->setAction();

    expect($system->modules['module'])->toBe('reports');
    expect($system->controller)->toBeAnInstanceOf(SalesController::class);
    expect($system->action)->toBe('export');
});

test('correctly passes extra parameters to the controller', function () {
    // Simulate a URL like index.php?module=reports&controller=sales&action=view&id=123
    $_GET['module'] = 'reports';
    $_GET['controller'] = 'sales';
    $_GET['action'] = 'view';
    $_GET['id'] = '123';

    $system = system::Instance();
    $system->setView();
    $system->setController();
    $system->setAction();

    // Assert that the routing is correct
    expect($system->modules['module'])->toBe('reports');
    expect($system->controller)->toBeAnInstanceOf(SalesController::class);
    expect($system->action)->toBe('view');

    // Assert that the 'id' parameter is passed to the controller's data
    expect($system->controller->_data)
        ->toBeArray()
        ->toHaveKey('id')
        ->and($system->controller->_data['id'])->toBe('123');
});
