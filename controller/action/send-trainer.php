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
				
				$expGained = MyTraining::gainExp((int) $pet['id'], (int) $expGain);
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

// Supply List
$supplies = MySupplies::getSupplyList(Me::$id);

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
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . '"><img src="' . (Me::$vals['avatar_opt'] ? Avatar::image((int) Me::$id, (int) Me::$vals['avatar_opt']) : ProfilePic::image((int) Me::$id, "huge")) . '" /></a><div class="uc-bold">' . Me::$vals['display_name'] . '</div></div>
	
	<div class="uc-action-block hide-600">
		<div class="supply-block"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note">' . number_format($supplies['components']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note">' . number_format($supplies['coins']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note">' . number_format($supplies['crafting']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note">' . number_format($supplies['alchemy']) . '</div></div>
	</div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>';

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
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
