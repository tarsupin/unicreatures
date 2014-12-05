<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the MyExotic Plugin ------
---------------------------------------

This plugin allows you to purchase exotic pets and use the exotic pet shop.


-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class MyExotic {
	
	
/****** Plugin Variables ******/
	public static $shopCreatures = 5;		// <int> The number of creatures to have in the exotic shop.
	public static $daysGap = 4;				// <int> The number of days inbetween creatures leaving the shop.
	
	
/****** Get the list of exotic pets available in the shop ******/
	public static function getShopList (
	)						// RETURNS <int:[str:mixed]> The data and list of the exotic shop creatures.
	
	// $shopExotics = MyExotic::getShopList();
	{
		return Database::selectMultiple("SELECT ec.date_start, ec.date_end, ct.* FROM exotic_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ec.date_end > ? ORDER BY ec.date_end ASC", array(time()));
	}
	
	
/****** Get data about a single exotic pet available in the shop ******/
	public static function getShopPet
	(
		$typeID		// <int> The type ID of the pet to get information on.
	)				// RETURNS <str:mixed> The data of the exotic pet in the shop.
	
	// $petData = MyExotic::getShopPet($typeID);
	{
		return Database::selectOne("SELECT ec.date_start, ec.date_end, ct.* FROM exotic_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ec.type_id=? LIMIT 1", array($typeID));
	}
	
	
/****** Check how many exotics are in the shop currently ******/
	public static function exoticCount (
	)						// RETURNS <int> The number of creatures that are in the shop.
	
	// $count = MyExotic::exoticCount();
	{
		return (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM exotic_creatures WHERE date_end > ? LIMIT 1", array(time()));
	}
	
	
/****** Choose a random exotic to add to the shop ******/
	public static function chooseForShop (
	)					// RETURNS <int> the creature type ID to add to the shop.
	
	// $typeID = MyExotic::chooseForShop();
	{
		return (int) Database::selectValue("SELECT id FROM creatures_types WHERE rarity=? AND evolution_level=? AND prefix=? ORDER BY RAND() LIMIT 1", array(20, 1, ""));
	}
	
	
/****** Expire exotic pets from the shops ******/
	public static function expireShopPets (
	)					// RETURNS <bool> TRUE on successful expiration, FALSE on failure.
	
	// MyExotic::expireShopPets();
	{
		return Database::query("DELETE FROM exotic_creatures WHERE date_end < ? LIMIT 1", array(time()));
	}
	
	
/****** Update the exotic pet list ******/
	public static function updateExotics (
	)					// RETURNS <bool> TRUE if the exotics were updated properly, FALSE on failure.
	
	// MyExotic::updateExotics();
	{
		// Expire any old pets in the list
		self::expireShopPets();
		
		// Check the current count of exotic pets available
		$count = self::exoticCount();
		
		while($count < self::$shopCreatures)
		{
			// Get the last date
			$lastDate = (int) Database::selectValue("SELECT date_end FROM exotic_creatures ORDER BY date_end DESC", array());
			
			if($lastDate < time())
			{
				$lastDate = time() + (3600 * 24 * self::$daysGap);
			}
			
			// Get a new exotic pet to add to the shop
			$typeID = MyExotic::chooseForShop();
			
			// Add the creature to the shop
			Database::query("INSERT INTO exotic_creatures (type_id, date_start, date_end) VALUES (?, ?, ?)", array($typeID, time(), $lastDate + (3600 * 24 * self::$daysGap)));
			
			$count++;
		}
		
		return true;
	}
	
	
/****** Purchase exotic credits ******/
	public static function purchaseCredit
	(
		$uniID		// <int> The UniID that is purchasing the exotic credits.
	,	$credits	// <int> The number of credits being purchased.
	)				// RETURNS <bool> TRUE on successful purchase, FALSE on failure.
	
	// MyExotic::purchaseCredit($uniID, $credits);
	{
		$timestamp = time();
		$pass = false;
		
		Database::startTransaction();
		
		for($a = 1; $a <= $credits; $a++)
		{
			if(!$pass = Database::query("INSERT INTO exotic_credits (uni_id, purchase_date) VALUES (?, ?)", array($uniID, $timestamp)))
			{
				break;
			}
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Spend an exotic credit on a creature ******/
	public static function spendCreditOnCreature
	(
		$uniID			// <int> The UniID that purchased a creature with the exotic credit.
	,	$creatureID		// <int> The creature ID that was purchased with this credit.
	)					// RETURNS <bool> TRUE on successful update, FALSE on failure.
	
	// MyExotic::spendCreditOnCreature($uniID, $creatureID);
	{
		Database::query("UPDATE exotic_credits SET creature_id=? WHERE uni_id=? AND creature_id=? LIMIT 1", array($creatureID, $uniID, 0));
		
		return (Database::$rowsAffected >= 1 ? true : false);
	}
	
	
/****** Count the user's available exotic credits ******/
	public static function countMyAvailableCredits
	(
		$uniID		// <int> The UniID that is having their exotic credits counted.
	)				// RETURNS <int> the number of available credits.
	
	// $creditCount = MyExotic::countMyAvailableCredits($uniID);
	{
		return (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM exotic_credits WHERE uni_id=? AND creature_id=? LIMIT 1", array($uniID, 0));
	}
	/*
	
		Database::exec("
		CREATE TABLE IF NOT EXISTS `exotic_credits`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`purchase_date`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`creature_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `exotic_creatures`
		(
			`type_id`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`date_start`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `exotic_purchases`
		(
			`type_id`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`added_to_shop`			mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`times_purchased`		mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
		");
	*/
}
