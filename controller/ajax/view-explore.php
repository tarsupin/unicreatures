<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(!isset($_POST['zone']))
{
	exit;
}

// Prepare Variables
$zone = $_POST['zone'];
$zoneList = MyExplore::zoneList();

// Make sure you're in a valid zone
if(!isset($zoneList[$zone]))
{
	exit;
}

// Get Map Data
//if(Cache::exists("uc2:explore-map-" . $zone))
{
	$data = json_decode(Cache::get("uc2:explore-map-" . $zone), true);
}
//else
{
	// get all currently available exploration pets
	$day = date("z");
	$data = Database::selectMultiple("SELECT ec.type_id, ec.day_start, ec.day_end, ct.family, ct.name, ct.prefix, ct.blurb, ct.rarity FROM explore_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE (ec.explore_zone=? OR ec.explore_zone='') AND ct.prefix != 'Noble' AND ct.prefix NOT LIKE 'Noble%' AND ct.prefix != 'Exalted' AND ct.prefix NOT LIKE 'Exalted%'", array($zone));
	
	function sortalphabetically($a, $b)
	{
		if($a['family'] != $b['family'])
			return strcmp($a['family'], $b['family']);
		return strcmp($a['prefix'], $b['prefix']);
	}
	
	usort($data, "sortalphabetically");

	// lasts until the next midnight
	$expire = mktime(0, 0, 0, date("n"), date("j")+1) - time();
	Cache::set("uc2:explore-map-" . $zone, json_encode($data), $expire);
}

$day = date("z");
$unavailable = array();
echo '<h3>Available in ' . $zoneList[$zone]['title'] . '</h3>';
foreach($data as $pet)
{
	if($pet['day_start'] != -1 && ($day < $pet['day_start'] || $day > $pet['day_end']))
	{
		$unavailable[] = $pet;
		continue;
	}

	echo MyBlocks::petInfo($pet);
}

function sortchronologically($a, $b)
{
	global $day;
	if($a['day_start'] - $day > 0)
		$time_a = $a['day_start'] - $day;
	elseif($a['day_start'] - $day < 0)
		$time_a = $a['day_start'] + 365 - $day;
	else
		$time_a = 0;
	if($b['day_start'] - $day > 0)
		$time_b = $b['day_start'] - $day;
	elseif($b['day_start'] - $day < 0)
		$time_b = $b['day_start'] + 365 - $day;
	else
		$time_b = 0;
	return ($time_a - $time_b);
}

usort($unavailable, "sortchronologically");

echo '<div style="clear:both;"></div><h3>Currently Unavailable</h3>';
foreach($unavailable as $pet)
	echo MyBlocks::petInfo($pet);
?>