<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active herd
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/herds");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Prepare Values
$family = Sanitize::variable($url[1]);
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$showNum = 30;
$morePages = false;

// Remove a pet from the herd
if($link = Link::clicked() && isset($_GET['remove_type']) && isset($_GET['remove_nickname']))
{
	if($link == "remove-" . $family . "-" . $_GET['remove_type'] . "-" . $_GET['remove_nickname'])
	{
		if(MyHerds::removeFromHerd((int) $_GET['remove_type'], $_GET['remove_nickname']))
		{
			Alert::saveSuccess("Pet Removed", "The pet has been removed from the herd.");
		}
	}
}

// Check the herd
if(!$herdData = MyHerds::getData($userData['uni_id'], $family))
{
	header("Location: /herd-list"); exit;
}

// Get the list of the user's pets
$herdPets = MyHerds::getPets($userData['uni_id'], $family, $currentPage, $showNum);

// Check if there are more pages to the right
if(count($herdPets) == $showNum + 1)
{
	$morePages = true;
	array_pop($herdPets);
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
<div id="content">' . Alert::display() . '

<div id="uc-left">
	' . MyBlocks::avatar($userData) . '
	<div class="uc-bold-block">Herd Score: ' . $herdData['score'] . '</div>
	<div class="uc-note" style="text-align:center;">';	
$herdTypes = MyHerds::getTypes($userData['uni_id'], $family, (int) $herdData['population']);
foreach($herdTypes as $t)
{
	echo '
		' . ($t['prefix'] ? $t['prefix'] . ' ' : '') . $t['name'] . ': ' . $t['number'] . '<br/>';
}	
echo '
	</div>
</div>
<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '

<h2>' . (Me::$id == $userData['uni_id'] ? 'My ' : $userData['display_name'] . '\'s ') . $family . ' Herd</h2>';

// Cycle through each creature in the herd and display it
foreach($herdPets as $pet)
{
	echo '
	<div class="pet-cube">
		<div class="pet-cube-inner">
			<div><img src="' . MyCreatures::imgSrc($family, $pet['name'], $pet['prefix']) . '" /></div>
			<div>' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . ($userData['uni_id'] == Me::$id ? ' <a onclick="return confirm(\'Are you sure you want to remove ' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . ' from the herd and send it out into the world? It will not return to your areas. This cannot be undone.\');" href="/herds/' . $family . '?remove_type=' . $pet['type_id'] . '&remove_nickname=' . $pet['nickname'] . '&' . Link::prepare("remove-" . $family . "-" . $pet['type_id'] . "-" . $pet['nickname']) . '"><span class="icon-globe" title="Remove from Herd"></a>' : '') . '</div>
		</div>
	</div>';
}

echo '
	<div style="margin-top:12px;">';

	if($currentPage > 1)
	{
		echo '<a class="button" href="' . $urlAdd . '/herds/' . $family . '?page=' . ($currentPage - 1) . '">Previous Page</a>';
	}

	if($morePages == true)
	{
		echo ' <a class="button" href="' . $urlAdd . '/herds/' . $family . '?page=' . ($currentPage + 1) . '">Next Page</a>';
	}

	echo '
	</div>';

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
