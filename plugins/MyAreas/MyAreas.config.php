<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyAchievements_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyAchievements";
	public $title = "Achievement System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides an achievement system for UniCreatures.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
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
		CREATE TABLE IF NOT EXISTS `land_plots`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`area_type_id`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`name`					varchar(22)					NOT NULL	DEFAULT '',
			
			`population`			tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			`max_population`		tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 23;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `land_plots_by_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sort_order`			tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			`area_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`, `sort_order`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 23;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("land_plots_types", array("id", "type"));
		$pass2 = DatabaseAdmin::columnsExist("land_plots", array("id", "uni_id"));
		$pass3 = DatabaseAdmin::columnsExist("land_plots_by_user", array("uni_id", "area_id"));
		
		return ($pass1 and $pass2 and $pass3);
	}
	
}
