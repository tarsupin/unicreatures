<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Base style sheet for this site
Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/unifaction-2col.css" />');
Metadata::addFooter('<script src="/assets/scripts/info.js"></script>');

// Update the user activity module
UserActivity::update();

// Load the Social Menu
require(SYS_PATH . "/controller/includes/social-menu.php");

// Prepare Logged-In Modules
if(Me::$loggedIn)
{
	// UniFaction Dropdown Menu
	WidgetLoader::add("UniFactionMenu", 10, '
	<div class="menu-wrap hide-600">
		<ul class="menu">' . (isset($uniMenu) ? $uniMenu : '') . '
			<li class="menu-slot' . ((in_array($url[0], array("", "achievements", "training-center", "herd-list", "team-list", "treasure-chest", "settings")) && in_array(You::$id, array(0, Me::$id))) || $url[0] == Me::$vals['handle'] ? ' nav-active' : '') . '"><a href="/">My Profile</a><ul><li class="dropdown-slot"><a href="/">Pet Areas</a></li><li class="dropdown-slot"><a href="/' . Me::$vals['handle'] . '">Visit Center</a></li><li class="dropdown-slot"><a href="/achievements">Achievements</a></li><li class="dropdown-slot"><a href="/training-center">Training Center</a></li><li class="dropdown-slot"><a href="/herd-list">Herds</a></li><li class="dropdown-slot"><a href="/team-list">Teams</a></li><li class="dropdown-slot"><a href="/treasure-chest">Treasure Chest</a></li><li class="dropdown-slot"><a href="/settings">Settings</a></li></ul>
			
			<li class="menu-slot' . (in_array($url[0], array("caretaker-hut", "caretaker-hut-predict")) ? ' nav-active' : '') . '"><a href="/caretaker-hut">Caretaker Hut</a></li><li class="menu-slot' . (in_array($url[0], array("shop", "shop-pets", "exotic-pets", "shop-supplies", "shop-deeds")) ? ' nav-active' : '') . '"><a href="/shop">Shop</a><ul><li class="dropdown-slot"><a href="/shop-pets">Pet Shop</a></li><li class="dropdown-slot"><a href="/exotic-pets">Exotic Pet Shop</a></li><li class="dropdown-slot"><a href="/shop-supplies">Supply Shop</a></li><li class="dropdown-slot"><a href="/shop-deeds">Land Plots</a></li></ul>
			
			<li class="menu-slot' . (in_array($url[0], array("explore", "explore-zones", "explore-rush")) ? ' nav-active' : '') . '"><a href="/explore-zones">Explore</a></li><li class="menu-slot' . (in_array($url[0], array("creature-database", "schedule", "explore-map", "tos")) ? ' nav-active' : '') . '"><a href="#">Info</a><ul><li class="dropdown-slot"><a href="/creature-database">Pet Database</a></li><li class="dropdown-slot"><a href="/schedule">Rotation Schedule</a></li><li class="dropdown-slot"><a href="/explore-map">Exploration Map</a></li><li class="dropdown-slot"><a href="#">Help &amp; Tutorial</a></li><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures' . Me::$slg . '">UniCreatures Forum</a></li><li class="dropdown-slot"><a href="/tos">UniCreatures TOS</a></li></ul>
		</ul>
	</div>');
	
	// Main Navigation
	WidgetLoader::add("MobilePanel", 10, '
	<div class="panel-box">
		<ul class="panel-slots">
			<li class="nav-slot"><a href="/">My Profile<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/' . Me::$vals['handle'] . '">Visit Center<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/achievements">Achievements<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/training-center">Training Center<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/herds">Herds<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/teams">Teams<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/treasure-chest">Treasure Chest<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/settings">Settings<span class="icon-circle-right nav-arrow"></span></a></li>
			
			<li class="nav-slot"><a href="/caretaker-hut">Caretaker Hut<span class="icon-circle-right nav-arrow"></span></a></li>
			
			<li class="nav-slot"><a href="/shop">Shop<span class="icon-circle-right nav-arrow"></span></a></li>
			
			<li class="nav-slot"><a href="/explore-zones">Explore<span class="icon-circle-right nav-arrow"></span></a></li>		
			
			<li class="nav-slot"><a href="/creature-database">Pet Database<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/schedule">Rotation Schedule<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/explore-map">Exploration Map<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures' . Me::$slg . '">UniCreatures Forum<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/tos">UniCreatures TOS<span class="icon-circle-right nav-arrow"></span></a></li>
		</ul>
	</div>');
}
else
{
	// UniFaction Dropdown Menu
	WidgetLoader::add("UniFactionMenu", 10, '
	<div class="menu-wrap hide-600">
		<ul class="menu">' . (isset($uniMenu) ? $uniMenu : '') . '
			<li class="menu-slot' . ($url[0] == "welcome" ? " nav-active" : "") . '"><a href="/welcome">Welcome</a></li><li class="menu-slot' . ($url[0] == "login" ? " nav-active" : "") . '"><a href="/login">Log In</a></li><li class="menu-slot"><a href="/login">Join UniCreatures</a></li><li class="menu-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures">Forum</a></li><li class="menu-slot' . ($url[0] == "creature-database" ? " nav-active" : "") . '"><a href="/creature-database">Creature Database</a></li><li class="menu-slot' . ($url[0] == "tos" ? " nav-active" : "") . '"><a href="/tos">TOS</a></li>
		</ul>
	</div>');
	
	// Main Navigation
	WidgetLoader::add("MobilePanel", 10, '
	<div class="panel-box">
		<ul class="panel-slots">
			<li class="nav-slot"><a href="/welcome">Welcome Page<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/login">Log In<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/login">Join UniCreatures<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures">UniCreatures Forum<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/creature-database">Creature Database<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot"><a href="/tos">UniCreatures TOS<span class="icon-circle-right nav-arrow"></span></a></li>
		</ul>
	</div>');
}
