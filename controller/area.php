<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure area exists
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Get necessary area data
if(!$area = MyAreas::areaData((int) $url[1]))
{
	header("Location: /"); exit;
}

// Get the necessary user data
if(!$userData = User::get((int) $area['uni_id'], "uni_id, handle, display_name, avatar_opt"))
{
	header("Location: /"); exit;
}

// Get pets from the area
$pets = MyAreas::areaPets((int) $area['id']);

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
	<div class="uc-static-block"><img src="/assets/areas/' . $area['type'] . '.png" /><div class="uc-bold">' . $area['name'] . '</div><div class="uc-note">' . $area['population'] . ' / ' . $area['max_population'] . '</div></div>
	' . MyBlocks::avatar($userData);

if(Me::$id == $userData['uni_id'])
{
	echo '
	<div class="uc-bold-block"><a href="/action/sort-pets?area=' . $area['id'] . '">Sort Pets in this Area</a></div>
	<div class="uc-bold-block"><a href="/action/edit-area/' . $area['id'] . '">Edit Area</a></div>';
}

echo '
</div>
<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]);

if(!$pets)
{
	echo '
	<div>There are no pets in the "' . $area['name'] . '" area right now.</div>';
}

foreach($pets as $pet)
{	
	echo MyBlocks::petPlain($pet, '/pet/' . $pet['id']);
	
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
