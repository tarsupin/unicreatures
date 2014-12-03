<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Sanitize Variables
$_GET['family'] = Sanitize::variable($_GET['family']);
$_GET['name'] = Sanitize::variable($_GET['name']);
$_GET['prefix'] = Sanitize::variable($_GET['prefix']);
$_GET['rarity'] = Sanitize::number($_GET['rarity'], 0, 9);

// Add the Pet
if(Form::submitted("pet-add-basket"))
{
	// Get new pet type
	if($typeID = MyCreatures::getTypeID($_GET['family'], $_GET['name'], $_GET['prefix']))
	{
		// Attempt to add the Basket Pet
		if(!MyCreaturesAdmin::addBasketCreature($typeID, $_GET['rarity']))
		{
			Alert::saveSuccess("Creature Added", "You have added " . $_GET['name'] . " to the basket at Rarity " . $_GET['rarity'] . "!");
			
			header("Location: /staff/basket/pet-list"); exit;
		}
		else
		{
			Alert::error("Already Added", "That creature is already in the basket.");
		}
	}
	else
	{
		Alert::error("Does Not Exist", "That creature type does not exist. Please check your family, name, and prefix.");
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

echo 'Add a Basket Pet
<br /><a href="/staff">Admin</a> &gt; <a href="/staff/basket/pet-list">Basket Pet List</a>';

echo '
<br /><br />
<form class="uniform" action="/staff/basket/pet-add" method="post">' . Form::prepare("pet-add-basket") . '
	
	<p>Family: <input type="text" name="family" value="' . $_GET['family'] . '" /> (e.g. "Blizz")</p>
	<p>Name: <input type="text" name="name" value="' . $_GET['name'] . '" /> (e.g. "Asmo", "Blizz")</p>
	<p>Prefix: <input type="text" name="prefix" value="' . $_GET['prefix'] . '" /> (e.g. "Noble", "Exalted", "Blue", etc.)</p>
	
	<p>Rarity:
	<select name="rarity">' . str_replace('value="' . $_GET['rarity'] . '"', 'value="' . $_GET['rarity'] . '" selected', '
		<option value="0">[0] Common - 25% chance to appear</option>
		<option value="1">[1] Somewhat Common - 20% chance to appear</option>
		<option value="2">[2] Uncommon - 17% chance to appear</option>
		<option value="3">[3] Limited - 15% chance to appear</option>
		<option value="4">[4] Sparse - 12% chance to appear</option>
		<option value="5">[5] Very Sparse - 6% chance to appear</option>
		<option value="6">[6] Rare - 3% chance to appear</option>
		<option value="7">[7] Very Rare - 1.2% chance to appear</option>
		<option value="8">[8] Epic - 0.6% chance to appear</option>
		<option value="9">[9] Legendary - 0.2% chance to appear</option>') . '
	</select>
	</p>
	
	<p>Note about Rarity: the chance to appear counts for each creature slot in the basket (five of them), every hour. Additionally, if a rarity slot only has one creature in it, that creature will be picked 100% of the time. Therefore, rarities must have balanced numbers in them.</p>
	
	<p><input type="submit" name="submit" value="Add Shop Pet" /></p>
</form>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
