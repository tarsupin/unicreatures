<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/"); exit;
}

// Make sure the appropriate data is provided
if(!isset($_GET['area']) or !$area = MyAreas::areaData((int) $_GET['area']))
{
	header("Location: /"); exit;
}

// Make sure you own the area
if($area['uni_id'] != Me::$id)
{
	Alert::saveError("No Ownership", "You do not own that land plot.", 9);
	
	header("Location: /"); exit;
}

// Get pets from the area
$pets = MyAreas::areaPets((int) $area['id']);

// Provide Instructions
Alert::info("Click Pet", "Click on the pet you would like to move.");

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

foreach($pets as $pet)
{
	$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $pet['prefix']);
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><a href="/action/sort-pets-to?area=' . $area['id'] . '&s=' . $pet['sort_order'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div class="uc-note">' . ($prefix != "" && $pet['nickname'] == $pet['name'] ? $prefix . " " : "") . ($pet['name'] == "Egg" && $pet['nickname'] == "Egg" ? $pet['family'] . ' Egg' : $pet['nickname']) . (MyCreatures::petRoyalty($pet['prefix']) != "" ? ' <img src="/assets/medals/' . MyCreatures::petRoyalty($pet['prefix']) . '.png" />' : '') . '</div></div>';
	
	// Prepare a line break after this creature if necessary
	if($pet['special'])
	{
		echo '<div></div>';
	}
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
