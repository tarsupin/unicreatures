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
<div id="content" style="overflow:hidden;">' . Alert::display();

echo '
<div id="plot-page-left"><div id="lp-caretaker" style="margin-top:0px;">
	<img src="' . ProfilePic::image((int) $userData['uni_id'], "huge") . '" />
	<div class="lp-bold">' . $userData['display_name'] . '</div>
</div></div>
<div id="plot-page-right">';

if(count($herds) > 0)
{
	// Cycle through each creature in the herd and display it
	foreach($herds as $herd)
	{
		echo '
		<div class="plot-pet">
			<div class="plot-pet-inner"><a href="' . $urlAdd . '/herds/' . $herd['family'] . '"><img src="/creatures/' . $herd['family'] . '/' . strtolower($herd['family']) . '.png" /></a></div>
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
