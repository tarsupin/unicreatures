<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class MyBlocks_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "MyBlocks";
	public $title = "Common HTML Blocks";
	public $version = 1.0;
	public $author = "Brint Paris & Pegasus";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides html for common site elements.";
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return true;
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		return true;
	}
	
}
