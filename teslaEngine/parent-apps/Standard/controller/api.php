<?php

/*
	This page runs the API system.
	
	The API that gets loaded is equal to $url[1].
	
	http://example.com/api/API_NAME
*/

// The name of the API to run
$api = Sanitize::variable($url[1]);

// Make sure the runAPI method exists.
if(!method_exists($api, "processRequest")) { exit; }

echo $api::processRequest();