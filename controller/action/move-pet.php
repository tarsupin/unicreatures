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
$pet = MyCreatures::petData((int) $url[2], "id, uni_id, area_id, type_id, nickname, gender, activity, active_until, experience, total_points, date_acquired");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");

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

// Get details about pet
$level = MyTraining::getLevel((int) $pet['experience']);

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
	' . MyBlocks::pet($pet, $petType, Me::$vals['handle']) . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
	<h2>Where would you like to move ' . $pet['nickname'] . '?</h2>';

// List the areas you can move it to
$areas = MyAreas::areas(Me::$id);

foreach($areas as $area)
{
	// Only show the area if you can actually move a pet into it
	if($area['population'] < $area['max_population'] && $area['id'] != $pet['area_id'])
	{
		echo '
		<div class="area-cube">
			<a href="/action/move-pet/' . $pet['id'] . '?area=' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
			<div class="uc-bold">' . $area['name'] . '</div>
			<div class="uc-note">Pop: ' . $area['population'] . ' / ' . $area['max_population'] . '</div>
		</div>';
	}
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
