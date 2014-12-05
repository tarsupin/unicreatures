<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare the Page's Active Hashtag
if(!isset($config['active-hashtag']))
{
	$config['active-hashtag'] = "UniCreatures";
}

// Base style sheet for this site
Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/unifaction-2col.css" />');

// Load the Social Menu
require(SYS_PATH . "/controller/includes/social-menu.php");

// Prepare Logged-In Modules
if(Me::$loggedIn)
{
	// UniFaction Dropdown Menu
	WidgetLoader::add("UniFactionMenu", 10, '
	<div class="menu-wrap hide-600">
		<ul class="menu">' . (isset($uniMenu) ? $uniMenu : '') . '
			
			<li class="menu-slot"><a href="/' . Me::$vals['handle'] . '">My Profile</a><ul><li class="dropdown-slot"><a href="/treasure-chest">Treasure Chest</a></li><li class="dropdown-slot"><a href="/training-center">Training Center</a></li><li class="dropdown-slot"><a href="/achievements">My Achievements</a></li><li class="dropdown-slot"><a href="/' . Me::$vals['handle'] . '">Visiting Page</a></li></ul>
			
			<li class="menu-slot"><a href="/land-plots">My Pets</a><ul><li class="dropdown-slot"><a href="/wild">My Wild Area</a></li><li class="dropdown-slot"><a href="/action/sort-plots">Sort My Plots</a></li></ul>
			
			<li class="menu-slot"><a href="/">Caretaker Hut</a></li><li class="menu-slot"><a href="javascript:void(0);">Shop</a><ul><li class="dropdown-slot"><a href="/shop-pets">Pet Shop</a></li><li class="dropdown-slot"><a href="/exotic-pets">Exotic Pet Shop</a></li><li class="dropdown-slot"><a href="/shop-supplies">Supply Shop</a></li><li class="dropdown-slot"><a href="/shop-deeds">Land Plots</a></li></ul>
			
			<li class="menu-slot"><a href="/herd-list">Herds</a></li><li class="menu-slot"><a href="/explore">Explore</a></li><li class="menu-slot"><a href="javascript:void(0)">Info</a><ul><li class="dropdown-slot"><a href="/creature-database">Pet Database</a></li><li class="dropdown-slot"><a href="javascript:void(0);">Help &amp; Tutorial</a></li><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures">UniCreatures Forum</a></li></ul>
		</ul>
	</div>');
	
	// Main Navigation
	WidgetLoader::add("MobilePanel", 10, '
	<div class="panel-box">
		<ul class="panel-slots">
			<li class="nav-slot' . ($url[0] == Me::$vals['handle'] ? " nav-active" : "") . '"><a href="/' . Me::$vals['handle'] . '">My Profile<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "" ? " nav-active" : "") . '"><a href="/">Caretaker Hut<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "land-plots" ? " nav-active" : "") . '"><a href="/land-plots">Land Plots<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "herds" ? " nav-active" : "") . '"><a href="/herds">Herds<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "shop" ? " nav-active" : "") . '"><a href="/shop">Shop<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "explore-zones" ? " nav-active" : "") . '"><a href="/explore-zones">Explore<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "achievements" ? " nav-active" : "") . '"><a href="/achievements">Achievements<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "exotic-pets" ? " nav-active" : "") . '"><a href="/exotic-pets">Exotic Pets<span class="icon-circle-right nav-arrow"></span></a></li>
		</ul>
	</div>');
}
else
{
	// UniFaction Dropdown Menu
	WidgetLoader::add("UniFactionMenu", 10, '
	<div class="menu-wrap hide-600">
		<ul class="menu">' . (isset($uniMenu) ? $uniMenu : '') . '
			
			<li class="menu-slot"><a href="/login">My Profile</a><ul><li class="dropdown-slot"><a href="/treasure-chest">Treasure Chest</a></li><li class="dropdown-slot"><a href="/training-center">Training Center</a></li><li class="dropdown-slot"><a href="/achievements">My Achievements</a></li></ul>
			
			<li class="menu-slot"><a href="/land-plots">My Pets</a><ul><li class="dropdown-slot"><a href="/wild">My Wild Area</a></li><li class="dropdown-slot"><a href="/action/sort-plots">Sort My Plots</a></li></ul>
			
			<li class="menu-slot"><a href="/">Caretaker Hut</a></li><li class="menu-slot"><a href="javascript:void(0);">Shop</a><ul><li class="dropdown-slot"><a href="/shop-pets">Pet Shop</a></li><li class="dropdown-slot"><a href="/exotic-pets">Exotic Pet Shop</a></li><li class="dropdown-slot"><a href="/shop-supplies">Supply Shop</a></li><li class="dropdown-slot"><a href="/shop-deeds">Land Plots</a></li></ul>
			
			<li class="menu-slot"><a href="/herd-list">Herds</a></li><li class="menu-slot"><a href="/explore">Explore</a></li><li class="menu-slot"><a href="javascript:void(0)">Info</a><ul><li class="dropdown-slot"><a href="/creature-database">Pet Database</a></li><li class="dropdown-slot"><a href="javascript:void(0);">Help &amp; Tutorial</a></li><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_community() . '/unicreatures">UniCreatures Forum</a></li></ul>
		</ul>
	</div>');
	
	// Main Navigation
	WidgetLoader::add("MobilePanel", 10, '
	<div class="panel-box">
		<ul class="panel-slots">
			<li class="nav-slot' . ($url[0] == "login" ? " nav-active" : "") . '"><a href="/login">Log In<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "" ? " nav-active" : "") . '"><a href="/">Caretaker Hut<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "land-plots" ? " nav-active" : "") . '"><a href="/land-plots">Land Plots<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "herds" ? " nav-active" : "") . '"><a href="/herds">Herds<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "shop" ? " nav-active" : "") . '"><a href="/shop">Shop<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "explore-zones" ? " nav-active" : "") . '"><a href="/explore-zones">Explore<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "achievements" ? " nav-active" : "") . '"><a href="/achievements">Achievements<span class="icon-circle-right nav-arrow"></span></a></li>
			<li class="nav-slot' . ($url[0] == "exotic-pets" ? " nav-active" : "") . '"><a href="/exotic-pets">Exotic Pets<span class="icon-circle-right nav-arrow"></span></a></li>
		</ul>
	</div>');
}
