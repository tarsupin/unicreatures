<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyExplore_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyExplore";
	public $title = "Energy Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides methods to use the explore zones and explore creatures.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `explore_area`
		(
			`type`					varchar(12)					NOT NULL	DEFAULT '',
			`explore_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			`history`				text						NOT NULL	DEFAULT '',
			
			UNIQUE (`type`, `explore_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `explore_creatures`
		(
			`explore_zone`			varchar(16)					NOT NULL	DEFAULT '',
			`rarity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`day_start`				smallint(3)					NOT NULL	DEFAULT '0',
			`day_end`				smallint(3)					NOT NULL	DEFAULT '0',
			
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`explore_zone`, `rarity`),
			INDEX (`day_start`, `day_end`)
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
		$pass1 = DatabaseAdmin::columnsExist("explore_area", array("type", "explore_id"));
		$pass2 = DatabaseAdmin::columnsExist("explore_creatures", array("explore_zone", "rarity"));
		
		return ($pass1 and $pass2);
	}
	
}
