<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Get the creatures available in the shop
$creatures = Database::selectMultiple("SELECT sc.*, ct.family, ct.name, ct.prefix FROM shop_creatures sc INNER JOIN creatures_types ct ON ct.id=sc.type_id", array());

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

<h2>Manage Shop Pets</h2>
<p><a href="/staff/shop/pet-add">Add a pet to the shop</a></p>

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
		<td>Buy ID</td>
		<td>Pet</td>
		<td>Cost (in Coins)</td>
		<td>Day Start</td>
		<td>Day End</td>
	</tr>';

foreach($creatures as $creature)
{
	echo '
	<tr>
		<td><a href="/staff/shop/pet-edit/' . $creature['id'] . '">Edit</a></td>
		<td>' . $creature['id'] . '</td>
		<td><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['prefix'] != "" ? $creature['prefix'] . ' ' : '') . $creature['name'] . '</td>
		<td>' . $creature['cost'] . '</td>
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
