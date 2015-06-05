<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	header("Location: /"); exit;
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
$score = MyHerds::getScore(Me::$id, $petType['family']);

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

foreach($petType as $key => $val)
	$pet[$key] = (is_numeric($val) ? (int) $val : $val);

echo '
<div id="uc-left">
	<div class="uc-static-block">' . MyBlocks::petPlain($pet, '/pet/' . $pet['id']) . '<div class="uc-note">Evolution Points: ' . $pet['total_points'] . '</div><div class="uc-note">Level: ' . MyTraining::getLevel((int) $pet['experience']) . '</div></div>
	<div class="uc-action-block"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['family'], "") . '" style="max-width:100%;" /><div class="uc-bold">The ' . $petType['family'] . ' Herd</div><div class="uc-note">Pop: ' . $population . ', Score: ' . $score . '</div></div>
</div>
<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
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
