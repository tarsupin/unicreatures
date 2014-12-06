<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

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
	
	
/****** Plugin Variables ******/
	public static $scoreAchieve2 = 100;		// <int> The number of points required to get level 2 achievement.
	public static $scoreAchieve1 = 30;		// <int> The number of points required to get level 1 achievement.
	
	
/****** Get data from a herd ******/
	public static function getData
	(
		$uniID		// <int> The UniID that you're pulling the herd from.
	,	$family		// <str> The creature family's herd to identify.
	)				// RETURNS <str:mixed> the data about the herd.
	
	// $herdData = MyHerds::getData($uniID, $family);
	{
		return Database::selectOne("SELECT population, score FROM herds WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Get population from a herd ******/
	public static function population
	(
		$uniID		// <int> The UniID that you're pulling the herd from.
	,	$family		// <str> The creature family's herd to identify.
	)				// RETURNS <int> the current population of the herd.
	
	// $population = MyHerds::population($uniID, $family);
	{
		return (int) Database::selectValue("SELECT population FROM herds WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Get score of a herd ******/
	public static function getScore
	(
		$uniID		// <int> The UniID that you're pulling the herd from.
	,	$family		// <str> The creature family's herd to get the score of.
	)				// RETURNS <int> the score of the herd.
	
	// $score = MyHerds::getScore($uniID, $family);
	{
		return (int) Database::selectValue("SELECT score FROM herds WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Update the score of a herd ******/
	public static function updateScore
	(
		$uniID		// <int> The UniID that you're pulling the herd from.
	,	$family		// <str> The creature family's herd to get the score of.
	,	$score		// <int> The amount of points to add to the herd score.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyHerds::updateScore($uniID, $family, $score);
	{
		return Database::query("UPDATE herds SET score=score+? WHERE uni_id=? AND family=? LIMIT 1", array($score, $uniID, $family));
	}
	
	
/****** Count the number of creatures in a herd (to ensure current population) ******/
	public static function countCreatures
	(
		$uniID		// <int> The UniID that you're pulling herds from.
	,	$family		// <str> The creature family's herd to count the creatures of.
	)				// RETURNS <int> the list of user's herds.
	
	// $creatureCount = MyHerds::countCreatures($uniID, $family);
	{
		return (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM herds_creatures WHERE uni_id=? AND family=? LIMIT 1", array($uniID, $family));
	}
	
	
/****** Update the population of a herd ******/
	public static function updatePopulation
	(
		$uniID		// <int> The UniID that you're updating the herd count of.
	,	$family		// <str> The creature family (the herd) to update.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		$uniID			// <int> The UniID that you're pulling herds from.
	,	$page = 1		// <int> The page of herds to review.
	,	$numRows = 30	// <int> The number of rows to return for this page.
	)					// RETURNS <int:[str:mixed]> the list of user's herds.
	
	// $herdList = MyHerds::herdList($uniID, $page, $numRows);
	{
		return Database::selectMultiple("SELECT family, population FROM herds WHERE uni_id=? ORDER BY family ASC LIMIT " . (($page - 1) * $numRows) . ', ' . ($numRows + 0), array($uniID));
	}
	
	
/****** Get the list of pets from a herd ******/
	public static function getPets
	(
		$uniID			// <int> The UniID that you're finding pets in.
	,	$family			// <str> The creature family's herd to count the creatures of.
	,	$page = 1		// <int> The page of pets to review.
	,	$numRows = 30	// <int> The number of rows to return for this page.
	)					// RETURNS <int:[str:mixed]> the array of creatures in the herd.
	
	// $herdPets = MyHerds::getPets($uniID, $family, [$page], [$numRows]);
	{
		return Database::selectMultiple("SELECT hc.type_id, hc.nickname, ct.name, ct.prefix, ct.evolution_level FROM herds_creatures hc INNER JOIN creatures_types ct ON hc.type_id=ct.id WHERE hc.uni_id=? AND hc.family=? LIMIT " . (($page - 1) * $numRows) . ', ' . ($numRows + 0), array($uniID, $family));
	}
	
	
/****** Send a creature to a herd ******/
	public static function sendToHerd
	(
		$creatureID		// <int> The creature to send a herd to.
	)					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
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
		
		$isBusy = MyCreatures::isBusy($petData['activity'], (int) $petData['active_until']);
		
		// Pets that are currently active cannot be moved
		if($isBusy)
		{
			Alert::saveError("Cannot Herd", "You cannot herd a creature that is busy.");
			return false;
		}
		
		// Get the pet type data
		$petTypeData = MyCreatures::petTypeData((int) $petData['type_id'], "family, evolution_level");
		
		// Check if a herd currently exists
		if(!$population = self::population($uniID, $petTypeData['family']))
		{
			Database::query("REPLACE INTO herds (uni_id, family, population) VALUES (?, ?, ?)", array($uniID, $petTypeData['family'], 1));
			
			if(!$population = self::population($uniID, $petTypeData['family']))
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
		
		// If adding the pet to the herd is successful, attempt to update the achievements
		if(Database::endTransaction($pass))
		{
			// Update the score
			if(self::updateScore($uniID, $petTypeData['family'], ($petTypeData['evolution_level'] - 1)))
			{
				$score = self::getScore($uniID, $petTypeData['family']);
				
				if($score >= self::$scoreAchieve2)
				{
					MyAchievements::set($uniID, $petTypeData['family'], "herd", 2);
				}
				else if($score >= self::$scoreAchieve1)
				{
					MyAchievements::set($uniID, $petTypeData['family'], "herd", 1);
				}
			}
		}
		
		return $pass;
	}
}
