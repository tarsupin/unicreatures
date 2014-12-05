<?php

/****** Preparation ******/
define("CONF_PATH",		dirname(__FILE__));
define("SYS_PATH", 		dirname(CONF_PATH) . "/system");

// Load phpTesla
require(SYS_PATH . "/phpTesla.php");

if(ENVIRONMENT == "production")
{
	if(isset($_GET['u6access']))
	{
		$_SESSION['u6access'] = true;
	}

	if(!isset($_SESSION['u6access']))
	{
		header("Location: http://orig.unicreatures.com"); exit;
	}
}

// Prepare Value
$urlAdd = "";

// Initialize Active User
Me::$getColumns = "uni_id, role, clearance, handle, display_name, date_joined, avatar_opt";

Me::initialize();

// Determine which page you should point to, then load it
require(SYS_PATH . "/routes.php");

/****** Dynamic URLs ******/
// If a page hasn't loaded yet, check if there is a dynamic load
if($url[0] != '')
{
	require(APP_PATH . '/controller/profile.php'); exit;
}
//*/

/****** 404 Page ******/
// If the routes.php file or dynamic URLs didn't load a page (and thus exit the scripts), run a 404 page.
require(SYS_PATH . "/controller/404.php");