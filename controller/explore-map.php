<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/explore-map"); exit;
}

// Get Map Data
if(Cache::exists("uc2:explore-map"))
{
	$data = json_decode(Cache::get("uc2:explore-map"), true);
}
else
{
	// get all currently available exploration pets
	$day = date("z");
	$fetchOptions = Database::selectMultiple("SELECT ec.explore_zone, ec.day_start, ec.day_end, ec.type_id, ct.family, ct.name, ct.prefix, ct.blurb, ct.rarity FROM explore_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ct.prefix != 'Noble' AND ct.prefix NOT LIKE 'Noble%' AND ct.prefix != 'Exalted' AND ct.prefix NOT LIKE 'Exalted%' AND ec.day_start = ? OR (ec.day_start <= ? AND ec.day_end >= ?) OR (ec.day_end >= ? AND ec.day_start <= ? AND ec.day_end >= ?)", array(-1, $day, $day, 365, $day+365, $day+365));
	$data = array();
	foreach($fetchOptions as $o)
	{
		$data[($o['explore_zone'] ? $o['explore_zone'] : "everywhere")][] = $o;
	}
	unset($fetchOptions);
	
	function sortalphabetically($a, $b)
	{
		if($a['family'] != $b['family'])
			return strcmp($a['family'], $b['family']);
		return strcmp($a['prefix'], $b['prefix']);
	}
	
	foreach($data as $key => $val)
	{
		usort($val, "sortalphabetically");
		$data[$key] = $val;
	}

	// lasts until the next midnight
	$expire = mktime(0, 0, 0, date("n"), date("j")+1) - time();
	Cache::set("uc2:explore-map", json_encode($data), $expire);
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

// Get List of Explore Zones
$zones = MyExplore::zoneList();
$zones['everywhere'] = array("title" => "All Exploration Zones");

foreach($zones as $key => $zone)
{
	echo "<h2>" . $zone['title'] . "</h2>";
	foreach($data[$key] as $pet)
		echo MyBlocks::petInfo($pet);
	
	echo '<div style="clear:both;"></div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
