<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } Database::initRoot();

// NOTE!! It is VERY important that you clone the database BEFORE running /setup
// Otherwise, certain tables will be broken during the update.
// Step 1. Clone this file to _prepare_backup
// Step 2. Run each exit; function until completion, deleting as each step finishes.


/******************************
****** Copy the Database ******
******************************/

// Step 1. Create an empty unicreatures database.

	// mysql -u root -e "create database unicreatures"; 
	
// Step 2. Copy the existing UniCreatures database.

	// mysqldump -h localhost -u root -p unicreatures_backup | mysql -h localhost -u root -p unicreatures

	// Note: this might take 5 minutes to actually complete, and you won't see any results until then.
	
exit;

/**************************
****** Run SQL Setup ******
**************************/

// Run /install

exit;

/*********************************************
****** Update the UNICREATURES database ******
*********************************************/

// Remove unnecessary tables, or unusable tables
DatabaseAdmin::dropTable("accomplishments_quest");
DatabaseAdmin::dropTable("accomplishments_standard");
DatabaseAdmin::dropTable("adventure_options");
DatabaseAdmin::dropTable("adventure_types");
DatabaseAdmin::dropTable("alerts");
DatabaseAdmin::dropTable("chatbox");
DatabaseAdmin::dropTable("cheat_detection");
DatabaseAdmin::dropTable("combat_ranking");
DatabaseAdmin::dropTable("combat_squad");
DatabaseAdmin::dropTable("comments");
DatabaseAdmin::dropTable("community_chest");
DatabaseAdmin::dropTable("community_chest_ip");
//DatabaseAdmin::dropTable("creature_abilities");
DatabaseAdmin::dropTable("creature_color");
DatabaseAdmin::dropTable("creature_color_shop");
//DatabaseAdmin::dropTable("creature_components");
DatabaseAdmin::dropTable("creature_database");
DatabaseAdmin::dropTable("creature_interaction");
DatabaseAdmin::dropTable("creature_legends");
//DatabaseAdmin::dropTable("creature_base_abilities");
//DatabaseAdmin::dropTable("creature_max_abilities");
DatabaseAdmin::dropTable("creature_powers");
DatabaseAdmin::dropTable("creature_powers_list");
DatabaseAdmin::dropTable("creature_powers_list");
DatabaseAdmin::dropTable("creature_talk");
DatabaseAdmin::dropTable("creature_training");

DatabaseAdmin::dropTable("donations_post");

DatabaseAdmin::dropTable("fb_click_rewards");
DatabaseAdmin::dropTable("fb_processed_referrals");
DatabaseAdmin::dropTable("fb_referrals");
DatabaseAdmin::dropTable("ip_records");

DatabaseAdmin::dropTable("paypal_cart_info");
DatabaseAdmin::dropTable("paypal_subscription_info");
DatabaseAdmin::dropTable("rss_uc_posts");
DatabaseAdmin::dropTable("stage");

DatabaseAdmin::dropTable("tech_buildings");
DatabaseAdmin::dropTable("tech_building_types");
DatabaseAdmin::dropTable("tech_building_types_components");
DatabaseAdmin::dropTable("tech_knowledge");
DatabaseAdmin::dropTable("tech_missions");
DatabaseAdmin::dropTable("tech_mission_log");
DatabaseAdmin::dropTable("tech_research_types");
DatabaseAdmin::dropTable("transmute");
DatabaseAdmin::dropTable("world_areas");
DatabaseAdmin::dropTable("world_options");

//DatabaseAdmin::dropTable("stories");

DatabaseAdmin::renameTable("pen_areas", "old_pen_areas");

echo "Updated UniCreatures Database: Dropped several unnecessary tables.";

exit;

/******************************************
****** Update the UNICREATURES table ******
******************************************/

// Note: This function may take a while to run
ini_set('max_execution_time', 120);
if(DatabaseAdmin::dropIndex("creatures", "set_order"))
{
	echo "Pass Phase #1 - Creatures Table";
}

