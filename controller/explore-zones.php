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
<h3>Exploration Zones</h3>
<div>Achievements: ' . $achievements . '</div>
<div>Current Energy: ' . $energy . '</div>
<div style="margin-top:12px;">Click on the exploration zone you would like to explore:</div>

<div class="area-cube">
	<a href="/explore-rush"><img src="/assets/explore_zones/great_plains.png" /></a>
	<div>Speed Run</div>
	<div class="uc-note">&nbsp;</div>
</div>';

foreach($zones as $key => $zone)
{
	echo '
	<div class="area-cube">
		<a href="/explore/' . $key . '?' . Link::prepare($key) . '"><img src="/assets/explore_zones/' . $key. '.png" /></a>
		<div><span' . ($achievements < $zone['achievements'] ? ' style="color:red"' : '') . '>' . $zone['title']  . ' <a href="javascript:viewExplore(\'' . $key . '\');"><span class="icon-circle-info"></span></a></span></div>
		<div class="uc-note">' .  ($achievements < $zone['achievements'] ? $zone['achievements'] . '+' : '&nbsp;') . '</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
