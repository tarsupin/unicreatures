<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyCreatures_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyCreatures";
	public $title = "Creature Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides methods to interact with creatures.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `creatures_types`
		(
			`id`					smallint(6)		unsigned	NOT NULL	AUTO_INCREMENT,
			
			`family`				varchar(12)					NOT NULL	DEFAULT '',
			`name`					varchar(12)					NOT NULL	DEFAULT '',
			`prefix`				varchar(12)					NOT NULL	DEFAULT '',
			
			`evolution_level`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`evolves_from`			smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`required_points`		smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			`gender`				char(1)						NOT NULL	DEFAULT 'b',
			`blurb`					varchar(140)				NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			UNIQUE (`family`, `name`, `prefix`),
			INDEX (`evolves_from`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `creatures_owned`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`area_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`nickname`				varchar(22)					NOT NULL	DEFAULT '',
			`gender`				char(1)						NOT NULL	DEFAULT 'm',
			
			`activity`				varchar(12)					NOT NULL	DEFAULT '',
			`active_until`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`experience`			mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`total_points`			smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`date_acquired`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 61;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `creatures_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`creature_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `creature_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 61;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `creatures_area`
		(
			`area_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sort_order`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`creature_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`special`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`area_id`, `sort_order`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(area_id) PARTITIONS 61;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `basket_creatures`
		(
			`rarity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`rarity`, `type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("creatures_types", array("id", "family"));
		$pass2 = DatabaseAdmin::columnsExist("creatures_owned", array("id", "type_id"));
		$pass3 = DatabaseAdmin::columnsExist("creatures_user", array("uni_id", "creature_id"));
		$pass4 = DatabaseAdmin::columnsExist("creatures_area", array("area_id", "creature_id"));
		$pass5 = DatabaseAdmin::columnsExist("basket_creatures", array("rarity", "type_id"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4 and $pass5);
	}
	
}
