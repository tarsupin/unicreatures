<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Run action on list
if($link = Link::clicked() and $link == "delete-pet" and isset($_GET['delete']))
{
	Database::query("DELETE FROM explore_creatures WHERE explore_zone=? AND type_id=? LIMIT 1", array($url[3], $_GET['delete']));
}

// Get the creatures available in the shop
$creatures = Database::selectMultiple("SELECT ec.*, ct.family, ct.name, ct.prefix FROM explore_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ec.explore_zone=? ORDER BY ec.rarity ASC", array($url[3]));

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

<h2>Manage Pets in "' . ucwords(str_replace("_", " ", $url[3])) . '"</h2>
<p><a href="/staff/explore/pet-add/' . $url[3] . '">Add a pet to the zone</a></p>

<style>
table { margin:8px; border: solid 1px black; }
tr:nth-child(odd) { background-color: #EEEEEE; }
tr:nth-child(even) { background-color: #DDDDDD; }
td:nth-child(even) { background-color: rgba(180, 180, 180, 0.3); }
td { text-align:center; }
</style>

<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td>Options</td>
		<td>Rarity</td>
		<td>Type ID</td>
		<td>Pet</td>
		<td>Day Start</td>
		<td>Day End</td>
	</tr>';

foreach($creatures as $creature)
{
	echo '
	<tr>
		<td>
			<a href="/staff/explore/pet-edit/' . $url[3] . '/' . $creature['type_id'] . '">Edit</a>
			| <a href="/staff/explore/pet-list/' . $url[3] . '?delete=' . $creature['type_id'] . '&' . Link::prepare("delete-pet") . '">Delete</a>
		</td>
		<td>' . $creature['rarity'] . '</td>
		<td>' . $creature['type_id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . ($creature['name'] == "Egg" ? $creature['family'] . " Egg" : $creature['name']) . '</td>
		<td>' . ($creature['day_start'] == -1 ? "" : $creature['day_start']) . '</td>
		<td>' . ($creature['day_end'] == -1 ? "" : $creature['day_end']) . '</td>
	</tr>';
}

echo '
</table>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
