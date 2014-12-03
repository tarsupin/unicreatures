<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/staff"); exit;
}

// Confirm Clearance
if(Me::$clearance < 5)
{
	header("Location: /"); exit;
}
