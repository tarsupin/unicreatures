<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active herd
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Check the herd
if(!$herdData = MyHerds::herdData($url[1]))
{
	header("Location: /herd-list"); exit;
}

// Return a creature to the wild
if($link = Link::clicked() and $link == "return-to-wild")
{
	if(isset($_GET['wild']) && isset($_GET['nickname']) && isset($_GET['tID']))
	{
		$_GET['nickname'] = Sanitize::safeword($_GET['nickname'], " ");
		
		if(MyHerds::backToWild(Me::$id, $herdData['id'], $_GET['tID'] + 0, $_GET['nickname']))
		{
			Alert::success("Pet Moved", "You have returned \"" . $_GET['nickname'] . "\" to your wild.");
		}
	}
}

// Get the list of the user's pets
$herdPets = MyHerds::getPets($herdData['id']);

// Prepare Values
$linkProtect = Link::prepare("return-to-wild");

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
.pet { display:inline-block; padding:8px; text-align:center; }
.pet img { max-height: 120px; }
</style>';

echo '
<h2>' . $herdData['name'] . '</h2>';

// Cycle through each creature in the herd and display it
foreach($herdPets as $pet)
{
	echo '
	<div class="pet">
		<img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" />
		<br />' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . '
		<br /><a href="/herds/' . $herdData['id'] . '?wild=1&nickname=' . $pet['nickname'] . '&tID=' . $pet['type_id'] . '&' . $linkProtect . '">Return</a>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
