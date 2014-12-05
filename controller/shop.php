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
<p><a href="/shop-pets">Pets</a></p>
<p><a href="/shop-supplies">Supplies</a></p>
<p><a href="/shop-deeds">Land Deeds</a></p>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
