<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Staff Permissions Page
require("/includes/staff_global.php");

// Check if a user was selected
$userData = false;
$userSupplies = false;
$userEnergy = 0;

if(isset($_POST['handle']))
{
	$userData = User::getDataByHandle($_POST['handle'], "uni_id, handle");
	
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Get User's Supplies (if applicable)
if($userData)
{
	$userSupplies = MySupplies::getSupplyList($userData['uni_id']);
	$userEnergy = MyEnergy::get($userData['uni_id']);
	
	// Check form submission
	if(Form::submitted("supply-user-uc"))
	{
		$userSupplies = array(
			"coins"			=> $_POST['coins'] + 0
		,	"components"	=> $_POST['components'] + 0
		,	"crafting"		=> $_POST['crafting'] + 0
		,	"alchemy"		=> $_POST['alchemy'] + 0
		);
		
		// Update Supplies
		Database::query("REPLACE INTO users_supplies (uni_id, coins, components, crafting, alchemy) VALUES (?, ?, ?, ?, ?)", array($userData['uni_id'], $userSupplies['coins'], $userSupplies['components'], $userSupplies['crafting'], $userSupplies['alchemy']));
		
		// Update Energy
		$userEnergy = MyEnergy::set($userData['uni_id'], (int) $_POST['energy']);
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

<div class="uniform">
' . Search::searchBarUserHandle() . '
</div>

<h2 style="margin-top:20px;">Supply User' . (isset($userData) ? ': ' . $userData['handle'] : '') . '</h2>
<div>';

// Show User Data
if($userData && $userSupplies)
{
	echo '
	<form class="uniform" action="/staff/supply-user?handle=' . $userData['handle'] . '" method="post">' . Form::prepare("supply-user-uc") . '
		<p>User: ' . $userData['handle'] . '</p>
		<p>Energy: <input type="text" name="energy" value="' . ($userEnergy + 0) . '" maxlength="8" /></p>
		<p>Coins: <input type="text" name="coins" value="' . ($userSupplies['coins'] + 0) . '" maxlength="8" /></p>
		<p>Components: <input type="text" name="components" value="' . ($userSupplies['components'] + 0) . '" maxlength="8" /></p>
		<p>Crafting: <input type="text" name="crafting" value="' . ($userSupplies['crafting'] + 0) . '" maxlength="8" /></p>
		<p>Alchemy: <input type="text" name="alchemy" value="' . ($userSupplies['alchemy'] + 0) . '" maxlength="8" /></p>
		<p><input type="submit" name="submit" value="Update User" /></p>
	</form>
	';
}

echo '
</div>

</div>';

echo '
<script>
function UserHandle(handle)
{
	window.location = "/staff/supply-user?user=" + handle;
}
</script>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
