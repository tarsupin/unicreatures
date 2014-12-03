<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyEnergy_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyEnergy";
	public $title = "Energy Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the system to use and work with energy.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_energy`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`energy`				smallint(4)		unsigned	NOT NULL	DEFAULT '0',
			`energy_lastUse`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 13;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("users_energy", array("uni_id", "energy"));
		
		return ($pass1);
	}
	
}
