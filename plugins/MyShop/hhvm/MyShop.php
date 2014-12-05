<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the MyShop Plugin ------
-------------------------------------

This plugin allows you to handle the shops in UniCreatures.


-------------------------------
------ Methods Available ------
-------------------------------

$creatureID = MyShop::shopList($uniID, $typeID);
$petData = MyShop::shopPet($shopBuyID);

*/

abstract class MyShop {
	
	
/****** Get the creatures for sale in the shop ******/
	public static function shopList (
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> the list of creatures available in the shop, or FALSE on failure.
	
	// $creatures = MyShop::shopList();
	{
		$day = date("z");
		
		return Database::selectMultiple("SELECT sc.id, sc.type_id, sc.cost, sc.day_end, ct.family, ct.name, ct.prefix, ct.blurb FROM shop_creatures sc INNER JOIN creatures_types ct ON ct.id=sc.type_id WHERE sc.day_start = ? OR (sc.day_start <= ? AND sc.day_end >= ?)", array(-1, $day, $day));
	}
	
	
/****** Get pet data on a shop pet ******/
	public static function shopPet
	(
		int $shopBuyID		// <int> The purchase ID for the shop pet.
	): array <str, mixed>					// RETURNS <str:mixed> the data of the new creature, or array() on failure.
	
	// $petData = MyShop::shopPet($shopBuyID);
	{
		return Database::selectOne("SELECT sc.type_id as id, sc.cost, sc.day_start, sc.day_end, ct.family, ct.name, ct.prefix FROM shop_creatures sc INNER JOIN creatures_types ct ON ct.id=sc.type_id WHERE sc.id=? LIMIT 1", array($shopBuyID));
	}
	
	
/****** Update the exotic shop ******/
	public static function updateExoticShop (
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyShop::updateExoticShop();
	{
		
	}
	
}