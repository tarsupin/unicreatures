<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/change-gender"); exit;
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

// Supply List
$supplies = MySupplies::getSupplyList(Me::$id);

$supplies['alchemy'] = (int) $supplies['alchemy'];

// Cost of the Effect
$swapCost = 5;

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");
$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $petType['prefix']);

// If you changed the gender of the pet
if(isset($_GET['gender']) and $value = Link::clicked() and $value == "change-gender-uc")
{
	if($supplies['alchemy'] >= $swapCost)
	{
		// Update the gender of the pet
		Database::startTransaction();
		$supplies['alchemy'] = MySupplies::changeSupplies(Me::$id, "alchemy", -$swapCost);
		Database::query("UPDATE creatures_owned SET gender=? WHERE id=? LIMIT 1", array(($pet['gender'] == "m" ? "f" : "m"), $pet['id']));
		Database::endTransaction();
		
		Alert::saveSuccess("Changed Gender", "You have changed " . $pet['nickname'] . "'s gender to " . ($pet['gender'] == "m" ? "female" : "male") . "!");
		
		// Return to the pet's page with the new gender
		header("Location: /pet/" . $pet['id']); exit;
	}
	else
	{
		Alert::error("No Alchemy", "You don't have enough alchemy ingredients to achieve this effect.");
	}
}

// Prepare Link Protection
$linkProtect = Link::prepare("change-gender-uc");

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

foreach($petType as $key => $val)
	$pet[$key] = (is_numeric($val) ? (int) $val : $val);

echo '
<div id="uc-left">
	<div class="uc-static-block">' . MyBlocks::petPlain($pet, '/pet/' . $pet['id']) . '<div class="uc-note">Evolution Points: ' . $pet['total_points'] . '</div><div class="uc-note">Level: ' . MyTraining::getLevel((int) $pet['experience']) . '</div></div>
	' . MyBlocks::inventory() . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
	<div>
		<div class="uc-bold">Are you sure you want to change ' . $pet['nickname'] . '\'s gender to ' . ($pet['gender'] == 'm' ? 'female' : 'male') . '?</div>
		
		<div>This effect will require ' . $swapCost . ' alchemy ingredients.</div>
		
		<div class="uc-action-block"><a href="/action/change-gender/' . $pet['id'] . '?gender=true&' . $linkProtect . '" style="display:block; padding:4px;">Yes, change ' . ($pet['gender'] == 'm' ? 'his' : 'her') . ' gender to ' . ($pet['gender'] == 'm' ? 'female' : 'male') . '.</a></div>
	</div>
	
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
