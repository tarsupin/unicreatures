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

<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="uc-bold">' . $userData['display_name'] . '</div></div>
	<div class="uc-static-block hide-600" style="margin-top:0px;"><img src="/assets/npcs/echo_trainer.png" /><div class="uc-bold">Echo</div><div class="uc-note">The Trainer</div></div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . $userData['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_course.png" /><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	
	<h2>The Training Center</h2>
	<div style="margin-bottom:16px;">Send pets to the training center to advance their skill level and attributes.</div>';

if(!$petList)
{
	echo '
	<div>You do not have any pets in the training center at this time.</div>';
}

foreach($petList as $pet)
{
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div class="uc-bold">' . $pet['nickname'] . '</div><div class="pet-note">Done ' . Time::fuzzy((int) $pet['active_until']) . '</div></div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
