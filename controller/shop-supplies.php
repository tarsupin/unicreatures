<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/shop-supplies"); exit;
}

// Get user's supply list
$supplies = MySupplies::getSupplyList(Me::$id);

// Buy or sell something from the shop
if($link = Link::clicked() and $link == "shop-supply-uc")
{
	// Purchase something at the shop
	if(isset($_GET['purchase']))
	{
		$pass = false;
		Database::startTransaction();
		
		switch($_GET['purchase'])
		{
			// Purchase Crafting Supplies
			case "crafting":
				
				if($supplies['coins'] >= 50)
				{
					if($supplies['coins'] = MySupplies::changeSupplies(Me::$id, "coins", -50))
					{
						if($supplies['crafting'] = MySupplies::changeSupplies(Me::$id, "crafting", 10))
						{
							$pass = true;
							
							Alert::success("Purchased Supplies", "You have purchased 10 crafting supplies.");
						}
					}
				}
				
			break;
			
			// Purchase Components
			case "components":
				
				if($supplies['coins'] >= 40)
				{
					if($supplies['coins'] = MySupplies::changeSupplies(Me::$id, "coins", -40))
					{
						if($supplies['components'] = MySupplies::changeSupplies(Me::$id, "components", 10))
						{
							$pass = true;
							
							Alert::success("Purchased Components", "You have purchased 10 components.");
						}
					}
				}
				
			break;
			
			// Purchase Alchemy Ingredients
			case "alchemy":
				
				if($supplies['coins'] >= 15)
				{
					if($supplies['coins'] = MySupplies::changeSupplies(Me::$id, "coins", -15))
					{
						if($supplies['alchemy'] = MySupplies::changeSupplies(Me::$id, "alchemy", 1))
						{
							$pass = true;
							
							Alert::success("Purchased Ingredient", "You have purchased an alchemy ingredient.");
						}
					}
				}
				
			break;
		}
		
		Database::endTransaction($pass);
	}
	
	// Sell something at the shop
	else if(isset($_GET['sell']))
	{
		Database::startTransaction();
		
		switch($_GET['sell'])
		{
			// Purchase Components
			case "components":
				
				if($supplies['components'] >= 10)
				{
					if($supplies['coins'] = MySupplies::changeSupplies(Me::$id, "coins", 25))
					{
						if($supplies['components'] = MySupplies::changeSupplies(Me::$id, "components", -10))
						{
							$pass = true;
							
							Alert::success("Sold Components", "You have sold 10 components.");
						}
					}
				}
				
			break;
		}
		
		Database::endTransaction();
	}
}

// Prepare Values
$linkProtect = Link::prepare("shop-supply-uc");

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

<style>
.sup-block { display:inline-block; padding:12px; text-align:center; background-color:#deefff; border-radius:6px; margin:4px;}
.sup-block img { max-height: 120px; }
</style>

<h2>Supply Shop</h2>
<p>You have ' . number_format($supplies['coins']) . ' Coins. Click on the supply you would like to purchase.</p>

<div class="sup-block">
	<a href="/shop-supplies?purchase=crafting&' . $linkProtect . '"><img src="/assets/supplies/supplies.png" /></a><br />
	<p>10 Crafting Supplies<br />50 coins</p>
</div>

<div class="sup-block">
	<a href="/shop-supplies?purchase=components&' . $linkProtect . '"><img src="/assets/supplies/component_bag.png" /></a><br />
	<p>10 Components<br />40 coins</p>
</div>

<div class="sup-block">
	<a href="/shop-supplies?purchase=alchemy&' . $linkProtect . '"><img src="/assets/supplies/tree_seeds.png" /></a><br />
	<p>1 Alchemy Ingredient<br />15 coins</p>
</div>

<div class="sup-block">
	<a href="/shop-supplies?sell=components&' . $linkProtect . '"><img src="/assets/supplies/sunnyseed.png" /></a><br />
	<p>Sell 10 Components<br />25 coins</p>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
