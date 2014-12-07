<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/action/sort-plots"); exit;
}

// Prepare Values
$_GET['from'] = (isset($_GET['from']) ? max(1, $_GET['from'] + 0) : false);
$_GET['to'] = (isset($_GET['to']) ? max(1, $_GET['to'] + 0) : false);

Alert::info("Plot Movement", "Click the " . ($_GET['from'] ? "location to move this plot." : "plot you would like to move."));

// Get Land Plots
$areas = MyAreas::areas(Me::$id);

// Leave page if you have no plots available
if(count($areas) <= 1)
{
	Alert::saveError("No Plots", "You need more plots to reorder them.");
	
	header("Location: /"); exit;
}

// Update the plot location
if($link = Link::clicked() and $link == "move-plot")
{
	if(MyAreas::relocate(Me::$id, $_GET['from'], $_GET['to']))
	{
		Alert::saveSuccess("Sort Plot", "You have resorted the plots!");
		
		header("Location: /action/sort-plots"); exit;
	}
}

// Prepare Values
$linkProtect = Link::prepare("move-plot");

// Supply List
$supplies = MySupplies::getSupplyList(Me::$id);

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
<div id="uc-left">
	<div class="uc-static-block" style="margin-top:0px;"><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . '"><img src="' . (Me::$vals['avatar_opt'] ? Avatar::image((int) Me::$id, (int) Me::$vals['avatar_opt']) : ProfilePic::image((int) Me::$id, "huge")) . '" /></a><div class="uc-bold">' . Me::$vals['display_name'] . '</div></div>
	
	<div class="uc-action-block hide-600">
		<div class="supply-block"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note">' . number_format($supplies['components']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note">' . number_format($supplies['coins']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note">' . number_format($supplies['crafting']) . '</div></div>
		<div class="supply-block"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note">' . number_format($supplies['alchemy']) . '</div></div>
	</div>
</div>

<div id="uc-right">
	<div class="uc-action-block">
		<div class="uc-action-inline"><a href="/"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
		<div class="uc-action-inline"><a href="/' . Me::$vals['handle'] . '"><img src="/assets/icons/button_visit.png" /></a><div class="uc-note-bold">Visit Center</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
		<div class="uc-action-inline"><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
	</div>';

foreach($areas as $area)
{
	echo '
	<div class="area-cube">
		<a href="/action/sort-plots?' . ($_GET['from'] ? 'from=' . $_GET['from'] . '&to=' . $area['id'] . '&' . $linkProtect : 'from=' . $area['id'] ) . '"><img src="/assets/areas/' . $area['type'] . '.png" /></a>
		<br />' . $area['name'] . '
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
