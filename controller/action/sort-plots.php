<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/sort-plots"); exit;
}

// Prepare Values
$_GET['from'] = (isset($_GET['from']) ? max(1, $_GET['from'] + 0) : false);
$_GET['to'] = (isset($_GET['to']) ? max(1, $_GET['to'] + 0) : false);

Alert::info("Plot Movement", "Click the " . ($_GET['from'] ? "location to move this plot." : "plot you would like to move."));

// Get Land Plots
$areas = MyAreas::areas(Me::$id);

// Leave page if you have no plots available
if(count($areas) <= 1)
{
	Alert::saveError("No Plots", "You need more plots to reorder them.");
	
	header("Location: /"); exit;
}

// Update the plot location
if($link = Link::clicked() and $link == "move-plot")
{
	if(MyAreas::relocate(Me::$id, $_GET['from'], $_GET['to']))
	{
		Alert::saveSuccess("Sort Plot", "You have resorted the plots!");
		
		header("Location: /action/sort-plots"); exit;
	}
}

// Prepare Values
$linkProtect = Link::prepare("move-plot");

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
	' . MyBlocks::inventory() . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]);

foreach($areas as $area)
{
	echo '
	<div class="area-cube">
		<a href="/action/sort-plots?' . ($_GET['from'] ? 'from=' . $_GET['from'] . '&to=' . $area['id'] . '&' . $linkProtect : 'from=' . $area['id'] ) . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<br />' . $area['name'] . '
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
