<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the MyCreatures Plugin ------
------------------------------------------

This plugin allows you to interact with the creatures you own.


-------------------------------
------ Methods Available ------
-------------------------------

$creatureID = MyCreatures::acquireCreature($uniID, $typeID, [$gender], [$nickname], [$exp], [$points]);
MyCreatures::changePetType($petID, $newTypeID);				// Evolves or modifies a creature
MyCreatures::deleteCreature($creatureID);

$typeID = MyCreatures::getTypeID($family, $name, $prefix);

$src = MyCreatures::imgSrc($family, $name, $prefix);
$royalty = MyCreatures::petRoyalty($prefix);

$petData = MyCreatures::petData($petID, $columns = "*");
$petTypeData = MyCreatures::petTypeData($typeID, $columns = "*");
$petList = MyCreatures::activityList($uniID, $activity);

$isBusy = MyCreatures::isBusy($activity, $activeUntil);

MyCreatures::feedPet($petID, $points);

*/

abstract class MyCreatures {
	
	
/****** Get a new Creature ******/
	public static function acquireCreature
	(
		int $uniID				// <int> The Uni-Account acquiring the creature.
	,	int $typeID				// <int> The type of the creature that you're receiving.
	,	string $forceGender = ""	// <str> The character of gender, if you want to force it.
	,	string $forceNickname = ""	// <str> The nickname of the creature, if you want to force it.
	,	int $forceEXP = 0		// <int> The EXP to enforce, if desired.
	,	int $forcePoints = 0	// <int> The points to enforce, if desired.
	): int						// RETURNS <int> the ID of the new creature, or 0 on failure.
	
