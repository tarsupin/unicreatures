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
	Alert::saveError("Cannot Join Team", "A pet cannot join a team while it is busy with another activity.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");

// Join a Herd
if($link = Link::clicked() and $link == "send-to-herd" && isset($_GET['to']))
{
	$_GET['to'] = $_GET['to'] + 0;
	
	// Get the herd to send to
	if(MyTeams::sendToHerd((int) $pet['id'], (int) $_GET['to']))
	{
		Alert::saveSuccess("Creature Herded", $pet['nickname'] . ' has successfully joined a team!');
		
		header("Location: /teams/" . $_GET['to']); exit;
	}
}

// Get your list of herds
$herds = MyTeams::userHerds(Me::$id);

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
</div>
<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
	<h1>Let ' . $pet['nickname'] . ' join a Team</h1>';
	
if(count($herds) > 0)
{
	// List each herd
	foreach($herds as $herd)
	{
		echo '
		<div class="pet-cube">
			<div class="pet-cube-inner"><a href="/action/join-team/' . $pet['id'] . '?to=' . $herd['id'] . '&' . $linkProtect . '"><img src="' . $herd['image'] . '" /></a></div>
			<div>' . $herd['name'] . '</div>
		</div>';
	}
}
else
{
	echo "You currently have no teams.";
}
	
echo '
	<div class="uc-action-block">
		<a href="/action/start-team/' . $pet['id'] . '" style="display:block; font-size:1.2em;">Start a new Team with ' . $pet['nickname'] . '</a>
	</div>
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
