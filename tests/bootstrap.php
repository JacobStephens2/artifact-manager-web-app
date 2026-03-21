<?php

/**
 * PHPUnit test bootstrap.
 *
 * Sets up constants, superglobals, and autoloading so the project's
 * source files can be loaded without starting a real web session.
 */

// Paths ---------------------------------------------------------------
define('PROJECT_PATH', dirname(__DIR__));
define('PRIVATE_PATH', PROJECT_PATH . '/private');
define('PUBLIC_PATH',  PROJECT_PATH . '/artifacts');
define('SHARED_PATH',  PRIVATE_PATH . '/shared');
define('WWW_ROOT',     '');

// Fake superglobals that many functions rely on -----------------------
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SESSION = [];

// Autoloader ----------------------------------------------------------
// Composer autoloader lives inside private/vendor (project dependency root).
if (file_exists(PRIVATE_PATH . '/vendor/autoload.php')) {
    require_once PRIVATE_PATH . '/vendor/autoload.php';
}

// Also load the project-root vendor autoloader (PHPUnit lives here).
if (file_exists(PROJECT_PATH . '/vendor/autoload.php')) {
    require_once PROJECT_PATH . '/vendor/autoload.php';
}

// Source files under test ---------------------------------------------
require_once PRIVATE_PATH . '/functions.php';
require_once PRIVATE_PATH . '/validation_functions.php';
require_once PRIVATE_PATH . '/cache.php';
require_once PRIVATE_PATH . '/app_logger.php';
