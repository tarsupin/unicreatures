<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/"); exit;
}

// Make sure the appropriate data is provided
if(!isset($_GET['area']) or !$area = MyAreas::areaData((int) $_GET['area']))
{
	header("Location: /"); exit;
}

// Make sure you own the area
if($area['uni_id'] != Me::$id)
{
	Alert::saveError("No Ownership", "You do not own that land plot.", 9);
	
	header("Location: /"); exit;
}

// Get pets from the area
$pets = MyAreas::areaPets((int) $area['id']);

if($link = Link::clicked() and $link == "change-pet-linebreak")
{
	if(isset($_GET['pet']))
	{
		if($petData = Database::selectOne("SELECT special FROM creatures_area WHERE area_id=? AND creature_id=? LIMIT 1", array($area['id'], $_GET['pet'] + 0)))
		{
			if(Database::query("UPDATE creatures_area SET special=? WHERE area_id=? AND creature_id=? LIMIT 1", array(1 - $petData['special'], $area['id'], $_GET['pet'] + 0)))
			{
				Alert::success("Linebreak Changed", "The linebreak has been " . ($petData['special'] ? "removed" : "added") . ".");
				foreach($pets as $key => $pet)
					if($pet['id'] == $_GET['pet'])
					{
						$pets[$key]['special'] = 1 - $petData['special'];
						break;
					}
			}
		}
	}
}

// Provide Instructions
Alert::info("Click Pet", "Click on the pet you would like to move.");

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
	<div class="uc-static-block"><a href="/area/' . $area['id'] . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a><div class="uc-bold">' . $area['name'] . '</div><div class="uc-note">' . $area['population'] . ' / ' . $area['max_population'] . '</div></div>
	' . MyBlocks::avatar(Me::$vals) . '
</div>

<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]);

$linkProtect = Link::prepare("change-pet-linebreak");
foreach($pets as $pet)
{
	echo MyBlocks::petPlain($pet, '/action/sort-pets-to?area=' . $area['id'] . '&s=' . $pet['sort_order'], ' <a href="/action/sort-pets?area=' . $area['id'] . '&pet=' . $pet['id'] . '&linebreak=' . (1 - $pet['special']) . '&' . $linkProtect . '" title="' . ($pet['special'] ? 'Remove' : 'Add') . ' Linebreak"><span class="icon-' . ($pet['special'] ? 'undo' : 'redo') . '"></span></a>');
	
	// Prepare a line break after this creature if necessary
	if($pet['special'])
	{
		echo '<div></div>';
	}
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
