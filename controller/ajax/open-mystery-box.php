<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// The user must be logged in
if(!Me::$loggedIn)
{
	exit;
}

if($treasure = MyTreasure::openMysteryBox(Me::$id))
	echo json_encode($treasure); exit;