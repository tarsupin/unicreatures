<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Get the shop creature that you're editing
if(!$creature = Database::selectOne("SELECT sc.*, ct.family, ct.name, ct.prefix FROM shop_creatures sc INNER JOIN creatures_types ct ON ct.id=sc.type_id WHERE sc.id=?", array($url[3])))
{
	header("Location: /staff/shop-pets"); exit;
}

// Prepare Date Conversion
$_GET['day_start'] = ($_GET['day_start'] == "" ? -1 : (is_numeric($_GET['day_start']) ? $_GET['day_start'] : date('z', strtotime($_GET['day_start']))));
$_GET['day_end'] = ($_GET['day_end'] == "" ? -1 : (is_numeric($_GET['day_end']) ? $_GET['day_end'] : date('z', strtotime($_GET['day_end']))));

// Update the Pet
if(Form::submitted("uc-pet-edit"))
{
	// Get new pet type
	$newType = Database::selectOne("SELECT id FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($_GET['family'], $_GET['name'], $_GET['prefix']));
	
	if($newType)
	{
		// Update the Shop Pet
		Database::query("UPDATE shop_creatures SET type_id=?, cost=?, day_start=?, day_end=? WHERE id=? LIMIT 1", array($newType['id'], $_GET['cost'], $_GET['day_start'], $_GET['day_end'], $creature['id']));
		
		$creature = Database::selectOne("SELECT sc.*, ct.family, ct.name, ct.prefix FROM shop_creatures sc INNER JOIN creatures_types ct ON ct.id=sc.type_id WHERE sc.id=?", array($url[3]));
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

// Sanitize
$_GET['family'] = Sanitize::variable($_GET['family']);
$_GET['name'] = Sanitize::variable($_GET['name']);
$_GET['prefix'] = Sanitize::variable($_GET['prefix']);
$_GET['cost'] = (isset($_GET['cost']) ? $_GET['cost'] + 0 : "");
$_GET['day_start'] = (isset($_GET['day_start']) ? $_GET['day_start'] + 0 : "");
$_GET['day_end'] = (isset($_GET['day_end']) ? $_GET['day_end'] + 0 : "");

echo '
<div id="content">
' . Alert::display() . '

<h2>Edit Shop Pet</h2>
<p><a href="/staff/shop/pet-list">Go back to the shop pets list</a></p>

<style>
table { margin:8px; border: solid 1px black; }
tr:nth-child(odd) { background-color: #EEEEEE; }
tr:nth-child(even) { background-color: #DDDDDD; }
td:nth-child(even) { background-color: rgba(180, 180, 180, 0.5); }
td { text-align:center; }
</style>

<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td>Buy ID</td>
		<td>Pet</td>
		<td>Cost (in Coins)</td>
		<td>Day Start</td>
		<td>Day End</td>
	</tr>';
	
echo '
	<tr>
		<td>' . $creature['id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . $creature['name'] . '</td>
		<td>' . $creature['cost'] . '</td>
		<td>' . ($creature['day_start'] == -1 ? "" : $creature['day_start']) . '</td>
		<td>' . ($creature['day_end'] == -1 ? "" : $creature['day_end']) . '</td>
	</tr>';

echo '
</table>

<form class="uniform" action="/staff/shop/pet-edit/' . $creature['id'] . '" method="post">' . Form::prepare("uc-pet-edit") . '
	Family: <input type="text" name="family" value="' . $creature['family'] . '" /><br />
	Name: <input type="text" name="name" value="' . $creature['name'] . '" /><br />
	Prefix: <input type="text" name="prefix" value="' . $creature['prefix'] . '" /><br />
	Cost (in Coins): <input type="text" name="cost" value="' . $creature['cost'] . '" /><br />
	Day to Start Showing Pet in Shops: <input type="text" name="day_start" value="' . $creature['day_start'] . '" /><br />
	Day to Stop Showing Pet in Shops: <input type="text" name="day_end" value="' . $creature['day_end'] . '" /><br />
	<input type="submit" name="submit" value="Update Pet" />
</form>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
