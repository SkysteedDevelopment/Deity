<?php

// Make sure an appropriate environment is being used
switch(ENVIRONMENT)
{
	case "local":
	case "development":
	case "staging":
	case "production":
		break;
	
	default:
		Alert::error("Improper Environment", "You must set the ENVIRONMENT value properly.");
}

// If the server configuration are acceptable
if(Validate::pass())
{
	// Check if the form was submitted (to continue to the next page)
	if(Form::submitted("install-server-config"))
	{
		header("Location: /install/config-site"); exit;
	}
	
	Alert::success("Server Config", "Your server is properly configured!");
}

// Installation Header
require(dirname(ROUTE_SECOND_PATH) . "/includes/install_header.php");

// Run Global Script
require(dirname(ROUTE_SECOND_PATH) . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/config-server" method="post">' . Form::prepare("install-server-config");

echo '
<h3>Update Your Server Configurations:</h3>
<p>Config File: ' . SYS_PATH . '/environment.php</p>
<p style="margin-top:12px;">Make sure the following variables are set appropriately:</p>

<p>
<style>
	.left-tb-col { width:220px; font-weight:bold; text-align:right; padding-right:10px; }
</style>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td class="left-tb-col">Environment:</td>
		<td>' . (ENVIRONMENT ? ENVIRONMENT : '<span style="color:red;">Must assign a valid Environment</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">Server Name:</td>
		<td>' . (SERVER_HANDLE ? SERVER_HANDLE : '<span style="color:red;">Must choose a valid Server Name</span>') . '</td>
	</tr>
</table>
</p>';

if(Validate::pass())
{
	echo '
	<p><input type="submit" name="submit" value="Continue to Next Step" /></p>';
}

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);