<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get URL Creature Info
if(!isset($url[1]))
{
	header("Location: /creature-database"); exit;
}

// Get the Creature Type Data
if(strpos($url[1], "-") !== false)
{
	$exp = explode("-", $url[1]);
	
	$petType = Database::selectOne("SELECT ct.*, ce.name as evolved_name FROM creatures_types ct LEFT JOIN creatures_types ce ON ct.evolves_from=ce.id WHERE ct.family=? AND ct.name=? AND ct.prefix=? LIMIT 1", array($exp[0], $exp[1], ""));
}
else
{
	$petType = Database::selectOne("SELECT ct.*, ce.name as evolved_name FROM creatures_types ct LEFT JOIN creatures_types ce ON ct.evolves_from=ce.id WHERE ct.name=? AND ct.prefix=? LIMIT 1", array($url[1], ""));
}

// Make sure the pet exists
if(!$petType)
{
	header("Location: /creature-database"); exit;
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
<div id="content">' . Alert::display();

echo '
<div id="uc-left-wide">
	<div id="pet">
		<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], "") . '" />
		<div id="pet-nickname">' . (($petType['name'] == "Egg" and $petType['evolution_level'] == 1) ? $petType['family'] . ' Egg' : $petType['name']) . '</div>
	</div>
	<div id="pet-details">
		<div style="text-align:center; font-weight:bold;"></div>
		<div style="text-align:center; font-weight:bold;">The ' . $petType['family'] . ' Family</div>
		<div style="margin-top:12px;">Rarity: ' . $petType['rarity'] . '</div>
		<div>Evolves From: ' . ($petType['evolved_name'] ? $petType['evolved_name'] : '---') . '</div>
		<div>Genders: ' . ($petType['gender'] == "b" ? "Male and Female" : ($petType['gender'] == "m" ? "Male" : "Female") . " Only") . '</div>
		<div style="margin-top:12px;">';
	
	$attributes = MyTraining::getAttributes($petType['family'], 1);
	
	foreach($attributes as $key => $value)
	{
		echo '
		<div class="attr-row"><div class="attr-name">' . ucfirst($key) . '</div><div class="attr-num">' . $value . '</div><div class="attr-graph">' . str_pad("", ($value / 4), "-")  . '</div></div>';
	}
	
	echo '
		</div>
	</div>
</div>

<div id="uc-right-wide">
	<div id="pet-actions">
		<a href="/creature-database" style="display:block; padding:10px 0 10px 0;">Back to Creature Database</a>
	</div>
	<div id="pet-blurb">' . $petType['blurb'] . '</div>
	<div id="pet-desc" style="margin-top:12px;">' . nl2br(MyCreatures::descMarkup($petType['description'], $petType['name'], $petType['gender'])) . '</div>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
