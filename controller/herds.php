<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active herd
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

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

// Prepare Values
$family = Sanitize::variable($url[1]);

// Check the herd
if(!$herdData = MyHerds::getData($userData['uni_id'], $family))
{
	header("Location: /herd-list"); exit;
}

// Get the list of the user's pets
$herdPets = MyHerds::getPets($userData['uni_id'], $family);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '

<style>
.pet { display:inline-block; padding:8px; text-align:center; }
.pet img { max-height: 120px; }
</style>

<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;">
		<img src="' . ProfilePic::image((int) $userData['uni_id'], "huge") . '" />
		<div class="uc-bold">' . $userData['display_name'] . '</div>
	</div>
	<div class="uc-bold-block">Herd Score: ' . $herdData['score'] . '</div>
</div>
<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . $userData['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>

<h2>' . (Me::$id == $userData['uni_id'] ? 'My ' : $userData['display_name'] . '\'s ') . $family . ' Herd</h2>';

// Cycle through each creature in the herd and display it
foreach($herdPets as $pet)
{
	echo '
	<div class="pet">
		<img src="' . MyCreatures::imgSrc($family, $pet['name'], $pet['prefix']) . '" />
		<div>' . $pet['nickname'] . '</div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
