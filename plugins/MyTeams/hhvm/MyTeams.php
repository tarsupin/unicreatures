<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*
 
--------------------------------------
------ About the MyTeams Plugin ------
--------------------------------------

This plugin allows you to handle teams.

Teams can have any creatures in them. Teams have experience ratings and point ratings. Creatures can be returned from the team back to the regular system. However, the team may be penalized when this happens.


-------------------------------
------ Methods Available ------
-------------------------------

$teamData = MyTeams::teamData($teamID);

$teamID = MyTeams::createHerd($uniID, $family);

$teamList = MyTeams::userHerds($uniID);
$teams = MyTeams::userHerdIDs($uniID);

MyTeams::sendToHerd($creatureID, $teamID);

$teamPets = MyTeams::getPets($teamID);

*/

abstract class MyTeams {
	
	
/****** Get data about a team ******/
	public static function teamData
	(
		int $teamID			// <int> The team that you're sending the creature to.
	): array <str, mixed>					// RETURNS <str:mixed> the ID of the new creature, or array() on failure.
	
	// $teamData = MyTeams::teamData($teamID);
	{
		return Database::selectOne("SELECT id, uni_id, name, image, experience, points FROM teams WHERE id=? LIMIT 1", array($teamID));
	}
	
	
/****** Create a new team ******/
	public static function createHerd
	(
		int $uniID			// <int> The UniID creating the team.
	,	int $creatureID		// <int> The creature ID that you're creating the team with.
	,	string $teamName		// <str> The name of the team to create.
	): int					// RETURNS <int> the ID of the new team, or 0 on failure.
	