exit;

// Note: This function may take a while to run
ini_set('max_execution_time', 120);
if(DatabaseAdmin::dropIndex("creatures", "time_acquired"))
{
	echo "Pass Phase #2 - Creatures Table";
}

exit;

// Note: This function may take a while to run
ini_set('max_execution_time', 120);
DatabaseAdmin::dropIndex("creatures", "creature_family");
echo "Pass Phase #3 - Creatures Table";

exit;

// Note: This function may take a while to run
ini_set('max_execution_time', 120);
DatabaseAdmin::dropIndex("creatures", "pen_id");
echo "Pass Phase #4 - Creatures Table";

exit;


/****** Drop Columns ******/

ini_set('max_execution_time', 120);
DatabaseAdmin::dropColumns("creatures", "can_evolve", "image");
echo "Pass Phase #5 - Creatures Table";

exit;

ini_set('max_execution_time', 120);
DatabaseAdmin::dropColumns("creatures", "creature_family", "arena_game", "arena_position");
echo "Pass Phase #6 - Creatures Table";

exit;

ini_set('max_execution_time', 120);
DatabaseAdmin::dropColumns("creatures", "set_order", "gather_option", "in_pen");
echo "Pass Phase #7 - Creatures Table";

exit;

ini_set('max_execution_time', 120);
DatabaseAdmin::dropColumns("creatures", "m_interact", "m_train", "m_powers", "blank", "pen_id");
echo "Pass Phase #8 - Creatures Table";

exit;

DatabaseAdmin::renameColumn("creatures", "user_id", "uni_id");
DatabaseAdmin::renameColumn("creatures", "time_acquired", "date_acquired");
DatabaseAdmin::renameColumn("creatures", "set_order", "sort_order");
DatabaseAdmin::renameColumn("creatures", "total_clicks", "total_points");
DatabaseAdmin::renameColumn("creatures", "creature_type_id", "type_id");
echo "Pass Phase #9 - Creatures Table";

exit; 


ini_set('max_execution_time', 120);
DatabaseAdmin::addColumn("creatures", "location_id", "int(11) unsigned NOT NULL", "0");
echo "Pass Phase #10 - Creatures Table";

exit;


ini_set('max_execution_time', 120);
DatabaseAdmin::addColumn("creatures", "experience", "int(11) unsigned NOT NULL", "0");
echo "Pass Phase #11 - Creatures Table";

exit;


ini_set('max_execution_time', 120);
DatabaseAdmin::editColumn("creatures", "gender", "char(1) not null", "m");
echo "Pass Phase #12 - Creatures Table";

exit;


ini_set('max_execution_time', 120);
DatabaseAdmin::editColumn("creatures", "type_id", "smallint(6) unsigned not null", 0);
echo "Pass Phase #13 - Creatures Table";

exit;


// Step 1. Change the collation of nickname, gender, and the entire table to "utf8_general_ci"
// Note: do this before the change to InnoDB
DatabaseAdmin::changeCollation("creatures", "utf8");

echo "Changed collation of Creatures Table";

// Note: Double check that this worked. I haven't confirmed this yet.

exit;

// READ THIS:
//
// Note: Do this part in Navicat. Didn't work through script last time.
//
// NOTE: It takes like 15 - 20 minutes for this to work, and it will say "Not Responding" the whole time.
// Don't interrupt the process.

ini_set('max_execution_time', 120);
// DatabaseAdmin::setEngine("creatures", "INNODB");
echo "Change the creatures table to INNODB in Navicat";

exit; 

/********************************************
****** Update the Creature Types Table ******
********************************************/

ini_set('max_execution_time', 120);
DatabaseAdmin::copyTable("creatures_types", "creatures_types_copy");

echo "Created backup of Creature Type Table";

exit; 

