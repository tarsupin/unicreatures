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
		CREATE TABLE IF NOT EXISTS `achievements`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`creature_family`		varchar(16)					NOT NULL	DEFAULT '',
			
			`finished`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`fully_evolved`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`evolutions`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`trained`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`royalty`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`herd`					tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`awards`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `creature_family`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 31;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("achievements", array("uni_id", "creature_family"));
		
		return ($pass1);
	}
	
}
