<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require(APP_PATH . "/includes/staff_global.php");

// Make sure this exploration zone is legitimate
if(!$exploreType = Database::selectValue("SELECT type FROM explore_area WHERE type=? LIMIT 1", array($url[3])))
{
	header("Location: /staff/explore/zone-list"); exit;
}

// Get the explore creature that you're editing
if(!$creature = Database::selectOne("SELECT ec.*, ct.family, ct.name, ct.prefix FROM explore_creatures ec INNER JOIN creatures_types ct ON ct.id=ec.type_id WHERE ec.explore_zone=? AND ec.type_id=?", array($url[3], $url[4])))
{
	header("Location: /staff/explore/zone-list"); exit;
}

// Sanitize
$_GET['family'] = Sanitize::variable($_GET['family']);
$_GET['name'] = Sanitize::variable($_GET['name']);
$_GET['prefix'] = Sanitize::variable($_GET['prefix'], " ");
$_GET['rarity'] = (int) (isset($_GET['rarity']) ? $_GET['rarity'] + 0 : $creature['rarity']);

$_GET['day_start'] = (!$_GET['day_start'] ? -1 : (is_numeric($_GET['day_start']) ? $_GET['day_start'] : date('z', strtotime($_GET['day_start']))));
$_GET['day_end'] = (!$_GET['day_end'] ? -1 : (is_numeric($_GET['day_end']) ? $_GET['day_end'] : date('z', strtotime($_GET['day_end']))));

// Update the Pet
if(Form::submitted("uc-pet-explore-edit"))
{
	// Get new pet type
	if($newTypeID = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($_GET['family'], $_GET['name'], $_GET['prefix'])))
	{
		// Update the Explore Pet
		Database::query("UPDATE explore_creatures SET type_id=?, rarity=?, day_start=?, day_end=? WHERE explore_zone=? AND type_id=? LIMIT 1", array($newTypeID, $_GET['rarity'], $_GET['day_start'], $_GET['day_end'], $url[3], (int) $creature['type_id']));
		
		Alert::saveSuccess("Updated Pet", "You have updated the explore pet.");
		
		header("Location: /staff/explore/pet-edit/" . $url[3] . "/" . $newTypeID); exit;
	}
	else
	{
		Alert::error("Invalid Creature", "That creature type does not exist. Please check your family, name, and prefix.");
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
<div id="content">' . Alert::display() . '

<h2>Edit Shop Pet</h2>
<p><a href="/staff/explore/pet-list/' . $exploreType . '">Go back to the shop pets list</a></p>

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
		<td>Rarity</td>
		<td>Day Start</td>
		<td>Day End</td>
	</tr>';
	
echo '
	<tr>
		<td>' . $creature['type_id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . $creature['name'] . '</td>
		<td>' . $creature['rarity'] . '</td>
		<td>' . ($creature['day_start'] == -1 ? "" : $creature['day_start']) . '</td>
		<td>' . ($creature['day_end'] == -1 ? "" : $creature['day_end']) . '</td>
	</tr>';

echo '
</table>

<form class="uniform" action="/staff/explore/pet-edit/' . $url[3] . '/' . $creature['type_id'] . '" method="post">' . Form::prepare("uc-pet-explore-edit") . '
	<p>Family: <input type="text" name="family" value="' . $creature['family'] . '" /></p>
	<p>Name: <input type="text" name="name" value="' . $creature['name'] . '" /></p>
	<p>Prefix: <input type="text" name="prefix" value="' . $creature['prefix'] . '" /></p>
	
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
	
	<p>Day to Start Showing Pet in Shops: <input type="text" name="day_start" value="' . $creature['day_start'] . '" /></p>
	<p>Day to Stop Showing Pet in Shops: <input type="text" name="day_end" value="' . $creature['day_end'] . '" /></p>
	
	<p><input type="submit" name="submit" value="Update Pet" /></p>
</form>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
