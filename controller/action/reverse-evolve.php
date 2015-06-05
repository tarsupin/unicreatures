<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/reverse-evolve"); exit;
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

// Cost of the Effect
$swapCost = 10;
$alchemy = MySupplies::getSupplies(Me::$id, "alchemy");

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");
$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $petType['prefix']);

if($petType['evolves_from'] == 0)
{
	Alert::saveSuccess("Cannot Devolve", "You cannot reverse evolve this pet.");
	header("Location: /pet/" . $pet['id']); exit;
}

// If you reversed this pet's evolution
if(isset($_GET['reverse']) and $value = Link::clicked() and $value == "reverse-evolve-uc")
{
	if($alchemy >= $swapCost)
	{
		// Get the Changed Type
		$newType = MyCreatures::petTypeData((int) $petType['evolves_from'], "id, family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");
		
		if($newType)
		{
			// Reverse this pet's evolution
			Database::startTransaction();
			$alchemy = MySupplies::changeSupplies(Me::$id, "alchemy", $swapCost);
			Database::query("UPDATE creatures_owned SET type_id=? WHERE id=? LIMIT 1", array($newType['id'], $pet['id']));
			// Update the Pet Name, if original was default
			if($pet['nickname'] == $petType['name'])
			{
				$pet['nickname'] = $newType['name'];
				
				Database::query("UPDATE creatures_owned SET nickname=? WHERE id=? LIMIT 1", array($pet['nickname'], $pet['id']));
			}
			Database::endTransaction();
			
			Alert::saveSuccess("Reversed Evolution", "You have reversed " . ($petType['prefix'] != "" ? $petType['prefix'] . ' ' : "") . $petType['name'] . "'s evolution to a " . ($newType['prefix'] != "" ? $newType['prefix'] . ' ' : "") . $newType['name'] . "!");
			
			// Return to the pet's page with the new gender
			header("Location: /pet/" . $pet['id']); exit;
		}
	}
	else
	{
		Alert::error("No Alchemy", "You don't have enough alchemy ingredients to achieve this effect.");
	}
}

// Prepare Link Protection
$linkProtect = Link::prepare("reverse-evolve-uc");

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
	if($key != "id")
		$pet[$key] = (is_numeric($val) ? (int) $val : $val);

echo '
<div id="uc-left">
	<div class="uc-static-block">' . MyBlocks::petPlain($pet, '/pet/' . $pet['id']) . '<div class="uc-note">Evolution Points: ' . $pet['total_points'] . '</div><div class="uc-note">Level: ' . MyTraining::getLevel((int) $pet['experience']) . '</div></div>
	' . MyBlocks::inventory() . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
	<div>
		<div class="uc-bold">Are you sure you want to reverse evolve ' . $pet['nickname'] . ' to its earlier stage?</div>
		
		<div>This effect will require ' . $swapCost . ' alchemy ingredients.</div>
		
		<div class="uc-action-block"><a href="/action/reverse-evolve/' . $pet['id'] . '?reverse=true&' . $linkProtect . '" style="display:block; padding:4px;">Yes, reverse-evolve this pet.</a></div>
	</div>

</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
