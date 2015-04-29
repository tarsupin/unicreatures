<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }


// Staff Permissions Page
require(APP_PATH . "/includes/staff_global.php");

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo '
<h2>Staff Page</h2>

<p><a href="/staff/shop/pet-list">Manage Shop Pets</a></p>
<p><a href="/staff/basket/pet-list">Manage Basket Pets</a></p>
<p><a href="/staff/explore/zone-list">Manage Exploration Pets</a></p>
<p><a href="/staff/supply-user">Supply User</a></p>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
