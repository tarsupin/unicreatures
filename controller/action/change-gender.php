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
$pet = MyCreatures::petData($url[2], "id, uni_id, type_id, nickname, gender");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Cost of the Effect
$swapCost = 5;
$alchemy = MySupplies::getSupplies(Me::$id, "alchemy");

// Get the Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "family, name, prefix");

// If you changed the gender of the pet
if(isset($_GET['gender']) && Link::clicked())
{
	if($alchemy >= $swapCost)
	{
		// Update the gender of the pet
		Database::startTransaction();
		$alchemy = MySupplies::changeSupplies(Me::$id, "alchemy", $swapCost);
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
You currently have ' . $alchemy . ' alchemy ingredients.
<div>
	Are you sure you want to change ' . $pet['nickname'] . '\'s gender to ' . ($pet['gender'] == 'm' ? 'female' : 'male') . '? This effect will require ' . $swapCost . ' alchemy ingredients.<br />
	<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
	<br /><br /><a href="/action/change-gender/' . $pet['id'] . '?gender=true&' . Link::prepare() . '">Yes, change ' . ($pet['gender'] == 'm' ? 'his' : 'her') . ' gender.</a>
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
