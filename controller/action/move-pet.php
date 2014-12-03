<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/move-pet"); exit;
}

// Make sure pet exists
if(!isset($url[2]))
{
	header("Location: /"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData($url[2], "id, area_id, uni_id, type_id, nickname, gender, experience, total_points, date_acquired");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");

// If you moved the pet into an area
if(isset($_GET['area']))
{
	$areaData = MyAreas::areaData((int) $_GET['area'], "*");
	
	// Make sure the area exists and that you own it
	if($areaData && $areaData['uni_id'] == Me::$id)
	{
		// Move the pet
		if(MyAreas::movePet((int) $pet['id'], (int) $areaData['id']))
		{
			Alert::saveSuccess("Moved Pet", "You have moved " . $pet['nickname'] . " to " . $areaData['name'] . "!");
			
			$pet['area_id'] = $areaData['id'];
			
			// Go to Area
			header("Location: /area/" . $areaData['id']); exit;
		}
	}
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">
' . Alert::display();

echo '
<style>
.area { display:inline-block; padding:8px; text-align:center; }
</style>';

echo '
<div class="pet">
	<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
	<br />' . $pet['nickname'] . '
	<br />' . ($pet['gender'] == "m" ? "Male" : "Female") . '
</div>';

// List the areas you can move it to
$areas = MyAreas::areas(Me::$id);

foreach($areas as $area)
{
	// Only show the area if you can actually move a pet into it
	if($area['population'] < $area['max_population'] && $area['id'] != $pet['area_id'])
	{
		echo '
		<div class="area">
			<a href="/action/move-pet/' . $pet['id'] . '?area=' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
			<br />' . $area['name'] . '
		</div>';
	}
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
