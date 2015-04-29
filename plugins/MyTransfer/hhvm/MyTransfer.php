<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the MyTransfer Plugin ------
-----------------------------------------

This plugin allows you to handle the UniCreature database during the transferring process.


-------------------------------
------ Methods Available ------
-------------------------------

$oldID = MyTransfer::user($uniID);

MyTransfer::pets($uniID);
MyTransfer::areas($uniID);

*/

abstract class MyTransfer {
	
	
/****** Get the old user ID ******/
	public static function user
	(
		int $uniID			// <int> The Uni-Account to match for the old user ID.
	): int					// RETURNS <int> the ID of the old user, or 0 on failure.
	
	// $oldID = MyTransfer::user($uniID);
	{
		return (int) Database::selectValue("SELECT old_id FROM transfer_users WHERE uni_id=? LIMIT 1", array($uniID));
	}
	
	
/****** Transfer Pets ******/
	public static function pets
	(
		int $uniID			// <int> The Uni-Account acquiring the creature.
	): void					// RETURNS <void>
	
	// MyTransfer::pets($uniID);
	{
		$oldID = self::user($uniID);
		
		if(!$oldID) { return; }
		
		// Transfer the pets
		$fetch = Database::selectMultiple("SELECT c.id, c.total_points, c.image, c.nickname, c.gender, c.date_acquired, c.is_rare, ct.family, ct.name FROM creatures c INNER JOIN creatures_types ct ON ct.id=c.type_id WHERE uni_id=?", array($oldID));
		
		Database::startTransaction();
		foreach($fetch as $creature)
		{
			// Determine the Prefix of the Creature
			// Color
			$prefix = "";
			$elements = explode("_", substr($creature['image'], 0, -4));
			if (count($elements) >= 2 && $elements[0] != "noble" && $elements[0] != "exalted" && strtolower($creature['family']) == $elements[1])
				$prefix = ucfirst($elements[0]);
			elseif (count($elements) >= 3 && ($elements[0] == "noble" || $elements[0] == "exalted") && strtolower($creature['family']) == $elements[2])
				$prefix = ucfirst($elements[1]);
			
			// Royalty
			if($creature['is_rare'] == 1) { $prefix = "Noble" . ($prefix != "" ? " " . $prefix : ""); }
			else if($creature['is_rare'] == 2) { $prefix = "Exalted" . ($prefix != "" ? " " . $prefix : ""); }
			
			// Gather experience for creature
			$exp = Database::selectValue("SELECT SUM(strength + agility + speed + intelligence + wisdom + charisma + creativity + willpower + focus) as val FROM creature_abilities WHERE creature_id=? LIMIT 1", array($creature['id']));
			
			$exp = (!$exp ? 0 : ceil(($exp - 63) * 10));
			
			// Determine the creature type
			$type = Database::selectOne("SELECT id, evolution_level FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($creature['family'], $creature['name'], $prefix));
			
			Database::query("INSERT INTO creatures_owned (uni_id, type_id, nickname, gender, total_points, date_acquired, experience) VALUES (?, ?, ?, ?, ?, ?, ?)", array($uniID, (int) $type['id'], $creature['nickname'], $creature['gender'], $creature['total_points'], $creature['date_acquired'], $exp));
			
			MyAchievements::set($uniID, $creature['family'], "evolutions", (int) $type['evolution_level']);
			$level = MyTraining::getLevel((int) $pet['experience']);
			if($level >= 5) { MyAchievements::set($uniID, $creature['family'], "trained", ($level >= 10 ? 2 : 1)); }
			if($creature['is_rare'] == 1) { MyAchievements::set($uniID, $creature['family'], "royalty", 1); }
			else if($creature['is_rare'] == 2) { MyAchievements::set($uniID, $creature['family'], "royalty", 2); }
		}
		
		Database::query("DELETE FROM creatures WHERE uni_id=?", array($oldID));
		Database::endTransaction();
	}
	
	
/****** Transfer Areas ******/
	public static function areas
	(
		int $uniID			// <int> The Uni-Account acquiring the areas.
	): void					// RETURNS <void>
	
	// MyTransfer::areas($uniID);
	{
		$oldID = self::user($uniID);
		
		if(!$oldID) { return; }
		
		$sortOrder = 0;
		
		// Change naming convention of areas
		$switch = array(
			'castle_1'		=>	'castle_ruins'
		,	'castle_2'		=>	'fortress'
		,	'meadow_2'		=>	'farmstead'
		,	'meadow_3'		=>	'country_house'
		,	'beach_2'		=>	'traveled_beach'
		,	'beach_3'		=>	'pirate_beach'
		,	'forest_2'		=>	'campgrounds'
		,	'forest_3'		=>	'tree_village'
		,	'underwater_2'	=>	'seabed'
		,	'underwater_3'	=>	'coral_reef'
		,	'outback_1'		=>	'river'
		,	'outback_2'		=>	'outback'
		,	'outback_3'		=>	'trading_bay'
		,	'pond_2'		=>	'village_pond'
		,	'pond_3'		=>	'city_pond'
		,	'city_1'		=>	'ghost_town'
		,	'city_2'		=>	'city'
		,	'city_3'		=>	'metropolis'
		,	'mountain_2'	=>	'caves'
		,	'mountain_3'	=>	'mountain_base'
		);
		
		// Transfer the pets
		$fetch = Database::selectMultiple("SELECT type, level, population, name, set_order, description FROM old_pen_areas WHERE user_id=?", array($oldID));
		
		Database::startTransaction();
		foreach($fetch as $area)
		{
			// Change the type if necessary (such as "castle_2" => "fortress")
			// since the old names changed
			if(isset($switch[$area['type'] . '_' . $area['level']]))
			{
				$area['type'] = $switch[$area['type'] . '_' . $area['level']];
				echo $area['type'] . '<br />';
			}
			
			$sortOrder++;
			$typeID = (int) Database::selectValue("SELECT id FROM land_plots_types WHERE type=? AND max_population=? LIMIT 1", array($area['type'], 20 + ($area['level'] * 10)));
			
			Database::query("INSERT INTO land_plots (uni_id, area_type_id, name, address_id, sort_order) VALUES (?, ?, ?, ?, ?)", array($uniID, $typeID, $area['name'], UniqueID::get(), $sortOrder));
		}
		
		//Database::query("DELETE FROM old_pen_areas WHERE user_id=?", array($oldID));
		Database::endTransaction();
	}
	
}