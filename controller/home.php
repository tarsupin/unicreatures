<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// If you're a guest, point them to the welcome page.
if(!Me::$loggedIn)
{
	header("Location: /welcome"); exit;
}

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/uc-static-blocks");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Retrieve the list of areas
$areas = MyAreas::areas($userData['uni_id']);

// If there are no plots, generate one automatically for the player (a free meadow)
if(count($areas) < 1)
{
	if(MyAreas::acquireDeed($userData['uni_id'], 1))
	{
		$areas = MyAreas::areas($userData['uni_id']);
	}
}

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
<div id="uc-left">
	' . MyBlocks::avatar($userData) . '
	' . ($userData['uni_id'] == Me::$id ? MyBlocks::inventory() : '') . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '
	
	<div class="area-cube">
		<a href="' . $urlAdd . '/wild"><img src="/assets/areas/wild.png" /></a>
		<div class="uc-bold">The Wild</div>
		<div class="uc-note">Unlimited</div>
	</div>';

foreach($areas as $area)
{
	echo '
	<div class="area-cube">
		<a href="' . $urlAdd . '/area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<div class="uc-bold">' . $area['name'] . '</div>
		<div class="uc-note">' . $area['population'] . ' / ' . $area['max_population'] . '</div>
	</div>';
}

// Display options that only the user can see.
if(Me::$id == $userData['uni_id'])
{
	echo '
	<div class="uc-action-block" style="margin-top:42px;">
		<div class="uc-action-inline"><a href="/treasure-chest"><img src="/assets/icons/button_chest.png" /></a><div class="uc-note-bold">My Treasure</div></div>
		<div class="uc-action-inline"><a href="/action/edit-plots"><img src="/assets/icons/button_area_edit.png" /></a><div class="uc-note-bold">Edit Areas</div></div>
		<div class="uc-action-inline"><a href="/action/sort-plots"><img src="/assets/icons/button_area_move.png" /></a><div class="uc-note-bold">Sort Areas</div></div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