/********************************************
****** Update the Creature Types Table ******
********************************************/

ini_set('max_execution_time', 120);

DatabaseAdmin::dropColumns("creatures_types", "item_dropped", "unique_rating", "score_required", "multi_color", "area_type", "creature_type", "image");
DatabaseAdmin::dropColumns("creatures_types", "shop_cost", "day_start", "day_end", "year_available");

DatabaseAdmin::renameColumn("creatures_types", "creature_family", "family");
DatabaseAdmin::renameColumn("creatures_types", "creature_name", "name");
DatabaseAdmin::renameColumn("creatures_types", "gender_only", "gender");
DatabaseAdmin::renameColumn("creatures_types", "visual_description", "blurb");
DatabaseAdmin::renameColumn("creatures_types", "lifestyle_description", "description");
DatabaseAdmin::renameColumn("creatures_types", "required_clicks", "required_points");
DatabaseAdmin::renameColumn("creatures_types", "stage", "evolution_level");

DatabaseAdmin::addColumn("creatures_types", "evolves_from", "smallint(6) unsigned NOT NULL", "0");
DatabaseAdmin::addColumn("creatures_types", "prefix", "varchar(12) NOT NULL", "");

// Modify gender to be char(1)
DatabaseAdmin::editColumn("creatures_types", "gender", "char(1) not null", "b");
echo "Pass Phase #1 - Creature Type Table";

exit;

// Set the table to InnoDB
ini_set('max_execution_time', 180);
DatabaseAdmin::setEngine("creatures_types", "INNODB");
echo "Pass Phase #2 - Creature Type Table";

exit;


/**********************************
****** Miscellaneous Updates ******
**********************************/
Database::query("UPDATE creatures_types SET gender=? WHERE gender=?", array("b", ""));

echo "Genders Updated";

exit;

/*******************************************
****** Reset the Rarity for Creatures ******
*******************************************/

Database::startTransaction();
Database::query("UPDATE creatures_types SET rarity=rarity-1 WHERE rarity < 8", array());
Database::query("UPDATE creatures_types SET rarity=rarity+10 WHERE rarity >= 8", array());
Database::endTransaction();

echo "Rarity Updated";

exit;



/**********************************************************
****** Create the new creature types (prefixes, etc) ******
**********************************************************/

// Cycle through the list. Create a noble and exalted for anything that has deny_noble set to 0.

$list = Database::selectMultiple("SELECT * FROM creatures_types WHERE deny_noble = 0 LIMIT 1, 870", array());

