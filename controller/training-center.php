<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the user is logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/training-center");
}

// Get a list of pets actively training
$petList = MyCreatures::activityList(Me::$id, "training");

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

<h2>The Training Center</h2>';

foreach($petList as $pets)
{
	echo '
	<div class="pet">
		<a href="/pet/' . $pets['id'] . '"><img src="' . MyCreatures::imgSrc($pets['family'], $pets['name'], $pets['prefix']) . '" /></a>
		<div class="pet-name">' . ($pets['name'] == "Egg" ? $pets['family'] . " Egg" : $pets['name']) . '</div>
		<div class="pet-note">Training ends ' . Time::fuzzy((int) $pets['active_until']) . '</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
