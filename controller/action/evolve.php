<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/evolve"); exit;
}

// Make sure pet exists
if(!isset($url[2]))
{
	header("Location: /"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData($url[2], "id, uni_id, type_id, nickname, gender, total_points");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "family, name, prefix, required_points");

$evolvedTypes = Database::selectMultiple("SELECT id, description, family, name, prefix, evolution_level FROM creatures_types WHERE evolves_from=?", array($pet['type_id']));

// If there are no creatures that can evolve from this type
if(count($evolvedTypes) == 0)
{
	Alert::saveSuccess("Cannot Evolve", "This pet has reached its final evolution.");
	header("Location: /pet/" . $pet['id']); exit;
}

// If there is more than one creature that can evolve from this type, go choose which type
else if(count($evolvedTypes) > 1)
{
	header("Location: /action/evolve-choose/" . $pet['id']); exit;
}

// Determine amount of points to evolve to this level
if($pet['total_points'] < $petType['required_points'])
{
	Alert::saveSuccess("Cannot Evolve", "This pet doesn't have enough points to evolve.");
	header("Location: /pet/" . $pet['id']); exit;
}

// Update the Pet Type
$newType = $evolvedTypes[0];
MyCreatures::changePetType($pet['id'], (int) $newType['id']);

// Update the Pet Name, if original was default
if($pet['nickname'] == $petType['name'])
{
	$pet['nickname'] = $newType['name'];
	
	Database::query("UPDATE creatures_owned SET nickname=? WHERE id=? LIMIT 1", array($pet['nickname'], $pet['id']));
}

// Make sure your achievements reflect this evolution
MyAchievements::set(Me::$id, $petType['family'], "evolutions", (int) $newType['evolution_level']);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" style="overflow:hidden;">' . Alert::display();

echo '
<div id="pet-page-left">
	<div id="pet">
		<img src="' . MyCreatures::imgSrc($newType['family'], $newType['name'], $newType['prefix']) . '" />
	</div>
	<div id="pet-blurb">' . $petType['name'] . ' has evolved into ' . $newType['name'] . '!</div>
	<div id="pet-details" style="text-align:center; font-size:1.2em;">
		<a href="/pet/' . $pet['id'] . '" style="display:block;">Return to Pet</a>
	</div>
</div>
<div id="pet-page-right">' . nl2br(MyCreatures::descMarkup($newType['description'], $newType['name'], $pet['gender'])) . '</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");

