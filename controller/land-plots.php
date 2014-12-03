<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/land-plots");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Retrieve the list of areas
$areas = MyAreas::areas($userData['uni_id']);

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
.area { display:inline-block; padding:8px; text-align:center; }
</style>';

echo '
<div id="plot-page-left">
	<div id="lp-caretaker" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="lp-bold">' . $userData['display_name'] . '</div></div>';
	
if(Me::$id == $userData['uni_id'])
{
	echo '
	<div style="margin-top:10px; text-align:center; font-size:1.1em;"><a href="/action/edit-plots">Edit Areas</a></div>';
}

echo '
</div>
<div id="plot-page-right">';

// The Wild Area
echo '
<div class="area">
	<a href="/wild"><img src="/assets/areas/wild.png" /></a>
	<div class="lp-bold">Wild Area</div>
	<div class="lp-note">Unlimited</div>
</div>';

foreach($areas as $area)
{
	echo '
	<div class="area">
		<a href="' . $urlAdd . '/area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<div class="lp-bold">' . $area['name'] . '</div>
		<div class="lp-note">' . $area['population'] . ' / ' . $area['max_population'] . '</div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
