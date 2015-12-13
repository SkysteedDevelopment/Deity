<?php

/**************************
****** Set Variables ******
**************************/

$engineDirectory = "/var/www";


/**************************
****** Validate Root ******
**************************/

// Get the active user
$activeUser = trim(shell_exec('whoami'));

// Make sure the user is root, or prevent further use
if($activeUser != "root")
{
	echo "You must be root to use this script.\n";
	exit;
}


/****************************
****** Get Environment ******
****************************/

// Run through the directory
if(is_dir($engineDirectory))
{
	passthru('chown www-data:www-data ' . $engineDirectory . ' -R');
}

// Scan the folders
$folders = scandir($engineDirectory);

foreach($folders as $folder)
{
	if(!is_dir($engineDirectory . "/" . $folder) or ($folder == "." or $folder == "..")) { continue; }
	
	echo "\nPulling from Git for: " . $engineDirectory . '/' . $folder;
	
	shell_exec("cd " . $engineDirectory . '/' . $folder . ' && git fetch --all && git reset --hard origin/master');
}

echo "\n";

exit;
