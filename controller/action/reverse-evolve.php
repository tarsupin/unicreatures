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
$pet = MyCreatures::petData($url[2], "id, uni_id, type_id, nickname");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Cost of the Effect
$swapCost = 10;
$alchemy = MySupplies::getSupplies(Me::$id, "alchemy");

// Get the Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "evolves_from, family, name, prefix");

if($petType['evolves_from'] == 0)
{
	Alert::saveSuccess("Cannot Devolve", "You cannot reverse evolve this pet.");
	header("Location: /pet/" . $pet['id']); exit;
}

// If you reversed this pet's evolution
if(isset($_GET['reverse']) && Link::clicked())
{
	if($alchemy >= $swapCost)
	{
		// Get the Changed Type
		$newType = MyCreatures::petTypeData($petType['evolves_from'], "id, name, prefix");
		
		if($newType)
		{
			// Reverse this pet's evolution
			Database::startTransaction();
			$alchemy = MySupplies::changeSupplies(Me::$id, "alchemy", $swapCost);
			Database::query("UPDATE creatures_owned SET type_id=? WHERE id=? LIMIT 1", array($newType['id'], $pet['id']));
			Database::endTransaction();
			
			Alert::saveSuccess("Reversed Evolution", "You have reversed " . $pet['nickname'] . "'s evolution to a " . ($newType['prefix'] != "" ? $newType['prefix'] . ' ' : "") . $newType['name'] . "!");
			
			// Return to the pet's page with the new gender
			header("Location: /pet/" . $pet['id']); exit;
		}
	}
	else
	{
		Alert::error("No Alchemy", "You don't have enough alchemy ingredients to achieve this effect.");
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
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '

You currently have ' . $alchemy . ' alchemy ingredients.
<div>
	Are you sure you want to reverse evolve ' . $pet['nickname'] . ' to its earlier stage? This effect will require ' . $swapCost . ' alchemy ingredients.<br />
	<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
	<br /><br /><a href="/action/reverse-evolve/' . $pet['id'] . '?reverse=true&' . Link::prepare() . '">Yes, reverse-evolve this pet.</a>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
