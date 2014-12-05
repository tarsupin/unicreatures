<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the MyTraining Plugin ------
-----------------------------------------

This plugin handles training.

UniCreatures in this version train in groups, rather than individually. Put them in the training center to improve.
Additionally, others can challenge them (or be challenged by them) and gain experience that way.

You can only train five creatures at a time in the training center. They gain 10000 / (level + 1) exp per day.
You can alternatively gain +200 / (level + 1) exp per challenge.

Levels increase like this: 0xp, 10k, 20k, 30k, 40k, 50k...
Attributes increase respective to the 10th level, by percentage. Level 0 = 50% of level 10. Each upgrade = 5% closer.


-----------------------------------
------ Attributes and Skills ------
-----------------------------------

	// Attributes
	Strength
	Agility
	Speed
	Intelligence
	Wisdom
	Charisma
	Creativity
	Willpower
	Focus
	
-------------------------------
------ Methods Available ------
-------------------------------

$attributes = MyTraining::getAttributes($creatureFamily, $level, $bonus = 0);

$expGained = MyTraining::gainExp($creatureID, $exp);
$level = MyTraining::getLevel($exp);

*/

abstract class MyTraining {
	
	
/****** Get the current attributes of a creature family ******/
	public static function getAttributes
	(
		string $creatureFamily		// <str> The creature family to base this level from.
	,	int $level				// <int> The hypothetical level of the creature.
	,	int $bonus = 0			// <int> Any additional bonuses provided by the creature.
	): array <str, int>						// RETURNS <str:int> a list of the family's attributes.
	
	// $attributes = MyTraining::getAttributes($creatureFamily, $level, $bonus = 0);
	{
		$attr = Database::selectOne("SELECT * FROM abilities_max WHERE creature_family=? LIMIT 1", array($creatureFamily));
		
		$multiplier = (50 + ($level * 5) + $bonus) / 100;
		
		return array(
			'strength'		=> round($attr['strength'] * $multiplier)
		,	'agility'		=> round($attr['agility'] * $multiplier)
		,	'speed'			=> round($attr['speed'] * $multiplier)
		,	'intelligence'	=> round($attr['intelligence'] * $multiplier)
		,	'wisdom'		=> round($attr['wisdom'] * $multiplier)
		,	'charisma'		=> round($attr['charisma'] * $multiplier)
		,	'creativity'	=> round($attr['creativity'] * $multiplier)
		,	'willpower'		=> round($attr['willpower'] * $multiplier)
		,	'focus'			=> round($attr['focus'] * $multiplier)
		);
	}
	
	
/****** Add experience to a creature ******/
	public static function gainExp
	(
		int $creatureID		// <int> The creature ID that is gaining experience.
	,	int $exp			// <int> The base amount of experience the creature would gain (not adjusted for level).
	): int					// RETURNS <int> total experience gained, or FALSE on failure.
	
	// $expGained = MyTraining::gainExp($creatureID, $exp);
	{
		// Get the creature's current experience to determine the adjusted amount they'll get
		$currentEXP = (int) Database::selectValue("SELECT experience FROM creatures_owned WHERE id=? LIMIT 1", array($creatureID));
		
		// Assign a random fluctuation of exp gained from this encounter
		$exp = floor($exp * mt_rand(95, 110) / 100);
		
		// Assign the actual amount that will be acquired, minimum of 1
		// $level = self::getLevel($currentEXP);
		// $exp = max(1, floor($exp / ($level + 1)));
		
		// Update the creature's EXP
		Database::query("UPDATE creatures_owned SET experience=experience+? WHERE id=? LIMIT 1", array($exp, $creatureID));
		
		return $exp;
	}
	
	
/****** Get the current level of a creature (based on experience) ******/
	public static function getLevel
	(
		int $exp		// <int> The experience of the creature.
	): int				// RETURNS <int> the level of the creature.
	
	// $level = MyTraining::getLevel($exp);
	{
		return (int) floor($exp / 10000);
	}
	
	
/****** Get the cost of training a creature automatically ******/
	public static function getTrainingData
	(
		int $level		// <int> The level of the creature.
	): array <int, int>				// RETURNS <int:int> a list that contains the training cost and exp gain.
	
	// list($trainingCost, $expGain) = MyTraining::getTrainingData($level);
	{
		// Base Values
		$trainingCost = 20;
		$expGain = 12000;
		
		// Run the algorithm to determine the new cost
		for($a = 0;$a < $level;$a++)
		{
			$trainingCost = round($trainingCost * 1.15 / 5) * 5;
			$expGain = round($expGain * 0.95 / 5) * 5;
		}
		
		return array((int) $trainingCost, (int) $expGain);
	}
	
}