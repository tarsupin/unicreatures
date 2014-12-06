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

// Run Profile Actions
if($link = Link::clicked() and $link == "uc-connect-avatar")
{
	// Connecting Game Avatar
	if($userData['uni_id'] == Me::$id)
	{
		if(Avatar::hasAvatar())
		{
			Database::query("UPDATE users SET avatar_opt=? WHERE uni_id=? LIMIT 1", array(1, Me::$id));
			
			$userData['avatar_opt'] = 1;
		}
		else
		{
			// Direct to avatar site to create one
			header("Location: " . URL::avatar_unifaction_com() . Me::$slg);
		}
	}
}

// Prepare Values
$linkPrepare = Link::prepare("uc-connect-avatar");

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
		$rand = mt_rand(0, 100);
		
		$treasure = "coins";	// 50% chance
		
		// Alchemy (2% chance)
		if($rand >= 99) { $treasure = "alchemy"; }
		
		// Crafting (3% chance)
		else if($rand >= 96) { $treasure = "crafting"; }
		
		// Components (45% chance)
		else if($rand >= 50) { $treasure = "components"; }
		
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
<style>
.dual-col-item { display:inline-block; width:45%; padding:1%; margin-bottom:12px; }
</style>

<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="uc-bold">' . $userData['display_name'] . '</div></div>
</div>
<div id="uc-right">
	<div class="uc-action-block">
		<div style="font-size:1.1em; font-weight:bold; margin-bottom:12px;">Visit ' . $userData['display_name'] . '\'s Pages</div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_visit.png" /><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	<div id="pet-desc">';
	
	// Provide Treasures to the user
	if(MyTreasure::$treasure)
	{
		echo '
		<h3>You just gave ' . $userData['display_name'] . ' the following gifts:</h3>';
		
		foreach(MyTreasure::$treasure as $treasureData)
		{
			if($treasureData['count'] > 0)
			{
				echo '
				<div style="display:inline-block; text-align:center; width:130px;"><img src="' . $treasureData['image'] . '" /><div style="font-size:0.9em;">' . $treasureData['title'] . '</div><div style="font-size:0.9em;">' . $treasureData['count'] . ' Given</div></div>';
			}
		}
		
		echo '
		<div style="margin-bottom:10px;">&nbsp;</div>';
	}
	
	echo '
		<div style="font-weight:bold; font-size:1.2em;">Welcome to ' . $userData['display_name'] . '\'s Visitation Page!</div>
		<div>Every visitor grants three goodies to ' . $userData['display_name'] . ', up to once per day. The first five visitors grant fifty goodies! So far, ' . ($count == 1 ? 'one person has' : Number::toWord($count) . ' people have') . " given gifts to " . $userData['display_name'] . ' today!</div>';
	
	// Show off Pets
	echo '
	<div style="font-weight:bold; font-size:1.2em; margin-top:22px;">Some of ' . $userData['display_name'] . '\'s pets come visit you!</div>';
	
	// Get a list of Pet ID's
	$getPets = Database::selectMultiple("SELECT creature_id FROM creatures_user WHERE uni_id=? ORDER BY creature_id DESC LIMIT 25", array($userData['uni_id']));
	
	shuffle($getPets);
	
	$getPets = array_splice($getPets, 0, mt_rand(4, 6));
	$petIDs = array();
	$eggVisit = false;
	
	foreach($getPets as $pid)
	{
		$petIDs[] = (int) $pid['creature_id'];
	}
	
	list($sqlWhere, $sqlArray) = Database::sqlFilters(array("co.id" => $petIDs));
	
	$pets = Database::selectMultiple("SELECT co.id, co.nickname, ct.family, ct.name, ct.prefix FROM creatures_owned co INNER JOIN creatures_types ct ON co.type_id=ct.id WHERE " . $sqlWhere, $sqlArray);
	
	foreach($pets as $pet)
	{
		if($pet['name'] == "Egg") { $eggVisit = true; }
		
		echo '
		<div class="pet-cube"><div class="pet-cube-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div>' . $pet['nickname'] . '</div></div>';
	}
	
	if($eggVisit)
	{
		echo '
		<div class="uc-note" style="margin-top:22px;">Yes, an egg just visited you. Deal with it.</div>';
	}
	
	
	// If you're logged in to your own visit-center page
	if(Me::$id == $userData['uni_id'])
	{
		echo '
		<div style="font-weight:bold; font-size:1.2em; margin-top:22px;">Attract Users For Free Goodies</div>
		<div>Use these links to bring people to this page. Everyone that visits your page will earn you free components, coins, or other supplies!</div>
		
		<form class="uniform">
		<div style="margin-top:10px;">
			<div class="uc-note" style="font-weight:bold;">Direct Link To Your Visitation Page:</div>
			<input type="text" name="dir_link" value="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:16px;">
			<div class="uc-note" style="font-weight:bold;">BBCode For Visitation Page:</div>
			<input type="text" name="bb_link" value="[url=' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . ']' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '[/url]" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:16px;">
			<div class="uc-note" style="font-weight:bold;">HTML Link To Visitation Page:</div>
			<input type="text" name="bb_link" value=\'<a href="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '">' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '</a>\' style="width:100%;" readonly onclick="this.select();" />
		</div>
		</form>';
	}
	
echo '
	</div>
</div>';

// echo '<br /><a href="/' . $userData['handle'] . '?' . $linkPrepare . '">Connect Your Avatar</a>';

/*
	// <h3>A pet challenges you to a game of UniBall. Do you accept the challenge? (You\'re on, PET!)</h3>
*/

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
