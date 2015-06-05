<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Count the number of UniCreatures
$count = Database::selectValue("SELECT COUNT(*) FROM creatures_types", array());

// Prepare some example UniCreatures to show off
$showList = array("Sakuya", "Qiana", "Pearl", "Ori", "Nishiki", "Jolie", "Arishia", "Bamboo", "Cardi", "Libra", "Leo", "Kun", "Gedris", "Chen", "Culican", "Darini");

shuffle($showList);

$showList = array_splice($showList, 0, 8);

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
	<div class="uc-static-block uc-bold"><a href="/login" style="display:block;">Join UniCreatures Now!</a></div>
	<div class="uc-bold" style="text-align:center;">Collect all ' . $count . ' UniCreatures!</div>
</div>
<div id="uc-right-wide">
	<h1>Welcome to UniCreatures!</h1>';

foreach($showList as $pet)
{
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><img src="' . MyCreatures::imgSrc($pet, $pet, "") . '" /></div><div class="uc-bold">' . $pet . '</div></div>';
}

echo '
	<h2 style="margin-top:42px;">How to Play</h2>
	<div>UniCreatures is a virtual pet site where you take care of pets and help them evolve. As you progress through the game, you can collect more pets, unlock new areas to explore, and gain recognition for your achievements. You can also help your friends by visiting their pets!</div>
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
