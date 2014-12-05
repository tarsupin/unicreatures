<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the MyExplore Plugin ------
----------------------------------------

This plugin handles the exploration zones.


-------------------------------
------ Methods Available ------
-------------------------------

$encounterData = MyExplore::encounter($zone);
$zones = MyExplore::zoneList();

*/

abstract class MyExplore {
	
	
/****** Get random encounter from a zone ******/
	public static function encounter
	(
		string $zone			// <str> The zone that you're traversing for an encounter.
	): array <str, mixed>					// RETURNS <str:mixed> data for the encounter that you got.
	
	// $encounterData = MyExplore::encounter($zone);
	{
		if($zList = Cache::get("explore:" . $zone))
		{
			$zList = json_decode($zList, true);
		}
		else
		{
			// Get a random zone
			if(!$fetch = Database::selectMultiple("SELECT explore_id FROM explore_area WHERE type=?", array($zone)))
			{
				return array();
			}
			
			$zList = array();
			
			foreach($fetch as $f)
			{
				$zList[] = (int) $f['explore_id'];
			}
			
			Cache::set("explore:" . $zone, json_encode($zList), 60 * 60 * 24 * 15);
		}
		
		// Select a random address from the zone list
		$rnd = mt_rand(0, count($zList) - 1);
		
		// Return the area
		return Database::selectOne("SELECT explore_id, title, description, history FROM explore_area WHERE type=? AND explore_id=? LIMIT 1", array($zone, $zList[$rnd]));
	}
	
	
/****** Retrieve a list of areas ******/
	public static function zoneList
	(
		int $achievements = 99999	// <int> The number of achievements the user possesses.
	): array <str, array<str, mixed>>							// RETURNS <str:[str:mixed]> the list of exploration zones visible to user.
	
	// $zones = MyExplore::zoneList([$achievements]);
	{
		$zoneList = array(
			"great_plains"		=>	array("title" => 'The Great Plains',	'achievements'	=> 0)
		,	"sargasso"			=>	array("title" => 'Sargasso',			'achievements'	=> 0)
		,	"silva"				=>	array("title" => 'Silva',				'achievements'	=> 25)
		,	"new_atlantis"		=>	array("title" => 'New Atlantis',		'achievements'	=> 50)
		,	"mountains"			=>	array("title" => 'The Mountains',		'achievements'	=> 100)
		,	"old_city"			=>	array("title" => 'Old City',			'achievements'	=> 200)
		,	"red_sand"			=>	array("title" => 'Red Sand',			'achievements'	=> 300)
		,	"life_tree"			=>	array("title" => 'Life Tree',			'achievements'	=> 400)
		,	"cloud_city"		=>	array("title" => 'Cloud City',			'achievements'	=> 500)
		,	"skyland"			=>	array("title" => 'Skyland',				'achievements'	=> 600)
		,	"hephland"			=>	array("title" => 'Hephland',			'achievements'	=> 700)
		);
		
		if($achievements >= 99999)
		{
			return $zoneList;
		}
		
		$userZones = array();
		
		foreach($zoneList as $key => $val)
		{
			if($val['achievements'] > $achievements)
			{
				break;
			}
			
			$userZones[$key] = $zoneList[$key];
		}
		
		return $userZones;
	}
}