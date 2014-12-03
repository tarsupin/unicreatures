<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyTreasure_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyTreasure";
	public $title = "Treasure Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides methods to work with the treasure system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `queue_treasure`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`treasure`				varchar(22)					NOT NULL	DEFAULT '',
			`json`					varchar(250)				NOT NULL	DEFAULT '',
			
			`date_disappears`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `date_disappears`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 21;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("queue_treasure", array("uni_id", "treasure"));
		
		return ($pass1);
	}
	
}
