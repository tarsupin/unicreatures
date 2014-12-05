<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get List of Explore Zones
$zones = MyExplore::zoneList();

// Get my achievements
if(!$achievements = MySupplies::getSupplies(Me::$id, "achievements"))
{
	$achievements = 0;
}

// Get a list of the user's supplies
$energy = MyEnergy::get(Me::$id);

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
.zone { display:inline-block; padding:8px; text-align:center; }
</style>

<h3>Exploration Zones</h3>
<div>Achievements: ' . $achievements . '</div>
<div>Current Energy: ' . $energy . '</div>
<div style="margin-top:12px;">Click on the exploration zone you would like to explore:</div>

<div class="zone">
	<a href="/explore-rush"><img src="/assets/explore_zones/great_plains.png" /></a>
	<br />Speed Run<br />&nbsp;
</div>';

foreach($zones as $key => $zone)
{
	echo '
	<div class="zone">
		<a href="/explore/' . $key . '"><img src="/assets/explore_zones/' . $key. '.png" /></a>
		<br /><span' . ($achievements < $zone['achievements'] ? ' style="color:red"' : '') . '>' . $zone['title']  . '</span>
		' .  ($achievements < $zone['achievements'] ? '<br />' . $zone['achievements'] . '+' : '<br />&nbsp;') . '
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
