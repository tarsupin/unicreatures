<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }  /*

---------------------------------------------
------ About the MyAchievements Plugin ------
---------------------------------------------

This plugin handles achievements.

You only show achievements for creatures that you have acquired at some point. Remaining families are not listed.

-----------------------------------
------ Achievements Possible ------
-----------------------------------

	// Every creature family has the following
	1 point for every evolution you've acquired
	1 point for training a family to level 5
	1 point for training a family to level 10
	1 point for having a noble
	1 point for having an exalted
	1 point for having a small herd
	1 point for having a large herd
	1 point for winning a trophy with them
	1 point for winning gold with them
	
	++add++ 1 point for crowning the family
	
	
-------------------------------
------ Methods Available ------
-------------------------------

$score = MyAchievements::getScore($uniID);
$achieveList = MyAchievements::getList($uniID);

MyAchievements::set($uniID, $creatureFamily, $type, $value);
MyAchievements::update($uniID);

*/

abstract class MyAchievements {
	
	
/****** Get Achievement Score for a user ******/
	public static function getScore
	(
		int $uniID		// <int> The UniID to get the achievements score for.
	): int				// RETURNS <int> the achievement score of the user.
	
	// $score = MyAchievements::getScore($uniID);
	{
		return (int) MySupplies::getSupplies($uniID, "achievements");
	}
	
	
/****** Get Achievement List ******/
	public static function getList
	(
		int $uniID		// <int> The UniID to get achievements for.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> the list of achievements of the user, or empty array on failure.
	
	// $achieveList = MyAchievements::getList($uniID);
	{
		return Database::selectMultiple("SELECT creature_family, finished, fully_evolved, evolutions, trained, royalty, herd, awards FROM achievements WHERE uni_id=? ORDER BY creature_family", array($uniID));
	}
	
	
/****** Set Achievement ******/
	public static function set
	(
		int $uniID				// <int> The UniID to add the achievement to.
	,	string $creatureFamily		// <str> The creature family to add the achievement for.
	,	string $type				// <str> The type of achievement being updated.
	,	mixed $value				// <mixed> The value associated with the update that we need to interpret.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyAchievements::set($uniID, $creatureFamily, $type, $value);
	{
		// Make sure there is a creature family provided
		if(!$creatureFamily)
		{
			return false;
		}
		
		// Check if the user has any achievements of this family
		if(!$achievementData = Database::selectOne("SELECT * FROM achievements WHERE uni_id=? AND creature_family=? LIMIT 1", array($uniID, $creatureFamily)))
		{
			if(!Database::query("INSERT IGNORE INTO achievements (uni_id, creature_family) VALUES (?, ?)", array($uniID, $creatureFamily)))
			{
				return false;
			}
			
			$achievementData = Database::selectOne("SELECT * FROM achievements WHERE uni_id=? AND creature_family=? LIMIT 1", array($uniID, $creatureFamily));
		}
		
		// Handle standard achievement types
		if(in_array($type, array("evolutions", "trained", "herd", "awards")))
		{
			if($achievementData[$type] >= $value) { return true; }
			$achievementData[$type] = $value;
		}
		
		// Handle the "royalty" achievement type (behaves differently)
		else if($type == "royalty")
		{
			// Nobles can combine with exalted to get "3" points
			if($value == 1)
			{
				$value = ($achievementData['royalty'] == 2 ? 3 : 1);
			}
			
			// Exalted can combine with noble to get "3" points"
			else if($value == 2)
			{
				$value = ($achievementData['royalty'] == 1 ? 3 : 2);
			}
			
			if($achievementData['royalty'] >= $value) { return true; }
			$achievementData['royalty'] = $value;
		}
		
		// If we couldn't recognize the achievement type, return false
		else
		{
			return false;
		}
		
		// Check if the evolution is now complete
		if($type == "evolutions")
		{
			// Check the highest evolution of this family
			$highestEv = (int) Database::selectValue("SELECT evolution_level FROM creatures_types WHERE family=? ORDER BY evolution_level DESC LIMIT 1", array($creatureFamily));
			
			if($highestEv <= $value)
			{
				// Set the achievement for fully evolved
				Database::query("UPDATE achievements SET fully_evolved=? WHERE uni_id=? AND creature_family=? LIMIT 1", array(1, $uniID, $creatureFamily));
			}
		}
		
		// Check if all of the achievements have been finalized
		if($achievementData['royalty'] >= 3 and $achievementData['trained'] >= 2 and $achievementData['herd'] >= 2 and $achievement['awards'] >= 2 and $achievement['fully_evolved'] >= 1)
		{
			Database::query("UPDATE achievements SET finished=? WHERE uni_id=? AND creature_family=? LIMIT 1", array(1, $uniID, $creatureFamily));
		}
		
		// Update the Achievement
		$success = Database::query("UPDATE achievements SET " . Sanitize::variable($type) . "=? WHERE uni_id=? AND creature_family=? LIMIT 1", array($achievementData[$type], $uniID, $creatureFamily));
		
		// Update Achievement Score
		MyAchievements::update($uniID);
		
		// Set an announcement for this achievement
		// ANNOUNCEMENT HERE
		
		return $success;
	}
	
	
/****** Update Achievement Score ******/
	public static function update
	(
		int $uniID				// <int> The UniID to add the achievement to.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyAchievements::update($uniID);
	{
		// Count total achievements
		if(!$count = (int) Database::selectValue("SELECT SUM(evolutions + trained + royalty + herd + awards) as total FROM achievements WHERE uni_id=?", array($uniID)))
		{
			return false;
		}
		
		MySupplies::setSupplies($uniID, "achievements", $count);
		
		return true;
	}
	
}