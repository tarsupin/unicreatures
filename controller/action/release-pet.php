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
$pet = MyCreatures::petData((int) $url[2], "id, uni_id, type_id, nickname, gender");

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, prefix");
$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $petType['prefix']);

// If you moved the pet into an area
if(isset($_GET['release']) and $value = Link::clicked() and $value == "release-pet-uc")
{
	MyCreatures::deleteCreature((int) $pet['id']);
	Alert::saveSuccess("Released Pet", "You have released " . $pet['nickname'] . " back to Esme!");
	header("Location: /"); exit;
}

// Prepare Link Protection
$linkProtect = Link::prepare("release-pet-uc");

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
	' . MyBlocks::avatar(Me::$vals) . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]) . '
	
	<div>
		<div class="uc-bold">Are you sure you want to release ' . $pet['nickname'] . ' back into Esme? ' . ($pet['gender'] == 'm' ? "He" : "She") . ' will be free to roam the world of Esme, but will be impossible to track down again.</div>
		
		<div class="pet-cube"><div class="pet-cube-inner"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></div>
		
		<div class="uc-note">' . ($prefix != "" && $pet['nickname'] == $petType['name'] ? $prefix . " " : "") . ($petType['name'] == "Egg" && $pet['nickname'] == "Egg" ? $petType['family'] . ' Egg' : $pet['nickname']) . (MyCreatures::petRoyalty($petType['prefix']) != "" ? ' <img src="/assets/medals/' . MyCreatures::petRoyalty($petType['prefix']) . '.png" />' : '') . '</div></div>
		
		<div class="uc-action-block"><a href="/action/release-pet/' . $pet['id'] . '?release=true&' . $linkProtect . '" style="display:block; padding:4px;">Yes, I understand ' . $pet['nickname'] . ' will be released forever.</a></div>
	</div>

</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
