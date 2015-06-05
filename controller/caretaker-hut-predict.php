<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// If not logged in, go to the welcome page
if(!Me::$loggedIn)
{
	Me::redirectLogin("/caretaker-hut-predict"); exit;
}

$confirm = new Confirm("prediction-" . Me::$id);
if(!$confirm->validate())
{
	Alert::saveError("No Access", "You need to redeem a certain type of coupon to gain access to this page.");
	header("Location: /caretaker-hut"); exit;
}

// occasionally delete expired entries
if(mt_rand(1, 50) == 22)
{
	$confirm->purge();
}

// Prepare Values
$data = $confirm->data;
$start = $confirm->dateCreated - $data['span'] * 3600;

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
	<div class="uc-static-block uc-bold">The Caretaker Hut Prediction</div>
	<div class="uc-bold-block">As predictions tend to do, these may be thrown off by a change in circumstances, including but not limited to the discovery of a new species.</div>
</div>
<div id="uc-right">';

// Caretaker Pets
echo '
<div>';

for($i=1; $i<=$confirm->data['span']; $i++)
{
	$time = $start + $i * 3600;
	$test = mktime(date("H", $time), 0, 0, date("n", $time), date("j", $time), date("Y", $time));
	if(time() >= $test)
		continue;
	
	$seed = date("ymdH", $time);
	$basket = MyAreas::checkBasket(Me::$id, (string) $seed);
	
	echo '<h2>' . date("M j, ga", $time) . '</h2>';
	
	foreach($basket as $typeID)
	{
		$pet = MyCreatures::petTypeData($typeID, "family, name, prefix, blurb, rarity");
		echo MyBlocks::petInfo($pet);
	}
	
	echo '<div style="clear:both;"></div>';
}

echo '
</div>';

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");