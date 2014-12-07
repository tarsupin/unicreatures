<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/explore-zones"); exit;
}

// Prepare Variables
$treasures = array();

// Get the user's energy. You must have enough to go on a speed run.
$energy = MyEnergy::get(Me::$id);

if($energy < 100)
{
	Alert::saveError("Insufficient Energy", "You must have at least 100 energy available to go on a speed run.");
	
	header("Location: /explore-zones"); exit;
}

// Run Form
if(Form::submitted("explore-zone-rush"))
{
	// Determine the zone you entered
	$zoneExplored = key($_POST['expZone']);
	
	MyTreasure::$exploreZone = $zoneExplored;
	
	// Determine how much energy is being used
	if(is_numeric($_POST['energy_use']) and $_POST['energy_use'] >= 100 and $energy >= $_POST['energy_use'])
	{
		$energyUsed = (int) $_POST['energy_use'];
		MyEnergy::set(Me::$id, $energy - $_POST['energy_use']);
	}
	else
	{
		// Remove all of the user's energy
		$energyUsed = $energy;
		MyEnergy::set(Me::$id, 0);
	}
	
	// Use the energy you have available to search for treasure
	$treasureCount = round($energyUsed * mt_rand(78, 82) / 100);
	
	for($a = 1;$a <= $treasureCount;$a++)
	{
		if($treasure = MyTreasure::random(Me::$id))
		{
			if(!isset($treasures[$treasure]))
			{
				$treasures[$treasure] = 0;
			}
			
			$treasures[$treasure] += 1;
		}
	}
	
	// If the user was searching for something specific, adjust the results accordingly
	$tList = array("alchemy", "coins", "components", "crafting");
	
	if($_POST['searchingFor'])
	{
		foreach($treasures as $k => $v)
		{
			if($k == $_POST['searchingFor'])
			{
				$treasures[$k] = $v * 2;
			}
			else
			{
				$treasures[$k] = round($v / 2);
			}
		}
		
		// Booster for Creatures
		if($_POST['searchingFor'] == "creatures")
		{
			MyTreasure::$locateEggBoost += 20;
		}
	}
	
	// More eggs increases the chance for better result
	if(isset($treasures['pet']) and $treasures['pet'] > 1)
	{
		MyTreasure::$locateEggBoost = min(50, MyTreasure::$locateEggBoost + (5 * $treasures['pet']));
	}
}

// Grant user treasures
if($treasures !== array())
{
	MyTreasure::acquireBulk(Me::$id, $treasures);
}

// Get your list of user zones
$achievements = MyAchievements::getScore(Me::$id);

$zones = MyExplore::zoneList($achievements);

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
<style>
.zone { display:inline-block; padding:3px; text-align:center; }
.zoneInput input { cursor:pointer; width: 140px; height: 140px; border: none; }
</style>';

// If you did a speed run successfully
if($treasures !== array())
{
	echo '
	<h2>Your "Speed Run" Adventure:</h2>
	<p>You\'ve just completed a Speed Run, and received the following:</p>';
	
	foreach(MyTreasure::$treasure as $tData)
	{
		$count = isset($tData['count']) ? " (" . $tData['count'] . ")" : "";
		
		echo '
		<div style="display:inline-block; padding:12px; text-align:center;">
			<img src="' . $tData['image'] . '" /><br />
			' . $tData['title'] . $count . '
		</div>';
	}
	
	echo '
	<p>&nbsp;</p>
	<p><a class="button" href="/explore-rush">Return to Speed Runs</a></p>
	<p><a class="button" href="/explore-zones">Return to Exploration Zones</a></p>';
}

// If you're looking to go on a speed run
else
{
	echo '
	<h2>Go on a "Speed Run" Adventure</h2>
	<p>Explore maps instantly, acquiring 80% of the supplies you would find during normal exploration. You can also search for specific supplies, which greatly improves the odds of finding those supplies (but reduces the quantity of other supplies).</p>
	
	<form class="uniform" action="/explore-rush" method="post">' . Form::prepare("explore-zone-rush") . '
	<p>
		<strong>Search Specifically For:</strong><br />
		<select name="searchingFor">
			<option value="">Nothing in particular</option>
			<option value="alchemy">Alchemy Ingredients</option>
			<option value="coins">Coins</option>
			<option value="components">Components</option>
			<option value="crafting">Crafting Supplies</option>
			<option value="creatures">Rare Creatures</option>
		</select>
	</p>
	
	<p>
		<strong>How much energy to spend on this speed run?</strong><br />
		<select name="energy_use">
			<option value="">Use all of my available energy</option>
			' . ($energy >= 300 ? '<option value="300">Use 300 energy</option>' : '') . '
			' . ($energy >= 200 ? '<option value="200">Use 200 energy</option>' : '') . '
			<option value="100">Use 100 energy</option>
		</select>
	</p>
	<p>' . $energy . ' Energy Available</p>';
	
	foreach($zones as $key => $zone)
	{
		echo '
		<div class="zone">
			<div class="zoneInput">
				<input type="submit" name="expZone[' . $key . ']" value="" style="background:url(/assets/explore_zones/' . $key . '.png) no-repeat;" />
			</div>
			<div>' . $zone['title']  . '</div>
		</div>';
	}
	
	echo '
	</form>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
