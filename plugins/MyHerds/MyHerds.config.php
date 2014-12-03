<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyHerds_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyHerds";
	public $title = "Energy Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the system to use and work with creature herds.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `herds`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`family`				varchar(22)					NOT NULL	DEFAULT '',
			`population`			tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `family`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 23;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `herds_creatures`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`family`				varchar(22)					NOT NULL	DEFAULT '',
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`nickname`				varchar(22)					NOT NULL	DEFAULT '',
			
			INDEX (`uni_id`, `family`, `nickname`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 61;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("herds", array("uni_id", "family"));
		$pass2 = DatabaseAdmin::columnsExist("herds_creatures", array("family", "type_id"));
		
		return ($pass1 and $pass2);
	}
	
}
