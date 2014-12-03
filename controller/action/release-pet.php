<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/release-pet"); exit;
}

// Make sure pet exists
if(!isset($url[2]))
{
	header("Location: /"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData($url[2], "id, uni_id, type_id, nickname, gender");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "family, name, prefix");

// If you moved the pet into an area
if(isset($_GET['release']) && Link::clicked())
{
	MyCreatures::deleteCreature($pet['id']);
	Alert::saveSuccess("Released Pet", "You have released " . $pet['nickname'] . " back to Esme!");
	header("Location: /land-plots"); exit;
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">
' . Alert::display();

echo '
<div>
	Are you sure you want to release ' . $pet['nickname'] . ' back into Esme? ' . ($pet['gender'] == 'm' ? "He" : "She") . ' will be free to roam the world of Esme, but will be impossible to track down again.<br />
	<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
	<br /><br /><a href="/action/release-pet/' . $pet['id'] . '?release=true&' . Link::prepare() . '">Yes, I understand ' . $pet['nickname'] . ' will be released forever.</a>
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
