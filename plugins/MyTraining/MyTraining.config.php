<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyTraining_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyTraining";
	public $title = "Supply Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides methods to handle UniCreature training.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `abilities_base`
		(
			`creature_family`		varchar(16)					NOT NULL	DEFAULT '',
			
			`strength`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`agility`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`speed`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`intelligence`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`wisdom`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`charisma`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`creativity`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`willpower`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`focus`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`creature_family`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `abilities_max`
		(
			`creature_family`		varchar(16)					NOT NULL	DEFAULT '',
			
			`strength`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`agility`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`speed`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`intelligence`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`wisdom`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`charisma`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`creativity`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`willpower`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`focus`					smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`creature_family`)
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
		$pass1 = DatabaseAdmin::columnsExist("abilities_base", array("strength", "agility"));
		$pass2 = DatabaseAdmin::columnsExist("abilities_max", array("strength", "agility"));
		
		return ($pass1 and $pass2);
	}
	
}
