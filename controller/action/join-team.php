<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/uc-static-blocks");
}

// Make sure pet exists
if(!isset($url[2]))
{
	header("Location: /"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData($url[2], "id, uni_id, type_id, nickname, experience, total_points, activity, active_until");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /uc-static-blocks"); exit;
}

if($isBusy = MyCreatures::isBusy($pet['activity'], $pet['active_until']))
{
	Alert::saveError("Cannot Join Herd", "Cannot join a herd while busy.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "family, name, prefix");

// Join a Herd
if($link = Link::clicked() and $link == "send-to-herd" && isset($_GET['to']))
{
	$_GET['to'] = $_GET['to'] + 0;
	
	// Get the herd to send to
	if(MyHerds::sendToHerd($pet['id'], $_GET['to']))
	{
		Alert::saveSuccess("Creature Herded", $pet['nickname'] . ' has successfully joined a herd!');
		
		header("Location: /herds/" . $_GET['to']); exit;
	}
}

// Get your list of herds
$herds = MyHerds::userHerds(Me::$id);

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
	<div id="pet"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></a><div class="uc-bold">' . $pet['nickname'] . '</div><div class="uc-note">Level ' . MyTraining::getLevel($pet['experience']) . ' ' . $petType['name'] . '</div></div>
	<div id="pet-blurb">Choose a herd to add this pet to.</div>
	<div id="pet-details" style="text-align:center; font-size:1.2em;">
		<a href="/action/start-herd/' . $pet['id'] . '" style="display:block;">Start a new herd with ' . $pet['nickname'] . '</a>
	</div>
</div>
<div id="uc-right-wide">';

if(count($herds) > 0)
{
	// List each herd
	foreach($herds as $herd)
	{
		echo '
		<div class="pet-cube">
			<div class="pet-cube-inner"><a href="/action/join-herd/' . $pet['id'] . '?to=' . $herd['id'] . '&' . $linkProtect . '"><img src="' . $herd['image'] . '" /></a></div>
			<div>' . $herd['name'] . '</div>
		</div>';
	}
}
else
{
	echo "You currently have no herds.";
}

echo '
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
