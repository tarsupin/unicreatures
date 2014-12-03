<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(!Me::$loggedIn)
{
	Me::redirectLogin("/treasure-queue");
}

// Retrieve the user's treasure queue
if(!$queueData = MyTreasure::getQueue(Me::$id))
{
	Alert::saveError("No Treasure Queue", "There is nothing in your treasure queue.");
	
	header("Location: /" . Me::$vals['handle']); exit;
}

// Collect something from the queue
if($link = Link::clicked() and $link == "treasure-queue" and isset($_GET['dd']))
{
	if(MyTreasure::retrieveQueueItem(Me::$id, (int) $_GET['dd']))
	{
		Alert::saveSuccess("Retrieved Pet", "The pet has been added to your Wild.");
		
		header("Location: /treasure-queue"); exit;
	}
}

// Prepare Values
$linkProtect = Link::prepare("treasure-queue");

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display();

echo '
<h2>The Treasure Queue</h2>';

if(!$queueData)
{
	echo '
	<p>There is currently no treasure available. The treasure queue will populate with the eggs and special treasures you find during exploration, or from other special events. You can come to the queue to claim them.</p>';
}

foreach($queueData as $treasure)
{
	if($treasure['treasure'] == "pet")
	{
		$details = json_decode($treasure['json'], true);
		
		$prefix = $details['petData']['prefix'];
		$family = $details['petData']['family'];
		$name = $details['petData']['name'];
		
		echo '
		<p><a href="/treasure-queue?dd=' . $treasure['date_disappears'] . '&' . $linkProtect . '">Collect ' . ($prefix ? $prefix . " " : "") . ($name == "Egg" ? $family . " " . $name : $name) . ' <img src="' . $details['image'] . '" /></a> (Will be collected by the Caretaker Hut ' . Time::fuzzy($treasure['date_disappears']) . ')</p>';
	}
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