	// $teamID = MyTeams::createHerd($uniID, $creatureID, $teamName);
	{
		// Get creature details
		if(!$petData = MyCreatures::petData($creatureID, "id, uni_id, type_id"))
		{
			return 0;
		}
		
		if($petData['uni_id'] != $uniID)
		{
			Alert::saveError("Illegal Team", "Illegal team creation attempt.");
			
			return 0;
		}
		
		// Get creature type details
		if(!$typeData = MyCreatures::petTypeData((int) $petData['type_id'], "family, name, prefix"))
		{
			return 0;
		}
		
		$image = MyCreatures::imgSrc($typeData['family'], $typeData['name'], $typeData['prefix']);
		
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO teams (uni_id, name, image) VALUES (?, ?, ?)", array($uniID, $teamName, $image)))
		{
			$lastID = Database::$lastID;
			
			if($pass = Database::query("INSERT INTO teams_by_user (uni_id, team_id) VALUES (?, ?)", array($uniID, $lastID)))
			{
				$pass = self::sendToHerd($creatureID, $lastID);
			}
		}
		
		return (Database::endTransaction($pass) ? $lastID : 0);
	}
	
	
/****** Get the team list (and data) rom a user ******/
	public static function userHerds
	(
		int $uniID		// <int> The UniID to get teams and data from.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> the list of team and their data.
	
	// $teamList = MyTeams::userHerds($uniID);
	{
		return Database::selectMultiple("SELECT h.* FROM teams_by_user hu INNER JOIN teams h ON hu.team_id=h.id WHERE hu.uni_id=?", array($uniID));
	}
	
	
/****** Get a list of team IDs from a user ******/
	public static function userHerdIDs
	(
		int $uniID			// <int> The UniID to get teams from.
	): array <int, int>					// RETURNS <int:int> the list of team ID's that belong to the user.
	
	// $teams = MyTeams::userHerdIDs($uniID);
	{
		$teamList = array();
		$teams = Database::selectMultiple("SELECT team_id FROM teams_by_user WHERE uni_id=?", array($uniID));
		
		foreach($teams as $team)
		{
			$teamList[] = (int) $team['team_id'];
		}
		
		return $teamList;
	}
	
	
/****** Send a creature to a team ******/
	public static function sendToHerd
	(
		int $creatureID		// <int> The creature to send a team to.
	,	int $teamID			// <int> The team that you're sending the creature to.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyTeams::sendToHerd($creatureID, $teamID);
	{
		if(!$petData = MyCreatures::petData($creatureID, "uni_id, type_id, nickname, gender, experience, total_points, activity, active_until"))
		{
			return false;
		}
		
		$isBusy = MyCreatures::isBusy($petData['activity'], (int) $petData['active_until']);
		
		// Pets that are currently active cannot be moved
		if($isBusy)
		{
			Alert::saveError("Cannot Herd", "You cannot team a creature that is busy.");
			return false;
		}
		
		if(!$teamData = MyTeams::teamData($teamID, "uni_id"))
		{
			return false;
		}
		
		// Must own the pet to team it
		if($petData['uni_id'] != $teamData['uni_id'])
		{
			Alert::saveError("Cannot Herd", "Illegal team attempt.");
			return false;
		}
		
		// Check maximum population
		$population = Database::selectValue("SELECT COUNT(type_id) FROM teams_creatures WHERE team_id=? LIMIT 30", array($teamID));
		
		// Cannot add over 30 pets to a team
		if($population >= 30)
		{
			Alert::saveError("Cannot Herd", "Maximum population of a team is 30 creatures.");
			return false;
		}
		
		// Cannot team 2 pets of the same type
		if($exist = Database::selectOne("SELECT type_id FROM teams_creatures WHERE team_id=? AND type_id=? LIMIT 1", array($teamID, $petData['type_id'])))
		{
			Alert::saveError("Cannot Herd", "You cannot add multiple pets of the same family and color to the same team.");
			return false;
		}		
		
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO teams_creatures (team_id, type_id, nickname, gender, experience, points) VALUE (?, ?, ?, ?, ?, ?)", array($teamID, $petData['type_id'], $petData['nickname'], $petData['gender'], $petData['experience'], $petData['total_points'])))
		{
			if($pass = Database::query("UPDATE teams SET experience=experience+?, points=points+? WHERE id=? LIMIT 1", array($petData['experience'], $petData['total_points'], $teamID)))
			{
				$pass = MyCreatures::deleteCreature($creatureID);
			}
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Send a creature from the team to the wild area ******/
	public static function backToWild
	(
		int $uniID			// <int> The UniID to handle the teaming with.
	,	int $teamID			// <int> The team that you're sending the creature to.
	,	int $typeID			// <int> The creature type ID to return.
	,	string $nickname		// <str> The nickname of the pet to return.
	): bool					// RETURNS <bool> TRUE on success, or FALSE on failure.
	
	// MyTeams::backToWild($uniID, $teamID, $typeID, $nickname);
	{
		if(!$teamData = self::teamData($teamID))
		{
			return false;
		}
		
		if($teamData['uni_id'] != $uniID)
		{
			Alert::saveError("Illegal Update", "Illegal update to pet. Must be the proper owner.");
			return false;
		}
		
		// Get the team pet
		$fetchPet = Database::selectOne("SELECT gender, experience, points FROM teams_creatures WHERE team_id=? AND nickname=? AND type_id=? LIMIT 1", array($teamID, $nickname, $typeID));
		
		// Process the transition
		Database::startTransaction();
		
		if($pass = Database::query("DELETE FROM teams_creatures WHERE team_id=? AND nickname=? AND type_id=? LIMIT 1", array($teamID, $nickname, $typeID)))
		{
			$pass = false;
			if($creatureID = MyCreatures::acquireCreature($uniID, $typeID, $fetchPet['gender'], $nickname, (int) $fetchPet['experience'], (int) $fetchPet['points']))
			{
				$pass = Database::query("UPDATE teams SET experience=experience-?, points=points-? WHERE id=? LIMIT 1", array($fetchPet['experience'], $fetchPet['points'], $teamID));
			}
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Get the list of pets from a team ******/
	public static function getPets
	(
		int $teamID		// <int> The Herd ID to get creatures from.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> the array of creatures in the team.
	
	// $teamPets = MyTeams::getPets($teamID);
	{
		return Database::selectMultiple("SELECT hc.type_id, hc.nickname, ct.family, ct.name, ct.prefix FROM teams_creatures hc INNER JOIN creatures_types ct ON hc.type_id=ct.id WHERE hc.team_id=? LIMIT 30", array($teamID));
	}
	
	
/****** Get the list of pets from a team ******/
	public static function deleteHerd
	(
		int $teamID		// <int> The Herd ID to delete.
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $teamPets = MyTeams::deleteHerd($teamID);
	{
		$population = Database::selectValue("SELECT COUNT(type_id) FROM teams_creatures WHERE team_id=? LIMIT 1", array($teamID));
		if($population)
		{
			Alert::error("Creatures Present", "You cannot disband a team that has creatures in it.");
			return false;
		}
		
		Database::startTransaction();
		
		if($pass = Database::query("DELETE FROM teams WHERE id=? LIMIT 1", array($teamID)))
		{
			$pass = Database::query("DELETE FROM teams_by_user WHERE uni_id=? AND team_id=? LIMIT 1", array(Me::$id, $teamID));
		}
		
		return Database::endTransaction($pass);
	}
	
}