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
$pet = MyCreatures::petData((int) $url[2], "id, uni_id, area_id, type_id, nickname, gender, total_points");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix, required_points");

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
MyCreatures::changePetType((int) $pet['id'], (int) $newType['id']);

// Update the Pet Name, if original was default
if($pet['nickname'] == $petType['name'])
{
	$pet['nickname'] = $newType['name'];
	
	Database::query("UPDATE creatures_owned SET nickname=? WHERE id=? LIMIT 1", array($pet['nickname'], $pet['id']));
}

// Make sure your achievements reflect this evolution
MyAchievements::set(Me::$id, $petType['family'], "evolutions", (int) $newType['evolution_level']);

// Get Area Data
$areaData = MyAreas::areaData((int) $pet['area_id']);

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
<style>
.uc-action-inline img { max-width:90px; max-height:110px; }
</style>

<div id="uc-left-wide">
	<div class="uc-static-block">
		<img src="' . MyCreatures::imgSrc($newType['family'], $newType['name'], $newType['prefix']) . '" />
	</div>
	<div class="uc-bold-block">' . $petType['name'] . ' has evolved into ' . $newType['name'] . '!</div>
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($newType['family'], $newType['name'], $newType['prefix']) . '" style="max-width:100%; max-height:120px;" /></a><div class="uc-note-bold">To Pet</div><div class="uc-note">' . $pet['nickname'] . '</div></div>';
	
	if($areaData)
	{
		echo '
		<div class="uc-action-inline"><a href="/area/' . $areaData['id'] . '"><img src="/assets/areas/' . $areaData['type'] . '.png" style="max-width:100%;" /></a><div class="uc-note-bold">To Area</div><div class="uc-note">' . $areaData['name'] . '</div></div>';
	}
	
	echo '
		<div class="uc-action-inline"><a href="/wild"><img src="/assets/areas/wild.png" style="max-width:100%;" /></a><div class="uc-note-bold">To Wild</div><div class="uc-note">&nbsp;</div></div>
	</div>
</div>
<div id="uc-right-wide">
	<div class="uc-action-block">
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_hut.png" /><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	' . nl2br(MyCreatures::descMarkup($newType['description'], $newType['name'], $pet['gender'])) . '
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");

