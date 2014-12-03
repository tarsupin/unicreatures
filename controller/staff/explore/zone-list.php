<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Get List of Explore Zones
$zones = MyExplore::zoneList();

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">
' . Alert::display() . '

<style>
.zone { display:inline-block; padding:8px; text-align:center; }
</style>

<h2>Edit which exploration zone creatures?</h2>';

foreach($zones as $key => $zone)
{
	echo '
	<div class="zone">
		<a href="/staff/explore/pet-list/' . $key . '"><img src="/assets/explore_zones/' . $key. '.png" /></a>
		<br />' . $zone['title']  . '
		<br />Level: ' .  $zone['achievements'] . '
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");