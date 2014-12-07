<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/"); exit;
}

// Make sure the appropriate data is provided
if(!isset($_GET['area']) or !isset($_GET['s']) or !$area = MyAreas::areaData((int) $_GET['area']))
{
	header("Location: /"); exit;
}

// Make sure you own the area
if($area['uni_id'] != Me::$id)
{
	Alert::saveError("No Ownership", "You do not own that land plot.", 9);
	
	header("Location: /"); exit;
}

// Get the active pet data (the bet being moved)
if(!$activePet = MyCreatures::petDataBySortID((int) $area['id'], (int) $_GET['s']))
{
	header("Location: /"); exit;
}

// Update the plot location
if(isset($_GET['to']))
{
	// Prepare Values
	$positionFrom = (int) $_GET['s'];
	$positionTo = (int) $_GET['to'];
	
	$low = min($positionFrom, $positionTo);
	$high = max($positionFrom, $positionTo);
	
	$shift = $positionFrom > $positionTo ? 1 : -1;
	
	// Begin the positioning
	Database::startTransaction();
	
	if($pass = Database::query("UPDATE creatures_area SET sort_order=? WHERE area_id=? AND sort_order=? LIMIT 1", array(0, $area['id'], $positionFrom)))
	{
		if($pass = Database::query("UPDATE creatures_area SET sort_order=sort_order+? WHERE area_id=? AND sort_order >= ? AND sort_order <= ?", array($shift, $area['id'], $low, $high)))
		{
			$pass = Database::query("UPDATE creatures_area SET sort_order=? WHERE area_id=? AND sort_order=? LIMIT 1", array($positionTo, $area['id'], 0));
		}
	}
	
	if(Database::endTransaction($pass))
	{
		Alert::saveSuccess("Creature Moved", 'You have successfully moved "' . $activePet['nickname'] . '"!');
		
		header("Location: /action/sort-pets?area=" . $area['id']); exit;
	}
}

// Get pets from the area
$pets = MyAreas::areaPets((int) $area['id']);

// Supply List
$supplies = MySupplies::getSupplyList(Me::$id);

// Provide Instructions
Alert::info("Click Location", "Click the location that you would like to move \"" . $activePet['nickname'] . "\" to.");

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
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . '"><img src="' . (Me::$vals['avatar_opt'] ? Avatar::image((int) Me::$id, (int) Me::$vals['avatar_opt']) : ProfilePic::image((int) Me::$id, "huge")) . '" /></a><div class="uc-bold">' . Me::$vals['display_name'] . '</div></div>
	
	<div class="uc-action-block hide-600">
		<div class="supply-block"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note">' . number_format($supplies['components']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note">' . number_format($supplies['coins']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note">' . number_format($supplies['crafting']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note">' . number_format($supplies['alchemy']) . '</div></div>
	</div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>';

foreach($pets as $pet)
{
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><a href="/action/sort-pets-to?area=' . $area['id'] . '&s=' . $_GET['s'] . '&to=' . $pet['sort_order'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" style="max-width:50%;" /></a></div><div>' . $pet['nickname'] . '</div></div>';
	
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
