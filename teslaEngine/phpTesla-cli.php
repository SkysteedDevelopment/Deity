<?php /*

----------------------------------
------ URL & MISC CONSTANTS ------
----------------------------------
	
	DOMAIN				// The full domain, such as "forum.unifaction.com"
	SITE_URL			// The FULL_DOMAIN with "http://" in front of it, and the URL_PREFIX (dev.) if applicable
	
	URL_PREFIX			// A value to append to URL's for different environments (e.g. "dev.")
	
---------------------------------
------ DIRECTORY CONSTANTS ------
---------------------------------
	
	SYS_PATH			// The path to the system directory.
	
	ROUTE_PATH			// The path that all URL routes get routed to.
	ROUTE_SECOND_PATH	// The path that all URL routes get routed to if not found in first path.
	
-----------------------------
------ THEME CONSTANTS ------
-----------------------------

	HEADER_PATH
	FOOTER_PATH

------------------------------
------ Important Values ------
------------------------------
	
	$url_relative			// The URL segments as one; e.g. "/friends/requests/Joe"
	$url					// An array of the URL segments; e.g. $url = array("friends", "requests", "Joe");
	
	$_SESSION[SITE_HANDLE]	// Store the active user's session data
	
*/

/****** Prepare Important Paths ******/
define("SYS_PATH", 		dirname(__DIR__) . "/system");

/****** CLI Handler ******/
define("CLI", strpos(php_sapi_name(), "cli") !== false);

// If loading through the CLI, identify the site's location based on the active directory
if(CLI)
{
	$filesLoaded = get_included_files();
	
	$_SERVER['SERVER_NAME'] = basename(dirname($filesLoaded[0])) . '.' . basename(dirname(dirname($filesLoaded[0])));
	$_SERVER['REQUEST_URI'] = "/";
}


/****** Extract $url and $url_relative ******/
$url = $_SERVER['REQUEST_URI'];

// Strip out any query string data (if used)
$url_relative = explode("?", rawurldecode($url));

// Sanitize any unsafe characters from the URL
$url_relative = trim(preg_replace("/[^a-zA-Z0-9_\-\/\.\+]/", "", $url_relative[0]), "/");

// Section the URL into multiple segments so that each can be added to the array individually
$url = explode("/", $url_relative);


/****** Load Server Configurations ******/

// Make sure that the environment was set
if(!ENVIRONMENT) { die("The ENVIRONMENT constant has not been set."); }


/****** Error Reporting ******/
// Report errors locally, but not on staging or production 
error_reporting(E_ALL);
ini_set("display_errors", ENVIRONMENT == "local" ? 1 : 0);


/****** Prepare the Auto-Loader ******/
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

// Assign the appropriate autoloader
require(SYS_PATH . "/autoloader" . (CLI ? "-cli" : "") . ".php");

/****** Set the database to connect to ******/
Database::$databaseName = Config::$siteConfig['Database Name'] ? Config::$siteConfig['Database Name'] : Config::$siteConfig['Site Handle'];


/****** Session Handling ******/
if(USE_SESSIONS)
{
	session_name(SERVER_HANDLE);
	session_set_cookie_params(0, '/', '.' . URL_PREFIX . BASE_DOMAIN);
	
	session_start();
}


/****** Prepare the Database Connection ******/
if(USE_DATABASE)
{
	Database::initialize(Database::$databaseName);
	
	// Make sure a connection to the database was created
	if(Database::$database)
	{
		// Make sure the base session value used is available
		if(!isset($_SESSION[SITE_HANDLE]))
		{
			$_SESSION[SITE_HANDLE] = array();
		}
		
		/****** Process Security Functions ******/
		Security_Fingerprint::run();
		
		/****** Setup Custom Error Handler ******/
		require(SYS_PATH . "/error-handler.php");
	}
	else
	{
		// If we're installing the system
		if(!$url[0] == "install")
		{
			die("There was an issue connecting to the database. Likely issues: wrong user/pass credentials or the table is missing.");
		}
	}
}


/****** CLI Routing ******/
if(CLI)
{
	// Load the appropriate script being called
	if(isset($_SERVER['argv'][1]))
	{
		if($scriptPath = realpath(SYS_PATH . "/cli/" . $_SERVER['argv'][1] . ".php"))
		{
			require($scriptPath); exit;
		}
	}
	
	// If no script was called, load the CLI menu
	require(SYS_PATH . "/cli/menu.php"); exit;
}

// Determine which page you should point to, then load it
require(SYS_PATH . "/routes.php");
