<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Prepare Date Conversion
$_GET['day_start'] = ($_GET['day_start'] == "" ? -1 : (is_numeric($_GET['day_start']) ? $_GET['day_start'] : date('z', strtotime($_GET['day_start']))));
$_GET['day_end'] = ($_GET['day_end'] == "" ? -1 : (is_numeric($_GET['day_end']) ? $_GET['day_end'] : date('z', strtotime($_GET['day_end']))));

// Update the Pet
if(Form::submitted())
{
	// Get new pet type
	if($newTypeID = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($_GET['family'], $_GET['name'], $_GET['prefix'])))
	{
		if($_GET['cost'] > 50)
		{
			// Add the Shop Pet
			if(Database::query("INSERT INTO shop_creatures (type_id, cost, day_start, day_end) VALUES (?, ?, ?, ?)", array($newTypeID, $_GET['cost'], $_GET['day_start'], $_GET['day_end'])))
			{
				Alert::saveSuccess("Creature Added", "You have added " . $_GET['name'] . " to the shop for " . $_GET['coin'] . " Coins!");
				header("Location: /staff/shop-pets"); exit;
			}
		}
		else
		{
			Alert::error("Too Cheap", "You cannot sell a pet in the stores for that cheap. Please select a new price.");
		}
	}
	else
	{
		Alert::error("Does Not Exist", "That creature type does not exist. Please check your family, name, and prefix.");
	}
}

// Sanitize
$_GET['family'] = Sanitize::variable($_GET['family']);
$_GET['name'] = Sanitize::variable($_GET['name']);
$_GET['prefix'] = Sanitize::variable($_GET['prefix']);
$_GET['cost'] = (isset($_GET['cost']) ? $_GET['cost'] + 0 : "");
$_GET['day_start'] = (isset($_GET['day_start']) ? $_GET['day_start'] + 0 : "");
$_GET['day_end'] = (isset($_GET['day_end']) ? $_GET['day_end'] + 0 : "");

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

<h2>Add a Shop Pet</h2>
<p><a href="/staff/shop/pet-list">Return to Shop Pets List</a></p>

<form class="uniform" action="/staff/shop/pet-add" method="post">' . Form::prepare() . '
	
	<p><input type="text" name="family" value="' . $_GET['family'] . '" placeholder="Creature Family . . ." /></p>
	<p><input type="text" name="name" value="' . $_GET['name'] . '" placeholder="Creature Name . . ." /></p>
	<p><input type="text" name="prefix" value="' . $_GET['prefix'] . '" placeholder="Prefix . . ." /></p>
	
	<p><input type="text" name="cost" value="' . $_GET['cost'] . '" placeholder="Coin Expense . . ." maxlength="6" /></p>
	
	<p><input type="text" name="day_start" value="' . $_GET['day_start'] . '" placeholder="Day to Start . . ." /></p>
	<p><input type="text" name="day_end" value="' . $_GET['day_end'] . '" placeholder="Day to End . . ." /></p>
	
	<p><input type="submit" name="submit" value="Add Shop Pet" /></p>
</form>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
