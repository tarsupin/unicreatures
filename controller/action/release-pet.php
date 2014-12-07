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
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . '"><img src="' . (Me::$vals['avatar_opt'] ? Avatar::image((int) Me::$id, (int) Me::$vals['avatar_opt']) : ProfilePic::image((int) Me::$id, "huge")) . '" /></a><div class="uc-bold">' . Me::$vals['display_name'] . '</div></div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	
	<div>
		<div class="uc-bold">Are you sure you want to release ' . $pet['nickname'] . ' back into Esme? ' . ($pet['gender'] == 'm' ? "He" : "She") . ' will be free to roam the world of Esme, but will be impossible to track down again.</div>
		
		<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" />
		
		<div class="uc-action-block"><a href="/action/release-pet/' . $pet['id'] . '?release=true&' . $linkProtect . '" style="display:block; padding:4px;">Yes, I understand ' . $pet['nickname'] . ' will be released forever.</a></div>
	</div>

</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
