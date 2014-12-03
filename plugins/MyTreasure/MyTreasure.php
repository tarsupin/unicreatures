<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the MyTreasure Plugin ------
-----------------------------------------

This plugin provides treasure acquisition and handling.


------------------------------------------
------ Types of Treasures / Coupons ------
------------------------------------------
	
	* Coupon to give people free gifts at a discounted rate.
	* Coupon to get exotic pets for a discounted price.
	* Items that let you change your pets in some way (cannot buy these).
	* Items that regain energy.
	* Coupon for extended premium account, or discounted price.
	* Coupon to activate the golden beams.
	
	
-------------------------------
------ Methods Available ------
-------------------------------

$treasure = MyTreasure::random($uniID);								// Returns a random treasure type, e.g. "coins"
$treasureData = MyTreasure::acquire($uniID, $treasure = "");		// Acquire a type of treasure
$treasureList = MyTreasure::acquireBulk($uniID, $treasures);		// Gives a bulk set of treasures to a user

$treasureData = MyTreasure::data($treasure);		// Returns treasure data (e.g. image, name, etc)

$queueData = MyTreasure::getQueue($uniID);

MyTreasure::retrieveQueueItem($uniID, $treasure, $dateEnds);
MyTreasure::addToQueue($uniID, $treasure, $parameters, $duration);
MyTreasure::removeFromQueue($uniID, $treasure, $dateEnds);

*/

abstract class MyTreasure {
	
	
/****** Class Variables ******/
	public static $exploreZone = "";		// <str> The current zone being explored.
	public static $treasure = array();		// <str:mixed> A list of treasures recovered.
	public static $locateEggBoost = 0;		// <int> A value of improved egg locating skills. (5 = low, 50 = v. high)
	
	
/****** Retrieve treasures from an area ******/
	public static function random
	(
		$uniID			// <int> The Uni-Account receiving the treasure.
	)					// RETURNS <str> the type of treasure to return, or "" on nothing.
	
	// $treasure = MyTreasure::random($uniID);
	{
		// Can get treasure (components, crafting, alchemy, items, etc)
		$rand = (mt_rand(1, 1000));
		//return "pet";
		// Energy regains 1 per 20 seconds.
		// 2250 energy for 10 hours of full activity
		// If acquiring a pet is 200 views, half supplies is 11 pets / 10 hours.
		
		/*
			Coins: 29%
			Components: 15%
			Crafting: 6%
			Alchemy: 4%
			Special Item: 0.6%			// Coupons, Mystery Boxes, etc.
			Pet: 0.5%
			Nothing: 45%
		*/
		
		// Receive a major bonus 1% of the time
		if($rand >= 990)
		{
			// Special Item (0.6% chance)
			if($rand >= 995) { /* Nothing Yet */ }
			
			// Random Pet (0.5% chance)
			else if($rand >= 990) { return "pet"; }
		}
		
		// Receive a supply 54% of the time
		else if($rand >= 450)
		{
			// Alchemy (4% chance)
			if($rand >= 950) { return "alchemy"; }
			
			// Crafting (6% chance)
			else if($rand >= 890) { return "crafting"; }
			
			// Components (15% chance)
			else if($rand >= 700) { return "components"; }
			
			// Coins (29% chance)
			return "coins";
		}
		
		return "";
	}
	
	
/****** Get a random egg rarity (exploration or hut) ******/
	public static function randomEggRarity (
	)					// RETURNS <int> random rarity for an egg based on probability.
	
	// $eggRarity = MyTreasure::randomEggRarity();
	{
		$chance = mt_rand(0, 1000);
		$rarity = 0;
		
		// Egg Boost can potentially provide benefits
		if(self::$locateEggBoost)
		{
			$diff = 1000 - $chance;
			$div = self::$locateEggBoost / 100;
			
			$booster = $diff * $div;
			$chance += $booster;
		}
		
		// Common (0): 25% chance
		if($chance > 250)
		{
			// Somewhat Common (1): 25% to 45% chance (20% chance)
			if($chance <= 450) { $rarity = 1; }
			
			// Uncommon (2): 45% to 62% chance (17% chance)
			else if($chance <= 620) { $rarity = 2; }
			
			// Limited (3): 62% to 77% chance (15% chance)
			else if($chance <= 770) { $rarity = 3; }
			
			// Sparse (4): 77% to 89% chance (12% chance)
			else if($chance <= 890) { $rarity = 4; }
			
			// Very Sparse (5): 89% to 95% chance (6% chance)
			else if($chance <= 950) { $rarity = 5; }
			
			// Rare (6): 95% to 98% chance (3% chance)
			else if($chance <= 980) { $rarity = 6; }
			
			// Very Rare (7): (1.2% chance, 1 in 166)
			else if($chance <= 992) { $rarity = 7; }
			
			// Epic (8): (0.6% chance, 2 in 333)
			else if($chance <= 998) { $rarity = 8; }
			
			// Legendary (9): (0.2% chance, 1 in 500)
			else if($chance <= 1000) { $rarity = 9; }
		}
		
		return $rarity;
	}
	
	
/****** Get a random exploration creature, based on a rarity provided ******/
	public static function randomExploreCreature
	(
		$rarity		// <int> The rarity of the exploration creature to retrieve.
	)				// RETURNS <int> the type ID of the creature to provide, or 0 if none.
	
