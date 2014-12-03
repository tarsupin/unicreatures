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

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display() . '

<style>
	.ach-row { display:table-row; }
	.ach-lcell { display:table-cell; padding:4px; border:solid 1px #eeeeee; }
	.ach-ccell { display:table-cell; padding:4px; text-align:center; border:solid 1px #eeeeee; }
	.ach-done { background-color:#bbffbb; }
</style>

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
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
