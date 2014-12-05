<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyShop_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyShop";
	public $title = "Shop Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides functions to work with the UniCreatures shop.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_creatures`
		(
			`id`					smallint(6)		unsigned	NOT NULL	AUTO_INCREMENT,
			
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`cost`					mediumint(7)	unsigned	NOT NULL	DEFAULT '0',
			
			`score_required`		smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`day_start`				smallint(3)					NOT NULL	DEFAULT '0',
			`day_end`				smallint(3)					NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`day_start`)
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
		$pass1 = DatabaseAdmin::columnsExist("shop_creatures", array("id", "type_id"));
		
		return ($pass1);
	}
	
}
