<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Determine the user being viewed
if(isset($url[1]))
{
	if(!$userData = User::getDataByHandle(Sanitize::variable($url[1]), "uni_id, handle, display_name"))
	{
		header("Location: /"); exit;
	}
	
	You::$id = (int) $userData['uni_id'];
	You::$handle = $userData['handle'];
}
else
{
	if(!Me::$loggedIn)
	{
		header("Location: /"); exit;
	}
	
	$userData = Me::$vals;
}

// Get your list of herds
$herds = MyHerds::herdList(Me::$id);

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
	<div class="uc-static-block" style="margin-top:0px;">
		<img src="' . ProfilePic::image((int) $userData['uni_id'], "huge") . '" />
		<div class="uc-bold">' . $userData['display_name'] . '</div>
	</div>
</div>
<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . $userData['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline" style="opacity:0.7;"><img src="/assets/icons/button_herds.png" /><div class="uc-note-bold">Herds</div></div>
	</div>';

if(count($herds) > 0)
{
	// Cycle through each creature in the herd and display it
	foreach($herds as $herd)
	{
		echo '
		<div class="pet-cube">
			<div class="pet-cube-inner"><a href="' . $urlAdd . '/herds/' . $herd['family'] . '"><img src="/creatures/' . $herd['family'] . '/' . strtolower($herd['family']) . '.png" /></a></div>
			<div>' . $herd['family'] . ' Herd</div>
			<div style="font-size:0.9em;">Pop: ' . $herd['population'] . '</div>
		</div>';
	}
}
else
{
	echo "You do not currently have any herds.";
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
