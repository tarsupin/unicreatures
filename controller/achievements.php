<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/achievements");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Get achievements
$achievements = MyAchievements::getList($userData['uni_id']);

$score = MySupplies::getSupplies($userData['uni_id'], "achievements");

// Prepare the Page's Active Hashtag
$config['active-hashtag'] = "UniCreatures";

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '

<style>
	.ach-row { display:table-row; }
	.ach-lcell { display:table-cell; padding:4px; border:solid 1px #eeeeee; }
	.ach-ccell { display:table-cell; padding:4px; text-align:center; border:solid 1px #eeeeee; }
	.ach-done { background-color:#bbffbb; }
</style>

<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="uc-bold">' . $userData['display_name'] . '</div></div>
</div>
<div id="uc-right">

	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . $userData['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_trophy.png" /><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>
	
<h2>' . (You::$name ? You::$name . "'s " : '') . 'Achievements</h2>
<div style="margin-bottom:18px;">' . (You::$name ? You::$name . " has " : 'You have') . ' acquired ' . $score . ' total achievements.</div>

	<div class="ach-row" style="font-weight:bold;">
		<div class="ach-lcell">Family</div>
		<div class="ach-ccell">Evolutions</div>
		<div class="ach-ccell">Trained</div>
		<div class="ach-ccell">Royalty</div>
		<div class="ach-ccell">Herd</div>
		<div class="ach-ccell">Awards</div>
	</div>';

foreach($achievements as $ach)
{
	echo '
	<div class="ach-row">
		<div class="ach-lcell' . ($ach['finished'] == 1 ? ' ach-done' : '') . '">' . $ach['creature_family'] . '</div>
		<div class="ach-ccell' . ($ach['fully_evolved'] == 1 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['evolutions'], "*") . '</div>
		<div class="ach-ccell' . ($ach['trained'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['trained'], "*") . '</div>
		<div class="ach-ccell' . ($ach['royalty'] == 3 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['royalty'], "*") . '</div>
		<div class="ach-ccell' . ($ach['herd'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['herd'], "*") . '</div>
		<div class="ach-ccell' . ($ach['awards'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['awards'], "*") . '</div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
