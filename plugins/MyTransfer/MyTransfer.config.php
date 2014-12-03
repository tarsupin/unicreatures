<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MySupplies_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MySupplies";
	public $title = "Supply Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides methods to work with the user's UniCreatures supplies.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transfer_users`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`old_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`)
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
		$pass1 = DatabaseAdmin::columnsExist("transfer_users", array("uni_id", "old_id"));
		
		return ($pass1);
	}
	
}
