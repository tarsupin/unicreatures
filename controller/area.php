<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure area exists
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Get necessary area data
if(!$area = MyAreas::areaData($url[1]))
{
	header("Location: /"); exit;
}

// Get the necessary user data
if(!$userData = User::get((int) $area['uni_id'], "uni_id, handle, display_name, avatar_opt"))
{
	header("Location: /"); exit;
}

// Get pets from the area
$pets = MyAreas::areaPets($area['id']);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display();

echo '
<div id="plot-page-left">
	<div id="land-plot"><img src="/assets/areas/' . $area['type'] . '.png" /><div class="lp-bold">' . $area['name'] . '</div><div class="lp-note">' . $area['population'] . ' / ' . $area['max_population'] . '</div></div>
	<div id="lp-caretaker"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /><div class="lp-bold">' . $userData['display_name'] . '</div></div>';

if(Me::$id == $userData['uni_id'])
{
	echo '
	<div style="margin-top:10px; text-align:center; font-size:1.1em;"><a href="/action/sort-pets?area=' . $area['id'] . '">Sort Pets in this Area</a></div>
	<div style="margin-top:10px; text-align:center; font-size:1.1em;"><a href="/action/edit-area/' . $area['id'] . '">Edit Area</a></div>';
}

echo '
</div>
<div id="plot-page-right">';

foreach($pets as $pet)
{
	echo '
	<div class="plot-pet"><div class="plot-pet-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div>' . $pet['nickname'] . '</div></div>';
	
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
