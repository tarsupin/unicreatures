<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/uc-static-blocks");
}

// Make sure pet exists
if(!isset($url[2]) or !$pet = MyCreatures::petData((int) $url[2], "id, uni_id, type_id, nickname, experience, total_points, activity, active_until"))
{
	header("Location: /"); exit;
}

// Make sure you own the pet
if($pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

if($isBusy = MyCreatures::isBusy($pet['activity'], (int) $pet['active_until']))
{
	Alert::saveError("Cannot Join Herd", "A pet cannot join a herd while it is busy with another activity.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");

// Join a Herd
if($link = Link::clicked() and $link == "send-to-herd" && isset($_GET['confirm']))
{
	// Get the herd to send to
	if(MyHerds::sendToHerd((int) $pet['id']))
	{
		Alert::saveSuccess("Creature Herded", $pet['nickname'] . ' has successfully joined a herd!');
		
		header("Location: /herds/" . $petType['family']); exit;
	}
}

// Prepare Values
$population = MyHerds::population(Me::$id, $petType['family']);

// Prepare Values
$linkProtect = Link::prepare("send-to-herd");

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
<div id="uc-left-wide">
	<div class="uc-static-block"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></a><div class="uc-bold">' . $pet['nickname'] . '</div><div class="uc-note">Level ' . MyTraining::getLevel((int) $pet['experience']) . ' ' . $petType['name'] . '</div><div style="font-size:0.8em;">' . $pet['total_points'] . ' Evolution Points</div></div>
	<div class="uc-action-block"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['family'], "") . '" /><div class="uc-bold">The ' . $petType['family'] . ' Herd</div><div class="uc-note">Population: ' . $population . '</div></div>
</div>
<div id="uc-right-wide">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	
	<h1>Send ' . $pet['nickname'] . ' to the Herd</h1>
	<div style="color:red; font-size:1.1em;"><span class="icon-flag"></span> Warning: Creatures that get sent to the herd CANNOT be returned, train, play in games, or be used in any other way. They become permanently associated with their family herd.</div>
	<div style="margin-top:22px; font-size:1.1em;">Are you sure you want to send this creature to the herd?</div>
	<div class="uc-action-block">
		<a href="/action/join-herd/' . $pet['id'] . '?confirm=true&' . $linkProtect . '" style="display:block; font-size:1.2em;">I\'m certain - add ' . $pet['nickname'] . ' to the Herd</a>
	</div>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
