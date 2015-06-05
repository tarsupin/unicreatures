<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(!Me::$loggedIn)
{
	Me::redirectLogin("/treasure-chest");
}

// Retrieve the user's treasure queue
$queueData = MyTreasure::getQueue(Me::$id);

// Collect something from the queue
if($link = Link::clicked() and isset($_GET['dd']))
{
	if($link == "treasure-chest-pet")
	{
		if(MyTreasure::retrieveQueueItem(Me::$id, (int) $_GET['dd']))
		{
			Alert::saveSuccess("Retrieved Pet", 'The pet has been added to your Wild. <a href="/pet/' . Database::$lastID . '">Would you like to visit it?</a>');
			
			header("Location: /treasure-chest"); exit;
		}
	}
	elseif($link == "treasure-chest-pet-del")
	{
		if(MyTreasure::removeFromQueue(Me::$id, (int) $_GET['dd']))
		{
			Alert::saveSuccess("Removed Pet", 'The pet has been collected by the Caretaker Hut.');
			
			header("Location: /treasure-chest"); exit;
		}
	}
	if($link == "treasure-chest-energy")
	{
		if(MyTreasure::retrieveQueueItem(Me::$id, (int) $_GET['dd']))
		{
			Alert::saveSuccess("Retrieved Coupon", 'The coupon has been redeemed. <a href="/explore-zones">Would you like to go exploring?</a>');
			
			header("Location: /treasure-chest"); exit;
		}
	}
	if($link == "treasure-chest-prediction")
	{
		$confirm = new Confirm("prediction-" . Me::$id);
		if(!$confirm->validate())
		{
			if(MyTreasure::retrieveQueueItem(Me::$id, (int) $_GET['dd']))
			{
				Alert::saveSuccess("Retrieved Coupon", 'The coupon has been redeemed. <a href="/caretaker-hut-predict">Would you like to see the Caretaker Hut Prediction?</a>');
				
				header("Location: /treasure-chest"); exit;
			}
		}
		else
		{
			Alert::error("Access Active", 'You already have an active coupon for this. Please wait until its use has expired. <a href="/caretaker-hut-predict">Would you like to see the Caretaker Hut Prediction?</a>');
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("treasure-chest-pet");
$linkProtectEnergy = Link::prepare("treasure-chest-energy");
$linkProtectPrediction = Link::prepare("treasure-chest-prediction");
$linkProtectDel = Link::prepare("treasure-chest-pet-del");

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
	<div class="uc-static-block">
		<img src="/assets/icons/chest.png" />
		<div class="uc-bold">The Treasure Chest</div>
		<div class="uc-note">' . count($queueData) . ' Treasures Available</div>
	</div>
	' . MyBlocks::inventory() . '
</div>
<div id="uc-right-wide">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
<h1>Your Treasure Chest</h1>';

if(!$queueData)
{
	echo '
	<p>There is currently no treasure in your treasure chest. The treasure chest will populate with the eggs and special treasures that you find during exploration or from other special events. You can come to this chest to claim them.</p>
	<p>Don\'t forget, any eggs that you leave here for two days will be reclaimed by the Caretaker Hut to be cared for and tended to.</p>';
}

foreach($queueData as $treasure)
{
	if($treasure['treasure'] == "pet")
	{
		$details = json_decode($treasure['json'], true);
		
		$prefix = $details['petData']['prefix'];
		$family = $details['petData']['family'];
		$name = $details['petData']['name'];
		
		$royalty = MyCreatures::petRoyalty($prefix);
		
		echo '
		<p><a href="/treasure-chest?dd=' . $treasure['date_disappears'] . '&' . $linkProtect . '"><img src="' . $details['image'] . '" /> ' . ($prefix ? $prefix . " " : "") . ($name == "Egg" ? $family . " " . $name : $name) . '</a>  <a href="javascript:viewAchievements(\'' . $family . '\');" title="View Achievements"><span class="icon-circle-info"></span></a>(will be collected by the Caretaker Hut ' . Time::fuzzy((int) $treasure['date_disappears']) . ' / <a href="/treasure-chest?dd=' . $treasure['date_disappears'] . '&' . $linkProtectDel . '" onclick="return confirm(\'Are you sure you want to give this pet to the Caretaker Hut? You will not be able to retrieve it.\');">now</a>)</p>';
	}
	elseif($treasure['treasure'] == "energy")
	{
		$details = json_decode($treasure['json'], true);
		
		echo '
		<p><a href="/treasure-chest?dd=' . $treasure['date_disappears'] . '&' . $linkProtectEnergy . '"><img src="' . $details['image'] . '" /> ' . $details['count'] . ' Energy</a> (will expire ' . Time::fuzzy((int) $treasure['date_disappears']) . ')</p>';
	}
	elseif($treasure['treasure'] == "prediction")
	{
		$details = json_decode($treasure['json'], true);
		
		echo '
		<p><a href="/treasure-chest?dd=' . $treasure['date_disappears'] . '&' . $linkProtectPrediction . '"><img src="' . $details['image'] . '" /> ' . $details['span'] . 'h Caretaker Hut Prediction</a> (will expire ' . Time::fuzzy((int) $treasure['date_disappears']) . ')</p>';
	}
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
