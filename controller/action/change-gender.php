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
$pet = MyCreatures::petData((int) $url[2], "id, uni_id, type_id, nickname, gender");

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
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");

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
	</div>
	
	<div>
		<div class="uc-bold">Are you sure you want to change ' . $pet['nickname'] . '\'s gender to ' . ($pet['gender'] == 'm' ? 'female' : 'male') . '?</div>
		
		<div>This effect will require ' . $swapCost . ' alchemy ingredients.</div>
		
		<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
		
		<div class="uc-action-block"><a href="/action/change-gender/' . $pet['id'] . '?gender=true&' . $linkProtect . '" style="display:block; padding:4px;">Yes, change ' . ($pet['gender'] == 'm' ? 'his' : 'her') . ' gender to ' . ($pet['gender'] == 'm' ? 'female' : 'male') . '.</a></div>
	</div>
	
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
