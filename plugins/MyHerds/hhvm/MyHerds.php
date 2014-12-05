<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the MyHerds Plugin ------
--------------------------------------

This plugin allows you to handle herds.

Herds are family-based, so adding a creature to the herd adds it to the family it is part of. You can complete herds by providing 50 points worth of creatures in them. Eggs count as 0, higher stages count as their evolution_level - 1.


-------------------------------
------ Methods Available ------
-------------------------------

$population = MyHerds::population($uniID, $family);

$creatureCount = MyHerds::countCreatures($uniID, $family);

MyHerds::updatePopulation($uniID, $family);

$herdList = MyHerds::herdList($uniID, $page, $numRows);

$herdPets = MyHerds::getPets($uniID, $family, [$page], [$numRows]);

MyHerds::sendToHerd($creatureID);

*/

abstract class MyHerds {
	
	
/****** Get population from a herd ******/
	public static function population
	(
		int $uniID		// <int> The UniID that you're pulling the herd from.
	,	string $family		// <str> The creature family's herd to identify.
	): int				// RETURNS <int> the current population of the herd.
	
	// $population = MyHerds::population($uniID, $family);
	{
		return (int) Database::selectValue("SELECT population FROM herds WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Count the number of creatures in a herd (to ensure current population) ******/
	public static function countCreatures
	(
		int $uniID		// <int> The UniID that you're pulling herds from.
	,	string $family		// <str> The creature family's herd to count the creatures of.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> the list of user's herds.
	
	// $creatureCount = MyHerds::countCreatures($uniID, $family);
	{
		return (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM herds_creatures WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Update the population of a herd ******/
	public static function updatePopulation
	(
		int $uniID		// <int> The UniID that you're updating the herd count of.
	,	string $family		// <str> The creature family (the herd) to update.
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyHerds::updatePopulation($uniID, $family);
	{
		// Get the full creature count
		if(!$creatureCount = self::countCreatures($uniID, $family))
		{
			return false;
		}
		
		return Database::query("UPDATE herds SET population=? WHERE uni_id=? AND family=? LIMIT 1", array($creatureCount, $uniID, $family));
	}
	
	
/****** Get the user's list of herds ******/
	public static function herdList
	(
		int $uniID			// <int> The UniID that you're pulling herds from.
	,	int $page = 1		// <int> The page of herds to review.
	,	int $numRows = 30	// <int> The number of rows to return for this page.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> the list of user's herds.
	
	// $herdList = MyHerds::herdList($uniID, $page, $numRows);
	{
		return Database::selectMultiple("SELECT family, population FROM herds WHERE uni_id=? ORDER BY family ASC LIMIT " . (($page - 1) * $numRows) . ', ' . ($numRows + 0), array($uniID));
	}
	
	
/****** Get the list of pets from a herd ******/
	public static function getPets
	(
		int $uniID			// <int> The UniID that you're finding pets in.
	,	string $family			// <str> The creature family's herd to count the creatures of.
	,	int $page = 1		// <int> The page of pets to review.
	,	int $numRows = 30	// <int> The number of rows to return for this page.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> the array of creatures in the herd.
	
	// $herdPets = MyHerds::getPets($uniID, $family, [$page], [$numRows]);
	{
		return Database::selectMultiple("SELECT hc.type_id, hc.nickname, ct.name, ct.prefix, ct.evolution_level FROM herds_creatures hc INNER JOIN creatures_types ct ON hc.type_id=ct.id WHERE hc.uni_id=? AND hc.family=? LIMIT " . (($page - 1) * $numRows) . ', ' . ($numRows + 0), array($uniID, $family));
	}
	
	
/****** Send a creature to a herd ******/
	public static function sendToHerd
	(
		int $creatureID		// <int> The creature to send a herd to.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyHerds::sendToHerd($creatureID);
	{
		// Retrieve the pet
		if(!$petData = MyCreatures::petData($creatureID, "id, uni_id, type_id, nickname, activity, active_until"))
		{
			return false;
		}
		
		// Prepare Values
		$uniID = (int) $petData['uni_id'];
		
		// If you're not the pet owner
		if(Me::$id != $uniID)
		{
			Alert::saveError("Not Owner", "You cannot herd a creature that does not belong to you.", 8);
			return false;
		}
		
		$isBusy = MyCreatures::isBusy($petData['activity'], $petData['active_until']);
		
		// Pets that are currently active cannot be moved
		if($isBusy)
		{
			Alert::saveError("Cannot Herd", "You cannot herd a creature that is busy.");
			return false;
		}
		
		// Get the pet type data
		$petTypeData = MyCreatures::petTypeData((int) $petData['type_id'], "family");
		
		// Check if a herd currently exists
		if(!$population = MyHerds::population($uniID, $petTypeData['family']))
		{
			Database::query("REPLACE INTO herds (uni_id, family, population) VALUES (?, ?, ?)", array($uniID, $petTypeData['family'], 1));
			
			if(!$population = MyHerds::population($uniID, $petTypeData['family']))
			{
				return false;
			}
		}
		
		// Cannot add over 120 pets to a herd
		if($population >= 120)
		{
			Alert::saveError("Cannot Herd", "Maximum population of a herd is 120 creatures.");
			return false;
		}
		
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO herds_creatures (uni_id, family, type_id, nickname) VALUE (?, ?, ?, ?)", array($uniID, $petTypeData['family'], $petData['type_id'], $petData['nickname'])))
		{
			if($pass = self::updatePopulation($uniID, $petTypeData['family']))
			{
				$pass = MyCreatures::deleteCreature((int) $petData['id']);
			}
		}
		
		return Database::endTransaction($pass);
	}
}