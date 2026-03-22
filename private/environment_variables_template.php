<?php

define("DB_SERVER", "");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "");

// SMTP Configuration
define("SMTP_HOST", "");
define("SMTP_PORT", 587);
define("SMTP_USER", "");
define("SMTP_PASS", "");
define("SMTP_FROM_EMAIL", "");

define(
  "ARTIFACTS_API_KEY", 
  ""
);

define(
  "JWT_SECRET", 
  ""
);
define("COOKIE_SECURE", true);

define("ARTIFACTS_DOMAIN", "artifact.stewardgoods.com");
define("DOMAIN", ARTIFACTS_DOMAIN);
define("API_ORIGIN", "api." . ARTIFACTS_DOMAIN);
define("REQUEST_ORIGIN", ARTIFACTS_DOMAIN);

define("SWEET_SPOT_BUTTONS_ON", false);

?>