<?php

// Server Configurations

/*
	Choose the appropriate environment for your server.
	
	By default, the engine provides three available environments:
		
		1. The "production" environment is your live server, where your final product is visible.
		2. The "staging" environment is your staging server, where you're testing it to be production-ready.
		3. The "local" environment is on your own personal computer.
*/
define("ENVIRONMENT", "local");

// Set the handle that this server is recognized by (i.e. the name of the server)
define("SERVER_HANDLE", "PersonalComp");

// Set a global salt used on this server
// Note: This is only one part of the salts used on your applications.
// It will be used for Cookies, Forms, etc - it does not permanently fix to anything (such as for passwords)
// Try to keep this value between 60 - 70 characters long
define("SERVER_SALT", "D:0E;EWj~ojFWIZ1jRO5nDdVNtc2ghquF:A;5kn-Wdn~-.Yk5p6avd14go_DHcpA-|6.TlVweqymD+yE:jBr");
//						|    5   10   15   20   25   30   35   40   45   50   55   60   65   |

// Does this server use HHVM as it's web server? If so, it can take advantage of the HACK language.
// Setting this to true will use the HHVM files rather than PHP where applicable
define("USE_HHVM" ,(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ? true : false));

// Prepare the Database Configurations
switch(ENVIRONMENT)
{
	// Production Database Configurations
	case "production":
		
		define("DATABASE_USER", "uni6user");
		define("DATABASE_PASS", "ylHoQSJiqzMJAqwFe6dWIk32dUhi7QzIusEC4t8QwizGf4LSBI30SpE");
		
		define("DATABASE_ADMIN_USER", "root");
		define("DATABASE_ADMIN_PASS", "1mA4aR5pgkYB4mc37Vn7zAJk4OmkI2AHloj5pkgiUHtapc3ERAPN9dAgaNDhuGeaiQj");
		
		define("DATABASE_HOST", "127.0.0.1");
		define("DATABASE_ENGINE", "mysql");
		
		break;
		
	// Development Database Configurations
	case "staging":
	case "development":
		
		define("DATABASE_USER", "uni6user");
		define("DATABASE_PASS", "WhereDidItGoGeorge?500questions!");
		
		define("DATABASE_ADMIN_USER", "root");
		define("DATABASE_ADMIN_PASS", "password");
		
		define("DATABASE_HOST", "127.0.0.1");
		define("DATABASE_ENGINE", "mysql");
		
		break;
		
	// Production Database Configurations
	case "local":
	default:
		
		define("DATABASE_USER", "root");
		define("DATABASE_PASS", "password");
		
		define("DATABASE_ADMIN_USER", "root");
		define("DATABASE_ADMIN_PASS", "password");
		
		define("DATABASE_HOST", "127.0.0.1");
		define("DATABASE_ENGINE", "mysql");
		
		break;
}
