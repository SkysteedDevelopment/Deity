<?php 

// End all user sessions and cookies for the server
Logout::server();

// Log out of the UniFaction's Auth system
header("Location: " . URL::auth_unifaction_com() . "/logout?ret=" . urlencode(URL_PREFIX . FULL_DOMAIN)); exit;