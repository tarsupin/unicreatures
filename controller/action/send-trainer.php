<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/"); exit;
}

// Make sure you've chosen a pet to send
if(!isset($_GET['pet']))
{
	header("Location: /"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData((int) $_GET['pet'], "id, uni_id, type_id, nickname, experience, activity, active_until");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Make sure your pet isn't already busy
if($isBusy = MyCreatures::isBusy($pet['activity'], (int) $pet['active_until']))
{
	Alert::saveError("Already Busy", "That pet is busy.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix, evolution_level");

// Make sure your pet is trainable
if($petType['evolution_level'] < 2)
{
	Alert::saveError("Untrainable", "This pet cannot be trained yet. Try evolving it.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Get the creature's level
$level = MyTraining::getLevel((int) $pet['experience']);

// Determine cost of training
list($trainCost, $expGain) = MyTraining::getTrainingData($level);

// Get your supply of coins
$coins = MySupplies::getSupplies(Me::$id, "coins");

// If the user sent the creature to train
if($link = Link::clicked())
{
	if($link == "send-trainer-" . $pet['id'])
	{
		// If you spent the money
		if($coins > $trainCost && MySupplies::changeSupplies(Me::$id, "coins", 0 - $trainCost))
		{
			if(Database::query("UPDATE creatures_owned SET activity=?, active_until=? WHERE id=? LIMIT 1", array("training", time() + (60 * 60 * 20), $pet['id'])))
			{
				// Update the training center (in case they go visit it)
				MyCreatures::activityList(Me::$id, "training", true);
				
				$expGained = MyTraining::gainExp((int) $pet['id'], $expGain);
				$newLevel = MyTraining::getLevel($expGained + $pet['experience']);
				
				// Provide an achievement if you made level 5 or level 10
				if($newLevel > $level and $newLevel >= 5)
				{
					MyAchievements::set((int) $pet['uni_id'], $petType['family'], "trained", ($newLevel >= 10 ? 2 : 1));
				}
				
				Alert::saveSuccess("Sent to Training", 'This creature has been sent to the <a href="/training-center">training center</a>.');
				
				header("Location: /pet/" . $pet['id']); exit;
			}
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
<h3>Send your creature to the training center?</h3>
<p>' . $pet['nickname'] . ' is level ' . $level . '. It will cost ' . $trainCost . ' coins to train this pet for 1 day, and they will gain roughly ' . number_format($expGain) . ' experience.<p>';

if($coins > $trainCost)
{
	echo '
	<a class="button" href="/action/send-trainer?pet=' . $pet['id'] . '&' . Link::prepare("send-trainer-" . $pet['id']) . '">Send ' . $pet['nickname'] . ' to Training Center</a>';
}
else
{
	echo 'You cannot afford to train ' . $pet['nickname'] . ' right now.';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
