<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the MySupplies Plugin ------
-----------------------------------------

This plugin allows you to handle the UniCreatures database.


-------------------------------
------ Methods Available ------
-------------------------------

MySupplies::createRow($uniID);		// Creates the database row for this user's supplies

$supplies = MySupplies::getSupplyList($uniID);
$coins = MySupplies::getSupplies($uniID, "coins");
$coins = MySupplies::changeSupplies($uniID, "coins", 100);

*/

abstract class MySupplies {
	
	
/****** Create the User's Row for the Supply Table ******/
	public static function createRow
	(
		$uniID		// <int> The UniID to add to the supplies table.
	)				// RETURNS <bool>
	
	// MySupplies::createRow($uniID);
	{
		return Database::query("INSERT IGNORE INTO `users_supplies` (uni_id) VALUES (?)", array($uniID));
	}
	
	
/****** Get Supplies ******/
	public static function getSupplyList
	(
		$uniID			// <int> The Uni-Account to get the supply list from.
	)					// RETURNS <str:mixed> the full amount of supplies the user has available.
	
	// $supplies = MySupplies::getSupplyList($uniID);
	{
		return Database::selectOne("SELECT * FROM users_supplies WHERE uni_id=? LIMIT 1", array($uniID));
	}
	
	
/****** Get Supplies ******/
	public static function getSupplies
	(
		$uniID			// <int> The Uni-Account to get supplies from.
	,	$supply			// <str> The type of supply to check the value of.
	)					// RETURNS <int> the amount of those supplies the user has, or 0 on failure.
	
	// $coins = MySupplies::getSupplies($uniID, "coins");
	{
		// Make sure the supply type is allowed
		$suppliesAllowed = array('coins', 'components', 'crafting', 'alchemy', 'ninja_boxes', 'mystery_boxes', 'achievements');
		
		if(!in_array($supply, $suppliesAllowed))
		{
			return 0;
		}
		
		$supply = Sanitize::variable($supply);
		
		// Get the user's current supply
		return (int) Database::selectValue("SELECT " . $supply . " FROM users_supplies WHERE uni_id=? LIMIT 1", array($uniID));
	}
	
	
/****** Add or Subtract Supplies ******/
	public static function changeSupplies
	(
		$uniID			// <int> The UniID to update supplies to.
	,	$supply			// <str> The type of supply to add or subtract.
	,	$amount			// <int> The amount of that supply to add (or subtract, if provide negative values).
	)					// RETURNS <int> amount of supplies the user has after update, or 0 on error.
	
	// $coins = MySupplies::changeSupplies($uniID, "coins", 100);
	{
		// Make sure the supply type is allowed
		$suppliesAllowed = array('coins', 'components', 'crafting', 'alchemy', 'ninja_boxes', 'mystery_boxes');
		
		if(!in_array($supply, $suppliesAllowed))
		{
			return 0;
		}
		
		$supply = Sanitize::variable($supply);
		
		// Get the user's current supply
		$has = (int) Database::selectValue("SELECT " . $supply . " FROM users_supplies WHERE uni_id=? LIMIT 1", array($uniID));
		
		if($has === false && $amount > 0)
		{
			if(Database::query("INSERT INTO users_supplies (uni_id, `" . $supply . "`) VALUES (?, ?)", array($uniID, $amount)))
			{
				return $amount;
			}
			
			return 0;
		}
		
		if($amount > 0 || ($amount < 0 && $has >= abs($amount)))
		{
			// Run the Update
			Database::query("UPDATE users_supplies SET `" . $supply . "`=`" . $supply . "`+? WHERE uni_id=? LIMIT 1", array($amount, $uniID));
			
			if(Database::$rowsAffected > 0)
			{
				$has += $amount;
			}
			else
			{
				Database::query("INSERT IGNORE INTO users_supplies (uni_id, " . $supply . ") VALUES (?, ?)", array($uniID, $amount));
				
				if(Database::$rowsAffected > 0)
				{
					$has += $amount;
				}
			}
		}
		else if($has < abs($amount))
		{
			return 0;
		}
		
		return $has;
	}
	
	
/****** Set Supplies to an exact amount ******/
	public static function setSupplies
	(
		$uniID			// <int> The UniID to set supplies for.
	,	$supply			// <str> The type of supply to set.
	,	$amount			// <int> The amount of that supply to set.
	)					// RETURNS <bool> TRUE on success, or FALSE on error.
	
	// MySupplies::setSupplies($uniID, "achievements", 100);
	{
		// Make sure the supply type is allowed
		$suppliesAllowed = array('coins', 'components', 'crafting', 'alchemy', 'ninja_boxes', 'mystery_boxes', 'achievements');
		
		if(!in_array($supply, $suppliesAllowed))
		{
			return false;
		}
		
		$supply = Sanitize::variable($supply);
		$amount = $amount + 0;
		
		if($amount < 0) { return false; }
		
		// Get the user's current supply
		$has = (int) Database::selectValue("SELECT " . $supply . " FROM users_supplies WHERE uni_id=? LIMIT 1", array($uniID));
		
		if($has === false) // Might equal "0"
		{
			if(Database::query("INSERT INTO users_supplies (uni_id, `" . $supply . "`) VALUES (?, ?)", array($uniID, $amount)))
			{
				return $amount;
			}
			
			return false;
		}
		
		// Run the Update
		return Database::query("UPDATE users_supplies SET " . $supply . "=? WHERE uni_id=? LIMIT 1", array($amount, $uniID));
	}
	
}
