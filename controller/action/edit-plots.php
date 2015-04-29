<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/edit-plots"); exit;
}

// Get Land Plots
$areas = MyAreas::areas(Me::$id);

// Leave page if you have no plots available
if(count($areas) <= 1)
{
	Alert::saveError("No Plots", "You don't currently have any plots that you can edit.");
	
	header("Location: /"); exit;
}

// Provide Info
Alert::info("Click Plot", "Click on the plot that you would like to rename or upgrade.");

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
	' . MyBlocks::avatar(Me::$vals) . '
	' . MyBlocks::inventory(Me::$id) . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]);

foreach($areas as $area)
{
	echo '
	<div class="area-cube">
		<a href="/action/edit-area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<div class="uc-bold">' . $area['name'] . '</div>
		<div class="uc-note">Pop: ' . $area['population'] . ' / ' . $area['max_population'] . '</div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
