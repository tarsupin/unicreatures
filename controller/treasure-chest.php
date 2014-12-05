<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(!Me::$loggedIn)
{
	Me::redirectLogin("/treasure-chest");
}

// Retrieve the user's treasure queue
$queueData = MyTreasure::getQueue(Me::$id);

// Collect something from the queue
if($link = Link::clicked() and $link == "treasure-chest" and isset($_GET['dd']))
{
	if(MyTreasure::retrieveQueueItem(Me::$id, (int) $_GET['dd']))
	{
		Alert::saveSuccess("Retrieved Pet", "The pet has been added to your Wild.");
		
		header("Location: /treasure-chest"); exit;
	}
}

// Prepare Values
$linkProtect = Link::prepare("treasure-chest");

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
</div>
<div id="uc-right-wide">
<h1>Your Treasure Chest</h1>';

if(!$queueData)
{
	echo '
	<p>There is currently no treasure in your treasure chest. The treasure chest will populate with the eggs and special treasures that you find during exploration or from other special events. You can come to this chest to claim them.</p>
	<p>Don\'t forget, any treasures that you leave here for two days will be reclaimed by the Caretaker Hut to be cared for and tended to.</p>';
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
		<p><a href="/treasure-chest?dd=' . $treasure['date_disappears'] . '&' . $linkProtect . '">Collect ' . ($prefix ? $prefix . " " : "") . ($name == "Egg" ? $family . " " . $name : $name) . ' <img src="' . $details['image'] . '" /></a> (Will be collected by the Caretaker Hut ' . Time::fuzzy((int) $treasure['date_disappears']) . ')</p>';
	}
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
