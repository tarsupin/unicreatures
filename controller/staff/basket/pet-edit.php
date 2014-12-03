<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Get the basket creature that you're editing
if(!$creature = Database::selectOne("SELECT bc.*, ct.family, ct.name, ct.prefix FROM basket_creatures bc INNER JOIN creatures_types ct ON ct.id=bc.type_id WHERE bc.type_id=?", array($url[2])))
{
	header("Location: /staff/basket/pet-list"); exit;
}

// Prepare Values
$_GET['rarity'] = (int) (isset($_GET['rarity']) ? Sanitize::number($_GET['rarity'], 0, 9) : $creature['rarity']);

// Update the Pet
if(Form::submitted("pet-edit-basket"))
{
	// Get new pet type
	if($typeID = MyCreatures::getTypeID($_GET['family'], $_GET['name'], $_GET['prefix']))
	{
		// Update the Basket Pet
		Database::query("UPDATE basket_creatures SET rarity=? WHERE type_id=? LIMIT 1", array($_GET['rarity'], $typeID));
		
		$creature = Database::selectOne("SELECT bc.*, ct.family, ct.name, ct.prefix FROM basket_creatures bc INNER JOIN creatures_types ct ON ct.id=bc.type_id WHERE bc.type_id=?", array($url[2]));
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

echo 'Edit the Basket Pet
<br /><a href="/staff">Admin</a> &gt; <a href="/staff/basket-pets">Basket Pet List</a>';

echo '
<style>
table { margin:8px; border: solid 1px black; }
tr:nth-child(odd) { background-color: #EEEEEE; }
tr:nth-child(even) { background-color: #DDDDDD; }
td:nth-child(even) { background-color: rgba(180, 180, 180, 0.5); }
td { text-align:center; }
</style>

<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td>Type ID</td>
		<td>Pet</td>
		<td>Family</td>
		<td>Rarity</td>
	</tr>';
	
echo '
	<tr>
		<td>' . $creature['type_id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . $creature['name'] . '</td>
		<td>' . $creature['family'] . '</td>
		<td>' . $creature['rarity'] . '</td>
	</tr>';
	
echo '
</table>

<br /><br />
<form class="uniform" action="/staff/basket/pet-edit/' . $creature['type_id'] . '" method="post">' . Form::prepare("pet-edit-basket") . '
	
	<p>Family: <input type="hidden" name="family" value="' . $creature['family'] . '" /></p>
	<p>Name: <input type="hidden" name="name" value="' . $creature['name'] . '" /></p>
	<p>Prefix: <input type="hidden" name="prefix" value="' . $creature['prefix'] . '" /></p>
	
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
	
	<p><input type="submit" name="submit" value="Update Pet" /></p>
</form>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
