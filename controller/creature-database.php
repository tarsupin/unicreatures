<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare Values
$currentPage = (isset($url[1]) && is_numeric($url[1]) ? $url[1] + 0 : 1);
$showRows = 15;

// Determine Filter Method
$sqlWhere = " WHERE prefix=?";
$sqlArray = array("");

// Get List Count
$count = (int) Database::selectValue("SELECT COUNT(DISTINCT family) as totalNum FROM creatures_types" . $sqlWhere, $sqlArray);

$page = new Pagination($count, $showRows, $currentPage);

// Get List of Creatures
$typeList = Database::selectMultiple("SELECT DISTINCT family FROM creatures_types" . $sqlWhere . " ORDER BY family" . $page->queryLimit, $sqlArray);

// Prepare Page Listing
$pageDiv = '
<ul class="paginate-wrap">Go to page: ';

foreach($page->pages as $page)
{
	if($page == $currentPage)
	{
		$pageDiv .= '
		<li class="paginate-active">' . $page . '</li>';
	}
	else
	{
		$pageDiv .= '
		<li class="paginate' . ($page == $currentPage ? '-active' : '') . '"><a href="/creature-database/' . $page . '">' . $page . '</a></li>';
	}
}

$pageDiv .= '
</ul>';

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<style>
	.paginate-wrap { text-align:center; font-size:1.5em; }
	.paginate { display:inline-block; text-align:center; border-radius:6px; background-color:#aae00b; }
	.paginate-active { display:inline-block; text-align:center; border-radius:6px; background-color:#cc44ab; padding:6px; }
	.paginate a { display:block; padding:6px; }
	.paginate:hover { background-color:#bee0aa; }
	
	.pet-hdr { width:200px; background-color:#eeeeee; border-top-left-radius:6px; border-top-right-radius:6px; border:solid 1px black; border-bottom:none; padding:6px; font-size:1.2em; }
	
	.pet-fam { background-color:#eeeeee; border-radius:6px; border:solid 1px black; border-top-left-radius:0px; padding:8px; margin-bottom:10px; margin-top:0px; }
	.pet { display:inline-block; text-align:center; background-color:#abcdef; border-radius:6px; border:solid 1px green; padding:6px; margin:6px; }
	.pet a { display:block; }
	.pet:hover { background-color:#789abc; }
</style>

<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo $pageDiv;

foreach($typeList as $type)
{
	echo '
	<div class="pet-hdr">' . $type['family'] . ' Family</div>
	<ul class="pet-fam">';
	
	$creatures = Database::selectMultiple("SELECT id, family, name, prefix FROM creatures_types WHERE family=? AND prefix=? ORDER BY evolution_level", array($type['family'], ""));
	
	foreach($creatures as $creature)
	{
		echo '
		<li class="pet"><a href="/creature-data/' . ($creature['name'] == "Egg" ? strtolower($creature['family']) . '-egg' : strtolower($creature['name'])) . '"><img src="' . MyCreatures::imgSrc($creature['family'], $creature['name'], $creature['prefix']) . '" /><br />' . ($creature['name'] == "Egg" ? $creature['family'] . " Egg" : $creature['name']) . '</a></li>';
	}
	
	echo '
	</ul>';
}

echo $pageDiv;

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
