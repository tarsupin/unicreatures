<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Make sure this exploration zone is legitimate
if(!$exploreType = Database::selectValue("SELECT type FROM explore_area WHERE type=? LIMIT 1", array($url[3])))
{
	header("Location: /staff/explore/zone-list"); exit;
}

// Sanitize
$_GET['family'] = Sanitize::variable($_GET['family']);
$_GET['name'] = Sanitize::variable($_GET['name']);
$_GET['prefix'] = Sanitize::variable($_GET['prefix']);
$_GET['rarity'] = min(max(0, $_GET['rarity'] + 0), 9);

$_GET['day_start'] = ($_GET['day_start'] == "" ? -1 : (is_numeric($_GET['day_start']) ? $_GET['day_start'] : date('z', strtotime($_GET['day_start']))));
$_GET['day_end'] = ($_GET['day_end'] == "" ? -1 : (is_numeric($_GET['day_end']) ? $_GET['day_end'] : date('z', strtotime($_GET['day_end']))));

// Update the Pet
if(Form::submitted("uc-add-explore-pet"))
{
	// Get new pet type
	if($newTypeID = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($_GET['family'], $_GET['name'], $_GET['prefix'])))
	{
		// Add the Shop Pet
		if(Database::query("INSERT INTO explore_creatures (explore_zone, rarity, day_start, day_end, type_id) VALUES (?, ?, ?, ?, ?)", array($url[3], $_GET['rarity'] + 0, $_GET['day_start'], $_GET['day_end'], $newTypeID)))
		{
			Alert::saveSuccess("Creature Added", "You have added " . $_GET['name'] . " to the " . $url[3] . " Explore Zone!");
			
			header("Location: /staff/explore/pet-edit/" . $url[3] . '/' . $newTypeID); exit;
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
' . Alert::display() . '

<style>
.uniform input[type=text] { min-width:250px; margin-top:8px; }
.uniform>p { margin:0px; padding:6px 0 0 0; }
</style>

<h2>Add a Creature to ' . ucwords(str_replace("_", " ", $url[3])) . '</h2>
<p><a href="/staff/explore/zone-list">Return to Explore Zone List</a></p>

<form class="uniform" action="/staff/explore/pet-add/' . $url[3] . '" method="post">' . Form::prepare("uc-add-explore-pet") . '
	
	<p><input type="text" name="family" value="' . $_GET['family'] . '" placeholder="Creature Family . . ." /></p>
	<p><input type="text" name="name" value="' . $_GET['name'] . '" placeholder="Creature Name . . ." /></p>
	<p><input type="text" name="prefix" value="' . $_GET['prefix'] . '" placeholder="Prefix . . ." /></p>
	
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
	
	<p>Day Start: <input type="text" name="day_start" value="' . $_GET['day_start'] . '" placeholder="Day to Start . . ." /></p>
	<p>Day End: <input type="text" name="day_end" value="' . $_GET['day_end'] . '" placeholder="Day to End . . ." /></p>
	
	<p><input type="submit" name="submit" value="Add Explore Pet" /></p>
</form>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
