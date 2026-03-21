<?php
  if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
  } else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
  }

  ob_start(); // output buffering is turned on

  session_start(); // turn on sessions
  
  // Assign file paths to PHP constants
  // __FILE__ returns the current path to this file
  // dirname() returns the path to the parent directory
  define("PRIVATE_PATH", dirname(__FILE__));
  define("PROJECT_PATH", dirname(PRIVATE_PATH));
  define("PUBLIC_PATH", PROJECT_PATH . '/artifacts');
  define("SHARED_PATH", PRIVATE_PATH . '/shared');

  // Assign the root URL to a PHP constant
  // * Do not need to include the domain
  // * Use same document root as webserver
  // * Can set a hardcoded value:
  // define("WWW_ROOT", '');
  // * Can dynamically find everything in URL up to "/public"
  // $public_end = strpos($_SERVER['SCRIPT_NAME'], '/artifacts') + 10;
  // $doc_root = substr($_SERVER['SCRIPT_NAME'], 0, $public_end);
  // define("WWW_ROOT", $doc_root);

  define("WWW_ROOT", '');

  require_once('vendor/autoload.php');
  require_once('environment_variables.php');

  require_once('functions.php');
  require_once('database.php');
  require_once('query_functions.php');
  require_once('validation_functions.php');
  require_once('auth_functions.php');

  $db = db_connect();
  $errors = [];

  // Load OOP data access layer (shared with API)
  require_once('classes/DatabaseObject.class.php');
  DatabaseObject::set_database($db);
  require_once('classes/Artifact.class.php');
  require_once('classes/User.class.php');

  // Generate CSRF token for all pages
  generate_csrf_token();

  // Validate CSRF token on all POST requests
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && php_sapi_name() !== 'cli') {
    if (!validate_csrf_token()) {
      http_response_code(403);
      exit('Invalid CSRF token.');
    }
  }

?>
