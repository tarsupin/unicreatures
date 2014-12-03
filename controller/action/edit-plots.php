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
	
	header("Location: /land-plots"); exit;
}

Alert::info("Click Plot", "Click on the plot that you would like to rename or upgrade.");

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display() . '

<style>
.area { display:inline-block; padding:8px; text-align:center; }
</style>';

foreach($areas as $area)
{
	echo '
	<div class="area">
		<a href="/action/edit-area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<div class="lp-bold">' . $area['name'] . '</div>
		<div class="lp-note">Pop: ' . $area['population'] . ' / ' . $area['max_population'] . '</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