	// $typeID = MyTreasure::randomExploreCreature($rarity);
	{
		// Set the likelihood of getting nobles and exalteds
		$noNoble = (mt_rand(1, 100) > 40) ? " AND ct.prefix != 'noble' " : '';
		$noExalted = (mt_rand(1, 100) > 20) ? " AND ct.prefix != 'Exalted' " : '';
		
		if($fetchOptions = Database::selectMultiple("SELECT ec.type_id FROM explore_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ec.explore_zone=? AND ec.rarity=?" . $noNoble . $noExalted, array(self::$exploreZone, $rarity)))
		{
			$rnd = mt_rand(0, count($fetchOptions) - 1);
			
			return (int) $fetchOptions[$rnd]['type_id'];
		}
		
		return 0;
	}
	
	
/****** Update a user to receive treasure ******/
	public static function acquire
	(
		$uniID			// <int> The UniID receiving the treasure.
	,	$treasure = ""	// <str> The treasure to provide the user.
	, 	$count = 1		// <int> The number of the treasure to acquire.
	)					// RETURNS <array> data regarding the treasure that was received, or links to retrieve it.
	
	// $treasureData = MyTreasure::acquire($uniID, $treasure = "", [$count]);
	{
		// If no treasure was assigned, end here
		if($treasure == "")
		{
			return array();
			
			//$treasure = MyTreasure::random($uniID);
		}
		
		$treasureData = array();
		
		// Prepare Treasures
		if(in_array($treasure, array("alchemy", "coins", "crafting", "components")))
		{
			$supply = MySupplies::changeSupplies($uniID, $treasure, $count);
			
			$treasureData = self::data($treasure);
			
			$treasureData['count'] = $count;
			$treasureData['total'] = $supply;
		}
		
		// Prepare Pet
		if($treasure == "pet")
		{
			$treasureData = self::data($treasure);
			
			if($treasureData !== array())
			{
				$prepareQueue = array(
					"petData"		=> $treasureData['petData']
				,	"image"			=> $treasureData['image']
				);
				
				// Add the pet to the item queue
				MyTreasure::addToQueue(Me::$id, $treasure, $prepareQueue, 86400 * 2);
			}
		}
		
		if($treasureData)
		{
			self::$treasure[$treasure] = $treasureData;
		}
		
		return $treasureData;
	}
	
	
/****** Update a user to receive treasure in bulk ******/
	public static function acquireBulk
	(
		$uniID			// <int> The UniID receiving the treasure.
	,	$treasures		// <array> List of treasures to provide the user.
	)					// RETURNS <bool> TRUE if the user received the update.
	
	// $treasures = MyTreasure::acquireBulk($uniID, $treasures);
	{
		// Provide the user with the relevant treasures
		foreach($treasures as $key => $count)
		{
			self::acquire($uniID, $key, $count);
		}
		
		return true;
	}
	
	
/****** Return Treasure Data ******/
	public static function data
	(
		$treasure		// <str> Type of treasure to return data on.
	)					// RETURNS <str:mixed> data on the treasure (image, name, etc).
	
