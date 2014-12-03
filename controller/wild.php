<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare Values
$urlAdd = '';

// Get the necessary user data
if(isset($url[1]))
{
	if(!$userData = User::getDataByHandle(Sanitize::variable($url[1]), "uni_id, handle, display_name, avatar_opt"))
	{
		header("Location: /"); exit;
	}
	
	$urlAdd = '/' . $userData['handle'];
}
else if(!Me::$loggedIn)
{
	Me::redirectLogin("/wild"); exit;
}
else
{
	$userData = Me::$vals;
}

// Get pets from the wild
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$showNum = 35;
$morePages = false;

$pets = MyAreas::wildPets(Me::$id, $currentPage, $showNum);

// Check if there are more pages to the right
if(count($pets) == $showNum + 1)
{
	$morePages = true;
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display();

echo '
<div id="plot-page-left">
	<div id="land-plot"><img src="/assets/areas/wild.png" /><div class="lp-bold">Wild Area</div><div class="lp-note">Unlimited Population</div></div>
	<div id="lp-caretaker"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="lp-bold">' . $userData['display_name'] . '</div></div>
</div>
<div id="plot-page-right">';

foreach($pets as $pet)
{
	echo '
	<div class="plot-pet"><div class="plot-pet-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div>' . $pet['nickname'] . '</div></div>';
}

echo '
<div style="margin-top:12px;">';

if($currentPage > 1)
{
	echo '<a class="button" href="/wild' . $urlAdd . '?page=' . ($currentPage - 1) . '">Previous Page</a>';
}

if($morePages == true)
{
	echo ' <a class="button" href="/wild' . $urlAdd . '?page=' . ($currentPage + 1) . '">Next Page</a>';
}

echo '
</div>';

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
