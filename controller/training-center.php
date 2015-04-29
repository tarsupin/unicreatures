<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/training-center");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Get a list of pets actively training
$petList = MyCreatures::activityList($userData['uni_id'], "training");

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo '
<style>
	.pet { display:inline-block; text-align:center; padding:12px; }
	.pet-name { font-weight:bold; font-size:1.1em; }
	.pet-note { font-size:0.9em; }
</style>

<div id="uc-left">
	' . MyBlocks::avatar($userData) . '
	<div class="uc-static-block hide-600" style="margin-top:0px;"><img src="/assets/npcs/echo_trainer.png" /><div class="uc-bold">Echo</div><div class="uc-note">The Trainer</div></div>
</div>

<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '
	
	<h2>The Training Center</h2>
	<div style="margin-bottom:16px;">Send pets to the training center to advance their skill level and attributes.</div>';

if(!$petList)
{
	echo '
	<div>' . (Me::$id == $userData['uni_id'] ? 'You do' : $userData['display_name'] . ' does') . ' not have any pets in the training center at this time.</div>';
}

foreach($petList as $pet)
{
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div class="uc-bold">' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . '</div><div class="pet-note">Done ' . Time::fuzzy((int) $pet['active_until']) . '</div></div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
