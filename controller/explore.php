<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/explore-zones"); exit;
}

// Prepare Variables
$zone = $url[1];
$zoneList = MyExplore::zoneList();
$totalAchievements = MyAchievements::getScore(Me::$id);

// Make sure you're in a valid zone
if(!isset($zoneList[$zone]))
{
	header("Location: /explore-zones");
}

// Make sure you have enough achievements
if($zoneList[$zone]['achievements'] > $totalAchievements)
{
	Alert::saveError("Insufficient Achievements", "You need more achievements to access that area.");
	
	header("Location: /explore-zones"); exit;
}

// Get a random encounter for this zone
if(!$encounter = MyExplore::encounter($zone))
{
	header("Location: /explore-zones"); exit;
}

// Prepare Values
MyTreasure::$exploreZone = $zone;
$treasureData = false;
$explored = false;

// Check if Exploration Value was set
if($link = Link::clicked() and $link == $zone)
{
	$treasureData = MyTreasure::acquire(Me::$id);
	
	if(!$energy = MyEnergy::change(Me::$id, -1))
	{
		Alert::saveError("No Energy", "You don't have enough energy to explore right now.");
		
		header("Location: /explore-zones"); exit;
	}
	
	$explored = true;
}
else
{
	$energy = MyEnergy::get(Me::$id);
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" style="overflow:hidden;">' . Alert::display();

// Can see if there are any pets here
// $encounter['explore_id']

echo '
<div style="float:left; width:200px;">
	<div style="text-align:center; background-color:#abcdef; border-radius:6px; width:100%; margin:0 12px 12px 0; font-size:1.2em; vertical-align:middle;">
		<a href="/explore/' . $zone . '?' . Link::prepare($zone) . '" style="display:block; width:100%; vertical-align:middle; padding:20px 0 20px 0;">' . ($explored ? "Continue" : "Start") . '<br />Exploring</a>
	</div>
	<div style="font-weight:bold; text-align:center; font-size:1.1em;">Current Energy: ' . $energy . '</div>';

// If you've located treasure on this search
if($treasureData)
{
	echo '
	<div style="text-align:center; background-color:#cdedef; border-radius:6px; width:100%; padding:10px 0 10px 0; margin-top:10px;">
		<div style="font-weight:bold; font-size:1.1em; margin-bottom:10px;">Found Something!</div>';
	
	if($treasureData['type'] == "pet")
	{
		echo '
		<div style="text-align:center;">
			<img src="' . $treasureData['image'] . '" /><br />
			' . $treasureData['title'] . '
			<div style="font-size:0.8em; margin-top:20px;">This pet has been added to your treasure queue.</div>
		</div>';
	}
	else
	{
		echo '
		<div style="text-align:center;">
			<img src="/assets/supplies/' . $treasureData['image'] . '" /><br />
			' . $treasureData['title'] . '
			<div style="font-size:0.8em;">Current: ' . $treasureData['count'] . '</div>
		</div>';
	}
	
	echo '
	</div>';
}

echo '
</div>
<div style="margin-left:210px;">';

if($explored)
{
	echo '
	<h2>' . $encounter['title'] . '</h2>
	' . $encounter['description'] . '
	' . $encounter['history'] . '';
}
else
{
	echo '
	<h2>Explore</h2>
	<p>Begin your exploration!</p>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
