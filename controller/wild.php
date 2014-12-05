<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the necessary user data
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/wild"); exit;
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Get pets from the wild
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$showNum = 35;
$morePages = false;

$pets = MyAreas::wildPets($userData['uni_id'], $currentPage, $showNum);

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
<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo '
<div id="uc-left">
	<div class="uc-static-block"><img src="/assets/areas/wild.png" /><div class="uc-bold">Wild Area</div><div class="uc-note">Unlimited Population</div></div>
	<div class="uc-static-block"><a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '"><img src="' . ($userData['avatar_opt'] ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ProfilePic::image((int) $userData['uni_id'], "huge")) . '" /></a><div class="uc-bold">' . $userData['display_name'] . '</div></div>
</div>
<div id="uc-right">';

foreach($pets as $pet)
{
	echo '
	<div class="pet-cube"><div class="pet-cube-inner"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></a></div><div class="uc-note">' . $pet['nickname'] . '</div></div>';
}

echo '
<div style="margin-top:12px;">';

if($currentPage > 1)
{
	echo '<a class="button" href="' . $urlAdd . '/wild?page=' . ($currentPage - 1) . '">Previous Page</a>';
}

if($morePages == true)
{
	echo ' <a class="button" href="' . $urlAdd . '/wild?page=' . ($currentPage + 1) . '">Next Page</a>';
}

echo '
</div>';

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
