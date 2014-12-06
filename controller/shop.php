<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/shop"); exit;
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '

<h3>UniFaction Shop</h3>
<div class="pet-cube"><div class="pet-cube-inner"><a href="/shop-pets"><img src="/assets/icons/button_hut.png" /></a><div class="uc-bold">Pets</div></div></div>
<div class="pet-cube"><div class="pet-cube-inner"><a href="/shop-pets"><img src="/assets/icons/button_hut.png" /></a><div class="uc-bold">Exotic Pets</div></div></div>
<div class="pet-cube"><div class="pet-cube-inner"><a href="/shop-supplies"><img src="/assets/icons/button_items.png" /></a><div class="uc-bold">Supplies</div></div></div>
<div class="pet-cube"><div class="pet-cube-inner"><a href="/shop-deeds"><img src="/assets/icons/button_forest.png" /></a><div class="uc-bold">Land Deeds</div></div></div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
