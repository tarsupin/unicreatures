<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/shop-deeds"); exit;
}

// Get user's supply list
$coins = MySupplies::getSupplies(Me::$id, 'coins');
$deedList = MyAreas::deedPurchaseList();

// Purchase something from the shop
if($link = Link::clicked() and $link == "shop-deeds" and isset($_GET['buy']))
{
	// Buy Deeds
	if(isset($deedList[$_GET['buy']]))
	{
		if($coins >= $deedList[$_GET['buy']]['cost'])
		{
			// Get the Land Deed Type
			if($typeData = MyAreas::areaTypeData($_GET['buy']))
			{
				// Check how many deeds you currently own
				$deedsOwned = (int) Database::selectValue("SELECT COUNT(*) as cnt FROM land_plots WHERE uni_id=?", array(Me::$id));
				
				if($deedsOwned >= 35)
				{
					Alert::error("Too Many Deeds", "You own the maximum amount of allowed deeds. You must sell some to purchase more.");
				}
				else
				{
					// Purchase the Land Deed
					$pass = false;
					
					Database::startTransaction();
					
					if($newCoins = MySupplies::changeSupplies(Me::$id, "coins", 0 - $deedList[$_GET['buy']]['cost']))
					{
						$pass = MyAreas::acquireDeed(Me::$id, $typeData['id']);
					}
					
					if(Database::endTransaction($pass))
					{
						$coins = $newCoins;
						Alert::success("Purchased Deed", "You have purchased the &quot;" . $deedList[$_GET['buy']]['title'] . "&quot; deed.");
					}
					else
					{
						Alert::error("Deed Issue", "There was an issue with purchasing that deed.");
					}
				}
			}
		}
		else
		{
			Alert::error("Too Expensive", "You do not have enough coins to purchase that deed.");
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("shop-deeds");

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

<style>
.deed-block { display:inline-block; padding:12px; text-align:center; }
.deed-block img { max-height: 120px; }
</style>

<h2>Land Deed Shop</h2>
<p>You have ' . number_format($coins) . ' Coins. Click on the land plot you would like to purchase.</p>';

// Display Land Deeds
foreach($deedList as $key => $value)
{
	echo '
	<div class="deed-block">
		<a href="/shop-deeds?buy=' . $key . '&' . $linkProtect . '"><img src="/assets/areas/' . $key . '.png" /></a>
		<div style="font-size:1.1em; font-weight:bold;">' . $value['title'] . '</div>
		<div style="font-size:0.9em;">' . number_format($value['cost']) . ' Coins</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