	// $treasureData = MyTreasure::data($treasure);
	{
		$treasureData = array();
		
		// Alchemy Components
		if($treasure == "alchemy")
		{
			$treasureData['type'] = "alchemy";
			$treasureData['title'] = "Alchemy Ingredient";
			
			$shuffle = array('elemental_earth_orb', 'elemental_earth_shard', 'elemental_fire_orb', 'elemental_fire_shard', 'elemental_water_orb', 'elemental_water_shard', 'elemental_wind_orb', 'elemental_wind_shard', 'tree_dew', 'tree_seeds', 'tree_spiritstone', 'tree_gemstone');
			shuffle($shuffle);
			
			$treasureData['image'] = '/assets/supplies/' . $shuffle[0] . '.png';
		}
		
		// Coins
		else if($treasure == "coins")
		{
			$treasureData['type'] = "coins";
			$treasureData['title'] = "Auro Coin";
			$treasureData['image'] = '/assets/supplies/coins_large.png';
		}
		
		// Crafting
		else if($treasure == "crafting")
		{
			$treasureData['type'] = "crafting";
			$treasureData['title'] = "Crafting Supplies";
			
			$shuffle = array('wood', 'stone', 'metal', 'supplies');
			shuffle($shuffle);
			
			$treasureData['image'] = '/assets/supplies/' . $shuffle[0] . '.png';
		}
		
		// Components
		else if($treasure == "components")
		{
			$treasureData['type'] = "components";
			$treasureData['title'] = "Component";
			
			$shuffle = array('echoberry', 'bluemaple', 'auraglass', 'essentia', 'ancientberry', 'astralune', 'heartwater', 'lifepowder', 'meadowgem', 'moonruby', 'riverstone', 'seamelon', 'skypollen', 'sunnyseed', 'timeshard', 'treescent', 'watervine', 'whiteroot');
			shuffle($shuffle);
			
			$treasureData['image'] = '/assets/supplies/' . $shuffle[0] . '.png';
		}
		
		// Pet
		else if($treasure == "pet")
		{
			// Determine a random pet to provide
			$eggRarity = MyTreasure::randomEggRarity();
			
			// If we successfully found a creature in this explore zone
			if($typeID = MyTreasure::randomExploreCreature($eggRarity))
			{
				// Get the Creature Data
				$typeData = MyCreatures::petTypeData($typeID, "id, family, name, prefix");
				
				$treasureData['type'] = "pet";
				$treasureData['title'] = "Found " . ($typeData['name'] == "Egg" ? $typeData['family'] . ' Egg' : $typeData['name']);
				
				$treasureData['petData'] = $typeData;
				
				$treasureData['image'] = MyCreatures::imgSrc($typeData['family'], $typeData['name'], $typeData['prefix']);
			}
		}
		
		return $treasureData;
	}
	
	
/****** Return contents of the treasure queue ******/
	public static function getQueue
	(
		$uniID			// <int> The UniID to retrieve the treasure queue of.
	)					// RETURNS <int:[str:mixed]> list of queued treasuers for that user, FALSE on failure.
	
	// $queueData = MyTreasure::getQueue($uniID);
	{
		// Delete from Queue
		Database::query("DELETE FROM queue_treasure WHERE uni_id=? AND date_disappears < ?", array($uniID, time()));
		
		return Database::selectMultiple("SELECT * FROM queue_treasure WHERE uni_id=?", array($uniID));
	}
	
	
/****** Retrieve an item from the treasure queue ******/
	public static function retrieveQueueItem
	(
		$uniID			// <int> The UniID to retrieve the treasure queue of.
	,	$dateEnds		// <int> The date that the treasure is disappearing (identifies which one to select).
	)					// RETURNS <bool> TRUE if you retrieved the item, FALSE on failure.
	
	// MyTreasure::retrieveQueueItem($uniID, $dateEnds);
	{
		if($queueItem = Database::selectOne("SELECT treasure, json FROM queue_treasure WHERE uni_id=? AND date_disappears=? LIMIT 1", array($uniID, $dateEnds)))
		{
			$json = json_decode($queueItem['json'], true);
			
			if(self::removeFromQueue($uniID, $dateEnds))
			{
				// Give a collected pet to the user (add to their wild)
				if($queueItem['treasure'] == "pet" && isset($json['petData']) && isset($json['petData']['id']))
				{
					MyCreatures::acquireCreature($uniID, (int) $json['petData']['id']);
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Add entry to the treasure queue ******/
	public static function addToQueue
	(
		$uniID			// <int> The UniID that you're adding the queue for.
	,	$treasure		// <str> The treasure to add to the queue.
	,	$parameters		// <array> The parameters to add to the treasure.
	,	$duration		// <int> The duration (in seconds) to keep the item in the queue.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyTreasure::addToQueue($uniID, $treasure, $parameters, $duration);
	{
		if(!is_array($parameters))
		{
			$parameters = array();
		}
		
		$parameters = json_encode($parameters);
		$disappears = time() + $duration;
		
		if(!Database::query("INSERT IGNORE INTO `queue_treasure` (uni_id, treasure, json, date_disappears) VALUES (?, ?, ?, ?)", array($uniID, $treasure, $parameters, $disappears)))
		{
			// If the treasure wasn't added, there was a date_disappears conflict. Set it one second back.
			return Database::query("INSERT IGNORE INTO `queue_treasure` (uni_id, treasure, json, date_disappears) VALUES (?, ?, ?, ?)", array($uniID, $treasure, $parameters, $disappears - 1));
		}
		
		return true;
	}
	
	
/****** Removes contents of the treasure queue ******/
	public static function removeFromQueue
	(
		$uniID			// <int> The UniID to delete a treasure queue entry.
	,	$dateEnds		// <int> The date that the treasure is disappearing (identifies which one to delete).
	)					// RETURNS <bool> TRUE if deleted, FALSE on failure.
	
	// MyTreasure::removeFromQueue($uniID, $dateEnds);
	{
		return Database::query("DELETE FROM queue_treasure WHERE uni_id=? AND date_disappears=? LIMIT 1", array($uniID, $dateEnds));
	}
	
}
