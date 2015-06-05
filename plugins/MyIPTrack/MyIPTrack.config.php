<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyIPTrack_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyIPTrack";
	public $title = "IP Tracking Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the system to use and work with IP tracking (for pet bonuses, etc).";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ip_visit_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`ip`					varchar(45)					NOT NULL	DEFAULT '',
			
			`date_visit`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `ip`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 61;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ip_visit_counter`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`cycle`					mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`count`					mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 3;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("ip_visit_user", array("uni_id", "ip"));
		$pass2 = DatabaseAdmin::columnsExist("ip_visit_counter", array("uni_id", "cycle"));
		
		return ($pass1 and $pass2);
	}
	
}
