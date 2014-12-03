<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyExotic_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyExotic";
	public $title = "MyExotic Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the use of the exotic shops and purchasing exotic pets.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
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
		CREATE TABLE IF NOT EXISTS `exotic_credits`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`purchase_date`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`creature_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`)
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
		$pass1 = DatabaseAdmin::columnsExist("exotic_creatures", array("type_id", "date_start"));
		$pass2 = DatabaseAdmin::columnsExist("exotic_credits", array("uni_id", "creature_id"));
		
		return ($pass1 and $pass2);
	}
	
}
