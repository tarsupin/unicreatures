<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/schedule"); exit;
}

// Get Schedule Data
if(Cache::exists("uc2:schedule"))
{
	$data = json_decode(Cache::get("uc2:schedule"), true);
}
else
{
	$data['basket'] = Database::selectMultiple("SELECT bc.day_start, bc.day_end, ct.family, ct.name, ct.blurb, ct.prefix FROM basket_creatures bc INNER JOIN creatures_types ct ON bc.type_id=ct.id WHERE bc.day_start!=? AND ct.prefix != ? AND ct.prefix NOT LIKE ? AND ct.prefix != ? AND ct.prefix NOT LIKE ? ORDER BY bc.day_start, bc.day_end, ct.family, ct.prefix", array(-1, "Noble", "Noble%", "Exalted", "Exalted%"));
	$data['explore'] = Database::selectMultiple("SELECT ec.day_start, ec.day_end, ct.family, ct.name, ct.blurb, ct.prefix FROM explore_creatures ec INNER JOIN creatures_types ct ON ec.type_id=ct.id WHERE ec.day_start!=? AND ct.prefix != ? AND ct.prefix NOT LIKE ? AND ct.prefix != ? AND ct.prefix NOT LIKE ? ORDER BY ec.day_start, ec.day_end, ct.family, ct.prefix", array(-1, "Noble", "Noble%", "Exalted", "Exalted%"));
	$data['shop'] = Database::selectMultiple("SELECT sc.day_start, sc.day_end, ct.family, ct.name, ct.blurb, ct.prefix FROM shop_creatures sc INNER JOIN creatures_types ct ON sc.type_id=ct.id WHERE sc.day_start!=? AND ct.prefix != ? AND ct.prefix NOT LIKE ? AND ct.prefix != ? AND ct.prefix NOT LIKE ? ORDER BY sc.day_start, sc.day_end, ct.family, ct.prefix", array(-1, "Noble", "Noble%", "Exalted", "Exalted%"));
	
	Cache::set("uc2:schedule", json_encode($data), 86400);
}

// Prepare the Page's Active Hashtag
$config['active-hashtag'] = "UniCreatures";

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

$day = date("z");

echo '
<h2>Rotation Schedule' . (isset($url[1]) ? ': ' . ($url[1] == "basket" ? '<a href="/caretaker-hut">Caretaker Hut</a>' : ($url[1] == "explore" ? '<a href="/explore-zones">Explore</a>' : ($url[1] == "shop" ? '<a href="/shop-pets">Pet Shop</a>' : ""))) : '') . '</h2>';

if(!isset($url[1]) || !isset($data[$url[1]]))
{
	echo '
	<p><a href="/schedule/basket/">Caretaker Hut</p>
	<p><a href="/schedule/explore/">Explore</p>
	<p><a href="/schedule/shop/">Pet Shop</p>';
}
else
{
	foreach($data[$url[1]] as $pet)
	{
		echo '
		<div class="shop-block">
			<div class="shop-block-inner">
				<div class="shop-block-left">
					<img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" />
				</div>
				<div class="shop-block-right">
					<div class="shop-block-title">' . ($pet['prefix'] != "" ? $pet['prefix'] . " " : "") . ($pet['name'] == "Egg" ? $pet['family'] : $pet['name']) . '</div>
					<div class="shop-block-note">' . $pet['blurb'] . '</div>';

		if($day >= $pet['day_start'] && $day <= $pet['day_end'])
		{
			echo '
					<div class="shop-block-leave">Leaves ' . ($pet['day_end'] - $day > 0 ? ' in ' . ($pet['day_end'] - $day) . ' Days' : ($pet['day_end'] - $day < 0 ? ' in ' . ($pet['day_end'] + 365 - $day) . ' Days' : 'Today')) . '</div>';
		}
		else
		{
			echo '
					<div class="shop-block-arrive">Arrives ' . ($pet['day_start'] - $day > 0 ? ' in ' . ($pet['day_start'] - $day) . ' Days' : ($pet['day_start'] - $day < 0 ? ' in ' . ($pet['day_start'] + 365 - $day) . ' Days' : 'Today')) . '</div>';
		}
				
		echo '
				</div>
			</div>
		</div>';
	}
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
