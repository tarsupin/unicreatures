<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/shop-pets"); exit;
}

// Get the shop creatures
$creatures = MyShop::shopList();

// Prepare Values
$day = date('z', time());
$coins = MySupplies::getSupplies(Me::$id, "coins");

// If you purchased a pet
if(isset($_GET['purchase']) and $value = Link::clicked() and $value == "pet-shop")
{
	// Get details about the pet
	if($petData = MyShop::shopPet((int) $_GET['purchase']))
	{
		// Make sure that the pet can be purchased during this time-frame
		if($petData['day_start'] == -1 or ($petData['day_start'] <= $day and $petData['day_end'] >= $day))
		{
			if($coins > $petData['cost'])
			{
				// Purchase the pet
				$creatureID = MyCreatures::acquireCreature(Me::$id, (int) $petData['id']);
				
				$coins = MySupplies::changeSupplies(Me::$id, "coins", 0 - abs($petData['cost']));
				
				Alert::success("Purchased Pet", 'You have purchased a ' . $petData['family'] . ' Egg' . '! <a href="/pet/' . $creatureID . '">Visit your pet</a>.');
			}
			else
			{
				Alert::error("Insufficient Coins", "You don't have enough coins to purchase this creature!");
			}
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("pet-shop");

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
<div id="content">' . Alert::display() . '

<h2>Pet Shop</h2>
<p>You have ' . number_format($coins) . ' Coins. Click on a pet to purchase it.</p>';

foreach($creatures as $shopPet)
{
	echo '
	<div class="shop-block">
		<div class="shop-block-inner">
			<div class="shop-block-left">
				<a href="/shop-pets?purchase=' . $shopPet['id'] . '&' . $linkProtect . '"><img src="' . MyCreatures::imgSrc($shopPet['family'], $shopPet['name'], $shopPet['prefix']) . '" /></a>
				<div>' . number_format($shopPet['cost']) . ' Coins</div>
			</div>
			<div class="shop-block-right">
				<div class="shop-block-title">' . ($shopPet['name'] == "Egg" ? $shopPet['family'] . ' Egg' : $shopPet['name']) . '</div>
				<div class="shop-block-note">' . $shopPet['blurb'] . '</div>';
			
			if($shopPet['day_end'] > -1)
			{
				echo '
				<div class="shop-block-leave">Leaves ' . ($shopPet['day_end'] - $day > 0 ? ' in ' . ($shopPet['day_end'] - $day) . ' Days' : 'Today') . '</div>';
			}
			
			echo '
			</div>
		</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
