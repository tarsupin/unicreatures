<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------------
------ About the MyCreaturesAdmin Plugin ------
-----------------------------------------------

This class allows you to work on the unicreatures database.

-------------------------------
------ Methods Available ------
-------------------------------

MyCreaturesAdmin::createAreaType($type, $upgradesFrom, $maxPop, $maxAllowed);

MyCreaturesAdmin::addBasketCreature($typeID, $rarity);

$typeID = MyCreaturesAdmin::createCreatureType($family, $stage, $name, $blurb, $description, $requiredPoints, $evolvesFrom = 0, $gender = "b");

*/

abstract class MyCreaturesAdmin {
	
	
/****** Generate `MyCreaturesAdmin` SQL ******/
	public static function sql()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `basket_creatures`
		(
			`rarity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`rarity`, `type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Display SQL
		DatabaseAdmin::showTable("herds");
		DatabaseAdmin::showTable("creatures_types");
		DatabaseAdmin::showTable("explore_creatures");
		DatabaseAdmin::showTable("basket_creatures");
		DatabaseAdmin::showTable("shop_creatures");
		DatabaseAdmin::showTable("users_supplies");
		DatabaseAdmin::showTable("users_energy");
		DatabaseAdmin::showTable("ip_visit_user");
		DatabaseAdmin::showTable("queue_treasure");
		DatabaseAdmin::showTable("abilities_base");
		DatabaseAdmin::showTable("abilities_max");
	}
	
	
/****** Create a new Area Type ******/
	public static function createAreaType
	(
		string $type				// <str> The type of area (lowercase name, like "meadow", and "pond").
	,	string $upgradesFrom = ""	// <str> The type of area that it upgrades from (e.g. "pond")
	,	int $maxPop = 50		// <int> The maximum allowed population in the area.
	,	int $maxAllowed = 0		// <int> The number of these types of areas you can own. 0 for infinite.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyCreaturesAdmin::createAreaType($type, $upgradesFrom, $maxPop, $maxAllowed);
	{
		return Database::query("INSERT INTO land_plots_types (type, upgrades_from, max_allowed, max_population) VALUES (?, ?, ?)", array($type, $upgradesFrom, $maxAllowed, $maxPop));
	}
	
	
/****** Create a new Creature Type ******/
	public static function addBasketCreature
	(
		int $typeID			// <int> The creature type ID of the creature to send to.
	,	int $rarity			// <int> The rarity of the pet (0 common, 2 uncommon, 6 rare, 9 legendary, etc).
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyCreaturesAdmin::addBasketCreature($typeID, $rarity);
	{
		// Check if the creature is already added to the basket
		if(!$check = (int) Database::selectValue("SELECT type_id FROM basket_creatures WHERE type_id=? LIMIT 1", array($typeID)))
		{
			// Add the Basket Pet
			return Database::query("INSERT INTO basket_creatures (rarity, type_id) VALUES (?, ?)", array($rarity, $typeID));
		}
		
		return false;
	}
	
	
/****** Create a new Creature Type ******/
	public static function createCreatureType
	(
		string $family				// <str> The name of the creature's family.
	,	string $name				// <str> The name of the creature.
	,	string $prefix				// <str> The prefix of the creature (a color, noble, exalted, etc).
	,	string $blurb				// <str> The mini-description / blurb (140 characters) of the creature.
	,	string $description		// <str> The full description of the creature.
	,	int $requiredPoints		// <int> The number of points required to evolve to this pet.
	,	int $evolvesFrom = 0	// <int> The creature type ID that this pet can evolve from.
	,	$gender = "b"		// <char> The gender of the pet (m, f, or b).
	): void						// RETURNS <void>
	
	// $typeID = MyCreaturesAdmin::createCreatureType($family, $name, $blurb, $description, $requiredPoints, $evolvesFrom = 0, $gender = "b");
	{
		$gender = $gender[0];
		
		Database::query("INSERT INTO creatures_types VALUES (family, name, prefix, gender, blurb, description, required_points, evolves_from) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($family, $name, $prefix, $blurb, $description, $requiredPoints, $evolvesFrom, $gender));
	}
	
}