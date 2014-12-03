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
