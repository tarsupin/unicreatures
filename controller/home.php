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

// Supply List
$supplies = MySupplies::getSupplyList($userData['uni_id']);

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
.dual-col-item { display:inline-block; width:45%; padding:1%; margin-bottom:12px; }
.area { display:inline-block; padding:8px; text-align:center; }
</style>

<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="uc-bold">' . $userData['display_name'] . '</div></div>
	
	<div class="uc-action-block hide-600">
		<div class="dual-col-item"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note">' . number_format($supplies['components']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note">' . number_format($supplies['coins']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note">' . number_format($supplies['crafting']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note">' . number_format($supplies['alchemy']) . '</div></div>
	</div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_hut.png" /><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . $userData['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	
	<div class="area">
		<a href="' . $urlAdd . '/wild"><img src="/assets/areas/wild.png" /></a>
		<div class="uc-bold">Wild Area</div>
		<div class="uc-note">Unlimited</div>
	</div>';

foreach($areas as $area)
{
	echo '
	<div class="area">
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
		<div class="uc-action-inline"><a href="' . $urlAdd . '/treasure-chest"><img src="/assets/icons/button_chest.png" /></a><div class="uc-note-bold">My Treasure</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/"><img src="/assets/icons/button_area_edit.png" /></a><div class="uc-note-bold">Edit Areas</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/"><img src="/assets/icons/button_area_move.png" /></a><div class="uc-note-bold">Sort Areas</div></div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
