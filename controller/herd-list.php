<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/training-center");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Get your list of herds
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$showNum = 30;
$morePages = false;

$herds = MyHerds::herdList($userData['uni_id'], $currentPage, $showNum);

// Check if there are more pages to the right
if(count($herds) == $showNum + 1)
{
	$morePages = true;
	array_pop($herds);
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
	' . MyBlocks::avatar($userData) . '
</div>
<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '
	
	<h1>' . $userData['display_name'] . '\'s Herds</h1>';

if(count($herds) > 0)
{
	// Cycle through each creature in the herd and display it
	foreach($herds as $herd)
	{
		echo '
		<div class="pet-cube">
			<div class="pet-cube-inner"><a href="' . $urlAdd . '/herds/' . $herd['family'] . '"><img src="/creatures/' . $herd['family'] . '/' . strtolower($herd['family']) . '.png" /></a></div>
			<div>' . $herd['family'] . ' Herd</div>
			<div style="font-size:0.8em;">Pop: ' . $herd['population'] . ', Points: ' . $herd['score'] . '</div>
		</div>';
	}
	
	echo '
	<div style="margin-top:12px;">';

	if($currentPage > 1)
	{
		echo '<a class="button" href="' . $urlAdd . '/herd-list?page=' . ($currentPage - 1) . '">Previous Page</a>';
	}

	if($morePages == true)
	{
		echo ' <a class="button" href="' . $urlAdd . '/herd-list?page=' . ($currentPage + 1) . '">Next Page</a>';
	}

	echo '
	</div>';
}
else
{
	echo (Me::$id == $userData['uni_id'] ? "You do" : $userData['display_name'] . " does") . " not currently have any herds.";
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
