<?php /*

---------------------------------
------ DIRECTORY CONSTANTS ------
---------------------------------
	
	SYS_PATH			// The path to the system directory.
	
	ROUTE_PATH			// The path that all URL routes get routed to.
	ROUTE_SECOND_PATH	// The path that all URL routes get routed to if not found in first path.
	
------------------------------
------ Important Values ------
------------------------------
	
	$url_relative			// The URL segments as one; e.g. "/friends/requests/Joe"
	$url					// An array of the URL segments; e.g. $url = array("friends", "requests", "Joe");
	
*/

// Load System Configurations
if(!file_exists(SYS_PATH . '/environment.php'))
{
	die("Unable to load the environment file.");
}

require(SYS_PATH . "/environment.php");

// Make sure that the environment was set
if(!ENVIRONMENT) { die("The ENVIRONMENT constant has not been set."); }

// Report errors locally, but not on staging or production 
error_reporting(E_ALL);

ini_set("display_errors", ENVIRONMENT == "local" ? 1 : 0);

// Prepare the Auto-Loader
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

// Assign the appropriate autoloader
require(SYS_PATH . "/autoloader.php");

// Prepare the URL and strip out any query string data (if used)
$url_relative = explode("?", rawurldecode($_SERVER['REQUEST_URI']));

// Sanitize any unsafe characters from the URL
$url_relative = trim(preg_replace("/[^a-zA-Z0-9_\-\/\.\+]/", "", $url_relative[0]), "/");

// Section the URL into multiple segments so that each can be added to the array individually
$url = explode("/", $url_relative);
