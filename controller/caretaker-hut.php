<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare Values
$showHut = false;

// Make sure the user hasn't gathered an egg this hour
$checkLastGather = Cache::get("user_gathered_hut:" . Me::$id);
$dateCheck = date("zH");

// If you haven't gathered this hour
if($checkLastGather != $dateCheck)
{
	$showHut = true;
	$basket = MyAreas::checkBasket(Me::$id);
	
	// If the user is attempting to gather an egg from the caretaker hut
	if(isset($_GET['gather']))
	{
		if(Me::$loggedIn)
		{
			$gatherID = (int) $_GET['gather'];
			
			// Check if the type is in the caretaker hut
			if(in_array($gatherID, $basket))
			{
				// Acquire the Egg
				if($creatureID = MyCreatures::acquireCreature(Me::$id, $gatherID))
				{
					// Prevent user from acquiring another for the next hour
					Cache::set("user_gathered_hut:" . Me::$id, $dateCheck, 60 * 61);
					
					header("Location: /pet/" . $creatureID);
				}
				else
				{
					Alert::error("Egg Error", "An error has occurred while trying to gather a pet.", 1);
				}
			}
		}
		else
		{
			Alert::error("Guest Account", "You'll have to log in to collect an egg.");
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
<div id="uc-left-wide">
	<div class="uc-static-block uc-bold">The Caretaker Hut</div>
	<div class="uc-bold-block">You can collect ONE egg here every hour. Choose wisely!</div>
</div>
<div id="uc-right-wide">';

if($showHut == true)
{
	// Caretaker Pets
	echo '
	<div style="text-align:center;">';
	
	foreach($basket as $typeID)
	{
		$typeData = MyCreatures::petTypeData($typeID, "family, name, prefix, blurb");
		
		echo '
		<p>
			<a href="/caretaker-hut?gather=' . $typeID . '"><img src="' . MyCreatures::imgSrc($typeData['family'], $typeData['name'], $typeData['prefix']) . '" /></a>
			<div style="font-size:1.1em;">' . $typeData['blurb'] . '</div>
			' . ($typeData['prefix'] != "" ? '<div style="font-size:0.9em; background-color:#abcdef; display:inline-block; padding:2px 6px 2px 6px; border-radius:6px;">' . $typeData['prefix'] . '</div>' : '') . '
		</p>';
	}
	
	echo '
	</div>';
}
else
{
	echo '
	<div>You\'ve already chosen a pet from the caretaker hut this hour. You can return next hour.</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
