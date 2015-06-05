<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure a url handle was provided
if(!isset($url[0]) or $url[0] == "visit-center")
{
	header("Location: /"); exit;
}

// Retrieve the user's data
if(!$userData = User::getDataByHandle($url[0], "uni_id, handle, display_name, avatar_opt"))
{
	Alert::saveError("Invalid User", "That user does not have a UniCreatures profile.");
	
	header("Location: /"); exit;
}

// Prepare Values
$userData['uni_id'] = (int) $userData['uni_id'];

if($userData['uni_id'] != Me::$id)
{
	$urlAdd = "/" . $userData['handle'];
	
	You::$id = $userData['uni_id'];
	You::$handle = $userData['handle'];
	You::$name = $userData['display_name'];
}

// If this is one of the user's pages, load the page with the user as the first information
if(isset($url[1]))
{
	if(File::exists(APP_PATH . "/controller/" . $url[1] . '.php'))
	{
		array_shift($url);
		
		require_once(APP_PATH . "/controller/" . $url[0] . '.php'); exit;
	}
}

// Check if the user has already visited this page
if(Me::$id != $userData['uni_id'] and MyIPTrack::userTracker($userData['uni_id']))
{
	$dayCountData = MyIPTrack::getDayCount($userData['uni_id'], true);
	$count = (int) $dayCountData['count'];
	
	// Prepare Values
	$treasures = array(
		'coins'			=> 0
	,	'alchemy'		=> 0
	,	'crafting'		=> 0
	,	'components'	=> 0
	);
	
	// Determine the total number to provide
	$total = 0;
	
	if($count <= 5)
	{
		$total = 50;
	}
	else if($count <= 600)
	{
		$total = 3;
	}
	
	// Grant supplies at random
	for($a = 0; $a < $total; $a++)
	{
		$rand = mt_rand(1, 100);
		
		$treasure = "coins";	// 50% chance
		
		// Alchemy (2% chance)
		if($rand > 98) { $treasure = "alchemy"; }
		
		// Crafting (3% chance)
		else if($rand > 95) { $treasure = "crafting"; }
		
		// Components (45% chance)
		else if($rand > 50) { $treasure = "components"; }
		
		// Add the result
		$treasures[$treasure] += 1;
	}
	
	// Give the treasure to the user you visited
	MyTreasure::acquireBulk((int) $userData['uni_id'], $treasures);
}
else
{
	$dayCountData = MyIPTrack::getDayCount($userData['uni_id']);
	$count = (int) $dayCountData['count'];
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

// Display the left side of the page
echo '
<div id="uc-left">
	' . MyBlocks::avatar($userData) . '
	' . (Me::$id == $userData['uni_id'] ? MyBlocks::inventory() : '') . '
</div>
<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '
	<div id="pet-desc">';
	
	$individual = false;
	if(isset($url[1]))
	{
		// show an individual pet		
		$pet = MyCreatures::petData((int) $url[1]);
		if($pet['uni_id'] == (int) $userData['uni_id'])
		{
			$individual = true;
			$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");
			foreach($pet as $key => $val)
				$pet[$key] = (is_numeric($val) ? (int) $val : $val);
			foreach($petType as $key => $val)
				$pet[$key] = (is_numeric($val) ? (int) $val : $val);
			echo MyBlocks::petPlain($pet, '/pet/' . $pet['id']);
		}
	}
	
	// Provide Treasures to the user
	if(MyTreasure::$treasure)
	{
		// show message for an individual pet
		if($individual)
		{
			echo '
		<p>You just gave ' . ($petType['prefix'] != "" && $pet['nickname'] == $petType['name'] ? $petType['prefix'] . " " : "") . ($petType['name'] == "Egg" && $pet['nickname'] == "Egg" ? $petType['family'] . ' Egg' : $pet['nickname']) . ' the following gifts. ' . ($pet['gender'] == "m" && $petType['name'] != "Egg" ? "He" : ($petType['name'] != "Egg" ? "She" : "It")) . ' cheerfully brings them to ' . ($pet['gender'] == "m" && $petType['name'] != "Egg" ? "his" : ($petType['name'] != "Egg" ? "her" : "its")) . ' caretaker ' . $userData['display_name'] . '.</p>';
		}
		else
		{
			echo '
		<h3>You just gave ' . $userData['display_name'] . ' the following gifts:</h3>';
		}
		
		foreach(MyTreasure::$treasure as $treasureData)
		{
			if($treasureData['count'] > 0)
			{
				echo '
				<div style="display:inline-block; text-align:center; width:130px;"><img src="' . $treasureData['image'] . '" /><div class="uc-note">' . $treasureData['title'] . '</div><div class="uc-note">' . $treasureData['count'] . ' Given</div></div>';
			}
		}
		
		echo '
		<div style="margin-bottom:10px;">&nbsp;</div>';
	}
	elseif($individual)
	{
		echo '
		<div style="margin-bottom:10px;">&nbsp;</div>';
	}
	
	echo '
		<h3>Welcome to ' . $userData['display_name'] . '\'s Visitation Page!</h3>
		<div>Every visitor grants three goodies to ' . $userData['display_name'] . ', up to once per day. The first five visitors grant fifty goodies! So far, ' . ($count == 1 ? 'one person has' : Number::toWord($count) . ' people have') . " given gifts to " . $userData['display_name'] . ' today!</div>';
	
	// Get a list of Pet ID's
	if($getPets = Database::selectMultiple("SELECT creature_id FROM creatures_user WHERE uni_id=? ORDER BY creature_id DESC LIMIT 25", array($userData['uni_id'])))
	{
		// Show off Pets
		echo '
		<div style="margin-bottom:10px;">&nbsp;</div>
		<h3>Some of ' . $userData['display_name'] . '\'s pets come visit you!</h3>';
		
		shuffle($getPets);
		
		$getPets = array_splice($getPets, 0, mt_rand(4, 6));
		$petIDs = array();
		$eggVisit = false;
		
		foreach($getPets as $pid)
		{
			$petIDs[] = (int) $pid['creature_id'];
		}
		
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("co.id" => $petIDs));
		
		$pets = $sqlWhere ? Database::selectMultiple("SELECT co.id, co.nickname, co.activity, co.active_until, ct.family, ct.name, ct.prefix FROM creatures_owned co INNER JOIN creatures_types ct ON co.type_id=ct.id WHERE " . $sqlWhere, $sqlArray) : array();
		
		foreach($pets as $pet)
		{
			if($pet['name'] == "Egg") { $eggVisit = true; }
			
			echo MyBlocks::petPlain($pet, '/pet/' . $pet['id']);
		}
		
		if($eggVisit)
		{
			echo '
			<div class="uc-note" style="margin-top:22px;">Yes, an egg just visited you. Eggs can do that here.</div>';
		}
	}
	
	// If you're logged in to your own visit-center page
	if(Me::$id == $userData['uni_id'])
	{
		echo '
		<div style="margin-bottom:10px;">&nbsp;</div>
		<h3>Attract Users For Free Goodies</h3>
		<div>Use these links to bring people to this page. Everyone who visits your page will earn you free components, coins, or other goodies!</div>
		
		<form class="uniform">
		<div style="margin-top:10px;">
			<div class="uc-note" style="font-weight:bold;">Direct Link:</div>
			<input type="text" name="dir_link" value="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:16px;">
			<div class="uc-note" style="font-weight:bold;">BBCode Link:</div>
			<input type="text" name="bb_link" value="[url=' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . ']' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '[/url]" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:16px;">
			<div class="uc-note" style="font-weight:bold;">HTML Link:</div>
			<input type="text" name="bb_link" value=\'<a href="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '">' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '</a>\' style="width:100%;" readonly onclick="this.select();" />
		</div>
		</form>';
	}
	
echo '
	</div>
</div>';

/*
	// <h3>A pet challenges you to a game of UniBall. Do you accept the challenge? (You\'re on, PET!)</h3>
*/

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
