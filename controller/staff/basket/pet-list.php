<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Check if a pet was deleted
if(isset($_GET['delete']) && Link::clicked())
{
	$getType = Database::selectOne("SELECT id, family, name, prefix FROM creatures_types WHERE id=? LIMIT 1", array($_GET['delete']));
	
	if($getType)
	{
		Database::query("DELETE FROM basket_creatures WHERE type_id=? LIMIT 1", array($_GET['delete']));
		Alert::success("Deleted Creature", "You have deleted " . $getType['name'] . " (" . $getType['family'] . " family) from the basket.");
	}
}

// Get the creatures available in the basket
$creatures = Database::selectMultiple("SELECT bc.*, ct.family, ct.name, ct.prefix FROM basket_creatures bc INNER JOIN creatures_types ct ON ct.id=bc.type_id", array());

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

echo 'Manage the shop pets.
<br /><a href="/staff/basket/pet-add">Add a pet to the basket</a>';

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
		<td>Options</td>
		<td>Type ID</td>
		<td>Pet</td>
		<td>Family</td>
		<td>Rarity</td>
	</tr>';

foreach($creatures as $creature)
{
	echo '
	<tr>
		<td><a href="/staff/basket-pet-edit/' . $creature['type_id'] . '">Edit</a>, <a href="/staff/basket-pets?delete=' . $creature['type_id'] . '&' . Link::prepare() . '">Delete</a></td>
		<td>' . $creature['type_id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . $creature['name'] . '</td>
		<td>' . $creature['family'] . '</td>
		<td>' . $creature['rarity'] . '</td>
	</tr>';
}

echo '
</table>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
