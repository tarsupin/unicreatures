<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure a url handle was provided
if(!isset($url[0]) or $url[0] == "profile")
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
$urlAdd = "/" . $userData['handle'];

You::$id = $userData['uni_id'];
You::$handle = $userData['handle'];
You::$name = $userData['display_name'];

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
if(MyIPTrack::userTracker($userData['uni_id']))
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
		$total = 50;	// 250
	}
	else if($count <= 25)
	{
		$total = 10;	// 200
	}
	else if($count <= 100)
	{
		$total = 5;		// 375
	}
	else if($count <= 200)
	{
		$total = 4;		// 400
	}
	else if($count <= 300)
	{
		$total = 3;		// 300
	}
	else if($count <= 500)
	{
		$total = 2;		// 400
	}
	else if($count <= 800)
	{
		$total = 1;		// 300
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

// Supply List
$supplies = MySupplies::getSupplyList($userData['uni_id']);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display();

// Display the left side of the page
echo '
<style>
.dual-col-item { display:inline-block; width:45%; padding:1%; margin-bottom:12px; }
</style>

<div id="plot-page-left">
	<div id="lp-caretaker" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="lp-bold">' . $userData['display_name'] . '</div></div>
	<div id="pet-rare-act" style="margin-top:12px;">
		<div class="dual-col-item"><img src="/assets/supplies/component_bag.png" /><div class="pet-rare-title">Components</div><div class="pet-rare-note">' . number_format($supplies['components']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/coins_large.png" /><div class="pet-rare-title">Coins</div><div class="pet-rare-note">' . number_format($supplies['coins']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/supplies.png" /><div class="pet-rare-title">Crafting</div><div class="pet-rare-note">' . number_format($supplies['crafting']) . '</div></div>
		<div class="dual-col-item"><img src="/assets/supplies/tree_seeds.png" /><div class="pet-rare-title">Alchemy</div><div class="pet-rare-note">' . number_format($supplies['alchemy']) . '</div></div>
	</div>
</div>
<div id="plot-page-right">
	<div id="pet-rare-act">
		<div style="font-size:1.1em; font-weight:bold; margin-bottom:12px;">Visit ' . $userData['display_name'] . '\'s Pages</div>
		<div class="pet-rare-bub"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/trophy_gold.png" /></a><div class="pet-rare-title">Achievements</div><div class="pet-rare-note">&nbsp;</div></div>
		<div class="pet-rare-bub"><a href="' . $urlAdd . '/land-plots"><img src="/assets/icons/cabin.png" /></a><div class="pet-rare-title">Pet Areas</div><div class="pet-rare-note">&nbsp;</div></div>
		<div class="pet-rare-bub"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/herd.png" /></a><div class="pet-rare-title">Herds</div><div class="pet-rare-note">&nbsp;</div></div>
	</div>
	<div id="pet-desc">';
	
	// Provide Treasures to the user
	if(MyTreasure::$treasure)
	{
		echo '
		<h3>You gave ' . $userData['display_name'] . ' the following gifts:</h3>';
		
		foreach(MyTreasure::$treasure as $treasureData)
		{
			if($treasureData['count'] > 0)
			{
				echo '
				<div style="display:inline-block; text-align:center; width:130px;"><img src="' . $treasureData['image'] . '" /><div style="font-size:0.9em;">' . $treasureData['title'] . '</div><div style="font-size:0.9em;">' . $treasureData['count'] . ' Given</div></div>';
			}
		}
	}
	
	echo '
	<div style="margin-top:22px;">So far, ' . ($count == 1 ? '1 person has' : $count . ' people have') . " given gifts to " . $userData['display_name'] . " today.</div>";
	
echo '
	</div>
</div>';

// echo '<br /><a href="/' . $userData['handle'] . '?' . $linkPrepare . '">Connect Your Avatar</a>';

/*

// <h3>A pet challenges you to a game of UniBall. Do you accept the challenge? (You\'re on, PET!)</h3>

echo '
<br /><a href="/training-center">View Training Center</a>

<br />
<br />Direct Link to My UC Page:<br /><textarea style="min-width:300px; min-height:50px;">' . htmlspecialchars(SITE_URL . '/visit/' . $userData['handle']) . '</textarea>
<br />(Note: every visitor to this page earns you a gift)

<br />
<br />HTML Tag to My UC Page:<br /><textarea style="min-width:300px; min-height:50px;">' . htmlspecialchars('<a href="' . SITE_URL . '/visit/' . $userData['handle'] . '">Visit My UniCreatures</a>') . '</textarea>
<br />(Note: every visitor to this page earns you a gift)

';

*/

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
