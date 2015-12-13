<?php

// Make sure the user has named the site
if(!defined("SITE_HANDLE"))
{
	Alert::error("Invalid Site Name", "You must provide a valid Site Name.");
}

// Make sure the database is named
else if(!defined("DATABASE_NAME"))
{
	Alert::error("Improper DB Name", "You must provide a valid Database Name.");
}

// Make sure that there is a valid application path
if(!defined("APP_PATH"))
{
	Alert::error("Improper App Path", "You must set a valid application or application path.");
}
else if(!Dir::exists(APP_PATH))
{
	Alert::error("Invalid App Path", "You must set a valid application or application path.");
}

// If the server configuration are acceptable
if(Validate::pass())
{
	// Check if the form was submitted (to continue to the next page)
	if(Form::submitted("install-site-config"))
	{
		header("Location: /install/config-database"); exit;
	}
	
	Alert::success("Site Config", "Your site configurations are valid!");
}

// Installation Header
require(dirname(ROUTE_SECOND_PATH) . "/includes/install_header.php");

// Run Global Script
require(dirname(ROUTE_SECOND_PATH) . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/config-site" method="post">' . Form::prepare("install-site-config");

echo '
<h3>Update Your Site Configurations:</h3>
<p>Config File: ' . PUBLIC_PATH . '/index.php</p>
<p style="margin-top:12px;">Make sure the following values are set properly:</p>

<p>
<style>
	.left-tb-col { width:220px; font-weight:bold; text-align:right; padding-right:10px; }
</style>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td class="left-tb-col">Site Name:</td>
		<td>' . (SITE_HANDLE ? SITE_HANDLE : '<span style="color:red;">Must set SITE_HANDLE</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">Application Path:</td>
		<td>' . (APP_PATH ? APP_PATH : '<span style="color:red;">Must point to a valid APP_PATH</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">A Valid Database Name:</td>
		<td>' . (DATABASE_NAME ? DATABASE_NAME : '<span style="color:red;">Must set a valid DATABASE_NAME</span>') . '</td>
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