<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyTeams_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyTeams";
	public $title = "Team Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides creature teams for UniCreatures.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return true;
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `teams`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`name`					varchar(22)					NOT NULL	DEFAULT '',
			`image`					varchar(42)					NOT NULL	DEFAULT '',
			
			`experience`			mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`points`				mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `teams_by_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`team_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `team_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `teams_creatures`
		(
			`team_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`type_id`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			`nickname`				varchar(22)					NOT NULL	DEFAULT '',
			`gender`				char(1)						NOT NULL	DEFAULT 'm',			
			`experience`			mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`points`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`team_id`, `type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(team_id) PARTITIONS 23;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("teams", array("id", "uni_id"));
		$pass2 = DatabaseAdmin::columnsExist("teams_by_user", array("uni_id", "team_id"));
		$pass3 = DatabaseAdmin::columnsExist("teams_creatures", array("team_id", "type_id"));
		
		return ($pass1 and $pass2 and $pass3);
	}
	
}
