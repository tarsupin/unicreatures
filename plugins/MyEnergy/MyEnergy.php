<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the MyEnergy Plugin ------
---------------------------------------

This class allows you to utilize your character's energy, or recover it.


****** Notes ******

Energy Max: 400 max
Energy Regain: 1 every 20 seconds

Energy Balance: Every 2 hours, you get 400 energy. That's 400 per site visit. x3 visits per day: 1200 per day.


-------------------------------
------ Methods Available ------
-------------------------------

MyEnergy::createRow($uniID);		// Creates the database row for this user's energy

$energy = MyEnergy::get($uniID);
$energy = MyEnergy::change($uniID, $energy);
$energy = MyEnergy::set($uniID, $energy);

*/

abstract class MyEnergy {
	
	
/****** Class Variables ******/
	public static $regenRate = 20;		// Number of seconds that must pass to regain a point of energy.
	public static $maxEnergy = 400;		// Maximum amount of energy the user can have.
	
	
/****** Create the User's Row for the Energy Table ******/
	public static function createRow
	(
		$uniID		// <int> The UniID to add to the energy table.
	)				// RETURNS <bool>
	
	// MyEnergy::createRow($uniID);
	{
		return Database::query("INSERT IGNORE INTO `users_energy` (uni_id, energy, energy_lastUse) VALUES (?, ?, ?)", array($uniID, self::$maxEnergy, time()));
	}
	
	
/****** Get Energy ******/
	public static function get
	(
		$uniID			// <int> The Uni-Account to identify the amount of energy from.
	)					// RETURNS <int> amount of energy currently available, or 0 on failure.
	
	// $energy = MyEnergy::get($uniID);
	{
		if(!$fetch = Database::selectOne("SELECT energy, energy_lastUse FROM users_energy WHERE uni_id=? LIMIT 1", array($uniID)))
		{
			self::createRow($uniID);
			
			return 0;
		}
		
		// Recognize Integers
		$fetch['energy'] = (int) min(self::$maxEnergy, $fetch['energy']);
		$fetch['energy_lastUse'] = (int) $fetch['energy_lastUse'];
		
		// Regenerate your energy (based on the energy regen rate)
		$regen = self::$regenRate;
		$now = time();
		
		if($fetch['energy_lastUse'] <= ($now - $regen))
		{
			$diff = $now - $fetch['energy_lastUse'];	// Duration since last update
			$totalAdd = floor($diff / $regen);			// Number of energy cycles
			$remain = $diff % $regen;					// Seconds remaining
			
			$fetch['energy'] += $totalAdd;
			$fetch['energy_lastUse'] = $now - $remain;
			
			// Update the energy
			Database::query("UPDATE users_energy SET energy=?, energy_lastUse=? WHERE uni_id=? LIMIT 1", array($fetch['energy'], $fetch['energy_lastUse'], $uniID));
		}
		
		return (int) min(self::$maxEnergy, $fetch['energy']);
	}
	
	
/****** Change Energy ******/
	public static function change
	(
		$uniID			// <int> The Uni-Account to set energy for.
	,	$energy			// <int> The energy amount to add (or subtract, if negative).
	)					// RETURNS <mixed> amount of energy currently available, or FALSE on error.
	
	// $energy = MyEnergy::change($uniID, $energy);
	{
		$current = self::get($uniID);
		
		// Make sure the energy provided is within bounds
		$newEnergy = $current + $energy;
		
		if($newEnergy < 0)
		{
			Database::query("UPDATE users_energy SET energy=? WHERE uni_id=? LIMIT 1", array(0, $uniID));
			
			return false;
		}
		else if($newEnergy > self::$maxEnergy)
		{
			$newEnergy = self::$maxEnergy;
		}
		
		// Update the energy
		if(Database::query("UPDATE users_energy SET energy=? WHERE uni_id=? LIMIT 1", array($newEnergy, $uniID)))
		{
			return $newEnergy;
		}
		
		return false;
	}
	
	
/****** Set Energy ******/
	public static function set
	(
		$uniID			// <int> The Uni-Account to set energy for.
	,	$energy			// <int> The energy amount to set the user at.
	)					// RETURNS <int> amount of energy currently available.
	
	// $energy = MyEnergy::set($uniID, $energy);
	{
		Database::query("UPDATE users_energy SET energy=?, energy_lastUse=? WHERE uni_id=? LIMIT 1", array($energy, time(), $uniID));
		
		return $energy;
	}
	
}