	// $creatureID = MyCreatures::acquireCreature($uniID, $typeID, [$gender], [$nickname], [$exp], [$points]);
	{
		if(!$creatureType = Database::selectOne("SELECT name, gender, prefix, family, evolution_level FROM creatures_types WHERE id=? LIMIT 1", array($typeID)))
		{
			return 0;
		}
		
		// Randomly select a gender (unless there is a specific gender associated with the creature type)
		if($forceGender == "")
		{
			$gender = ($creatureType['gender'] == "b" ? (mt_rand(0, 1) == 1 ? "m" : "f") : ($creatureType['gender'] == "m" ? "m" : "f"));
		}
		else
		{
			$gender = $forceGender;
		}
		
		// Prepare a nickname
		$nickname = ($forceNickname == "" ? $creatureType['name'] : $forceNickname);
		
		// Add the Creature
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO creatures_owned (uni_id, type_id, nickname, gender, experience, total_points, date_acquired) VALUES (?, ?, ?, ?, ?, ?, ?)", array($uniID, $typeID, $nickname, $gender, $forceEXP, $forcePoints, time())))
		{
			$creatureID = Database::$lastID;
			
			// Assign creature to the user
			if($pass = Database::query("INSERT INTO creatures_user (uni_id, creature_id) VALUES (?, ?)", array($uniID, $creatureID)))
			{
				$pass = false;
				
				// Find the wild area (
				if($wildID = MyAreas::wildAreaID($uniID))
				{
					// Get the next sort order in the wild area
					$sortOrder = (int) Database::selectValue("SELECT sort_order FROM creatures_area WHERE area_id=? ORDER BY sort_order DESC LIMIT 1", array($wildID));
					
					if($sortOrder === false) // Not "0", which it might result in
					{
						$sortOrder = -1;
					}
					
					$sortOrder++;
					
					// Add the creature to the wild area
					if($pass = Database::query("INSERT INTO creatures_area (area_id, sort_order, creature_id) VALUES (?, ?, ?)", array($wildID, $sortOrder, $creatureID)))
					{
						// Update the area ID on the creature
						$pass = Database::query("UPDATE creatures_owned SET area_id=? WHERE id=? LIMIT 1", array($wildID, $creatureID));
					}
				}
			}
		}
		
		if(Database::endTransaction($pass))
		{
			// Make sure your achievements reflect this evolution
			MyAchievements::set(Me::$id, $creatureType['family'], "evolutions", $creatureType['evolution_level']);
			
			// Run the royalty achievement if there is an appropriate prefix
			if(self::petRoyalty($creatureType['prefix']) == "Noble")
			{
				MyAchievements::set(Me::$id, $creatureType['family'], "royalty", 1);
			}
			
			else if(self::petRoyalty($creatureType['prefix']) == "Exalted")
			{
				MyAchievements::set(Me::$id, $creatureType['family'], "royalty", 2);
			}
			
			return $creatureID;
		}
		
		return 0;
	}
	
	
/****** Change the Pet Type (Evolve, Reverse-Evolve, Nobilize, etc) ******/
	public static function changePetType
	(
		int $petID			// <int> The pet that you want to evolve.
	,	int $newTypeID		// <int> The ID of the new creature type to set your evolution to.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyCreatures::changePetType($petID, $newTypeID);
	{
		return Database::query("UPDATE creatures_owned SET type_id=? WHERE id=? LIMIT 1", array($newTypeID, $petID));
	}
	
	
/****** Update the pet's name ******/
	public static function updateName
	(
		int $petID			// <int> The pet that you want to evolve.
	,	string $newName		// <str> The new pet name to use.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyCreatures::updateName($petID, $newName);
	{
		return Database::query("UPDATE creatures_owned SET nickname=? WHERE id=? LIMIT 1", array($newName, $petID));
	}
	
	
/****** Delete a Creature ******/
	public static function deleteCreature
	(
		int $creatureID		// <int> The creature to delete.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyCreatures::deleteCreature($creatureID);
	{
		if($petData = MyCreatures::petData($creatureID, "area_id, uni_id"))
		{
			Database::startTransaction();
			
			if($pass = Database::query("DELETE FROM creatures_owned WHERE id=? LIMIT 1", array($creatureID)))
			{
				if($pass = Database::query("DELETE FROM creatures_user WHERE uni_id=? AND creature_id=? LIMIT 1", array($petData['uni_id'], $creatureID)))
				{
					$pass = Database::query("DELETE FROM creatures_area WHERE area_id=? AND creature_id=? LIMIT 1", array($petData['area_id'], $creatureID));
				}
			}
			
			if(Database::endTransaction($pass))
			{
				MyAreas::updatePopCount((int) $petData['area_id']);
				
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Get the Type ID of the creature ******/
	public static function getTypeID
	(
		string $family			// <str> The creature family.
	,	string $name			// <str> The name of the creature.
	,	string $prefix = ""	// <str> The prefix of the creature, if applicable.
	): int					// RETURNS <int> the Type ID of the creature, or 0 on failure.
	
	// $typeID = MyCreatures::getTypeID($family, $name, $prefix);
	{
		$typeID = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($family, $name, $prefix));
		
		return ($typeID ? (int) $typeID : 0);
	}
	
	
/****** Return the image source of a creature ******/
	public static function imgSrc
	(
		string $family			// <str> The family name of the creature.
	,	string $name			// <str> The name of the creature.
	,	string $prefix = ""	// <str> The prefix of the creature.
	): string					// RETURNS <str> the image source (filepath) of the creature.
	
	// $src = MyCreatures::imgSrc($family, $name, $prefix);
	{
		return '/creatures/' . $family . '/' . strtolower(($prefix != "" ? str_replace(" ", "_", $prefix) . '_' : "") . ($family != $name ? $family . '_' : '') . $name) . '.png';
	}
	
/****** Return whether a pet is noble or exalted ******/
	public static function petRoyalty
	(
		string $prefix = ""	// <str> The prefix of the creature.
	): string					// RETURNS <str> the royalty of the pet, without color prefix.
	
	// $royalty = MyCreatures::petRoyalty($prefix);
	{
		if($prefix == "Noble" || $prefix == "Exalted")
			return $prefix;
		
		if(substr($prefix, 0, 6) == "Noble ")
			return "Noble";
		
		if(substr($prefix, 0, 8) == "Exalted ")
			return "Exalted";
		
		return "";
	}
	
	
/****** Retrieve the pet data of a pet ******/
	public static function petData
	(
		int $petID			// <int> The ID of the pet to get data on.
	,	string $columns = "*"	// <str> The columns to retrieve on the pet.
	): array <str, mixed>					// RETURNS <str:mixed> the data requested on the pet.
	
	// $petData = MyCreatures::petData($petID, $columns = "*");
	{
		return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,`*") . " FROM creatures_owned WHERE id=? LIMIT 1", array($petID));
	}
	
	
/****** Get pet data based on its position in a land plot ******/
	public static function petDataBySortID
	(
		int $areaID			// <int> The ID of the area.
	,	int $sortVal		// <int> The sort value.
	): array <str, mixed>					// RETURNS <str:mixed> the data requested on the pet.
	
	// $petData = MyCreatures::petDataBySortID($areaID, $sortVal);
	{
		return Database::selectOne("SELECT co.* FROM creatures_area ca INNER JOIN creatures_owned co ON ca.creature_id=co.id WHERE ca.area_id=? AND ca.sort_order=? LIMIT 1", array($areaID, $sortVal));
	}
	
	
/****** Retrieve pet type data ******/
	public static function petTypeData
	(
		int $typeID			// <int> The ID of the pet type to retrieve data from.
	,	string $columns = "*"	// <str> The columns to retrieve on the pet type.
	): array <str, mixed>					// RETURNS <str:mixed> the data requested on the pet type.
	
	// $petTypeData = MyCreatures::petTypeData($typeID, $columns = "*");
	{
		return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,`*") . " FROM creatures_types WHERE id=? LIMIT 1", array($typeID));
	}
	
	
/****** Get a list of creatures doing a chosen activity ******/
	public static function activityList
	(
		int $uniID					// <int> The UniID to get the training creatures from.
	,	string $activity				// <str> The activity to look for.
	,	bool $forceUpdate = false	// <bool> TRUE if we're going to force the training update (if one exists)
	): array <int, array<str, mixed>>							// RETURNS <int:[str:mixed]> the list of pets that are busy with this action.
	
	// $petList = MyCreatures::activityList($uniID, $activity, [$forceUpdate]);
	{
		// Check if data is already cached
		$petList = $forceUpdate ? false : Cache::get("pet-" . $activity . ":" . $uniID);
		
		if($petList === false)
		{
			// Prepare Values
			$timestamp = time();
			
			// Get the list of pets in training
			$petList = Database::selectMultiple("SELECT co.id, co.active_until, co.nickname, ct.family, ct.name, ct.prefix FROM creatures_user cu INNER JOIN creatures_owned co ON cu.creature_id=co.id INNER JOIN creatures_types ct ON co.type_id=ct.id WHERE cu.uni_id=? AND co.activity=?", array($uniID, "training"));
			
			// Loop through each pet
			foreach($petList as $key => $val)
			{
				// If the pet is no longer in training, remove their active rating
				if($val['active_until'] < $timestamp)
				{
					Database::query("UPDATE creatures_owned SET activity=?, active_until=? WHERE id=? LIMIT 1", array("", 0, $val['id']));
					
					unset($petList[$key]);
				}
			}
			
			// This will cache the related items for 3 minutes
			Cache::set("pet-" . $activity . ":" . $uniID, json_encode($petList), 120);
		}
		else
		{
			// Convert JSON to Array
			$petList = json_decode($petList, true);
		}
		
		return $petList;
	}
	
	
/****** Determine the activity of the creature, and how busy they are ******/
	public static function isBusy
	(
		string $activity		// <str> The type of activity the pet is doing.
	,	int $activeUntil	// <int> The date at which the activity will end.
	): bool					// RETURNS <bool> TRUE if busy, FALSE if not.
	
	// $isBusy = MyCreatures::isBusy($activity, $activeUntil);
	{
		if($activity === "") { return false; }
		
		// If the pet's activity is over, you can utilize the pet again
		if($activeUntil != 0 and $activeUntil < time())
		{
			Database::query("UPDATE creatures_owned SET activity=? AND active_until=? LIMIT 1", array("", 0));
			
			return false;
		}
		
		return true;
	}
	
	
/****** Feed a pet a number of points ******/
	public static function feedPet
	(
		int $petID			// <int> The ID of the pet that you're going to feed.
	,	int $points			// <int> The amount of points the pet is being given.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyCreatures::feedPet($petID, $points);
	{
		return Database::query("UPDATE creatures_owned SET total_points=total_points+? WHERE id=? LIMIT 1", array($points, $petID));
	}
	
	
/****** Reveal the description after parsing its special markup values ******/
	public static function descMarkup
	(
		string $desc			// <str> The description prior to parsing the markup.
	,	string $nickname		// <str> The nickname of the creature.
	,	string $gender			// <str> The gender of the creature.
	): string					// RETURNS <str> the new description after parsing the markup.
	
	// $desc = MyCreatures::descMarkup($desc, $nickname, $gender);
	{
		// Update the description
		$repThis = array("*", "#His", "#his", "#He", "#he", "#Him", "#him");
		
		$repWith = array($nickname);
		
		if($gender == "m")
		{
			$repWith[] = "His";
			$repWith[] = "his";
			$repWith[] = "He";
			$repWith[] = "he";
			$repWith[] = "Him";
			$repWith[] = "him";
		}
		else
		{
			$repWith[] = "Her";
			$repWith[] = "her";
			$repWith[] = "She";
			$repWith[] = "she";
			$repWith[] = "Her";
			$repWith[] = "her";
		}
		
		return str_replace($repThis, $repWith, $desc);
	}
	
}