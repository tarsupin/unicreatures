<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// UniCreatures Installation
abstract class Install extends Installation {
	
	
/****** Plugin Variables ******/
	
	// These addon plugins will be selected for installation during the "addon" installation process:
	public static array <str, bool> $addonPlugins = array(	// <str:bool>
		"Avatar"			=> true
	,	"Confirm"			=> true
	,	"Friends"			=> true
	,	"Notifications"		=> true
	);
	
	
/****** App-Specific Installation Processes ******/
	public static function setup(
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	{
		// Create the avatar table, which is used in multiple plugins
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_settings`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`avatar_list`			varchar(255)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`uni_id`) PARTITIONS 13;
		");
		
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("users_settings", array("uni_id", "avatar_list"));
	}
}