foreach($list as $cre)
{
	// Create Noble
	Database::query("INSERT INTO creatures_types (family, name, evolution_level, prefix, rarity, gender, blurb, description, required_points, deny_noble) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($cre['family'], $cre['name'], $cre['evolution_level'], "Noble", $cre['rarity'] + 2, $cre['gender'], $cre['blurb'], $cre['description'], $cre['required_points'], 1));
	
	// Create Exalted
	Database::query("INSERT INTO creatures_types (family, name, evolution_level, prefix, rarity, gender, blurb, description, required_points, deny_noble) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($cre['family'], $cre['name'], $cre['evolution_level'], "Exalted", $cre['rarity'] + 3, $cre['gender'], $cre['blurb'], $cre['description'], $cre['required_points'], 1));
}

echo "Created Nobles and Exalteds";


exit;


/************************************************************
****** Set the new evolution values with creature types *****
************************************************************/
$list = Database::selectMultiple("SELECT id, family, name, evolution_level, prefix FROM creatures_types WHERE evolution_level > 1", array());

foreach($list as $l)
{
	// Get the evolution_level it evolves from
	$origID = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND evolution_level=? AND prefix=? LIMIT 1", array($l['family'], $l['evolution_level'] - 1, $l['prefix']));
	
	Database::query("UPDATE creatures_types SET evolves_from=? WHERE id=? LIMIT 1", array($origID, $l['id']));
	
	echo $l['family'] . ' -> ' . $l['name'] . ' : ' . $origID . '<br />';
}

echo "Set the new evolution values";

exit;


/****************************************
****** Create the Basket Creatures ******
****************************************/

Database::exec("
CREATE TABLE IF NOT EXISTS `basket_creatures`
(
	`rarity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`rarity`, `type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

// Regular Pets
ini_set('max_execution_time', 60);
Database::startTransaction();

$getList = Database::selectMultiple("SELECT id, family, name, rarity FROM creatures_types WHERE name=? AND rarity < 8 AND prefix=? ORDER BY rarity", array("Egg", ""));

foreach($getList as $type)
{
	echo $type['family'] . ' -> ' . $type['name'] . ' (' . $type['rarity'] . ')<br />';
	MyCreaturesAdmin::addBasketCreature((int) $type['id'], (int) $type['rarity']);
}

Database::endTransaction();

exit;

// Exalted / Noble pets
ini_set('max_execution_time', 60);
Database::startTransaction();

$getList = Database::selectMultiple("SELECT id, family, name, rarity FROM creatures_types WHERE id > 774 AND name=? AND prefix != ? AND rarity < 10 ORDER BY rarity", array("Egg", ""));

foreach($getList as $type)
{
	echo $type['family'] . ' -> ' . $type['name'] . ' (' . $type['rarity'] . ')<br />';
	MyCreaturesAdmin::addBasketCreature((int) $type['id'], (int) $type['rarity']);
}

Database::endTransaction();

exit;


/********************************************
****** Update the Creature Types Table ******
********************************************/

DatabaseAdmin::dropColumn("creatures_types", "deny_noble");
DatabaseAdmin::dropColumn("creatures_types", "deny_herd");
DatabaseAdmin::dropColumn("creatures_types", "shop_cost");
DatabaseAdmin::dropColumn("creatures_types", "in_basket");

echo "Removing unnecessary columns from creatures_types";

exit;


/**************************************************************
****** Change Creatures to Noble / Exalted in New System ******
**************************************************************/

// Don't forget to change the LIMIT section. You have to update this each time until we hit all of the Exalted / Nobles.
// Keep in mind it's something like 690000 to go through, so it will take some time.
ini_set('max_execution_time', 120);

$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;




$rowsToAdd = 35000;	// Do not exceed 50,000. Can crush the memory limitations.

$fetch = Database::selectMultiple("SELECT c.id, c.type_id, c.is_rare, ct.family, ct.name FROM creatures c INNER JOIN creatures_types ct ON ct.id=c.type_id WHERE is_rare > 0 LIMIT " . ($start + 0) . ", " . ($rowsToAdd + 0), array());

$arrayTypes = array();

Database::startTransaction();

foreach($fetch as $creature)
{
	if($creature['is_rare'] == 1)
	{
		$prefix = "Noble";
	}
	else if($creature['is_rare'] == 2)
	{
		$prefix = "Exalted";
	}
	else
	{
		continue;
	}
	
	if(!isset($arrayTypes[$creature['family'] . $creature['name'] . $prefix]))
	{
		$typeID = Database::selectOne("SELECT * FROM creatures_types WHERE family=? AND name=? AND prefix=? LIMIT 1", array($creature['family'], $creature['name'], $prefix));
		
		$arrayTypes[$creature['family'] . $creature['name'] . $prefix] = (int) $typeID['id'];
	}
	
	Database::query("UPDATE creatures SET type_id=? WHERE id=? LIMIT 1", array($arrayTypes[$creature['family'] . $creature['name'] . $prefix], $creature['id']));
}

echo "Processed: " . count($fetch);
echo "<br /><br />Start at Row: <a href='/_prepare?start=" . ($start + $rowsToAdd) . "'>" . ($start + $rowsToAdd) . '</a>';
Database::endTransaction();

exit;


/***********************************************
****** Process the new Exploration System ******
***********************************************/

DBTransfer::copy("explore_types", "explore_types_backup", "", array(), 0);

DatabaseAdmin::renameTable("explore_types", "explore_area");

DatabaseAdmin::dropColumn("explore_area", "creature_1_family");
DatabaseAdmin::dropColumn("explore_area", "creature_2_family");
DatabaseAdmin::dropColumn("explore_area", "creature_3_family");
DatabaseAdmin::dropColumn("explore_area", "creature_1_stage");
DatabaseAdmin::dropColumn("explore_area", "creature_2_stage");
DatabaseAdmin::dropColumn("explore_area", "creature_3_stage");
DatabaseAdmin::dropColumn("explore_area", "creature_1_option");
DatabaseAdmin::dropColumn("explore_area", "creature_2_option");
DatabaseAdmin::dropColumn("explore_area", "creature_3_option");

DatabaseAdmin::renameColumn("explore_area", "area_type", "type");

DatabaseAdmin::addColumn("explore_area", "explore_id", "int(11) unsigned NOT NULL", "0");

DatabaseAdmin::dropColumn("explore_area", "id");

// Get the entire table and add new addresses to it
UniqueID::newCounter("exploreID");

$fetch = Database::selectMultiple("SELECT type, title FROM explore_area", array());

foreach($fetch as $fet)
{
	Database::query("UPDATE explore_area SET explore_id=? WHERE type=? AND title=? LIMIT 1", array(UniqueID::get("exploreID"), $fet['type'], $fet['title']));
	echo '<br />Updated: ' . $fet['title'];
}

exit;

/*****************************************
****** Rename the Exploration Zones ******
*****************************************/

Database::query("UPDATE explore_area SET type=? WHERE type=?", array("sargasso", "sea"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("great_plains", "grassland"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("silva", "forest"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("mountains", "mountain"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("new_atlantis", "urban"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("life_tree", "lifetree"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("old_city", "oldcity"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("red_sand", "redsand"));
Database::query("UPDATE explore_area SET type=? WHERE type=?", array("cloud_city", "cloudcity"));

echo "Exploration zones have been renamed";

exit;


/**************************************
****** Create the Pet Area Types ******
**************************************/

Database::exec("
CREATE TABLE IF NOT EXISTS `land_plots_types`
	(
		`id`					smallint(5)		unsigned	NOT NULL	AUTO_INCREMENT,
		
		`base_type`				varchar(16)					NOT NULL	DEFAULT '',
		`type`					varchar(16)					NOT NULL	DEFAULT '',
		`upgrades_from`			varchar(16)					NOT NULL	DEFAULT '',
		`upgrade_cost`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
		
		PRIMARY KEY (`id`),
		UNIQUE (`type`),
		UNIQUE (`base_type`, `type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

Database::exec("
INSERT INTO `land_plots_types` (`id`, `base_type`, `type`, `upgrades_from`, `upgrade_cost`) VALUES
(1, 'meadow', 'meadow', '', 0),
(2, 'meadow', 'farmstead', 'meadow', 100),
(3, 'meadow', 'country_house', 'farmstead', 250),
(4, 'pond', 'pond', '', 0),
(5, 'pond', 'village_pond', 'pond', 100),
(6, 'pond', 'city_pond', 'village_pond', 250),
(7, 'forest', 'forest', '', 0),
(8, 'forest', 'campgrounds', 'forest', 100),
(9, 'forest', 'tree_village', 'campgrounds', 250),
(10, 'castle_ruins', 'castle_ruins', '', 0),
(11, 'castle_ruins', 'fortress', 'castle_ruins', 1000),
(12, 'castle_ruins', 'castle', 'fortress', 2500),
(13, 'mountain', 'mountain', '', 0),
(14, 'mountain', 'caves', 'mountain', 200),
(15, 'mountain', 'mountain_base', 'caves', 400),
(16, 'ghost_town', 'ghost_town', '', 0),
(17, 'ghost_town', 'city', 'ghost_town', 250),
(18, 'ghost_town', 'metropolis', 'city', 500),
(19, 'beach', 'beach', '', 0),
(20, 'beach', 'traveled_beach', 'beach', 300),
(21, 'beach', 'pirate_beach', 'traveled_beach', 600),
(22, 'dry_zone', 'dry_zone', '', 0),
(23, 'dry_zone', 'outback', 'dry_zone', 350),
(24, 'dry_zone', 'trading_bay', 'outback', 700),
(25, 'underwater', 'underwater', '', 0),
(26, 'underwater', 'seabed', 'underwater', 300),
(27, 'underwater', 'coral_reef', 'seabed', 600);
");

echo "Create Area Types";

exit;

/*******************************************
****** Modify Creature Ability Tables ******
*******************************************/

Database::exec("
CREATE TABLE IF NOT EXISTS `abilities_base`
(
	`creature_family`		varchar(16)					NOT NULL	DEFAULT '',
	
	`strength`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`agility`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`speed`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`intelligence`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`wisdom`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`charisma`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`creativity`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`willpower`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`focus`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`creature_family`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

Database::exec("
CREATE TABLE IF NOT EXISTS `abilities_max`
(
	`creature_family`		varchar(16)					NOT NULL	DEFAULT '',
	
	`strength`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`agility`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`speed`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`intelligence`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`wisdom`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`charisma`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`creativity`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`willpower`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	`focus`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`creature_family`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

echo "Handling creature ability tables - part #1";

exit;

/***********************************************
****** Adding default values to abilities ******
***********************************************/

$creatures = Database::selectMultiple("SELECT DISTINCT creature_family FROM creatures_types_copy", array());

foreach($creatures as $c)
{
	$getStage = Database::selectOne("SELECT stage, creature_family FROM creatures_types_copy WHERE creature_family=? ORDER BY stage DESC LIMIT 1", array($c['creature_family']));
	
	// Get the row from max abilities and base abilities
	$max = Database::selectOne("SELECT * FROM creature_max_abilities WHERE creature_family=? AND stage=? LIMIT 1", array($getStage['creature_family'], $getStage['stage']));
	
	$min = Database::selectOne("SELECT * FROM creature_base_abilities WHERE creature_family=? LIMIT 1", array($getStage['creature_family']));
	
	//echo "--------------<br />" . $getStage['creature_family'] . ' (' . $getStage['stage'] . ')<br />';
	//var_dump($min);
	
	// Add to the Max Abilities Table
	Database::query("INSERT INTO abilities_max (creature_family, strength, agility, speed, intelligence, wisdom, charisma, creativity, willpower, focus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($getStage['creature_family'], $max['strength'], $max['agility'], $max['speed'], $max['intelligence'], $max['wisdom'], $max['charisma'], $max['creativity'], $max['willpower'], $max['focus']));
	
	// Add to the Base Abilities Table
	Database::query("INSERT INTO abilities_base (creature_family, strength, agility, speed, intelligence, wisdom, charisma, creativity, willpower, focus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($getStage['creature_family'], $min['strength'], $min['agility'], $min['speed'], $min['intelligence'], $min['wisdom'], $min['charisma'], $min['creativity'], $min['willpower'], $min['focus']));
}

echo "Handling creature ability tables - part #2";

exit;

/**************************
****** Table Cleanup ******
**************************/

DatabaseAdmin::dropTable("creature_base_abilities");
DatabaseAdmin::dropTable("creature_max_abilities");

echo "Table cleanup";

exit;

/********************************************
****** Preparing Exploration Creatures ******
********************************************/
Database::exec("
CREATE TABLE IF NOT EXISTS `explore_creatures`
(
	`explore_zone`			varchar(16)					NOT NULL	DEFAULT '',
	`rarity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`day_start`				smallint(3)		unsigned	NOT NULL	DEFAULT '0',
	`day_end`				smallint(3)		unsigned	NOT NULL	DEFAULT '0',
	
	`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
	
	INDEX (`explore_zone`, `rarity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

echo "Exploration Creatres - part #1";

exit;

/********************************************
****** Preparing Exploration Creatures ******
********************************************/

// Search for the existing creatures (original)
$mult = Database::selectMultiple("SELECT id, family, name, rarity FROM creatures_types WHERE name=? AND rarity < ?", array("Egg", 10));

foreach($mult as $m)
{
	$areaType = Database::selectValue("SELECT area_type FROM creatures_types_copy WHERE creature_family=? AND creature_name=? LIMIT 1", array($m['family'], $m['name']));
	
	if($areaType == "sea") { $areaType = "sargasso"; }
	else if($areaType == "forest") { $areaType = "silva"; }
	else if($areaType == "grassland" or $areaType == "meadow") { $areaType = "great_plains"; }
	else if($areaType == "oldcity") { $areaType = "old_city"; }
	else if($areaType == "urban") { $areaType = "new_atlantis"; }
	else if($areaType == "redsand") { $areaType = "red_sand"; }
	else if($areaType == "lifetree") { $areaType = "life_tree"; }
	else if($areaType == "cloudcity") { $areaType = "cloud_city"; }
	else if($areaType == "mountain") { $areaType = "mountains"; }
	else if($areaType == "skyland" or $areaType == "hephland") { /* Do nothing */ }
	else { continue; }
	
	// Insert into the explore zone
	Database::query("INSERT INTO explore_creatures (explore_zone, rarity, type_id) VALUES (?, ?, ?)", array($areaType, $m['rarity'], $m['id']));
}

echo "Exploration Creatres - part #2";

exit;


/**************************
****** Rename Tables ******
**************************/

DatabaseAdmin::renameTable("herd", "old_herds");
DatabaseAdmin::renameTable("creatures", "old_creatures");
DatabaseAdmin::renameTable("items", "old_items");
DatabaseAdmin::renameTable("user", "old_users");
DatabaseAdmin::renameTable("creature_types_copy", "old_creature_types");

echo "Updated UniCreatures tables.";

exit;


/*******************************
****** Add Shop Creatures ******
*******************************/

// Shop Pets
$results = Database::selectMultiple("SELECT * FROM old_creature_types WHERE day_start > ? AND shop_cost > ?", array(-1, 0));

foreach($results as $res)
{
	$typeID = MyCreatures::getTypeID($res['creature_family'], $res['creature_name'], "");
	
	// Insert into the shop
	Database::query("INSERT IGNORE INTO shop_creatures (type_id, cost, day_start, day_end) VALUES (?, ?, ?, ?)", array($typeID, $res['shop_cost'], $res['day_start'], $res['day_end']));
}

// Permanent Shop Pets
$results = Database::selectMultiple("SELECT * FROM old_creature_types WHERE day_start = ? AND shop_cost > ?", array(-1, 0));

foreach($results as $res)
{
	$typeID = MyCreatures::getTypeID($res['creature_family'], $res['creature_name'], "");
	
	// Insert into the shop
	Database::query("INSERT IGNORE INTO shop_creatures (type_id, cost, score_required, day_start, day_end) VALUES (?, ?, ?, ?, ?)", array($typeID, $res['shop_cost'], $res['score_required'], -1, -1));
}

echo "Updated Shop Creatures tables.";

exit;

/*************************************
****** Install Remaining Tables ******
*************************************/

MyUni6Update::addFinalTables();

exit;

/**************************
****** Once Finished ******
***************************

1. Check up on all indexes. Make sure that they match the SQL created.
	* For example, the explore_area section uses a different index than what ends up from the transfer.

2. Delete old tables? Perhaps back them up somewhere, but not needed here.

*/