<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active herd
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Prepare Values
$family = Sanitize::variable($url[1]);

// Check the herd
if(!$population = MyHerds::population(Me::$id, $family))
{
	header("Location: /herd-list"); exit;
}

// Get the list of the user's pets
$herdPets = MyHerds::getPets(Me::$id, $family);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">
' . Alert::display() . '

<style>
.pet { display:inline-block; padding:8px; text-align:center; }
.pet img { max-height: 120px; }
</style>';

echo '
<h2>The ' . $family . ' Herd</h2>';

// Cycle through each creature in the herd and display it
foreach($herdPets as $pet)
{
	echo '
	<div class="pet">
		<img src="' . MyCreatures::imgSrc($family, $pet['name'], $pet['prefix']) . '" />
		<div>' . $pet['nickname'] . '</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
