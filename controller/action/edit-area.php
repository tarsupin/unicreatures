<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/"); exit;
}

// Make sure you have the right information sent
if(!isset($url[2]))
{
	header("Location: /"); exit;
}

// Prepare Values
$areaID = (int) $url[2];

// Get Land Plots
$area = MyAreas::areaData($areaID);

$upgradedArea = MyAreas::upgradedAreaTypeData($area['type']);

// Determine the cost to upgrade the population
$engCost = (($area['max_population'] / 5) - 5) * 50;

// Supply List
$supplies = MySupplies::getSupplyList(Me::$id);

$supplies['crafting'] = (int) $supplies['crafting'];

// Check if a link was clicked
if($link = Link::clicked() and $link == "edit-area-uc" and isset($_GET['upgrade']))
{
	// Plot Upgrade
	if($_GET['upgrade'] == "plot" and $upgradedArea)
	{
		if($supplies['crafting'] >= $upgradedArea['upgrade_cost'])
		{
			Database::startTransaction();
			
			// Pay for the upgrade (in crafting supplies)
			$supplies['crafting'] = MySupplies::changeSupplies(Me::$id, "crafting", (int) (0 - $upgradedArea['upgrade_cost']));
			
			// Engineer the area (to upgrade to the next plot)
			$pass = MyAreas::upgradeAreaType($areaID);
			
			if(Database::endTransaction($pass))
			{
				Alert::saveSuccess("Plot Upgraded", 'You have successfully upgraded the "' . $area['name'] . '" land plot!');
				
				header("Location: /action/edit-area/" . $areaID); exit;
			}
		}
		else
		{
			Alert::error("Insufficient Supplies", "You don't have enough crafting supplies to do this plot upgrade right now.");
		}
	}
	
	// Engineering Upgrade
	else if($_GET['upgrade'] == "engineering")
	{
		if($supplies['crafting'] >= $engCost)
		{
			$pass = false;
			
			Database::startTransaction();
			
			// Pay for the upgrade (in crafting supplies)
			if($supplies['crafting'] = MySupplies::changeSupplies(Me::$id, "crafting", (int) (0 - $engCost)))
			{
				// Engineer the area to grant +5 Maximum Population
				$pass = MyAreas::engineerArea($areaID, 5);
			}
			
			if(Database::endTransaction($pass))
			{
				Alert::saveSuccess("Plot Engineer", 'You have successfully upgraded the engineering on the "' . $area['name'] . '" land plot!');
				
				header("Location: /action/edit-area/" . $areaID); exit;
			}
		}
		else
		{
			Alert::error("Insufficient Supplies", "You don't have enough crafting supplies to do this engineering right now.");
		}
	}
	
	// Delete the plot
	else if($_GET['upgrade'] == "delete")
	{
		if($area['population'] > 0)
		{
			Alert::error("Creatures Present", "You cannot delete an area that has creatures in it.");
		}
		else
		{
			if(MyAreas::deleteArea($areaID))
			{
				Alert::saveSuccess("Area Deleted", "The area was successfully deleted.");
				
				header("Location: /"); exit;
			}
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("edit-area-uc");

// Get the Form Submission
if(Form::submitted("uc-edit-area-form"))
{
	FormValidate::safeword("Rename Area", $_POST['rename'], 3, 22, " ");
	
	if(FormValidate::pass())
	{
		$area['name'] = $_POST['rename'];
		
		if(MyAreas::renameArea($areaID, $area['name']))
		{
			Alert::success("Area Renamed", "You have successfully renamed the area!");
		}
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
<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo '
<div id="uc-left">
	' . MyBlocks::avatar(Me::$vals) . '
	' . MyBlocks::inventory() . '
</div>
<div id="uc-right">
	<div class="area-cube">
		<a href="/area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<div class="uc-bold">' . $area['name'] . '</div>
		<div class="uc-note">Pop: ' . $area['population'] . ' / ' . $area['max_population'] . '</div>
	</div>';
	
	if($upgradedArea)
	{
		echo '
		<div class="area-cube">
			<a href="/action/edit-area/' . $area['id'] . '?upgrade=plot&' . $linkProtect . '" onclick="return confirm(\'Are you sure you want to upgrade this plot? Doing so will change its image, but not the population limit. To change the population limit, choose the Engineering option.\');"><img src="/assets/areas/' . $upgradedArea['type'] . '.png" /></a>
			<div class="uc-bold">Upgrade Plot</div>
			<div class="uc-note">' . $upgradedArea['upgrade_cost'] . '<span class="hide-800"> Crafting</span> Supplies</div>
		</div>';
	}
	
	echo '	
	<div style="text-align:center; width:160px; margin-top:22px;"><a href="/action/edit-area/' . $area['id'] . '?upgrade=engineering&' . $linkProtect . '"><img src="/assets/icons/button_supplies.png" /></a><div style="font-size:0.9em; font-weight:bold;">Engineering</div><div style="font-size:0.9em;">+5 Max. Population</div><div style="font-size:0.8em;">' . $engCost . ' Crafting Supplies</div></div>
	
	<div style="margin-top:22px;">
		<strong>Rename the Area</strong>
		<form class="uniform" action="/action/edit-area/' . $area['id'] . '" method="post">' . Form::prepare("uc-edit-area-form") . '
			<input type="text" name="rename" value="' . $area['name'] . '" maxlength="22" placeholder="Area name . . ." style="min-width:180px;" /> <input type="submit" name="submit" value="Rename" />
		</form>
	</div>
	
	<div style="margin-top:22px;">
		<a class="button" href="/action/edit-area/' . $area['id'] . '?upgrade=delete&' . $linkProtect . '" onclick="return confirm(\'Are you sure you want to delete this plot?\')">Delete This Area</a>
	</div>
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
