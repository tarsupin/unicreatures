<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the MyAreas Plugin ------
--------------------------------------

This plugin allows you to handle the pet areas.


-------------------------------
------ Methods Available ------
-------------------------------

$basket	= MyAreas::checkBasket($uniID, [$ymdH]);

MyAreas::acquireDeed($uniID, $typeID);
MyAreas::relocate($uniID, $fromID, $toID);

$areaData	= MyAreas::areaData($areaID);
$areas		= MyAreas::areas($uniID);
$pets		= MyAreas::areaPets($areaID);
$pets		= MyAreas::wildPets($uniID, $page, $showNum);

MyAreas::movePet($petID, $locAddr);

*/

abstract class MyAreas {
	
	
/****** Create a new Creature Type ******/
	public static function checkBasket
	(
		int $uniID			// <int> The Uni-Account that you're checking the basket of.
	,	int $ymdH = 0		// <int> The date to use for the seed, for example in predictions.
	): array					// RETURNS <array> list of available pets in the basket, empty array on failure.
	
	// $basket = MyAreas::checkBasket($uniID);
	{
		// Seed our randomizer with the current hour
		$seed = ($ymdH ? (int) ($uniID . $ymdH) : (int) ($uniID . date("ymdH")));
		
		mt_srand($seed);
		
		if(!$basket = Cache::get("basket:" . $uniID . ":" . $seed))
		{
			$rarityList = array();
			
			for($eggs = 0; $eggs < 6; $eggs++)
			{
				$eggRarity = MyTreasure::randomEggRarity();
				
				$rarityList[$eggRarity] = (isset($rarityList[$eggRarity]) ? $rarityList[$eggRarity] + 1 : 1);
			}
			
			// Prepare Values
			$basket = array();
			
			// Cycle through the basket options for rarity goods
			foreach($rarityList as $rarity => $count)
			{
				$noNoble = (mt_rand(1, 100) > MyTreasure::$nobleChance) ? " AND ct.prefix != 'Noble' AND ct.prefix NOT LIKE 'Noble%'" : '';
				$noExalted = (mt_rand(1, 100) > MyTreasure::$exaltChance) ? " AND ct.prefix != 'Exalted' AND ct.prefix NOT LIKE 'Exalted%' " : '';
				
				$day = date("z");
				if($fetchBasket = Database::selectMultiple("SELECT bc.type_id, ct.family, ct.evolution_level FROM basket_creatures bc INNER JOIN creatures_types ct ON bc.type_id=ct.id WHERE bc.rarity=?" . $noNoble . $noExalted . " AND bc.day_start = ? OR (bc.day_start <= ? AND bc.day_end >= ?) OR (bc.day_end >= ? AND bc.day_start <= ? AND bc.day_end >= ?)", array($rarity, -1, $day, $day, 365, $day+365, $day+365)))
				{
					$fetchBasket = MyTreasure::equalizeChances($fetchBasket);
					for($rnd = 0;$rnd < $count;$rnd++)
					{
						$val = mt_rand(0, count($fetchBasket) - 1);
						
						if(isset($fetchBasket[$val]))
						{
							$basket[] = (int) $fetchBasket[$val]['type_id'];
							unset($fetchBasket[$val]);
						}
					}
				}
			}
			
			// Cache the Basket
			Cache::set("basket:" . $uniID . ":" . $seed, json_encode($basket), 30 * 60);
			
			// Return the seed to random
			mt_srand();
			
			return $basket;
		}
		
		// Return the seed to random
		mt_srand();
		
		return json_decode($basket, true);
	}
	
	
/****** Acquire a Land Deed ******/
	public static function acquireDeed
	(
		int $uniID			// <int> The Uni-Account acquiring the deed.
	,	int $typeID			// <int> The ID of the area type.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyAreas::acquireDeed($uniID, $typeID);
	{
		if($areaTypeData = MyAreas::areaTypeData($typeID))
		{
			// Get your current sort order
			$sortOrder = (int) Database::selectValue("SELECT sort_order FROM land_plots_by_user WHERE uni_id=? ORDER BY sort_order DESC LIMIT 1", array($uniID));
			
			if($sortOrder !== false)	// Might be "0"
			{
				$startName = str_replace("_", " ", $areaTypeData['type']);
				$startName = ucwords($startName);
				
				// Acquire the Deed
				Database::startTransaction();
				
				if($pass = Database::query("INSERT INTO land_plots (uni_id, area_type_id, name, max_population) VALUES (?, ?, ?, ?)", array($uniID, $areaTypeData['id'], $startName, 30)))
				{
					$lastID = Database::$lastID;
					
					$pass = Database::query("INSERT INTO land_plots_by_user (uni_id, sort_order, area_id) VALUES (?, ?, ?)", array($uniID, $sortOrder + 1, $lastID));
				}
				
				return Database::endTransaction($pass);
			}
		}
		
		return false;
	}
	
	
/****** Get data about a Pet Area Type (pen type) ******/
	public static function areaTypeData
	(
		mixed $type			// <mixed> The ID or type of the area.
	): array <str, mixed>					// RETURNS <str:mixed> the data about the area type.
	
	// $areaTypeData = MyAreas::areaTypeData($type);
	{
		return Database::selectOne("SELECT * FROM land_plots_types WHERE " . (is_numeric($type) ? "id" : "type") . "=? LIMIT 1", array($type));
	}
	
	
/****** Relocate a Land Plot ******/
	public static function relocate
	(
		int $uniID			// <int> The Uni-Account that owns the land plots to relocate.
	,	int $fromID			// <int> The area ID of the land plot to move from.
	,	int $toID			// <int> The area ID of the land plot to move to.
	): bool					// RETURNS <bool> TRUE on success, FALSE on faiulre.
	
	// MyAreas::relocate($uniID, $fromID, $toID);
	{
		$fromData = MyAreas::areaData($fromID, "id, uni_id");
		$toData = MyAreas::areaData($toID, "id, uni_id");
		
		$fromSort = (int) Database::selectValue("SELECT sort_order FROM land_plots_by_user WHERE uni_id=? AND area_id=? LIMIT 1", array($uniID, $fromData['id']));
		$toSort = (int) Database::selectValue("SELECT sort_order FROM land_plots_by_user WHERE uni_id=? AND area_id=? LIMIT 1", array($uniID, $toData['id']));
		
		if($fromData['uni_id'] == $uniID && $toData['uni_id'] == $uniID && $fromData['id'] != $toData['id'])
		{
			Database::startTransaction();
			
			if($fromSort < $toSort)
			{
				Database::query("UPDATE land_plots_by_user SET sort_order=sort_order-1 WHERE uni_id=? AND sort_order > ? AND sort_order <= ?", array($uniID, $fromSort, $toSort));
			}
			else
			{
				Database::query("UPDATE land_plots_by_user SET sort_order=sort_order+1 WHERE uni_id=? AND sort_order < ? AND sort_order >= ?", array($uniID, $fromSort, $toSort));
			}
			
			Database::query("UPDATE land_plots_by_user SET sort_order=? WHERE uni_id=? AND area_id=? LIMIT 1", array($toSort, $uniID, $fromID));
			
			return Database::endTransaction();
		}
		
		return false;
	}
	
	
/****** Get the data for an area ******/
	public static function areaData
	(
		int $areaID			// <int> The area's ID.
	): array <str, mixed>					// RETURNS <str:mixed> the data requested on the area.
	
	// $areaData = MyAreas::areaData($areaID);
	{
		return Database::selectOne("SELECT p.id, p.uni_id, p.name, p.population, p.max_population, t.type FROM land_plots p INNER JOIN land_plots_types t ON p.area_type_id=t.id WHERE p.id=? LIMIT 1", array($areaID));
	}
	
	
/****** Get the upgraded area data based on an existing area type ******/
	public static function upgradedAreaTypeData
	(
		string $areaType		// <str> The area type to check.
	): array <str, mixed>					// RETURNS <str:mixed> the data requested on the area.
	
	// $upgradedAreaTypeData = MyAreas::upgradedAreaTypeData($areaType);
	{
		return Database::selectOne("SELECT * FROM land_plots_types WHERE upgrades_from=? LIMIT 1", array($areaType));
	}
	
	
/****** Retrieve a list of areas ******/
	public static function areas
	(
		int $uniID			// <int> The Uni-Account to retrieve areas for.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> a list of areas owned by that user, or FALSE on failure.
	
	// $areas = MyAreas::areas($uniID);
	{
		return Database::selectMultiple("SELECT pa.id, t.id as type_id, t.type, pa.name, pa.population, pa.max_population FROM land_plots_by_user pau INNER JOIN land_plots pa ON pau.area_id=pa.id INNER JOIN land_plots_types t ON pa.area_type_id=t.id WHERE pau.uni_id=? ORDER BY pau.sort_order ASC", array($uniID));
	}
	
	
/****** Retrieve a list of pets from an area ******/
	public static function areaPets
	(
		int $areaID			// <int> The area ID to retrieve pets from.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> the pets found in that address, or FALSE on failure.
	
	// $pets = MyAreas::areaPets($areaID);
	{
		return Database::selectMultiple("SELECT c.id, c.nickname, c.activity, c.active_until, ct.family, ct.name, ct.prefix, ca.sort_order, ca.special FROM creatures_area ca INNER JOIN creatures_owned c ON c.id=ca.creature_id INNER JOIN creatures_types ct ON ct.id=c.type_id WHERE ca.area_id=? ORDER BY ca.sort_order ASC", array($areaID));
	}
	
	
/****** Get the population count of an area ******/
	public static function getPopCount
	(
		int $areaID			// <int> The area ID to retrieve the population count for.
	): int					// RETURNS <int> the population count.
	
	// $count = MyAreas::getPopCount($areaID);
	{
		return (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM creatures_area WHERE area_id=? LIMIT 1", array($areaID));
	}
	
	
/****** Update the population count of an area ******/
	public static function updatePopCount
	(
		int $areaID			// <int> The area ID to update the population count for.
	): bool					// RETURNS <bool> TRUE if the population count is updated, FALSE on failure.
	
	// MyAreas::updatePopCount($areaID);
	{
		$count = MyAreas::getPopCount($areaID);
		
		return Database::query("UPDATE land_plots SET population=? WHERE id=? LIMIT 1", array($count, $areaID));
	}
	
	
/****** Run engineering on a plot ******/
	public static function engineerArea
	(
		int $areaID			// <int> The ID of the area to engineer.
	,	int $popBoost = 5	// <int> The amount of population upgrade to grant.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyAreas::engineerArea($areaID, $popBoost);
	{
		return Database::query("UPDATE land_plots SET max_population=max_population+? WHERE id=? LIMIT 1", array($popBoost, $areaID));
	}
	
	
/****** Upgrade an area type ******/
	public static function upgradeAreaType
	(
		int $areaID			// <int> The ID of the area to upgrade.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyAreas::upgradeAreaType($areaID);
	{
		if($areaData = MyAreas::areaData($areaID))
		{
			if($upgradedArea = MyAreas::upgradedAreaTypeData($areaData['type']))
			{
				// Determine the new name after the upgrade
				if($areaData['name'] == ucwords(str_replace("_", " ", $areaData['type'])))
				{
					$newName = ucwords(str_replace("_", " ", $upgradedArea['type']));
				}
				else
				{
					$newName = $areaData['name'];
				}
				
				// Upgrade the land plot
				return Database::query("UPDATE land_plots SET area_type_id=?, name=? WHERE id=? LIMIT 1", array($upgradedArea['id'], $newName, $areaID));
			}
		}
		
		return false;
	}
	
	
/****** Get the user's Wild Area ID (or create it if it doesn't exist) ******/
	public static function wildAreaID
	(
		int $uniID			// <int> The UniID to get (or create) the Wild Area from.
	): int					// RETURNS <int> ID of the wild area, or 0 on failure.
	
	// $wildID = MyAreas::wildAreaID($uniID);
	{
		if(!$wildID = (int) Database::selectValue("SELECT pau.area_id FROM land_plots_by_user pau INNER JOIN land_plots pa ON pau.area_id=pa.id WHERE pa.area_type_id=? LIMIT 1", array(0)))
		{
			// Create the wild area
			Database::startTransaction();
			
			if($pass = Database::query("INSERT INTO land_plots (uni_id, name) VALUES (?, ?)", array($uniID, "Wild Area")))
			{
				$wildID = Database::$lastID;
				
				if(!$pass = Database::query("INSERT INTO land_plots_by_user (uni_id, sort_order, area_id) VALUES (?, ?, ?)", array($uniID, 0, $wildID)))
				{
					$wildID = false;
				}
			}
			
			Database::endTransaction($pass);
		}
		
		return ($wildID ? $wildID : 0);
	}
	
	
/****** Retrieve a list of pets from your wild area ******/
	public static function wildPets
	(
		int $uniID			// <int> The Uni-Account to retrieve pets from.
	,	int $page = 0		// <int> The page to start searching by.
	,	int $showNum = 30	// <int> The number of creatures to show.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> the pets found in that address, or array() on failure.
	
	// $pets = MyAreas::wildPets($uniID, $page, $showNum);
	{
		// Gather the ID of the wild area
		if(!$wildID = MyAreas::wildAreaID($uniID))
		{
			return array();
		}
		
		return Database::selectMultiple("SELECT c.id, c.nickname, c.activity, c.active_until, ct.family, ct.name, ct.prefix FROM creatures_user cu INNER JOIN creatures_owned c ON cu.creature_id=c.id INNER JOIN creatures_types ct ON ct.id=c.type_id WHERE c.uni_id=? AND c.area_id=? ORDER BY id DESC LIMIT " . (($page - 1) * $showNum) . ", " . ($showNum + 1), array($uniID, $wildID));
	}
	
	
/****** Retrieve a list of pets from your wild area ******/
	public static function movePet
	(
		int $petID			// <int> The ID of the pet that you're going to move.
	,	int $toAreaID		// <int> The ID of the area that you want to move the pet to.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyAreas::movePet($petID, $toAreaID);
	{
		// Get the existing area ID
		if(!$fromAreaID = (int) Database::selectValue("SELECT area_id FROM creatures_owned WHERE id=? LIMIT 1", array($petID)))
		{
			return false;
		}
		
		// Resort the pets within the areas
		$sortLocation = (int) Database::selectValue("SELECT sort_order FROM creatures_area WHERE area_id=? AND creature_id=? LIMIT 1", array($fromAreaID, $petID));
		
		if($sortLocation === false) // Might equal "0"
		{
			return false;
		}
		
		$newLocation = (int) Database::selectValue("SELECT sort_order FROM creatures_area WHERE area_id=? ORDER BY sort_order DESC LIMIT 1", array($toAreaID));
		
		if($newLocation === false) // Might equal "0"
		{
			$newLocation = -1;
		}
		
		Database::startTransaction();
		
		// Resort the original area
		Database::query("UPDATE creatures_area SET sort_order=sort_order-1 WHERE area_id=? AND sort_order > ?", array($fromAreaID, $sortLocation));
		
		// Move the pet
		if($pass = Database::query("UPDATE creatures_owned SET area_id=? WHERE id=? LIMIT 1", array($toAreaID, $petID)))
		{
			$pass = Database::query("UPDATE creatures_area SET area_id=?, sort_order=? WHERE area_id=? AND creature_id=? LIMIT 1", array($toAreaID, $newLocation + 1, $fromAreaID, $petID));
		}
		
		if($pass)
		{
			// Update the population counts of the two areas
			MyAreas::updatePopCount($toAreaID);
			MyAreas::updatePopCount($fromAreaID);
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Rename an area ******/
	public static function renameArea
	(
		int $areaID		// <int> The area ID to rename.
	,	string $newName	// <str> The new name of the area.
	): bool				// RETURNS <bool> TRUE if the rename was successful, FALSE on failure.
	
	// MyAreas::renameArea($areaID, $newName);
	{
		return Database::query("UPDATE land_plots SET name=? WHERE id=? LIMIT 1", array($newName, $areaID));
	}
	
	
/****** Delete an area ******/
	public static function deleteArea
	(
		int $areaID		// <int> The area ID to delete.
	): bool				// RETURNS <bool> TRUE if the area was deleted, FALSE on failure.
	
	// MyAreas::deleteArea($areaID);
	{
		return Database::query("DELETE FROM land_plots WHERE id=? LIMIT 1", array($areaID));
	}
	
	
/****** Retrieve a list of Land Deeds for Sale ******/
	public static function deedPurchaseList (
	): array <str, array<str, mixed>>					// RETURNS <str:[str:mixed]> list of Land Deeds, or FALSE on failure.
	
	// $deedList = MyAreas::deedPurchaseList();
	{
		return array(
			'meadow'			=> array('cost' => 250,		'title' => 'Meadow')
		,	'forest'			=> array('cost' => 500,		'title' => 'Forest')
		,	'dry_zone'			=> array('cost' => 1000,	'title' => 'Dry Zone')
		,	'beach'				=> array('cost' => 2500,	'title' => 'Beach')
		,	'underwater'		=> array('cost' => 5000,	'title' => 'Underwater')
		,	'mountain'			=> array('cost' => 10000,	'title' => 'Mountains')
		,	'ghost_town'		=> array('cost' => 20000,	'title' => 'Ghost Town')
		,	'castle_ruins'		=> array('cost' => 50000,	'title' => 'Castle Ruins')
		);
	}
	
}