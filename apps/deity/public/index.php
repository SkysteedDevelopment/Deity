<?php

// Prepare a Site Handle
define("SITE_HANDLE", "starborn");

// Prepare the Database
define("DATABASE_NAME", "starbornrpg");

// Set Important PATH constants
define("APP_PATH", dirname(dirname(__DIR__)) . '/' . SITE_HANDLE);
define("SYS_PATH", dirname(dirname(dirname(__DIR__))) . '/teslaEngine');
define("PUBLIC_PATH", APP_PATH . '/public');
define("ROUTE_PATH", APP_PATH . '/controller');
define("ROUTE_SECOND_PATH", SYS_PATH . '/parent-apps/Standard/controller');

define("HEADER_PATH", APP_PATH . "/includes/header.php");
define("FOOTER_PATH", APP_PATH . "/includes/footer.php");

// A salt used for generic site-wide purposes
// Try to keep this value between 60 - 70 characters long
define("SITE_SALT",	"lvLrVzw5xTL3iGIaNTKlAxiXn0ODRLWn2WRn6vPk8wXLoTe0dqwmfjrQceDEhVS2rM");
//					|    5   10   15   20   25   30   35   40   45   50   55   60   65   |

// Load the phpTesla Engine
require(SYS_PATH . '/phpTesla.php');

// Set the local URL Prefix, if applicable
// This value is used to prefix a localhost domain, such as "dev.mydomain.com" where ".dev" is the URL_PREFIX.
define("URL_PREFIX", (ENVIRONMENT == "local" ? "dev." : ""));

if(ENVIRONMENT == "local")
{
	// Get the site's subdomain
	$val = str_replace(".starborn.skysteed.com", "", $_SERVER['HTTP_HOST']);
	
	define("SUBDOMAIN", ($val == "dev" ? "" : str_replace("dev.", "", $val)));
}
else
{
	// Get the site's subdomain
	define("SUBDOMAIN", strpos($_SERVER['HTTP_HOST'], ".starborn.skysteed.com") !== false ? str_replace(".starborn.skysteed.com", "", $_SERVER['HTTP_HOST']) : "");
}

// Set the site's domain
define("FULL_DOMAIN", strtolower($_SERVER['SERVER_NAME']));
define("BASE_DOMAIN", $_SERVER['HTTP_HOST']);
define("CDN", "http://" . URL_PREFIX . "skysteed.com");

/****** Quick-Load and Unique Behavior for Certain Files ******/
switch($url[0])
{
	case "api":
	case "script":
		require(ROUTE_PATH . "/" . $url[0] . ".php"); exit;
}

/****** Session Handling ******/
session_name(SITE_HANDLE);
session_set_cookie_params(0, '/', '.' .  BASE_DOMAIN);

session_start();

// Make sure the base session value used is available
if(!isset($_SESSION[SITE_HANDLE]))
{
	$_SESSION[SITE_HANDLE] = array();
}

/****** Prepare the Database Connection ******/
if(!defined("DATABASE_USER")) { die("The database has not been correctly configured."); }

Database::initialize(DATABASE_NAME);

// Make sure a connection to the database was created
if(!Database::$database)
{
	// If we're installing the system, we acknowledge that errors with the database will exist.
	if(!$url[0] == "install")
	{
		die("There was an issue connecting to the database. Likely issues: wrong user/pass credentials or the table is missing.");
	}
}

/****** Setup Custom Error Handler ******/
require(SYS_PATH . "/error-handler.php");

/****** Process Security Functions ******/
Security_Fingerprint::run();

/****** Metadata Handler ******/
Metadata::load();

/****** Identify the Device (1 = mobile, 2 = tablet, 3 = device) ******/
if(!isset($_SESSION['device']))
{
	$device = new DetectDevice();
	
	if($device->isMobile())
	{
		$_SESSION['device'] = 1;
	}
	else if($device->isTablet())
	{
		$_SESSION['device'] = 2;
	}
	else
	{
		$_SESSION['device'] = 3;
	}
}

// Load the routing system
require(SYS_PATH . "/routes.php");

// If the routes.php file or dynamic URLs didn't load a page (and thus exit the scripts), run a 404 page.
require(ROUTE_SECOND_PATH . "/404.php");