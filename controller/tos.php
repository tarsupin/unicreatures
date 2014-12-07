<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

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

<h3>UniCreatures Terms of Service</h3>

<p>UniCreatures follows all of the rules in the <a href="' . URL::unifaction_com() . '/tos">UniFaction TOS</a>, but also has an additional rule that apply specifically to UniCreatures:</p>

<p>Auto-clicking, botting, and other automated processes are not allowed. You can, however, still use click sites that perform by manual use.</p>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
