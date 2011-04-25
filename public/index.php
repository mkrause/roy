<?php
/**
 * Front controller. Feel free to customize this file.
 */

// Report all errors.
// 
// Note: you will want to suppress the display of errors in a production
// environment. If you use Roy's built-in error handling, recoverable errors
// will be handled gracefully in production mode, so it's okay to leave
// error_reporting on high.
error_reporting(E_ALL);

// Location of the core Roy module
$roy_path = '../roy';

// Location of the app module
$app_path = '../app';

require_once $roy_path . '/classes/roy.php';

// For performance, skip the autoloader for often-used classes.
// Note: this means that these classes cannot be overridden by a
// module anymore. If you want to override a built-in class,
// comment out the corresponding line.
require_once $roy_path . '/classes/controller.php';
require_once $roy_path . '/classes/html.php';
require_once $roy_path . '/classes/path.php';
require_once $roy_path . '/classes/request.php';
require_once $roy_path . '/classes/route.php';
require_once $roy_path . '/classes/url.php';
require_once $roy_path . '/classes/view.php';

// Initialize the framework
Roy::init(function() use ($app_path) {
    Roy::enable_autoload();
    Roy::enable_error_handling();
    
    Roy::add_module($app_path);
});

// Initialize the application
include Path::concat(Roy::module('app'), 'init.php');

// Dispatch to a controller and output (using PATH_INFO to route the request)
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$request = new Request($path);
echo $request->dispatch();
